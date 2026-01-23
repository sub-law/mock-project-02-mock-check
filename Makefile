setup:
    docker-compose up -d --build
    docker-compose exec php composer install
    docker-compose exec php cp .env.example .env
    docker-compose exec php php artisan key:generate
    docker-compose exec php php artisan migrate
    docker-compose exec php php artisan db:seed

bash:
    docker-compose exec php bash

migrate:
    docker-compose exec php php artisan migrate

seed:
    docker-compose exec php php artisan db:seed

fresh:
    docker-compose exec php php artisan migrate:fresh --seed

clear:
    docker-compose exec php php artisan view:clear
    docker-compose exec php php artisan route:clear
    docker-compose exec php php artisan config:clear
    docker-compose exec php php artisan cache:clear
