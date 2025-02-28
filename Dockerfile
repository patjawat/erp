# Step 1: เลือก PHP พร้อม Apache เป็น base image
FROM yiisoftware/yii2-php:8.2-apache

# ติดตั้ง dependencies ที่จำเป็น
RUN apt-get update && apt-get install -y \
    libssl-dev \
    pkg-config \
    git \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

    # เปิดใช้งาน Opcache
RUN docker-php-ext-install opcache

# ติดตั้ง Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# ตั้งค่า OPcache ใน php.ini
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini && \
    echo "opcache.enable_cli=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini && \
    echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini && \
    echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini && \
    echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini && \
    echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# ติดตั้ง Swoole
# RUN pecl install  swoole-5.0.3
# RUN docker-php-ext-enable swoole

# Step 2: กำหนด working directory ของโปรเจค
WORKDIR /app
RUN apt update && apt install -y nano
# Step 3: Copy ไฟล์ที่จำเป็นไปยัง image
COPY ./ /app/

# Step 4: ติดตั้ง dependencies ผ่าน composer

# RUN composer install --ignore-platform-reqs
# RUN composer install --prefer-dist --no-dev --optimize-autoloader


# ลบ  Cache Asset ออก
RUN rm -rf /app/web/assets/*

# Step 5: ตั้งค่าให้โฟลเดอร์ runtime และ web/assets สามารถเขียนได้
RUN mkdir -p /app/web/downloads /app/web/msword /app/web/import-csv /app/web/msword/results /app/runtime/cache && \
    chmod -R 777 /app/runtime /app/runtime/cache /app/web/assets /app/web/import-csv /app/web/downloads /app/web/msword /app/web/msword/results && \
    chown -R www-data:www-data /app/modules/filemanager && \
    chown -R www-data:www-data /app/web/msword



# Set YII_ENV to 'pro'
RUN sed -i "s/defined('YII_ENV') or define('YII_ENV', 'dev');/defined('YII_ENV') or define('YII_ENV', 'pro');/" /app/web/index.php

# Set memory_limit to 2048M
RUN echo "memory_limit = 2048M" > /usr/local/etc/php/conf.d/memory-limit.ini

# Step 6: เปิดพอร์ต 80 สำหรับ HTTP
EXPOSE 80

# Step 7: เริ่มต้นเซิร์ฟเวอร์ Apache
CMD ["apache2-foreground"]
