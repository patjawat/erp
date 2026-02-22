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

## ตั้งค่า (ถ้าต้องการ)

- ใน Docker: PHP ใช้ `OLLAMA_URL=http://ollama:11434` และ `OLLAMA_MODEL=llama3.2` (ตั้งใน docker-compose หรือ .env)
- รันแอปบนเครื่อง host (ไม่ใช้ Docker): ใส่ใน `.env` เช่น `OLLAMA_URL=http://localhost:11434` แล้วรัน Ollama บนเครื่อง (หรือ Docker แยก)

## หมายเหตุ

- PDF แบบสแกน (ภาพ) จะดึงข้อความไม่ได้ ต้องเป็น PDF ที่มีข้อความ
- ครั้งแรกที่กด "สรุปด้วย AI" อาจใช้เวลา 10–30 วินาที ขึ้นกับขนาดเอกสารและเครื่อง
