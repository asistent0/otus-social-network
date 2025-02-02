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

В приложении доступны 3 урл:
   - /user/register
   - /login
   - /user/get/{id}

Коллекция Postman находится в файле [SocialNetwork.postman_collection.json](SocialNetwork.postman_collection.json)

## Загрузка пользователей

   ```shell
   docker exec -ti social_network_php php bin/console doctrine:fixtures:load --append --no-debug
   ```
