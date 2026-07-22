# AI Module Checklist

สถานะฝั่งโมดูล: เสร็จแล้ว 25 งาน / 25 งาน

สถานะก่อน Production: เสร็จแล้ว 25 งาน / 50 งาน

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
- [x] สร้าง OpenAI provider
- [x] สร้าง Claude provider
- [x] สร้าง Ollama provider
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

## ต้องทำร่วมกับทีม Domain ก่อนใช้งานจริง

- [ ] สร้าง view `ai_hr_department_summary`
- [ ] สร้าง view `ai_leave_overview`
- [ ] สร้าง view `ai_vehicle_booking_schedule`
- [ ] สร้าง view `ai_meeting_booking_schedule`
- [ ] สร้าง view `ai_stock_balances`
- [ ] สร้าง view `ai_training_overview`
- [ ] สร้าง view `ai_document_overview`
- [ ] สร้าง view `ai_health_overview`
- [ ] ตรวจ field ของ view ให้ตรงกับ `datasets/default.php`

## ต้องทำร่วมกับทีม Infra/Security

- [ ] ตั้งค่า read-only DB component เช่น `dbReadonly`
- [ ] ตั้งค่า provider secret เช่น `OPENAI_API_KEY`, `ANTHROPIC_API_KEY`, `OLLAMA_ENDPOINT`
- [ ] Assign permission `ai.*` ให้ role จริง
- [ ] Mapping identity ให้มีค่า data scope เช่น `employee_id`, `department_id`, `managed_department_ids`, `managed_warehouse_ids`
- [ ] ทดสอบ policy ของ role ผู้ใช้ทั่วไป, หัวหน้า, HR, คลัง, สารบรรณ และสุขภาพ

## ต้องทำร่วมกับทีม QA

- [ ] Unit test `DatasetRegistry`
- [ ] Unit test `QueryGateway` field/filter/sort validation
- [ ] Unit test `DataScopeResolver`
- [ ] Integration test export Excel
- [ ] Integration test chat tool calling

## เกณฑ์ก่อนเปิดใช้งาน Production

- [ ] Query ทุก dataset ต้องผ่าน permission ตาม role
- [ ] User ที่ไม่มี scope ต้องไม่เห็นข้อมูล
- [ ] AI view ต้องไม่ expose field ลับ
- [ ] Export Excel ต้องบันทึก audit log
- [ ] Provider error ต้องไม่ทำให้ข้อมูลหลุดใน response
- [ ] ตรวจ performance ของ view และ index ในฐานข้อมูลจริง
