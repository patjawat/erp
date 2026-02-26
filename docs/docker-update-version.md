# วิเคราะห์ระบบอัปเดตเวอร์ชัน ERP (Docker Hub)

## สถานะปัจจุบัน

### 1. เวอร์ชันในแอป
- **ที่มา**: `config/version.php` คืนค่า string เช่น `'v1.5.0'`
- **การใช้งาน**: ถูกโหลดใน `config/web.php` เป็น `Yii::$app->version` และแสดงใน theme (เช่น footer ใน `themes/v4/layouts/main.php`)
- **UpdateTableController**: ใช้สำหรับอัปเดต auth_item / route ใน DB ไม่ได้เกี่ยวกับเวอร์ชัน Docker โดยตรง

### 2. การ build และ push ขึ้น Docker Hub
- **CI**: `.github/workflows/docker-image.yml`
  - Trigger: push หรือ pull_request ไปที่ branch `main`
  - Build: `docker build . --file Dockerfile --tag patjawat/erp:latest`
  - Push: `docker push patjawat/erp:latest`
- **ผลลัพธ์**: image จะอยู่ที่ `patjawat/erp:latest` บน Docker Hub (ใช้แค่ tag `latest`)

### 3. การรัน production (ผู้ใช้ ERP)
- **ที่ตั้ง**: โฟลเดอร์ `run-production/`
- **docker-compose**: ใช้ตัวแปร `APP_IMAGE` (เช่น จาก `.env` หรือ `.env-nginx-example` ที่มี `APP_IMAGE=patjawat/erp:1.1`)
- **โวลุ่ม**: mount เฉพาะ `./source/fileupload` เข้า container (ข้อมูลอัปโหลดอยู่ข้างนอก container)
- **ฐานข้อมูล**: แยก service เป็น MySQL 8.0 อยู่ container อื่น

---

## วิธีให้ผู้ใช้ ERP อัปเดตเมื่อมี image ใหม่บน Docker Hub

เมื่อคุณ build image ขึ้น Docker Hub แล้ว ผู้ที่ใช้โปรแกรม ERP (รันด้วย Docker) สามารถอัปเดตได้ดังนี้

### วิธีที่ 1: Pull image ใหม่แล้ว restart (แนะนำสำหรับใช้ tag `latest`)

บนเครื่องที่รัน production (โฟลเดอร์ `run-production/`):

```bash
cd /path/to/erp/run-production

# 1) ดึง image ล่าสุดจาก Docker Hub
docker compose pull app

# 2) สร้าง container ใหม่จาก image ที่ดึงมา (ไม่ลบ volume ของ DB)
docker compose up -d app

# 3) (สำคัญ) รัน migration ถ้ามี migration ใหม่
docker compose exec app php yii migrate --interactive=0
```

ถ้าใช้ชื่อ service อื่นหรือไฟล์ compose อื่น (เช่น `docker-compose-nginx.yml`):

```bash
docker compose -f docker-compose-nginx.yml pull app
docker compose -f docker-compose-nginx.yml up -d app
docker compose -f docker-compose-nginx.yml exec app php yii migrate --interactive=0
```

### วิธีที่ 2: ใช้ image แบบระบุ tag เวอร์ชัน (เช่น v1.5.0)

ถ้าต้องการให้ผู้ใช้เลือกอัปเดตเป็นเวอร์ชันเฉพาะ (ไม่ดึงแค่ `latest`) ควรเพิ่มขั้นตอนใน CI ให้ push หลาย tag:

- ใน GitHub Actions: นอกจาก `patjawat/erp:latest` แล้ว ให้ tag และ push อีกครั้งเป็น `patjawat/erp:v1.5.0` (อ่านจาก `config/version.php` หรือตัวแปรใน repo)
- ผู้ใช้ตั้งค่าใน `.env`: `APP_IMAGE=patjawat/erp:v1.5.0` เมื่อต้องการอัปเดตก็เปลี่ยนเป็น `v1.6.0` แล้ว `docker compose pull app` และ `docker compose up -d app` ตามวิธีที่ 1

### วิธีที่ 3: สคริปต์อัปเดตสำหรับผู้ดูแลระบบ

สร้างสคริปต์ใน repo (เช่น `run-production/update.sh`) ให้ผู้ใช้รันคำสั่งเดียวแล้วทำครบ: backup (ถ้าต้องการ), pull, up, migrate.

ตัวอย่างโครงสคริปต์:

```bash
#!/bin/bash
# run-production/update.sh
set -e
cd "$(dirname "$0")"
echo "Pulling latest ERP image..."
docker compose pull app
echo "Recreating app container..."
docker compose up -d app
echo "Running migrations..."
docker compose exec -T app php yii migrate --interactive=0
echo "Done. App version: check footer in browser or run: docker compose exec app php -r \"require '/app/config/version.php';\""
```

ผู้ใช้รัน: `./update.sh` (หรือ `bash update.sh`)

---

## สิ่งที่ต้องระวังเมื่ออัปเดต

| หัวข้อ | รายละเอียด |
|--------|-------------|
| **Migration** | ทุกครั้งที่อัปเดต image ควรรัน `yii migrate --interactive=0` เพื่อให้ schema ตรงกับโค้ด |
| **Auth / route** | ถ้ามีการเพิ่ม route ใหม่ในแอป หลังอัปเดตอาจต้องรัน `yii update-table` (ตามที่ใช้อยู่แล้วใน `db/update-db.php`) |
| **Volume ข้อมูล** | `fileupload` อยู่ที่ host แล้ว ไม่หายเมื่อ pull image ใหม่ |
| **Database** | ข้อมูล MySQL อยู่ที่ volume ของ service mysqlDB ไม่ถูกลบเมื่ออัปเดตแค่ service `app` |
| **.env** | ต้องมี `APP_IMAGE=patjawat/erp:latest` (หรือ tag เวอร์ชันที่ต้องการ) และค่าต่อ DB ถูกต้อง |

---

## สรุปคำตอบ: มีวิธีให้ผู้ใช้ ERP อัปเดตได้อย่างไรเมื่อ build image ขึ้น Docker Hub

1. **ตั้งค่าให้ production ใช้ image จาก Docker Hub**  
   ใน `run-production/.env` ใส่ `APP_IMAGE=patjawat/erp:latest` (หรือ `patjawat/erp:v1.5.0` ถ้ามี tag เวอร์ชัน)

2. **เมื่อมี image ใหม่**  
   ผู้ดูแลระบบไปที่โฟลเดอร์ `run-production/` แล้วรัน:
   - `docker compose pull app`
   - `docker compose up -d app`
   - `docker compose exec app php yii migrate --interactive=0`

3. **ทางเลือกเพิ่ม**  
   - เพิ่ม tag เวอร์ชันใน CI (จาก `config/version.php`) แล้วให้ผู้ใช้เลือกใช้ tag นั้นใน `APP_IMAGE`  
   - สร้างสคริปต์ `update.sh` ตามวิธีที่ 3 เพื่อลดขั้นตอนและลดโอกาสลืมรัน migration

ถ้าต้องการให้ช่วยเขียนสคริปต์ `update.sh` จริงหรือแก้ GitHub Actions ให้ push tag เวอร์ชันอัตโนมัติ บอกได้เลยว่าจะให้ทำส่วนไหนก่อน
