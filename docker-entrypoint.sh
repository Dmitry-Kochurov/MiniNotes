#!/bin/sh
set -e

# 1. Если файла .env еще нет, создаем его из примера
if [ ! -f .env ]; then
    echo "Создание файла .env..."
    cp .env.example .env
fi

# 2. Если папка vendor пуста, автоматически скачиваем зависимости ядра
if [ ! -d vendor ] || [ -z "$(ls -A vendor)" ]; then
    echo "Первый запуск: скачивание зависимостей Laravel (vendor)..."
    composer install --ignore-platform-reqs --no-interaction --quiet
fi

# 3. Генерируем ключ безопасности фреймворка
echo "Генерация ключа приложения..."
php artisan key:generate --force

# 4. Автоматически создаем базу данных SQLite, если её нет
if [ ! -f database/database.sqlite ]; then
    echo "Создание базы данных SQLite..."
    touch database/database.sqlite
fi

# 5. Накатываем миграции таблиц заметок
echo "Применение миграций базы данных..."
php artisan migrate --force

# 6. Запускаем встроенный веб-сервер Laravel на порту 80
echo "Запуск веб-сервера заметок..."
exec php artisan serve --host=0.0.0.0 --port=80
