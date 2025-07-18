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

 # การนำเข้าข้อมูลหนังสือสารบรรณ
yii import-document
yii import-document/upload-file
yii import-leave
yii import-meeting
yii import-vehicle


yii import-leave && yii import-document && yii import-meeting && yii import-vehicle && yii import-development



https://www.canva.com/ai/code/thread/a1329017-9e7f-4b54-a2b5-dd10c5acf3bd
https://www.canva.com/ai/code/thread/f82e0038-8164-4102-82d3-085d4d193cd0


ออกแบบระบบการแจ้งซ่อมสำหรับ user ฟอมการแจ้งซ่อม ระบุอาการเสีย/ความต้องการ หน่วยงานผู้แจ้ง 
งานซ่อมบำรุง
สถานที่อื่นๆ  ความเร่งด่วน เบอร์โทร เพิ่มเติม เลือกช่างที่ต้องการ ซ่อมทั่วไป ซ่อมครุภัณฑ์  สามารถเลือกส่ง งานซ่อมบำรุง ศูนย์คอมพิวเตอร์ ศูนย์เครื่องมือแพทย์ สามารถติดตามงานซ่อม เมื่อเสร็จแล้วให้คะแนนได้ bootstrap5 jquery  apexcharts สามารถทำงานได้ทุกเมนู


ออกแบบระบบการแจ้งซ่อมสำหรับ user ฟอมการแจ้งซ่อม ระบุอาการเสีย/ความต้องการ หน่วยงานผู้แจ้ง 
งานซ่อมบำรุง
สถานที่อื่นๆ  ความเร่งด่วน เบอร์โทร เพิ่มเติม เลือกช่างที่ต้องการ ซ่อมทั่วไป ซ่อมครุภัณฑ์  สามารถเลือกส่ง งานซ่อมบำรุง ศูนย์คอมพิวเตอร์ ศูนย์เครื่องมือแพทย์ สามารถติดตามงานซ่อม เมื่อเสร็จแล้วให้คะแนนได้ สามารถทำงานได้ทุกเมนู แยกการดูแลงานซ่อมของแต่ละงาน bootstrap5 jquery  apexcharts 

### ภาพรวมทรัพยากรบุคคล
https://dansaihospital.my.canva.site/hr-dashboard-web-app-development-summary

### ระบบติดตามอาชีวอนามัยและความปลอดภัย
https://dansaihospital.my.canva.site/dagnh8wv-us

### profile
https://www.canva.com/ai/code/thread/62a18386-9c98-4c12-b680-bd113446d996

### ห้องปชุม
https://www.canva.com/ai/code/thread/e120d73e-48a8-4022-a500-b613578ebfad

### ระบบบริหารงานซ่อมบำรุง
https://dansaihospital.my.canva.site/bootstrap
https://gemini.google.com/share/7236f4657713

### ระบบบันทึกทะเบียนครุภัณฑ
https://www.canva.com/ai/code/thread/ea327072-db24-488e-a38b-a838d74ec8e1
https://www.canva.com/ai/code/thread/ba6b2ae4-bc5b-443a-8ed2-7c92798ae56a



## ขั้นตอนการ  update ระบบทรัพย์สินใหม่
-- Update sturgture 
  ALTER TABLE `asset` ADD `asset_name` VARCHAR(255) NULL COMMENT 'ชื่อของครุภัณฑ์' AFTER `asset_group`;
  ALTER TABLE `categorise` CHANGE `title` `title` TEXT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT 'ชื่อ';
-- Update ชื่อ

-- สิ่งที่ต้องทำ
ALTER TABLE `asset` ADD `fsn_number` VARCHAR(255) NULL COMMENT 'รหัสครุภัณฑ์' AFTER `code`;
UPDATE asset a
LEFT JOIN categorise i ON i.code = a.asset_item
SET a.asset_name = i.title
WHERE i.name = 'asset_item';

-- UPDATE `asset`  SET fsn_number = asset_item;


-- ลบ asset_group,asset_type เดิมออก 
DELETE FROM `categorise` WHERE name = 'asset_group';
DELETE FROM `categorise` WHERE `category_id` ='3' AND `name`='asset_type';

UPDATE asset 
SET data_json = JSON_SET(data_json, '$.old_fsn', code);

INSERT INTO categorise (name, title, code, data_json) VALUES
                ('asset_group','ที่ดิน', 'LAND', JSON_OBJECT('description', 'ที่ดินและสิทธิในที่ดิน')),
                ('asset_group','อาคาร', 'BLDG', JSON_OBJECT('description', 'อาคารและสิ่งปลูกสร้างขนาดใหญ่')),
                ('asset_group','สิ่งปลูกสร้าง', 'CONST', JSON_OBJECT('description', 'โครงสร้างพื้นฐานและสาธารณูปโภค')),
                ('asset_group','ครุภัณฑ์', 'EQUIP', JSON_OBJECT('description', 'อุปกรณ์และเครื่องมือต่างๆ')),
                ('asset_group','ครุภัณฑ์ต่ำกว่าเกณฑ์', 'MINOR', JSON_OBJECT('description', 'ครุภัณฑ์มูลค่าต่ำ')),
                ('asset_group','สินทรัพย์ไม่มีตัวตน', 'INTAN', JSON_OBJECT('description', 'ลิขสิทธิ์ สิทธิบัตร ซอฟต์แวร์')),
                ('asset_group','วัสดุ', 'MATER', JSON_OBJECT('description', 'วัสดุสิ้นเปลืองและคงคลัง'));

INSERT INTO categorise 
        (name,group_id, title, code, data_json) 
        VALUES
        ('asset_type','EQUIP','ครุภัณฑ์การแพทย์', 'MED', JSON_OBJECT('title_en', 'Medical Equipment', 'description','อุปกรณ์ทางการแพทย์และเครื่องมือรักษาพยาบาล')),
        ('asset_type','EQUIP','ครุภัณฑ์ไฟฟ้าและวิทยุ', 'ELE', JSON_OBJECT('title_en','Electrical and Radio Equipment', 'description', 'อุปกรณ์ไฟฟ้าและเครื่องมือวิทยุกสารสนเทศ')),
        ('asset_type','EQUIP','ครุภัณฑ์โรงงาน', 'IND', JSON_OBJECT('title_en','Industrial Equipment', 'description', 'เครื่องจักรและอุปกรณ์ในงานโรงงาน การผลิต')),
        ('asset_type','EQUIP','ครุภัณฑ์การเกษตร', 'AGR', JSON_OBJECT('title_en','Agricultural Equipment', 'description', 'เครื่องมือและอุปกรณ์ทางการเกษตร')),
        ('asset_type','EQUIP','ครุภัณฑ์การศึกษา', 'EDU', JSON_OBJECT('title_en','Educational Equipment', 'description', 'อุปกรณ์การเรียนการสอนและวัสดุการศึกษา')),
        ('asset_type','EQUIP','ครุภัณฑ์คอมพิวเตอร์', 'COM', JSON_OBJECT('title_en','Computer Equipment', 'description', 'เครื่องคอมพิวเตอร์และอุปกรณ์เทคโนโลยีสารสนเทศ')),
        ('asset_type','EQUIP','ครุภัณฑ์โฆษณาและเผยแพร่', 'ADV', JSON_OBJECT('title_en','Advertising and Publishing Equipment', 'description', 'อุปกรณ์โฆษณา ประชาสัมพันธ์ และเผยแพร่ข้อมูล')),
        ('asset_type','EQUIP','ครุภัณฑ์งานบ้านงานครัว', 'HOM', JSON_OBJECT('title_en','Household and Kitchen Equipment', 'description', 'อุปกรณ์ใช้ในบ้านและครัว สำหรับงานทั่วไป')),
        ('asset_type','EQUIP','ครุภัณฑ์ยานพาหนะ', 'VEH', JSON_OBJECT('title_en','Vehicle Equipment', 'description', 'ยานพาหนะและอุปกรณ์การขนส่ง')),
        ('asset_type','EQUIP','ครุภัณฑ์วิทยาศาสตร์', 'SCI', JSON_OBJECT('title_en','Scientific Equipment', 'description', 'เครื่องมือและอุปกรณ์ทางวิทยาศาสตร์และการวิจัย')),
        ('asset_type','EQUIP','ครุภัณฑ์สำนักงาน', 'OFF', JSON_OBJECT('title_en','Office Equipment', 'description', 'อุปกรณ์สำนักงานและเครื่องใช้ในการบริหารงาน'));

        INSERT INTO categorise (name, category_id, title, code, data_json) VALUES
        ('asset_category','MED','กระตุกไฟฟ้าหัวใจ','DF',JSON_OBJECT('title_en', 'Defibrillation')),
        ('asset_category','MED','กล้องจุลทรรศน์ในการผ่าตัด','MC',JSON_OBJECT('title_en', 'Microscopy')),
        ('asset_category','MED','กล้องส่องตรวจวินิจฉัยและรักษา','ES',JSON_OBJECT('title_en', 'Endoscopic examination')),
        ('asset_category','MED','กายภาพบำบัด','PT',JSON_OBJECT('title_en', 'Physical Therapy')),
        ('asset_category','MED','คลังเลือด','BB',JSON_OBJECT('title_en', 'Blood Bank')),
        ('asset_category','MED','ควบคุมการให้สารน้ำ','IP',JSON_OBJECT('title_en', 'Infusion pump')),
        ('asset_category','MED','โคมไฟผ่าตัด','LO',JSON_OBJECT('title_en', 'Lamp operation')),
        ('asset_category','MED','จักษุ','EM',JSON_OBJECT('title_en', 'Eye medical')),
        ('asset_category','MED','จ่ายกลาง','CSSD',JSON_OBJECT('title_en', 'Central Sterile Supply Department')),
        ('asset_category','MED','จี้ห้ามเลือดและตัดเนื้อเยื่อ','CE',JSON_OBJECT('title_en', 'Cauterization equipment')),
        ('asset_category','MED','ช่วยหายใจ','RS',JSON_OBJECT('title_en', 'Respiration')),
        ('asset_category','MED','ชันสูตร','LAB',JSON_OBJECT('title_en', 'Laboratory')),
        ('asset_category','MED','ตรวจทารกในครรภ์','FT',JSON_OBJECT('title_en', 'Fetus')),
        ('asset_category','MED','ตรวจรักษาหัวใจและปอด','HL',JSON_OBJECT('title_en', 'Heart Lung')),
        ('asset_category','MED','ตรวจวินิจฉัยและรักษาสมอง','NE',JSON_OBJECT('title_en', 'Neuro equipment')),
        ('asset_category','MED','ติดตามการทำงานของหัวใจและสัญญาณชีพ','ME',JSON_OBJECT('title_en', 'Monitor equipment')),
        ('asset_category','MED','เตียงผ่าตัด-คลอด','OB',JSON_OBJECT('title_en', 'Operation Bed')),
        ('asset_category','MED','เตียงผู้ป่วย','BP',JSON_OBJECT('title_en', 'Bed patient')),
        ('asset_category','MED','ไตเทียม','CKD',JSON_OBJECT('title_en', 'Chronic Kidney Disease')),
        ('asset_category','MED','ทันตกรรม','DE',JSON_OBJECT('title_en', 'Dental equipment')),
        ('asset_category','MED','ทารกแรกคลอด','NB',JSON_OBJECT('title_en', 'New born')),
        ('asset_category','MED','ผ่าตัด','OE',JSON_OBJECT('title_en', 'Operation equipment')),
        ('asset_category','MED','เภสัชกรรม','PHR',JSON_OBJECT('title_en', 'Pharmacy')),
        ('asset_category','MED','รังสีรักษา','RT',JSON_OBJECT('title_en', 'Radio therapy')),
        ('asset_category','MED','วิสัญญี','AE',JSON_OBJECT('title_en', 'Anesthesia equipment')),
        ('asset_category','MED','ศัลยกรรมออร์โธปิดิกส์','ORT',JSON_OBJECT('title_en', 'Orthopedic')),
        ('asset_category','MED','ศัลยศาสตร์ทางเดินปัสสาวะ','URO',JSON_OBJECT('title_en', 'Urology')),
        ('asset_category','MED','สนับสนุนการแพทย์','MP',JSON_OBJECT('title_en', 'Medical Support')),
        ('asset_category','MED','หู คอ จมูก','ENT',JSON_OBJECT('title_en', 'Ear Nose Throat')),
        ('asset_category','MED','อัลตราซาวด์','US',JSON_OBJECT('title_en', 'Ultrasound')),
        ('asset_category','MED','เอกซเรย์','XR',JSON_OBJECT('title_en', 'Xray')),
        ('asset_category','ELE','ไฟฟ้าและวิทยุ','EE',JSON_OBJECT('title_en', 'Electric equipment')),
        ('asset_category','IND','ช่างซ่อมบำรุง','HT',JSON_OBJECT('title_en', 'Hand Tool')),
        ('asset_category','AGR','การเกษตร','AC',JSON_OBJECT('title_en', 'Agriculture')),
        ('asset_category','EDU','การศึกษา','ED',JSON_OBJECT('title_en', 'Education')),
        ('asset_category','COM','กล้องโทรทัศน์วงจรปิด','CCTV',JSON_OBJECT('title_en', 'Close Circuit Television')),
        ('asset_category','COM','คอมพิวเตอร์และอุปกรณ์','COM',JSON_OBJECT('title_en', 'Computer')),
        ('asset_category','ADV','โฆษณาและเผยแพร่','AP',JSON_OBJECT('title_en', 'Advertise and publish')),
        ('asset_category','HOM','งานบ้านงานครัว','HK',JSON_OBJECT('title_en', 'Housework kitchen work')),
        ('asset_category','VEH','ยานพาหนะบริการทางการแพทย์','VM',JSON_OBJECT('title_en', 'Vehicle medical')),
        ('asset_category','VEH','ยานพาหนะและขนส่ง','VT',JSON_OBJECT('title_en', 'Vehicle transport')),
        ('asset_category','SCI','วิทยาศาสตร์','SC',JSON_OBJECT('title_en', 'Science')),
        ('asset_category','OFF','สำนักงาน','OFF',JSON_OBJECT('title_en', 'Office')),
        ('asset_category','OFF','เครื่องปรับอากาศและฟอกอากาศ','AIR',JSON_OBJECT('title_en', 'Air condition'));
