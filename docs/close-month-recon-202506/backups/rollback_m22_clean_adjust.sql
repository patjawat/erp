DELETE sd FROM stock_detail sd JOIN stock_order so ON so.id=sd.stock_order_id WHERE so.order_no IN ('RECON-M22-20260630-M22-2','RECON-M22-20260630-22-00234');
DELETE FROM stock_order WHERE order_no IN ('RECON-M22-20260630-M22-2','RECON-M22-20260630-22-00234');
