# โมดูล Notify (แจ้งเตือน)

แจ้งเตือนให้ผู้รับทราบเหตุการณ์ เช่น
- การขออนุมัติลา
- การขออนุมัติจัดซื้อจัดจ้าง
- การขออนุมัติลงเวลาเข้างาน
- การขออนุมัติใช้รถ / เบิกวัสดุ / อบรม / เคลื่อนย้ายครุภัณฑ์

## ติดตั้ง

1. รัน migration ในโมดูล:

```bash
php yii migrate --migrationPath=@app/modules/notify/migrations
```

2. โมดูลลงทะเบียนใน `config/add_modules.php` แล้ว เข้าดูได้ที่ **ของฉัน → แจ้งเตือน** หรือ URL `/notify/default/index`

## การสร้างการแจ้งเตือนจากระบบอื่น

เมื่อมีเหตุการณ์ที่ต้องแจ้งผู้รับ (เช่น สร้างใบขออนุมัติแล้ว ให้แจ้งหัวหน้าอนุมัติ):

```php
use app\modules\notify\models\Notify;

Notify::createFromApprove(
    Notify::TYPE_LEAVE_APPROVE,   // type
    'มีคำขอลาจาก ' . $empName,    // title
    $approverEmpId,               // recipient_emp_id (พนักงานที่ต้องรับทราบ)
    'approve',                    // ref_type
    $approveId,                   // ref_id (optional)
    $message,                     // message (optional)
    $dataJson                     // data_json (optional)
);
```

ประเภท (type) ที่มี: `leave_approve`, `purchase_approve`, `checkin_approve`, `vehicle_approve`, `stock_approve`, `development_approve`, `asset_move_approve`

## โครงสร้างตาราง

- **notify**: id, type, title, message, ref_type, ref_id, recipient_emp_id, read_at, created_at, data_json
