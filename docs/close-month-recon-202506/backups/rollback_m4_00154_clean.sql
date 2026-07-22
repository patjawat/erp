DELETE sd FROM stock_detail sd JOIN stock_order so ON so.id=sd.stock_order_id WHERE so.order_no='RECON-M4-20260630-04-00154';
DELETE FROM stock_order WHERE order_no='RECON-M4-20260630-04-00154';
