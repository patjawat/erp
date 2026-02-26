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

### ส่งไปยังผู้ใช้ที่ล็อกอิน

ถ้าต้องการแจ้งเตือน**ผู้ใช้ที่ล็อกอินอยู่** (ตัวเอง) ให้ใช้ `UserHelper::GetEmployee()` เพื่อเอา employee id ของคนปัจจุบัน แล้วส่งเป็น `recipient_emp_id`:

```php
use app\components\UserHelper;
use app\modules\notify\models\Notify;

$me = UserHelper::GetEmployee();
if ($me) {
    Notify::createFromApprove(
        Notify::TYPE_LEAVE_APPROVE,
        'มีคำขอลารออนุมัติ',
        $me->id,   // ส่งถึงตัวเอง (ผู้ใช้ที่ login)
        'approve',
        $approveId,
        null,
        null
    );
}
```

ส่งถึง**ผู้อนุมัติ/ผู้รับคนอื่น** ก็ใช้ `recipient_emp_id` เป็น id ของพนักงานคนนั้น (จากตาราง `employees`) แทน `$me->id`

## ให้ผู้ใช้ยอมรับการแจ้งเตือน (PWA / Browser)

เพื่อให้แจ้งเตือนแบบ **ป๊อปอัปบนอุปกรณ์** (แม้ปิดแท็บ) ได้:

1. **ต้องใช้ HTTPS** (localhost ใช้ได้สำหรับพัฒนา)
2. **ผู้ใช้ต้องอนุญาตการแจ้งเตือน** ของเบราว์เซอร์:
   - ครั้งแรกที่เข้า theme v4 ระบบจะถามสิทธิ์ `Notification.requestPermission()` (หรือผู้ใช้กดปุ่ม "เปิดการแจ้งเตือนบนอุปกรณ์" ในหน้าแจ้งเตือน)
   - ถ้าเลือก **อนุญาต** ระบบจะ poll รายการแจ้งเตือนทุก 60 วินาที แล้วแสดงการแจ้งเตือนบนหน้าจอ
3. **มือถือ iOS**: ต้องติดตั้งเป็น PWA (เพิ่มไปยังหน้าจอหลัก) แล้วเปิดจากไอคอนแอป การแจ้งเตือนถึงจะทำงาน
4. **มือถือ Android**: ใช้ Chrome/Edge เปิดเว็บผ่าน HTTPS แล้วอนุญาตการแจ้งเตือนในตั้งค่าไซต์

ถ้าผู้ใช้เคยกด **ปิดกั้น** ต้องไปที่ การตั้งค่าไซต์ → การแจ้งเตือน → เปลี่ยนเป็น "อนุญาต" เอง

## โครงสร้างตาราง

- **notify**: id, type, title, message, ref_type, ref_id, recipient_emp_id, read_at, created_at, data_json
