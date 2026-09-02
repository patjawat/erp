# Version API

API นี้ใช้สำหรับอ่านเวอร์ชันของ ERP instance ที่กำลังตอบคำขอ เพื่อให้ระบบภายนอกนำค่าไปเปรียบเทียบและตรวจสอบการอัปเดต

## Endpoint

```http
GET /api/version
```

ไม่ต้องเข้าสู่ระบบ และรองรับการเรียกข้าม origin สำหรับ `GET`, `HEAD` และ CORS preflight (`OPTIONS`)

ตัวอย่างคำขอ:

```bash
curl --fail --silent --show-error https://erp.example.go.th/api/version
```

ตัวอย่างผลลัพธ์:

```json
{
  "schema_version": 1,
  "version": "1.25.0",
  "display_version": "v1.25.0"
}
```

- `schema_version` คือเวอร์ชันของโครงสร้าง response
- `version` คือเวอร์ชันรูปแบบ Semantic Version สำหรับนำไปเปรียบเทียบ
- `display_version` คือข้อความเวอร์ชันตามที่ ERP ใช้แสดงผล

ค่าเวอร์ชันทั้งหมดอ่านจาก `config/version.php` ผ่าน `Yii::$app->version` จึงไม่ต้องกำหนดเวอร์ชันซ้ำใน controller

## การตรวจสอบอัปเดต

ระบบภายนอกควรเปรียบเทียบ `version` ด้วยไลบรารี Semantic Version ของภาษาที่ใช้งาน ไม่ควรเปรียบเทียบเป็นข้อความธรรมดา เพราะตัวอย่างเช่น `1.10.0` ใหม่กว่า `1.9.0`

API ส่ง `Cache-Control: no-store` เพื่อป้องกัน proxy หรือ browser ส่งค่าเวอร์ชันเก่าหลัง deploy รุ่นใหม่
