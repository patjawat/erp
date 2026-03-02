# UI Architecture: Employee Recognition & Challenge (Social Network Style)

## ภาพรวม

ระบบ "พลังแห่งคำขอบคุณ" ถูกออกแบบให้รู้สึกเหมือนโซเชียลเน็ตเวิร์กสมัยใหม่ (ผสม TikTok + Facebook) โดยใช้ **Yii2 + Bootstrap 5** เป็น stack หลัก ไม่ใช้ React/Next.js เพื่อให้สอดคล้องกับโปรเจกต์เดิม

---

## 1. UI/UX Design Language

### 1.1 Bento Grid + Glassmorphism (Bootstrap 5)

- **Layout**: Bento-style grid — ฟีดหลัก (8 col) + Sidebar (4 col) แบ่งเป็น "กล่อง" ชัดเจน
- **Glassmorphism**: ใช้ `bg-white`, `bg-opacity-10`, `rounded-3`, `shadow-sm` (และ `shadow` เมื่อต้องการเน้น) ไม่เพิ่ม CSS file ใหม่ตาม skill; ถ้าต้องการ blur จริงใช้ utility ที่มีในโปรเจกต์
- **การ์ด**: `card border-0 shadow-sm rounded-3` ทุกการ์ด

### 1.2 หน้าและ Component หลัก

| หน้า/Component | หน้าที่ | ไฟล์/ที่อยู่ |
|---------------|--------|----------------|
| **Dashboard (Feed)** | ฟีดรวมโพสต์คำขอบคุณ + Compose bar + Sidebar (โปรไฟล์, Leaderboard, Challenge) | `appreciation/views/default/index.php` |
| **Feed Item** | การ์ดโพสต์เดียว: avatar, ผู้ส่ง→ผู้รับ, ข้อความ, Like, คะแนน | `appreciation/views/default/_item.php` |
| **Compose** | ช่องโพสต์คำขอบคุณ (ลิงก์ไป create) | ส่วนหนึ่งของ index |
| **Create Thank You** | ฟอร์มส่งคำขอบคุณ + เลือกผู้รับ (Select2+avatar) + Badge/Core Value + ข้อความ | `appreciation/views/default/create.php` |
| **Leaderboard Widget** | อันดับรับคะแนน (รายบุคคล) แสดงใน Sidebar | partial ใน index + data จาก DefaultController |
| **Challenge Widget** | ภารกิจรายสัปดาห์/เดือน + Progress Bar + ลิงก์ไป Challenge | partial ใน index |
| **Challenge List** | หน้ารายการ Challenge กำลังจัด / เร็วๆ นี้ / สิ้นสุด | `appreciation/views/challenge/index.php` |
| **Challenge View** | รายละเอียด Challenge + Progress ของฉัน + ลีดเดอร์บอร์ดใน Challenge | `appreciation/views/challenge/view.php` |

### 1.3 Micro-interactions (JS เบาๆ)

- **Like**: กด Like → ส่ง AJAX → อัปเดตตัวเลข + **หัวใจพุ่ง (floating hearts)** รอบปุ่ม
- **ส่งคำขอบคุณสำเร็จ**: หลัง redirect กลับฟีด → แสดง **celebration สั้นๆ** (particle/heart burst) ถ้ามี query `?celebrate=1`

---

## 2. Core Features (ที่ implement ในเฟสนี้)

### 2.1 Thank You System (มีอยู่แล้ว + ปรับ)

- ส่งคำขอบคุณพร้อม **Points** (จาก Module config)
- **Badge / Core Value**: ใช้ฟิลด์ `badge_type` เป็น "สติกเกอร์/ประเภทคำขอบคุณ" แสดงเป็นข้อความหรือ emoji ในฟีด (ถ้าต้องการ emoji จริง map ใน view เช่น team_player → 🤝)
- **การแจ้งเตือน**: หลัง save → สร้าง Notify ให้ผู้รับ (type `appreciation_thank`)

### 2.2 Challenge Mode (มีอยู่แล้ว + ปรับ)

- ภารกิจรายสัปดาห์/เดือน: ใช้ตาราง `appreciation_challenge` (start_at, end_at, goal_type, goal_value)
- **Progress Bar**: ใน Dashboard widget และหน้า challenge/view แสดงความคืบหน้า (current_value / goal_value)
- **Badge เมื่อทำสำเร็จ**: แสดงใน challenge view (completed_at) และแจ้ง Notify เมื่อทำครบ (type `challenge_winner` ถ้าต้องการ)

### 2.3 Leaderboard

- **รายบุคคล**: อันดับผู้ได้รับคะแนนรวม (sum points_given where to_emp_id) แสดง Top 10 ใน Sidebar
- **Real-time**: โหลดพร้อมหน้า (ไม่ polling ในเฟสแรก); ถ้าต้องการ real-time ภายหลังใช้ Pjax หรือ setInterval โหลด widget

---

## 3. Integration & Notification

### 3.1 Existing Notify System

- **Notify Model**: เพิ่ม type
  - `appreciation_thank` — มีคนส่งคำขอบคุณให้คุณ (recipient = to_emp_id, ref_type = appreciation, ref_id = id)
  - `challenge_winner` — คุณทำ Challenge ครบเป้า (recipient = emp_id, ref_type = appreciation_challenge_progress, ref_id = id)
- **จุดเรียก**: 
  - หลัง `Appreciation::save()` ใน DefaultController → `Notify::createForAppreciation($model)`
  - หลังอัปเดต progress และพบ completed_at ใหม่ ใน DefaultController::updateChallengeProgress หรือใน ChallengeController (ถ้ามีที่ปิด challenge)

### 3.2 Smart Summary (อนาคต)

- Cron สรุปยอดคะแนนและคำขอบคุณประจำสัปดาห์ → ส่งเป็น Notify หรืออีเมลให้แต่ละคน (ออกแบบภายหลัง)

---

## 4. Technical Stack (ตามโปรเจกต์)

- **Backend**: Yii2 (PHP), MySQL
- **Frontend**: Bootstrap 5, jQuery (มีอยู่แล้ว), Pjax สำหรับฟีด
- **Styling**: Bootstrap 5 utility + component เท่านั้น (ตาม .cursor/skills/bootstrap5-tailwind-style)
- **Micro-interactions**: Vanilla JS หรือ jQuery สั้นๆ (floating hearts, celebration)

---

## 5. ไอเดียแหวกแนว (Roadmap ภายหลัง)

| ฟีเจอร์ | Concept | โครงสร้างที่ต้องเพิ่ม |
|--------|---------|------------------------|
| **Energy Chain** | A ขอบคุณ B → B ขอบคุณ C ภายใน 24 ชม. → โบนัสทวีคูณ (Combo) | ฟิลด์/ตารางเก็บ chain_id หรือ timestamp ล่าสุดของ "การส่งต่อ"; logic คำนวณ combo |
| **Story Mode** | อัปโหลดวิดีโอสั้น/รูปขอบคุณ | ตาราง appreciation เพิ่ม media_url หรือใช้ filemanager; หน้าเล่นวิดีโอ/สไลด์รูป |
| **Mystery Box (Gacha)** | คะแนนแลก "กล่องสุ่ม" ลุ้นรางวัล | ตาราง reward_pool, user_points_log, การจับฉลาก; หน้าเปิดกล่อง + sound/effect |

---

## 6. โครงสร้างไฟล์ที่เกี่ยวข้อง

```
modules/appreciation/
  controllers/DefaultController.php   # actionIndex (feed + leaderboard + challenge data), actionCreate, actionLike
  views/default/
    index.php                         # Dashboard: Bento layout, compose, feed, sidebar
    _item.php                         # โพสต์คำขอบคุณ 1 รายการ
    _leaderboard.php                  # Widget อันดับ (partial)
    _challenge_widget.php             # Widget Challenge + Progress (partial)
    create.php
  models/Appreciation.php, AppreciationLike.php, AppreciationChallenge.php, AppreciationChallengeProgress.php

modules/notify/
  models/Notify.php                   # เพิ่ม TYPE_APPRECIATION_THANK, TYPE_CHALLENGE_WINNER และ createForAppreciation()

web/js/ (หรือ inline ใน view)
  # Heart burst on Like; celebration on ?celebrate=1
```

---

## 7. Data Flow หลัก

1. **Dashboard โหลด**: DefaultController::actionIndex → query feed (AppreciationSearch), receivedCount/totalPoints, leaderboard (top by points), active challenges + my progress → render index + _leaderboard + _challenge_widget
2. **ส่งคำขอบคุณ**: Create → save → Notify::createForAppreciation(to_emp) → updateChallengeProgress → redirect index?celebrate=1
3. **Like**: AJAX default/like → อัปเดต like count → JS แสดง heart burst
4. **Challenge ทำครบ**: updateChallengeProgress ตั้ง completed_at → (optional) Notify::create challenge_winner
