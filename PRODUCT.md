# Product

## Register

product

## Users

บุคลากรของโรงพยาบาลด่านซ้าย ใช้งานในบริบทของระบบ ERP ราชการ ครอบคลุมทุก role:

- **เจ้าหน้าที่ทั่วไป** (พยาบาล, ธุรการ): ขอลา, จอง/ยืมรถ-ห้องประชุม, แจ้งซ่อม, ดูหนังสือราชการ
- **หัวหน้างาน/ผู้อนุมัติ**: อนุมัติใบลา/จอง/แจ้งซ่อม ตามลำดับชั้นการอนุมัติ
- **ช่างซ่อม/ผู้ดูแลระบบ**: รับงานซ่อม, จัดการห้องประชุม, admin tasks

บริบทการใช้งาน mobile module: เปิดผ่านมือถือระหว่างปฏิบัติงานจริงในโรงพยาบาล (เดินอยู่, แสงไฟผันแปร, มือไม่ว่าง) หรือผ่าน Telegram MiniApp จาก notification งานที่รออนุมัติ ต้องสแกนหา action สำคัญได้ใน 3 วินาที ไม่ต้องคิด

## Product Purpose

ระบบ ERP ภายในของโรงพยาบาลด่านซ้าย ครอบคลุม HR, จองรถ/ห้องประชุม, แจ้งซ่อม, ลา, ทรัพย์สิน, สารบรรณ ฯลฯ บน Yii2 + Bootstrap 5 + MySQL

`modules/mobile` คือ surface สำหรับมือถือโดยเฉพาะ — quick services + approvals + notifications — เพื่อให้บุคลากรปฏิบัติงาน ERP ผ่านมือถือ/Telegram MiniApp ได้โดยไม่ต้องเปิดเครื่อง desktop

ความสำเร็จคือ: เจ้าหน้าที่กรอกใบลา/จองรถ/อนุมัติได้จบบนมือถือใน <30 วินาที โดยไม่ต้องสลับไปที่ desktop UI

## Brand Personality

3 คำ: **เป็นทางการ · ใช้งานง่าย · เชื่อถือได้**

โทน: professional government enterprise ที่ไม่แข็งทื่อ — เน้นความชัดเจน ใช้สีน้ำเงิน (primary) เป็น anchor แสดงข้อมูลสถานะด้วย badge สีนุ่ม (subtle backgrounds) อ่านสบายตา ภาษาไทยล้วนที่ UI

อารมณ์ที่ต้องสร้างกับผู้ใช้: รู้ว่าตัวเองอยู่ตรงไหน, รู้ว่าต้องทำอะไรต่อ, ไว้ใจได้ว่าคลิกแล้วจะสำเร็จ ไม่ใช่ delight แต่เป็น confidence

## Anti-references

- **Consumer app สดใส (TikTok, IG, Shopee สไตล์)**: สีจัดจ้าน, gradient หนัก, gamification badges, micro-celebration animations — ผิดที่กับ workflow ราชการ
- **AI slop ERP grid**: card grid ไอคอน+title+description ซ้ำๆ ทั้งหน้า, eyebrow ตัวพิมพ์เล็กทุก section, hero-metric stats เป็น decoration
- **Glassmorphism / gradient text decorations**: ไม่เข้ากับโทน enterprise
- **Dense desktop-style table ที่ยัดลงจอเล็ก**: ห้ามทำให้มือถือต้อง scroll สอง direction

## Design Principles

1. **3-second rule** — ทุกหน้าผู้ใช้ต้องเข้าใจใน 3 วินาที: นี่คือหน้าอะไร, สถานะเป็นอย่างไร, ต้องกดอะไรต่อ (สะท้อนจาก AGENT.md §16)
2. **Card-based hierarchy ที่ไม่ซ้อนการ์ด** — ใช้ white card + soft shadow + rounded-4 เป็น containment unit หลัก ห้าม card ซ้อน card
3. **สีมีความหมายเสมอ** — primary blue = action/total, success/warning/danger = สถานะการอนุมัติ, ห้ามใช้สีเป็น decoration ล้วน
4. **Thai-first labels, English-only code** — ข้อความผู้ใช้เห็นต้องเป็นไทยกระชับ ตัวอักษร/ตัวแปรในโค้ดเป็นอังกฤษ
5. **Preserve over rewrite** — เพิ่ม/ปรับ UI ได้ ห้ามแตะ business logic, schema, route, permission, AJAX behavior ที่มีอยู่แล้ว (สะท้อนจาก AGENT.md §1)

## Accessibility & Inclusion

- **WCAG 2.1 AA** เป็นเส้นต่ำสุด ตามมาตรฐานเว็บภาครัฐไทย (TWCAG 2.0)
- **Color contrast**: body text ≥4.5:1, large text ≥3:1 — ระวัง muted gray บนพื้น tinted ขาว
- **Touch target ≥44×44px** — mobile module ใช้นิ้วโป้งกดระหว่างเดิน
- **Keyboard / screen reader**: ใช้ semantic HTML, ARIA label ที่ลิงก์/ปุ่ม icon-only
- **Reduced motion**: เคารพ `prefers-reduced-motion` กับ animation/transition ทุกตัว
- **ภาษาไทย**: font ต้องรองรับสระบน/ล่างไม่ตัด, รองรับชื่อยาว (ตำแหน่งทางการแพทย์) ไม่ overflow
