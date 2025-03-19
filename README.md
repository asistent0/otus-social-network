# Инструкция по установке и запуску приложения

## Установка и запуск

1. Убедитесь, что у вас установлен Docker и Docker Compose.
2. Клонируйте репозиторий на ваш компьютер:
3. Перейдите в каталог с репозиторием:
4. Соберите и запустите контейнеры Docker с помощью Docker Compose:

    ```shell
    docker-compose up -d
    ```

5. После успешного запуска контейнеров, откройте файл в зависимости от вашей операционной системы:

   - для Windows: `C:\WINDOWS\System32\drivers\etc\host`.
   - для Linux и macOS: `/etc/hosts`.

6. Добавьте строку с доменом в конец файла:

    ```
    127.0.0.1 social-network.local
    ```

7. Сохраните изменения и закройте редактор.

## Доступ к приложению

После завершения всех предыдущих шагов, вы можете получить доступ к вашему приложению,
перейдя по следующему URL: http://social-network.local

В приложении доступны урл:
- /user/register
- /login
- /user/get/{id}
- /user/search/?first_name=Ек&last_name=Пе

Коллекция Postman находится в файле [SocialNetwork.postman_collection.json](SocialNetwork.postman_collection.json)

## Загрузка пользователей

```shell
docker exec -ti social_network_php php bin/console doctrine:fixtures:load --append --no-debug --group=UserFixtures
```

# Тест производительности и настройки индексов

В папке JMeter находятся все отчеты и настройки.

# Тест производительности и репликации

В папке JMeter2 находятся все отчеты и настройки.

## Загрузка постов

```shell
docker exec -ti social_network_php php bin/console doctrine:fixtures:load --append --no-debug --group=PostFixtures
```
```shell
docker exec -ti social_network_php php bin/console doctrine:fixtures:load --append --no-debug --group=FriendFixtures
```

Добавлены новые доступы. Все доступны через авторизацию:
- /friend/set/{user_id}
- /friend/delete/{user_id}
- /friend/list
- /post/feed
- /post/create
- /post/update
- /post/get/{id}
- /post/delete/{id}

Коллекция Postman обновлена.

## Запуск очереди

Запуск очереди можно поставить на супервизорд.

```shell
docker exec -ti social_network_php php bin/console messenger:consume async -vv
```

## Перестройка кеша

Команду можно поставить на отработку в ночное время по крону.
В зависимости от количества пользователей и постов,
можно оптимизировать на постепенный запуск через супервизорд.

```shell
docker exec -ti social_network_php php bin/console app:rebuild-feeds -vv
```

## Загрузка диалогов

```shell
docker exec -ti social_network_php php bin/console doctrine:fixtures:load --append --no-debug --group=DialogFixtures
```

Добавлены новые доступы. Все доступны через авторизацию:
- /dialog/{user_id}/send
- /dialog/{user_id}/list

Коллекция Postman обновлена.

## Шардирование

Сделал бекап старой базы. Остановил старую базу и поднял новую базу через Citus.

### Подключение к координатору

```shell
docker exec -it citus_coordinator psql -U postgres -d social_network
```

### Определение координатора и добавление нод:

```postgresql
SELECT citus_set_coordinator_host('citus-coordinator', 5432);
SELECT * FROM citus_add_node('citus-worker1', 5432);
SELECT * FROM citus_add_node('citus-worker2', 5432);
```

### Восстановление БД

Восстанавливаем бекап в новой базе.

Выполняем миграции.

Было сделано шардирование с колокацией таблиц `dialog` и `message`.
Для обеих таблиц выбран ключ шардирования: `participant1_id` (ID инициатора диалога).
Потому что большинство запросов фильтруются по участнику диалога.
Гарантирует колокацию связанных данных (диалоги и сообщения хранятся на одних шардах).

## Проведение решардинга

### Добавление новых нод

```postgresql
SELECT citus_add_node('new-worker-1', 5432);
SELECT citus_add_node('new-worker-2', 5432);
```

### Запуск решардинга

```postgresql
SELECT citus_rebalance_start(
    shard_transfer_mode := 'block_writes',
    max_shard_moves := 100,
    excluded_shard_list := '{}'
);
```

### Мониторинг

```postgresql
-- Статус ребалансировки
SELECT * FROM citus_rebalance_status();

-- Прогресс перемещения шардов
SELECT 
    shard_id, 
    source_node_name, 
    target_node_name, 
    progress 
FROM citus_shard_moves;
```

### Откат (при необходимости)

```postgresql
-- Отмена перемещения шардов
SELECT citus_rebalance_stop();

-- Возврат к предыдущей конфигурации
SELECT undo_rebalance();
```

# Добавление вебсокетов и очередей

## Клиентская подписка на вебсокеты

```js
const eventSource = new EventSource('/.well-known/mercure?topic=' + 
  encodeURIComponent('/post/feed/posted'));
eventSource.onmessage = e => {
    const post = JSON.parse(e.data);
    prependPostToFeed(post);
};
```

Или через консоль

```shell
curl -v -N http://social-network.local:3000/.well-known/mercure?topic=/post/feed/posted
```

## Отправка сообщения в вебсокеты

Можно отправить сообщение по адресу `/post/create`, или для примера можно отправить через консоль

```shell
$ curl -X POST http://social-network.local:3000/.well-known/mercure -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InB1Ymxpc2giOlsiKiJdLCJzdWJzY3JpYmUiOlsiKiJdfX0.sHiaDWs_SoR0wLy2TaEZwQFmZLH-tOBpEMePyPDusao" -d "topic=/post/feed/posted&data=TEST"
```

## Очереди

Добавлены очереди для добавления новых постов в отдельную таблицу для друзей и для добавления новых друзей.

Добавлен supervisord для отслеживания очередей.

Добавлена таблица со связью постов для друзей.

При добавлении новых друзей, отправляется сообщение в очередь
для формирования новых связей постов с друзьями.

При добавлении новых потов, отправляется сообщение в очередь
для добавления этого постав всем друзьям.
