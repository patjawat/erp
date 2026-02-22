# DMS สรุป PDF ด้วย Ollama (AI)

## วิธีใช้งาน

1. **รัน Ollama ใน Docker**
   ```bash
   docker compose up -d ollama
   ```

2. **โหลดโมเดล (ครั้งแรกเท่านั้น)**
   ```bash
   docker compose exec ollama ollama pull llama3.2
   ```
   หรือใช้โมเดลอื่น เช่น `mistral`, `gemma2:2b` แล้วตั้งใน `.env`: `OLLAMA_MODEL=mistral`

3. **ติดตั้ง dependency PHP (ถ้ายังไม่ได้รัน)**
   ```bash
   docker compose exec php composer install
   # หรือถ้าเพิ่ม smalot/pdfparser ใหม่
   docker compose exec php composer require smalot/pdfparser:^2.0
   ```

4. **ในหน้าสร้างหนังสือ** (`/dms/documents/create`):
   - อัปโหลดไฟล์ PDF
   - กดปุ่ม **"สรุปด้วย AI"**
   - ระบบจะเติมช่อง **เรื่อง** และ **รายละเอียด** ให้อัตโนมัติ

## ความยาวการสรุป (ตัวปรับตั้งค่า)

- ในเมนู DMS: **ตั้งค่า > AI สรุปเนื้อหา** เลือกระดับความยาวได้ 3 ระดับ:
  - **สั้น** (1–3 ประโยค)
  - **ปานกลาง** (3–6 ประโยค) — ค่าเริ่มต้น
  - **ยาว/ครบถ้วน** (5–10 ประโยค)
- หรือตั้งใน `.env`: `OLLAMA_SUMMARY_LENGTH=short` หรือ `medium` หรือ `long`

## ตั้งค่า (ถ้าต้องการ)

- **รันใน Docker (compose)**  
  PHP ใช้ hostname `ollama` ได้เลย ใช้ `OLLAMA_URL=http://ollama:11434` (หรือไม่ตั้งก็ได้)

- **บน Server จริง / Production (แก้ error Could not resolve host: ollama)**  
  ชื่อ `ollama` ใช้ได้เฉพาะในเครือข่าย Docker บน server จริงต้องตั้งใน **`.env`** ให้ชี้ไปที่ที่รัน Ollama จริง:
  ```env
  OLLAMA_URL=http://127.0.0.1:11434
  ```
  หรือถ้า Ollama รันบนเครื่องอื่น:
  ```env
  OLLAMA_URL=http://ip-หรือ-hostname-ของเครื่องที่รัน-ollama:11434
  ```
  จากนั้น restart web/PHP (หรือ reload php-fpm) ให้โหลดค่าใหม่

## หมายเหตุ

- PDF แบบสแกน (ภาพ) จะดึงข้อความไม่ได้ ต้องเป็น PDF ที่มีข้อความ
- ครั้งแรกที่กด "สรุปด้วย AI" อาจใช้เวลา 10–30 วินาที ขึ้นกับขนาดเอกสารและเครื่อง
