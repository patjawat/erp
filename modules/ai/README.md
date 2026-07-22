# AI Module

โมดูล `ai` เป็น framework กลางสำหรับ AI Assistant ภายใน Yii2 ERP โดยออกแบบให้ AI อ่านข้อมูลได้เฉพาะ Dataset ที่ระบบลงทะเบียนไว้ และ query ผ่าน AI View เท่านั้น

## หลักความปลอดภัย

- AI ไม่รู้ชื่อตารางจริง
- AI ไม่รับ raw SQL
- AI ไม่เขียนข้อมูลลงฐานข้อมูล domain
- Query ทุกครั้งผ่าน `QueryGateway`
- `QueryGateway` ตรวจ permission, dataset, field, filter, sort, data scope และ limit ก่อน query
- Data scope ถูกเติมโดย Yii2 ผ่าน `DataScopeResolver` ไม่ใช่การตัดสินใจของ AI
- Export Excel ทำโดย Yii2 ผ่าน `AiExportService` และ `AiExcelExporter`

## ติดตั้ง

รัน migration ที่อยู่ใน module:

```bash
php yii migrate --migrationPath=@app/modules/ai/migrations
```

Migration ชุดนี้มี:

- ตาราง AI core
- seed dataset registry ตัวอย่าง
- permission/RBAC
- compatibility AI views สำหรับ dataset v1

โมดูลถูก register แล้วใน `config/add_modules.php`:

```php
$modules['ai'] = ['class' => 'app\modules\ai\Module'];
```

ตัวอย่าง config แบบ production:

```php
$modules['ai'] = [
    'class' => 'app\modules\ai\Module',
    'defaultProvider' => 'openrouter',
    'readDb' => 'dbReadonly',
    'defaultMaxRows' => 100,
    'absoluteMaxRows' => 1000,
    'providers' => [
        'openrouter' => [
            'class' => app\modules\ai\providers\OpenRouterProvider::class,
            'apiKey' => env('OPENROUTER_API_KEY') ?: '',
            'model' => env('OPENROUTER_MODEL', 'openai/gpt-5.2'),
            'endpoint' => env('OPENROUTER_CHAT_ENDPOINT', 'https://openrouter.ai/api/v1/chat/completions'),
        ],
    ],
];
```

## Permission

Migration `m260722_000003_create_ai_permissions` สร้าง permission หลัก:

- `ai.chat.use`
- `ai.hr.summary`
- `ai.leave.summary`
- `ai.vehicle.summary`
- `ai.meeting.summary`
- `ai.stock.summary`
- `ai.training.summary`
- `ai.document.summary`
- `ai.health.summary`
- `ai.export.excel`

และ permission สำหรับ data scope ตัวอย่าง:

- `ai.scope.leave.all`
- `ai.scope.leave.department`
- `ai.scope.stock.all`
- `ai.scope.document.all`
- `ai.scope.health.all`

ทีมระบบสิทธิ์ต้อง assign permission เหล่านี้ให้ role จริง เช่น ผู้ใช้ทั่วไป, หัวหน้า, HR, เจ้าหน้าที่คลัง, เจ้าหน้าที่สารบรรณ และเจ้าหน้าที่สุขภาพ

## Dataset Registry

ข้อมูล dataset ถูก seed ลงตาราง:

- `ai_datasets`
- `ai_dataset_fields`

ไฟล์ตัวอย่างอยู่ที่ `datasets/default.php` และใช้เป็น fallback เมื่อยังไม่ได้ migrate

Dataset v1:

- `hr_department_summary`
- `leave_overview`
- `vehicle_booking_schedule`
- `meeting_booking_schedule`
- `stock_balance`
- `training_overview`
- `document_overview`
- `health_overview`

## AI Views

AI query ได้เฉพาะ view ที่ขึ้นต้นด้วย `ai_` และต้อง register ไว้เท่านั้น:

- `ai_hr_department_summary`
- `ai_leave_overview`
- `ai_vehicle_booking_schedule`
- `ai_meeting_booking_schedule`
- `ai_stock_balances`
- `ai_training_overview`
- `ai_document_overview`
- `ai_health_overview`

ทีม domain ต้องสร้าง view เหล่านี้ในฐานข้อมูลจริงเอง โดยเลือก field ให้ตรงกับ `datasets/default.php`

มี migration `m260722_000004_create_ai_default_views` สร้าง compatibility view ให้ครบชุดแล้ว ถ้าพบตารางและคอลัมน์จริงจะ map field พื้นฐานให้อัตโนมัติ ถ้า schema ยังไม่ครบจะสร้าง empty view ที่มี column ตรง registry เพื่อไม่ให้ migration ล้ม

ก่อน production ทีมเจ้าของ domain ควร review/แทน SQL ของ compatibility view ให้ตรง business rule จริง เช่น สถานะอนุมัติ, การนับบุคลากร active, mapping หน่วยงาน, และ field ที่ต้อง mask

## เพิ่ม Dataset ใหม่

เมื่อ ERP มี module ใหม่ ให้ทำตามลำดับนี้:

1. สร้าง AI View ชื่อ `ai_<module>_<dataset>`
2. เพิ่ม row ใน `ai_datasets`
3. เพิ่ม field whitelist ใน `ai_dataset_fields`
4. กำหนด `permission_name`
5. กำหนด `metadata_json.scope_rules`
6. Assign RBAC permission ให้ role ที่เกี่ยวข้อง

ไม่ต้องแก้ `AiChatOrchestrator`, Provider, Tool Registry หรือ Query Gateway

## Data Scope Metadata

ตัวอย่าง scope rule:

```json
{
  "scope_rules": [
    {
      "name": "department",
      "permissions": ["ai.scope.leave.department"],
      "filters": [
        {
          "field": "department_id",
          "operator": "in",
          "value": "managed_department_ids"
        }
      ]
    },
    {
      "name": "self",
      "permissions": ["@authenticated"],
      "filters": [
        {
          "field": "employee_id",
          "operator": "=",
          "value": "current_employee_id"
        }
      ]
    }
  ]
}
```

ค่าที่ `DataScopeResolver` รองรับตั้งต้น:

- `current_user_id`
- `current_employee_id`
- `current_department_id`
- `managed_department_ids`
- `current_warehouse_id`
- `managed_warehouse_ids`

ถ้าองค์กรมี rule ซับซ้อนกว่านี้ ให้ override/extend `DataScopeResolver` โดยไม่ต้องแก้ AI Core ส่วนอื่น

## API

หน้า chat:

```text
/ai/chat/index
```

รายการโมเดลของ OpenRouter และบันทึกโมเดลที่เลือก:

```http
GET /ai/chat/openrouter-models

POST /ai/chat/openrouter-models
Content-Type: application/json

{
  "model": "openai/gpt-5.2"
}
```

ส่ง chat:

```http
POST /ai/chat/send
Content-Type: application/json

{
  "message": "สรุปยอดคงเหลือคลัง",
  "conversation_id": null,
  "provider": "openrouter"
}
```

Query dataset โดยตรง:

```http
POST /ai/query/run
Content-Type: application/json

{
  "dataset": "stock_balance",
  "fields": ["item_code", "item_name", "balance_qty"],
  "filters": [
    {"field": "balance_qty", "operator": "<=", "value": 10}
  ],
  "sort": [
    {"field": "balance_qty", "direction": "asc"}
  ],
  "limit": 50
}
```

Export Excel:

```http
POST /ai/export/excel
Content-Type: application/json

{
  "dataset": "leave_overview",
  "fields": ["employee_name", "leave_type", "start_date", "end_date", "status"],
  "limit": 100,
  "file_name": "leave_overview"
}
```

## AI Provider

โมดูลรองรับ OpenRouter เพียง provider เดียว กำหนด API key ผ่าน `OPENROUTER_API_KEY`
หรือวางคีย์ในส่วน "การเชื่อมต่อ OpenRouter" บนหน้าผู้ช่วย AI หลังเชื่อมต่อแล้ว
ระบบจะโหลดรายการโมเดลที่ API key ใช้งานได้ รองรับการค้นหาจากชื่อ/รหัส กรองเฉพาะโมเดลฟรี
และจดจำโมเดลที่เลือกไว้ใน session ของผู้ใช้

## Audit Log

ตาราง `ai_audit_logs` บันทึก:

- user
- provider
- dataset
- tool
- action
- status
- row count
- duration
- error
- request/response metadata
- IP

## ขอบเขต v1

รองรับ:

- AI Chat
- Query Dataset
- Export Excel
- Multi AI Provider

ยังไม่รองรับ:

- Generate Image
- PDF
- PowerPoint
- Word
- การเพิ่ม/แก้ไขข้อมูลผ่าน AI
- AI อนุมัติรายการ
