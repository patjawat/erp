# AI Module Checklist

สถานะฝั่งโมดูล: เสร็จแล้ว 32 งาน / 32 งาน

สถานะก่อน Production: เสร็จแล้ว 45 งาน / 62 งาน

## เสร็จแล้ว

- [x] สร้างโครงสร้าง `modules/ai`
- [x] สร้าง `Module.php`
- [x] Register module ใน `config/add_modules.php`
- [x] สร้าง migration ตาราง `ai_conversations`
- [x] สร้าง migration ตาราง `ai_messages`
- [x] สร้าง migration ตาราง `ai_datasets`
- [x] สร้าง migration ตาราง `ai_dataset_fields`
- [x] สร้าง migration ตาราง `ai_audit_logs`
- [x] สร้าง migration seed dataset ตัวอย่าง
- [x] สร้าง migration permission/RBAC
- [x] สร้าง ActiveRecord models
- [x] สร้าง `AiProviderInterface`
- [x] สร้าง OpenRouter provider
- [x] เพิ่มระบบวาง OpenRouter API key ในหน้า Chat
- [x] โหลดรายการและเลือกโมเดล OpenRouter ตาม API key
- [x] สร้าง provider factory
- [x] สร้าง Dataset Registry
- [x] สร้าง Query Gateway
- [x] สร้าง Permission Layer
- [x] สร้าง Data Scope Resolver
- [x] สร้าง Tool Registry
- [x] สร้าง `query_dataset` tool
- [x] สร้าง `export_excel` tool
- [x] สร้าง Excel export service ด้วย PhpSpreadsheet
- [x] สร้างหน้า AI Chat และ API endpoint
- [x] สร้าง migration สำหรับ AI views มาตรฐาน v1 แบบ compatibility-first
- [x] เพิ่ม unit test สำหรับ `DatasetDefinition`
- [x] เพิ่ม unit test สำหรับ `DatasetRegistry`
- [x] เพิ่ม unit test สำหรับ `QueryResult`
- [x] เพิ่ม unit test สำหรับ `QueryGateway`
- [x] เพิ่ม unit test สำหรับ `DataScopeResolver`

## ต้องทำร่วมกับทีม Domain ก่อนใช้งานจริง

- [x] สร้าง compatibility view `ai_hr_department_summary`
- [x] สร้าง compatibility view `ai_leave_overview`
- [x] สร้าง compatibility view `ai_vehicle_booking_schedule`
- [x] สร้าง compatibility view `ai_meeting_booking_schedule`
- [x] สร้าง compatibility view `ai_stock_balances`
- [x] สร้าง compatibility view `ai_training_overview`
- [x] สร้าง compatibility view `ai_document_overview`
- [x] สร้าง compatibility view `ai_health_overview`
- [ ] ตรวจ field ของ view ให้ตรงกับ `datasets/default.php` บนฐานข้อมูลจริง
- [ ] Review SQL ของ compatibility views กับเจ้าของ module แต่ละระบบ
- [ ] เพิ่ม index ที่จำเป็นให้ query ของ AI views ในฐานข้อมูลจริง

## ต้องทำร่วมกับทีม Infra/Security

- [ ] ตั้งค่า read-only DB component เช่น `dbReadonly`
- [ ] ตั้งค่า OpenRouter secret เช่น `OPENROUTER_API_KEY` หรือวาง key ในหน้า AI Chat
- [ ] Assign permission `ai.*` ให้ role จริง
- [ ] Mapping identity ให้มีค่า data scope เช่น `employee_id`, `department_id`, `managed_department_ids`, `managed_warehouse_ids`
- [ ] ทดสอบ policy ของ role ผู้ใช้ทั่วไป, หัวหน้า, HR, คลัง, สารบรรณ และสุขภาพ

## ต้องทำร่วมกับทีม QA

- [x] Unit test `DatasetRegistry`
- [x] Unit test `DatasetDefinition`
- [x] Unit test `QueryResult`
- [x] Unit test `QueryGateway` field/filter/sort validation
- [x] Unit test `DataScopeResolver`
- [ ] Integration test export Excel
- [ ] Integration test chat tool calling
- [ ] รัน Codeception บน environment ที่ vendor/PHP version compatible

## เกณฑ์ก่อนเปิดใช้งาน Production

- [ ] Query ทุก dataset ต้องผ่าน permission ตาม role
- [ ] User ที่ไม่มี scope ต้องไม่เห็นข้อมูล
- [ ] AI view ต้องไม่ expose field ลับ
- [ ] Export Excel ต้องบันทึก audit log
- [ ] Provider error ต้องไม่ทำให้ข้อมูลหลุดใน response
- [ ] ตรวจ performance ของ view และ index ในฐานข้อมูลจริง
