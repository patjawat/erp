# Step 1: เลือก PHP พร้อม Apache เป็น base image
FROM yiisoftware/yii2-php:8.2-apache

# ติดตั้ง dependencies ที่จำเป็น
RUN apt-get update && apt-get install -y \
    libssl-dev \
    pkg-config \
    git \
    ghostscript \
    tar gzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# เปิดใช้งาน Calendar extension
RUN docker-php-ext-install calendar

# ติดตั้ง Redis extension
# RUN pecl install redis \
#     && docker-php-ext-enable redis


# ติดตั้ง Swoole
# RUN pecl install  swoole-5.0.3
# RUN docker-php-ext-enable swoole

# Step 2: กำหนด working directory ของโปรเจค
WORKDIR /app
RUN apt update && apt install -y nano default-mysql-client

# Some packages (notably google/apiclient-services) contain many files and can
# take longer than Composer's default process timeout to extract in Docker.
# Keep Composer downloads in a BuildKit cache so a transient network failure
# does not force the next attempt/build to download every package again.
ENV COMPOSER_PROCESS_TIMEOUT=0 \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    COMPOSER_MAX_PARALLEL_HTTP=4

# Step 3: ติดตั้ง dependencies ก่อน copy source ทั้งหมด
# copy เฉพาะ composer.json/composer.lock ก่อน แล้วโหลด deps — layer นี้จะถูก cache
# ไว้ตราบใดที่ 2 ไฟล์นี้ไม่เปลี่ยน (แก้โค้ด .php ทั่วไปจะไม่ทำให้ต้องโหลด deps ใหม่)
# ใช้ --no-scripts --no-autoloader เพราะ post-install script ต้องใช้ไฟล์ config
# ที่ยังไม่ได้ copy เข้ามาในสเต็ปนี้
COPY composer.json composer.lock /app/
RUN --mount=type=cache,target=/tmp/composer-cache,sharing=locked \
    set -eu; \
    attempt=1; \
    while true; do \
        set +e; \
        composer install --ignore-platform-reqs --prefer-dist --no-interaction --no-progress \
            --no-scripts --no-autoloader; \
        status=$?; \
        set -e; \
        if [ "$status" -eq 0 ]; then \
            break; \
        fi; \
        if [ "$status" -ne 100 ] || [ "$attempt" -ge 3 ]; then \
            exit "$status"; \
        fi; \
        delay=$((attempt * 15)); \
        echo "Composer transport failure (exit 100); retrying in ${delay}s..." >&2; \
        sleep "$delay"; \
        attempt=$((attempt + 1)); \
    done

# Step 4: Copy source ที่เหลือ แล้วรัน composer อีกครั้งเพื่อ gen autoloader + scripts
# ตอนนี้ deps อยู่ใน vendor แล้ว (vendor/ อยู่ใน .dockerignore จึงไม่ถูกทับ) สเต็ปนี้
# จะไม่ download อะไรใหม่ ทำแค่ dump-autoload + post-install scripts จึงเร็ว
COPY ./ /app/
RUN composer install --ignore-platform-reqs --prefer-dist --no-interaction --no-progress
# RUN composer install --prefer-dist --no-dev --optimize-autoloader


# Patch ปัญหา yii\base\Object -> yii\base\BaseObject
RUN find /app/vendor/asyou99/yii2-cart -type f -name "*.php" -exec sed -i 's/yii\\base\\Object/yii\\base\\BaseObject/g' {} +

# ลบ  Cache Asset ออก
RUN rm -rf /app/web/assets/*

# ✅ เพิ่มขั้นตอนคัดลอกฟอนต์เข้าไปใน FPDF
COPY web/fonts/THSarabunNew* /app/vendor/setasign/fpdf/font/


# Step 5: ตั้งค่าให้โฟลเดอร์ runtime และ web/assets สามารถเขียนได้
RUN mkdir -p /app/web/assets /app/web/downloads /app/web/msword/results/leave /app/web/msword/results/development /app/web/import-csv /app/runtime/cache /app/runtime/backup && \
    chmod -R 777 /app/runtime /app/runtime/backup /app/runtime/cache /app/web/assets /app/web/import-csv /app/web/downloads /app/web/msword /app/web/msword/results && \
    chown -R www-data:www-data /app/modules/filemanager && \
    chown -R www-data:www-data /app/web/msword

RUN mkdir -p \
    /app/runtime/cache \
    /app/runtime/backup \
    /app/vendor/mpdf/mpdf/tmp/mpdf && \
    chmod -R 775 /app/vendor/mpdf/mpdf/tmp && \
    chown -R www-data:www-data /app/vendor/mpdf/mpdf/tmp

# Set YII_ENV to 'pro'
RUN sed -i "s/defined('YII_ENV') or define('YII_ENV', 'dev');/defined('YII_ENV') or define('YII_ENV', 'pro');/" /app/web/index.php

# Set memory_limit to 2048M
RUN echo "memory_limit = 2048M" > /usr/local/etc/php/conf.d/memory-limit.ini

# PHP input/post limits — กันฟอร์มหลายแถว (เช่น receive/create > 160 รายการ) ถูก PHP ตัด POST เงียบ ๆ เมื่อเกิน max_input_vars (default 1000)
RUN { \
        echo "max_input_vars = 50000"; \
        echo "post_max_size = 64M"; \
        echo "upload_max_filesize = 64M"; \
    } > /usr/local/etc/php/conf.d/php-input-limits.ini

# Step 6: เปิดพอร์ต 80 สำหรับ HTTP
EXPOSE 80

# Step 7: เริ่มต้นเซิร์ฟเวอร์ Apache
CMD ["apache2-foreground"]

# Set the system timezone
ENV TZ=Asia/Bangkok

# Set PHP timezone configuration
RUN echo "date.timezone=Asia/Bangkok" > /usr/local/etc/php/conf.d/timezone.ini
