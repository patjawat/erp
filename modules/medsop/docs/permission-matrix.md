# Permission Matrix

| ความสามารถ | Admin | Director | HR/QA | General User |
|---|---:|---:|---:|---:|
| ดู Draft/Pending ทุกแผนก | ✓ | ✓ | ✓ | ✗ |
| ดู Published | ทุกแผนก | ทุกแผนก | ทุกแผนก | เฉพาะแผนกตนเอง |
| สร้าง/แก้ไข | ✓ | ✗ | ✗ | ✗ |
| ส่งอนุมัติ | ✓ | ✗ | ✗ | ✗ |
| อนุมัติ | ตาม approveV2 | ตาม approveV2 | ตาม approveV2 | ✗ |
| Archive/ลบ Draft | ✓ | ✗ | ✗ | ✗ |
| ตั้งค่าระบบ | ✓ | ✗ | ✗ | ✗ |

## RBAC permissions

- `medsop.admin`
- `medsop.review`
- `medsop.viewAll`
- `medsop.viewPublished`

การซ่อนปุ่มเป็นเพียง presentation ทุก action ต้องตรวจสิทธิ์ฝั่งเซิร์ฟเวอร์อีกครั้ง
