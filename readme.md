#Labscape

### Laravel 5.6 + Angular 5.2.0(with Angular cli)

### Installation

```
composer install
npm install
bower install
php artisan key:generate
```

Create `.env` file (can be based on `.env.example`)

Specify the `DEFUALT_COUNTRY` within the `.env` file in order to enable app features, that are dependant on country
At the moment available countries are: `Switzerland`, `Russia` 

Set DB credentials within .env file

Run migrations

```
php artisan migrate
```

Run seeder
```
php artisan db:seed
```

Create storage link

```
php artisan storage:link
```


### Dev Build
to compile the project once, run:
```
npm run dev
```

to run porject watcher, run:
```
npm run watch-poll
```

### Production Build

go to project url in browser or run the command:

```
ng build
```

### Structure

Angular5 files are stored in  **/resources/assets/src folder**


###E-Mailing

Currently emailing is used for:
- reset password

for dev purpose please set-up appropriate smtp.mailtrap.io settings in .env file

###.env file

the following additional fields are obligate to be filled out within .env file:
- APP_URL
- MAIL_DRIVER
- MAIL_HOST
- MAIL_PORT
- MAIL_USERNAME
- MAIL_PASSWORD
- MAIL_ENCRYPTION


###Login to the app

To login to the app use the following creds:

```
admin@gmail.com
admin
```