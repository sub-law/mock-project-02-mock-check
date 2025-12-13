# Docker ビルド
build:
	docker-compose up -d --build

# PHPコンテナに入る
php:
	docker-compose exec php bash

# Laravel 初期設定
init:
	docker-compose exec php bash -c "\
        composer install && \
        cp .env.example .env && \
		php artisan key:generate \
    "

clear:
	docker-compose exec php bash -c "\
		php artisan view:clear && \
        php artisan route:clear && \
        php artisan config:clear && \
        php artisan cache:clear \
    "
