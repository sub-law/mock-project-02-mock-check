setup:
	docker-compose up -d --build
	sleep 15
	docker-compose exec php composer install
	@if [ ! -f .env ]; then docker-compose exec php cp .env.example .env; fi
	docker-compose exec php php artisan key:generate
	docker-compose exec php php artisan migrate --seed
	docker-compose exec php php artisan storage:link

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

up:
	docker-compose up -d

down:
	docker-compose down --remove-orphans

restart:
	make down
	make up
