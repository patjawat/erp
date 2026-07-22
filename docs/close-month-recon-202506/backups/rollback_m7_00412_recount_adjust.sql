-- rollback: ลบ ADJUST recount 07-00412 (31 พ.ค.) ที่เพิ่มเพื่อแก้ยอดต้นติดลบ
DELETE sd FROM stock_detail sd JOIN stock_order so ON so.id=sd.stock_order_id
 WHERE so.order_no='ADJ-RECON-20260531-07-00412';
DELETE FROM stock_order WHERE order_no='ADJ-RECON-20260531-07-00412';
