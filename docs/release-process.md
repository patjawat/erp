# การปล่อยเวอร์ชัน ERP (ช่องทาง latest / stable)

## ช่องทาง

| ช่องทาง | image | ใครใช้ | ได้ของใหม่เมื่อไร |
|---|---|---|---|
| ทดสอบ | `patjawat/erp:latest` | รพ.ด่านซ้าย (tester) | ทุกครั้งที่ push `decha_dev` (Jenkins build + deploy อัตโนมัติ) |
| stable | `patjawat/erp:stable` | รพ. อื่นทั้งหมด | เมื่อกด Release ใน Jenkins |
| ตรึงเวอร์ชัน | `patjawat/erp:vX.Y.Z` | รพ. ที่ต้องถอยกลับ | ไม่เปลี่ยน |

แต่ละ รพ. เลือกช่องทางที่ `APP_IMAGE` ในไฟล์ `.env` แล้วอัปเดตด้วย `./update.sh`

## ขั้นตอนปล่อยเวอร์ชัน (เช่น ทุกวันศุกร์)

1. แก้ `config/version.php` เป็นเลขใหม่ (เช่น `v1.29.0`) และเพิ่มบล็อกใน `config/changelog.php` (คีย์ต้องตรงกัน)
2. commit + push `decha_dev` → Jenkins build `:latest` + deploy ที่ด่านซ้าย → ตรวจว่าใช้งานได้
3. Jenkins → job ERP → **Build with Parameters** → ติ๊ก `RELEASE` → Build
   - ไม่ build ใหม่: เอา image `:latest` ตัวที่ด่านซ้ายใช้อยู่มาติดป้าย `:vX.Y.Z` และ `:stable` แล้ว push
   - จะหยุดเองถ้า `:latest` ยังเป็นเลขเก่า (build ยังไม่เสร็จ) หรือเลขนี้เคยปล่อยแล้ว (ลืม bump)
   - แจ้ง Telegram "ปล่อยเวอร์ชัน vX.Y.Z เป็น stable แล้ว"
4. รพ. อื่นรัน `./update.sh` (หรือ cron ตี 3) ได้เวอร์ชันใหม่ หน้า `/settings/update` จะขึ้น "มีเวอร์ชันใหม่"

## หลักที่ต้องรักษา

- **ห้ามแก้ migration ที่ปล่อยไปแล้ว** ให้เขียน migration ใหม่ต่อท้าย เพราะ รพ. ข้ามหลายเวอร์ชันได้ และ `yii migrate` จะรันส่วนที่ค้างเรียงกัน
- ถ้าเวอร์ชันใหม่มีปัญหา: รพ. แก้ `.env` เป็น `APP_IMAGE=patjawat/erp:v<เวอร์ชันก่อน>` แล้วรัน `./update.sh` (ฐานข้อมูลที่ migrate ไปแล้วไม่ถอยอัตโนมัติ ใช้ไฟล์ใน `backups/` ถ้าจำเป็น)

## สคริปต์ `scripts/update.sh`

ฝังอยู่ใน image ที่ `/app/scripts/update.sh` ดึงครั้งแรกด้วย

```bash
docker run --rm --entrypoint cat patjawat/erp:stable /app/scripts/update.sh > update.sh && chmod +x update.sh
```

ทำ: สำรองฐาน (`backups/` เก็บ 7 ชุด) → `compose pull` → recreate เฉพาะ app → `yii migrate` → แสดงเวอร์ชันก่อน/หลัง และอัปเดตตัวสคริปต์จาก image ใหม่ให้เอง
ตัวเลือก: `-f <compose file>`, `-y` (ไม่ถาม ใช้กับ cron), `--no-backup`
