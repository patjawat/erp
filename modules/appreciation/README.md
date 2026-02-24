# โมดูล พลังแห่งคำขอบคุณ (Appreciation Wall)

ให้คำชมต่อกันในหน่วยงาน สะสมคะแนน และร่วม Challenge รับของรางวัล

## การติดตั้ง

รัน migration (จากโฟลเดอร์โปรเจกต์):

```bash
php yii migrate --migrationPath=@app/modules/appreciation/migrations --interactive=0
```

หรือถ้าใช้ Docker:

```bash
docker-compose exec app php yii migrate --migrationPath=@app/modules/appreciation/migrations --interactive=0
```

## ฟีเจอร์

- **ฟีดคำขอบคุณ** – แสดงคำชมที่ส่งถึงกันแบบ feed (คล้ายโซเชียล)
- **ส่งคำขอบคุณ** – เลือกผู้รับ เลือกประเภทคำชม (Team Player, Problem Solver ฯลฯ) และเขียนข้อความ
- **Like** – กด like ที่คำขอบคุณได้
- **คะแนน** – ผู้รับได้ +50 คะแนนต่อ 1 คำชม (ปรับได้ที่ `Module::$pointsPerThank`)
- **Challenge** – กำหนดกิจกรรมเป้าหมาย (เช่น ส่งคำขอบคุณ 10 ครั้งใน 1 สัปดาห์) ทำครบรับรางวัล

## การสร้าง Challenge (กิจกรรมเป้าหมาย)

ตอนนี้สร้างได้ผ่าน DB โดยตรง หรือเพิ่มหน้า Admin ภายหลัง

ตัวอย่าง INSERT สำหรับ Challenge:

```sql
INSERT INTO appreciation_challenge (name, description, start_at, end_at, goal_type, goal_value, reward_name, status, created_at)
VALUES (
  'ส่งความดีกันสัปดาห์ละ 10 คำชม',
  'ส่งคำขอบคุณให้เพื่อนร่วมงานครบ 10 ครั้ง ภายใน 7 วัน รับของรางวัลจาก HR',
  '2025-02-24',
  '2025-03-02',
  'send_count',   -- send_count = นับการส่ง, receive_count = นับการรับ
  10,
  'ของรางวัลจาก HR',
  'active',
  NOW()
);
```

## URL หลัก

- ฟีด: `/appreciation/default/index`
- ส่งคำขอบคุณ: `/appreciation/default/create`
- รายการ Challenge: `/appreciation/challenge/index`

ลิงก์จากหน้า Me: ปุ่ม "คำขอบคุณ" ในเมนู และการ์ด "พลังแห่งคำขอบคุณ" บนแดชบอร์ด
