### Requirements

```
docker
composer

php 8.2+/nginx or use Laravel Herd
```

### CHAPI setup

**Get into your host root folder or into your Herd directory.**

```
git clone https://github.com/df-line/chapi.git
cd chapi
composer install
composer setup
```

**composer setup** is a custom script. Essentially it 

- copies over a .env file (customize if something goes south)
- regenerates the project key
- pulls the required docker images (**mariadb** and **mailpit**) and runs them
- runs the migrations (this requires the above mariadb docker image or an equivalent db)

Mail handling by default goes to mailpit, which is accessible at:

```
http://127.0.0.1:8025/
```

Have fun!
