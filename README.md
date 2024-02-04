#### Installation & Setup
##### Step 1: Download project

```sh
git clone git@gitlab.com:vnpost-team/laravel10.git folder_app
```
##### Step 2: Installing Composer
```sh
cd folder_app
# ubuntu
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/opt \
    -w /opt \
    laravelsail/php80-composer:latest \
    composer install --ignore-platform-reqs

# window
docker run --rm -v "C:\project\example-app:/var/www/html" -w /var/www/html laravelsail/php83-composer:latest composer install --ignore-platform-reqs
```

##### Step 3: Pre Setup
- `cp env.example .env`
##### Step 4: Run project
- `docker-composer up` - chạy dự án với docker
- `docker-composer down` - dừng dự án với docker
##### Step 5: Compile asset
- `docker exec -it php83_apache.web npm install`
- `docker exec -it php83_apache.web npm run prod`
##### Step 6: Run migrate
- `docker exec -it php83_apache.web php artisan migrate`
##### Step 7: Run seed
- `docker exec -it php83_apache.web php artisan db:seed`
