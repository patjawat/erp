-- Rollback สำหรับ: เพิ่มใบ OUT กระทบยอด M19 (Face shield 19-00069 x1 @700, คลัง3->sub34, 8 มิ.ย.2569)
-- เหตุผล: บัญชีนับจ่าย face shield 1 อันใน มิ.ย. (จากใบโอน IN-691811/3311 เข้าคลัง34)
--         แต่ระบบบันทึกใบนั้นเป็น IN ลงคลัง SUB จึงไม่ถูกนับเป็น OUT ของคลังหลัก 3
--         => เพิ่มใบ OUT สังเคราะห์ 1 บรรทัดเฉพาะ item นี้ ให้ยอดยกไป M19 = 498,106.87 ตรงบัญชี
-- วิธี rollback: รันไฟล์นี้ จะลบใบ + detail ที่เพิ่มเข้าไป (อ้างด้วย order_no ที่ unique)
DELETE sd FROM stock_detail sd
  JOIN stock_order so ON so.id = sd.stock_order_id
  WHERE so.order_no = 'RECON-M19-20260608-19-00069';
DELETE FROM stock_order WHERE order_no = 'RECON-M19-20260608-19-00069';
