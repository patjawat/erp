# Exit Interview — Migration and Rollback Runbook

สถานะ: แผนเตรียมพร้อม ยังไม่ได้อนุญาตให้รัน production

Migration ที่เกี่ยวข้อง:

1. `m260824_100000_create_exit_interview_tables`
2. `m260824_110000_assign_exit_interview_analytics`

Migration แรกสร้าง 9 ตาราง, seed แบบสอบถาม published version 1, สร้าง RBAC permissions และมอบสิทธิทั้งหมดให้ role `admin` หากมีอยู่ Migration ที่สองมอบ `exitInterviewViewAnalytics` ให้ role `hr` หากมีอยู่

## ข้อควรระวังสำคัญ

- `safeDown()` ของ migration แรกจะลบตาราง Exit Interview ทั้งหมดและข้อมูลทุกคำตอบอย่างถาวร
- rollback migration แรกจะลบ permissions ทั้ง 6 รายการออกจาก RBAC
- ห้าม rollback production หลังเริ่มเก็บคำตอบ เว้นแต่มีการอนุมัติ incident/change และ backup ที่กู้คืนได้จริง
- อย่าเก็บ database dump ที่มีคำตอบ Exit Interview ไว้ใน Git, `.tmp/` ที่แชร์ หรือพื้นที่ที่ไม่ได้เข้ารหัส

## 1. Pre-deployment checklist

- [ ] PR ได้รับ review/approve และ commit ที่จะ deploy ถูกระบุชัดเจน
- [ ] ทดสอบ PHP syntax และ Exit Interview regression suite ผ่าน
- [ ] UAT ผ่านตาม `docs/EXIT_INTERVIEW_UAT.md`
- [ ] ตรวจชื่อ/engine/collation ของฐานข้อมูลเป้าหมาย
- [ ] ตรวจว่าตาราง `employees` และ RBAC tables ใช้งานได้
- [ ] ตรวจว่ามี roles `admin` และ `hr` ตามนโยบายจริง
- [ ] ยืนยัน owner สำหรับข้อมูลส่วนบุคคล ระยะเวลาจัดเก็บ และสิทธิการเข้าถึง
- [ ] สำรองฐานข้อมูลและทดสอบ restore ใน environment แยก
- [ ] บันทึกจำนวนแถวและ checksum/metadata ที่จำเป็นก่อนเปลี่ยนแปลง
- [ ] กำหนด maintenance window, ผู้อนุมัติ, ผู้ปฏิบัติ และช่องทางแจ้งเหตุ
- [ ] ยืนยันว่าไม่มี migration อื่นที่ค้างหรือจะรันปะปนโดยไม่ตรวจ

## 2. Dry run บนสำเนาฐานข้อมูล

ใช้สำเนาฐานข้อมูลที่ปกปิดข้อมูลอ่อนไหว และใช้ commit เดียวกับที่จะ deploy

```powershell
docker compose run --rm php php yii migrate/up 2 --interactive=0
docker compose run --rm php php yii migrate/history 5
```

ตรวจหลัง migrate:

- [ ] migration ทั้งสองรายการอยู่ใน history ตามลำดับ
- [ ] ตารางทั้ง 9 รายการถูกสร้างครบ
- [ ] foreign keys และ unique indexes ถูกสร้างครบ
- [ ] template `HOSPITAL_EXIT` มี published version 1 เพียงรายการที่คาดหวัง
- [ ] permissions ทั้ง 6 รายการมีอยู่
- [ ] admin ได้ permissions ตามที่ออกแบบ
- [ ] hr ได้เฉพาะ Analytics ที่ migration กำหนด ไม่ได้รับสิทธิข้อมูลระบุตัวตนโดยอัตโนมัติ
- [ ] เปิดหน้า Exit Interview และทำ smoke test ด้วยข้อมูลทดสอบ

ชื่อตารางที่ต้องพบ:

- `exit_interview_template`
- `exit_interview_template_version`
- `exit_interview_section`
- `exit_interview_question`
- `exit_interview_question_option`
- `exit_interview`
- `exit_interview_answer`
- `exit_interview_link`
- `exit_interview_audit_log`

## 3. Production migration

ต้องได้รับอนุญาต production แยกต่างหากก่อนรัน คำสั่งด้านล่างเป็นตัวอย่าง runbook ไม่ใช่คำอนุญาตให้ดำเนินการ

1. เปิด maintenance/change window
2. ยืนยัน commit และ backup อีกครั้ง
3. แสดง pending migrations และตรวจว่ามีเฉพาะรายการที่อนุมัติ
4. รัน migration ตามกระบวนการ deployment ขององค์กร
5. ตรวจ migration history, schema, RBAC และ application logs
6. ทำ smoke test แบบไม่ใช้ข้อมูลอ่อนไหว
7. ปิด window เมื่อ owner ยืนยันผล

หากคำสั่ง `migrate/up 2` จะรวม migration อื่นที่ไม่ได้รับอนุมัติ ให้หยุดทันทีและจัดทำ deployment plan ใหม่ ห้ามเดาจำนวน migration จาก environment อื่น

## 4. Post-deployment verification

- [ ] หน้าเมนูและ route ทำงานเฉพาะผู้มีสิทธิ
- [ ] สร้างรายการทดสอบ บันทึกร่าง และลบ/ปกปิดข้อมูลทดสอบตามนโยบาย
- [ ] public link ใช้งานได้และ token ไม่ปรากฏใน application log ที่ไม่จำเป็น
- [ ] Analytics ปกปิดกลุ่มน้อยกว่า 5 คน
- [ ] Audit log ถูกสร้างเมื่อแก้คำตอบ
- [ ] ไม่มี error ใหม่ใน PHP/web/queue logs
- [ ] บันทึกเวลา deploy, migration duration, ผู้ดำเนินการ และผลตรวจ

## 5. Rollback decision

### กรณียังไม่มีข้อมูลจริง

อาจพิจารณา rollback migration ตามลำดับย้อนกลับ หลังได้รับอนุญาตและยืนยัน backup:

```powershell
docker compose run --rm php php yii migrate/down 2 --interactive=0
```

คำสั่งนี้เป็น destructive operation เพราะ migration แรก drop ทุกตาราง Exit Interview

### กรณีมีข้อมูลจริงแล้ว

ห้ามใช้ `migrate/down 2` เป็นค่าเริ่มต้น ให้เลือกแนวทางดังนี้:

1. ปิด route/menu หรือจำกัดสิทธิชั่วคราว
2. เก็บ schema และข้อมูลไว้
3. แก้ application แบบ forward fix
4. หากต้องย้อน schema ให้ export ข้อมูลแบบเข้ารหัสและทดสอบ restore ก่อน
5. ดำเนินการเฉพาะ change/incident approval ที่ระบุผลกระทบด้านข้อมูลส่วนบุคคล

## 6. Rollback verification

- [ ] application กลับสู่สถานะที่กำหนดใน change plan
- [ ] ไม่มี route/menu ที่ชี้ไปยัง schema ที่ถูกลบ
- [ ] ตรวจ RBAC ว่าไม่มี child relation ค้าง
- [ ] ตรวจ migration history ตรงกับ schema
- [ ] หากมีการกู้คืน ตรวจจำนวนรายการ คำตอบ ลิงก์ และ audit log
- [ ] บันทึกสาเหตุ rollback และหลักฐานการกู้คืน

## 7. Sign-off

| บทบาท | ชื่อ | วันที่/เวลา | ผล |
|---|---|---|---|
| ผู้อนุมัติการเปลี่ยนแปลง | | | |
| เจ้าของระบบ HR | | | |
| DBA/ผู้ดูแลฐานข้อมูล | | | |
| ผู้ดำเนินการ | | | |
| ผู้ตรวจหลัง deploy | | | |
