<p align="center">
    <h1 align="center">Office ERP</h1>
    <br>
</p>

Yii 2 Basic Project Template is a skeleton [Yii 2](http://www.yiiframework.com/) application best for
rapidly creating small projects.


REQUIREMENTS
------------

The minimum requirement by this project template that your Web server supports PHP 5.6.0.


INSTALLATION
------------

### Install via Composer
การติดตั้ง
~~~
1. copy file example_db.php to db.php
2. run คำสั่ง composer update --ignore-platform-reqs
~~~

Now you should be able to access the application through the following URL, assuming `basic` is the directory
directly under the Web root.

### Install with Docker

Update your vendor packages

    docker-compose run --rm php composer update --prefer-dist --ignore-platform-reqs
    
Run the installation triggers (creating cookie validation code)

    docker-compose run --rm php composer install --ignore-platform-reqs
    
Start the container

    docker-compose up -d
    
You can then access the application through the following URL:

    http://127.0.0.1:83

You can then access phpmyadmin:

    http://127.0.0.1:8500

**NOTES:** 
- Minimum required Docker engine version `17.04` for development (see [Performance tuning for volume mounts](https://docs.docker.com/docker-for-mac/osxfs-caching/))
- The default configuration uses a host-volume in your home directory `.docker-composer` for composer caches


CONFIGURATION
-------------

### Database

Edit the file `config/db.php` with real data, for example:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=erp',
    'username' => 'root',
    'password' => 'docker',
    'charset' => 'utf8',
];
```

## Migrate database
yii migrate
migrate Down step

yii migrate/down 1

**NOTES:**
- Yii won't create the database for you, this has to be done manually before you can access it.
- Check and edit the other files in the `config/` directory to customize your application as required.
- Refer to the README in the `tests` directory for information specific to basic application tests.



Docker Build Image
สร้าง File Image
docker build -t erp:v1 .

หากคุณต้องการส่งออก Docker image จากเครื่องหนึ่งไปใช้กับเครื่องอื่น มีขั้นตอนดังนี้

docker save -o <path-to-tar-file> <image-name>:<tag>

ตัวอย่าง: docker save -o yii2-app.tar yii2-app:latest

ตัวอย่างการใช้ scp ส่งไปยังเครื่องอื่น:
scp yii2-app.tar user@remote-server:/path/to/destination

Import Docker Image บนเครื่องใหม่
docker load -i yii2-app.tar

ตรวจสอบว่าการ Import สำเร็จหรือไม่
docker images

 รัน Docker Image บนเครื่องใหม่
 docker run -d -p 8080:80 yii2-app:latest

