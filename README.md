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
