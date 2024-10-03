# Step 1: เลือก PHP พร้อม Apache เป็น base image
FROM yiisoftware/yii2-php:8.2-apache

# Step 2: กำหนด working directory ของโปรเจค
WORKDIR /app

# Step 3: Copy ไฟล์ที่จำเป็นไปยัง image
COPY ./ /app/

# Step 4: ติดตั้ง dependencies ผ่าน composer
RUN composer install --prefer-dist --no-dev --optimize-autoloader
RUN composer install --ignore-platform-reqs


# ลบ  Cache Asset ออก
RUN rm -rf /app/web/assets/*

# Step 5: ตั้งค่าให้โฟลเดอร์ runtime และ web/assets สามารถเขียนได้
RUN mkdir /app/web/downloads
RUN chmod -R 777 /app/runtime  /app/web/assets /app/web/downloads /app/web/msword/results app/modules/filemanager/fileupload

# Set YII_ENV to 'pro'
RUN sed -i "s/defined('YII_ENV') or define('YII_ENV', 'dev');/defined('YII_ENV') or define('YII_ENV', 'pro');/" /app/web/index.php

# Set memory_limit to 2048M
RUN echo "memory_limit = 2048M" > /usr/local/etc/php/conf.d/memory-limit.ini

# Step 6: เปิดพอร์ต 80 สำหรับ HTTP
EXPOSE 80

# Step 7: เริ่มต้นเซิร์ฟเวอร์ Apache
CMD ["apache2-foreground"]
