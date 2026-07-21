-- rollback: ลบ true-up adjust ทั้งชุดของ M7
DELETE sd FROM stock_detail sd JOIN stock_order so ON so.id=sd.stock_order_id
 WHERE so.order_no LIKE 'RECON-TRUEUP-M7-%';
DELETE FROM stock_order WHERE order_no LIKE 'RECON-TRUEUP-M7-%';
