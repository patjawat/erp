# Helpdesk V2 – Tables & Linking Report

## 1. Directory structure under `modules/`

### helpdesk2
```
helpdesk2/
├── controllers/     (DefaultController, ServiceController, ServiceRecordController, MedicalController,
│                     DashboardController, SettingController, HelpdeskKanbanController, RepairPartsController,
│                     DeviceTypeController, TeamController, ComputerController, ExpensesController, GeneralController)
├── helpers/         (HelpdeskSlaHelper)
├── models/          (Helpdesk, HelpdeskDetail, HelpdeskSearch, HelpdeskDetailSearch, DeviceType, DeviceTypeSearch, RepairFormSetting)
├── models copy/      (duplicate model files)
├── views/
│   ├── dashboard/
│   ├── default/
│   ├── device-type/
│   ├── expenses/
│   ├── general/
│   ├── helpdesk-kanban/
│   ├── repair-parts/
│   ├── service/
│   ├── service-record/
│   ├── setting/
│   └── team/
├── menu.php
└── Module.php
```

### am (Asset Management)
```
am/
├── components/       (AssetHelper)
├── controllers/     (Default, Equip, Asset, AssetDetail, AssetItem, AssetV2, AssetBulk, AssetLifecycle,
│                     Borrow, Calibration, Depreciation, Fsn, FsnGroup, FsnType, Import, Land, Maintenance,
│                     Move, Report, Setting, Building, Construction, VehicleTax, Repair, AssetApi, AssetDocument, etc.)
├── docs/
├── models/          (Asset, AssetDetail, AssetSearch, AssetDetailSearch, AssetItem, AssetItemSearch,
│                     AssetCategory, AssetCategorySearch, AssetGroup, AssetType, AssetTypeSearch,
│                     Fsn, FsnSearch, AmAssetDepreciation, AmAssetDepreciationMonthly, AmDepreciationClosing,
│                     AmAssetNumberFormat, AssetImportForm)
├── services/        (AssetBulkCreateService, AssetDepreciationService, AssetNumberGenerator, AssetTransactionLogService,
│                     DashboardDataService, DepreciationClosingService, DepreciationScheduleService,
│                     MonthlyDepreciationService, ReportExportService)
├── sql/
├── views/           (asset, asset-detail, asset-item, asset-bulk, asset-lifecycle, calibration, depreciation,
│                     equip, land, move, report, setting, default, etc.)
├── menu.php
└── Module.php
```

### hr
```
hr/
├── components/
├── controllers/     (Default, Categorise, Development, Documentv2, Health, etc.)
├── development/
├── helpers/
├── models/          (Employees, EmployeesSearch, EmployeeDetail, Organization, Calendar, Development,
│                     DevelopmentDetail, Leave, LeaveType, LeaveSummary, LeaveEntitlements, LeavePolicies,
│                     LeaveStep, TeamGroup, TeamGroupDetail, AuthAssignment)
├── uploads/
├── views/           (categorise, default, development, employees, health, leave, organization, tree, etc.)
└── ...
```

### filemanager
```
filemanager/
├── components/      (FileManagerHelper, old_FileManagerHelper copy)
├── controllers/     (UploadsController)
├── fileupload/      (runtime upload dirs by ref)
├── models/          (Uploads)
├── views/
│   ├── default/
│   └── uploads/
└── ...
```

---

## 2. Migrations: tables for helpdesk, helpdesk_detail, asset (am), employees, organization, uploads

| Migration | Table | Main columns |
|-----------|--------|--------------|
| **Helpdesk** | | |
| m240510_123044_create_helpdesk_table | `helpdesk` | id, ref, code, category_id, **emp_id**, date_start, date_end, name, title, data_json, status, rating, move_out, **repair_group**, thai_year, created_at, updated_at, created_by, updated_by |
| m250722_064946_hekpdesk_update_field | `helpdesk` (alter) | + repair_number, device_type_id, **asset_number**, request_repair_date, receive_date, repair_type, repair_result, title |
| **Helpdesk detail** | | |
| m250722_073015_helpdesk_detail | `helpdesk_detail` | id, ref, **helpdesk_id**, emp_id, name, code, title, data_json, status, rating, move_out, thai_year, created_at, updated_at, created_by, updated_by |
| **Asset (AM)** | | |
| m231203_123323_create_asset_table | `asset` | id, ref, asset_group_id, asset_type_id, asset_category_id, asset_item_id, license_plate, car_type, **code**, fsn_number, order_number, receive_date, price, purchase, method_get, life, department, owner, depre_type, on_year, budget_type, asset_status, data_json, device_items, updated_at, created_at, created_by, updated_by, deleted_by, deleted_at |
| m240215_041352_asset_detail_table | `asset_detail` | id, ref, date, code, name, user_id, emp_id, date_start, date_end, data_json, ma_items, thai_year, updated_at, created_at, created_by, updated_by |
| m251213_121754_add_asset_id_to_asset_detail | `asset_detail` (alter) | + asset_id |
| m250611_095411_create_asset_items_table | `asset_items` | (asset items master) |
| m260315_100000_add_depreciation_fields_to_asset | `asset` (alter) | (depreciation fields) |
| m260317_100000_asset_lifecycle | `asset` (alter) | + lifecycle_status; asset_detail used for lifecycle (repair, etc.) |
| **Employees / HR** | | |
| m230720_154520_create_employees_table | `employees` | id, user_id, ref, avatar, phone, cid, email, gender, prefix, fname, lname, fname_en, lname_en, birthday, join_date, end_date, address, province, amphure, district, zipcode, position_*, **department**, status, data_json, emergency_contact, updated_at, created_at, created_by, updated_by |
| m230910_100543_create_employee_detail_table | `employee_detail` | id, ref, emp_id, name, data_json, updated_at, created_at, created_by, updated_by |
| m241106_073101_add_branch_to_employee | `employees` (alter) | + branch |
| **Organization** | | |
| m240113_084006_create_tree_table | `tree` | id (big PK), root, lft, rgt, lvl, name, code, tb_name, leader, data_json, icon, icon_type, active, selected, disabled, readonly, visible, collapsed, movable_*, removable, child_allowed, visibleOrig, disabledOrig |
| **Uploads** | | |
| m141018_105939_create_table_upload | `uploads` | id, name, ref, filename, file_name, real_filename, size, type |
| **Other related** | | |
| m240820_170545_created_stock_events_table | `stock_events` | (includes **helpdesk_id**) |
| m241102_101420_add_helpdesk_id | `stock_events` (alter) | + helpdesk_id (if not already in create) |

---

## 3. Model classes and tableName()

### modules/helpdesk2
| Model | tableName() |
|-------|-------------|
| Helpdesk | `helpdesk` |
| HelpdeskDetail | `helpdesk_detail` |
| HelpdeskSearch | (extends Helpdesk → helpdesk) |
| HelpdeskDetailSearch | (extends HelpdeskDetail → helpdesk_detail) |
| DeviceType | `categorise` |
| DeviceTypeSearch | (categorise) |
| RepairFormSetting | `categorise` |

### modules/am (Asset and related)
| Model | tableName() |
|-------|-------------|
| Asset | `asset` |
| AssetDetail | `asset_detail` |
| AssetSearch | asset |
| AssetDetailSearch | asset_detail |
| AssetItem | `asset_items` |
| AssetCategory | categorise |
| AssetGroup | categorise |
| AssetType | categorise |
| Fsn | categorise |
| AmAssetDepreciation | `am_asset_depreciations` |
| AmAssetDepreciationMonthly | `am_asset_depreciation_monthly` |
| AmDepreciationClosing | `am_depreciation_closings` |
| AmAssetNumberFormat | `am_asset_number_formats` |

### modules/hr (Employees, Organization)
| Model | tableName() |
|-------|-------------|
| Employees | `employees` |
| Organization | `tree` |
| EmployeeDetail | `employee_detail` |
| (department = employees.department → tree.id) | |

---

## 4. Relations detected in code

### Helpdesk (helpdesk2)
- **emp_id** → `Employees` (hr): `hasOne(Employees::class, ['id' => 'emp_id'])` — technician.
- **asset_number** / **code** → Asset: `hasOne(Asset::class, ['code' => 'code'])` — link by asset code (helpdesk also has asset_number; code is used in relation).
- **repair_group** → `Categorise` (name = 'repair_group', code = repair_group) — unit/team.
- **device_type_id** → `Categorise` (name = 'device_type').
- **status** → `Categorise` (name = 'repair_status').
- **Attachments**: `Uploads` where `ref = helpdesk.ref` and `name` in ('repair_request', 'repair', 'external_repair_bill').
- **helpdesk_detail**: `HelpdeskDetail` where `helpdesk_id = helpdesk.id` (name = 'service_record', 'repair_team', etc.).
- **stock_events**: `StockEvent` where `helpdesk_id = helpdesk.id`.

### Asset (am)
- **id** — PK.
- **code** — used by Helpdesk relation (and as asset number).
- **department** → `Organization` (tree): `Organization::findOne(['id' => $this->department])`.
- **asset_detail**: `AssetDetail` via `asset_id` (lifecycle, repair history in data_json/name).

### Employees (hr)
- **id** — PK; referenced by helpdesk.emp_id, helpdesk_detail.emp_id.
- **user_id** — auth.
- **department** → tree (Organization).

### Upload / attachment link to helpdesk
- **uploads**: no `helpdesk_id` column. Link by **ref**: helpdesk has `ref`; uploads have `ref`, `name`. Helpdesk uses `Uploads::find()->where(['ref' => $this->ref, 'name' => $name])` (e.g. repair_request, repair, external_repair_bill).
- **stock_events** has `helpdesk_id` → helpdesk.id (inventory/repair parts).

---

## 5. Concise report

### (a) Tables we can reuse for helpdesk V2

| Table | Use |
|-------|-----|
| **helpdesk** | Keep as main ticket header. Already has emp_id, asset_number, repair_group, ref, code, status, data_json, dates, repair_number, device_type_id, request_repair_date, repair_type, repair_result, etc. |
| **helpdesk_detail** | Reuse for service records and repair-team rows (name = 'service_record', 'repair_team'). Already has helpdesk_id, emp_id, name, title, data_json, status. |
| **uploads** | Keep. Link by **ref** = helpdesk.ref; use **name** for slot (repair_request, repair, external_repair_bill). No schema change. |
| **employees** | Reuse for requester (created_by → user_id → employees) and technician (emp_id → employees.id). |
| **tree** (Organization) | Reuse for department/repair_group display and filters. |
| **asset** | Reuse. Link by asset.code = helpdesk.code (or display by asset_number). |
| **categorise** | Reuse for repair_status, repair_group, device_type, helpdesk_urgency, etc. |
| **stock_events** | Already has helpdesk_id; reuse for repair-parts / stock movements tied to a ticket. |

No need to change existing helpdesk / helpdesk_detail / uploads / employees / tree / asset table definitions for a V2 that only adds new tables and links by existing keys.

---

### (b) Suggested new tables (without modifying helpdesk2 schema)

If you want a clearer V2 domain model **alongside** the current one (new features without touching existing helpdesk columns):

| Table | Suggested columns | Purpose |
|-------|-------------------|--------|
| **repair_ticket** | id, helpdesk_id (FK → helpdesk.id), ticket_code, status, priority, requested_at, assigned_emp_id, completed_at, data_json, created_at, updated_at, created_by, updated_by | Optional wrapper: one-to-one with helpdesk for V2 workflow/SLA; helpdesk_id links to existing helpdesk. |
| **repair_part** | id, helpdesk_id (FK → helpdesk.id), part_name, part_code, qty, unit, unit_price, stock_event_id (nullable, FK → stock_events), data_json, created_at, updated_by | Parts/spares used per ticket; link to existing helpdesk and optionally stock_events. |
| **repair_timeline** | id, helpdesk_id (FK → helpdesk.id), event_type (e.g. received, in_progress, completed, comment), title, description, data_json, emp_id (nullable), occurred_at, created_at, created_by | Explicit timeline events per ticket; can mirror or replace part of helpdesk_detail usage for service_record. |

All link to existing **helpdesk** via **helpdesk_id**. No new columns on helpdesk, employees, asset, or uploads required.

---

### (c) How to link to existing helpdesk / asset / employees without modifying helpdesk2

1. **Link by existing keys (no changes in helpdesk2)**  
   - **Ticket**: use `helpdesk.id` as the single source of truth.  
   - **Technician**: `helpdesk.emp_id` → `employees.id`.  
   - **Asset**: `helpdesk.code` → `asset.code` (or show `helpdesk.asset_number`; keep code in sync with asset code).  
   - **Repair group / org**: `helpdesk.repair_group` → categorise (name = 'repair_group') or map to `tree` if you store org id in data_json.  
   - **Attachments**: `uploads.ref = helpdesk.ref`, `uploads.name` = slot name.  
   - **Detail/timeline**: `helpdesk_detail.helpdesk_id = helpdesk.id`; or new table `repair_timeline.helpdesk_id = helpdesk.id`.  
   - **Parts**: `repair_part.helpdesk_id = helpdesk.id`; optionally `stock_events.helpdesk_id = helpdesk.id`.

2. **New tables only**  
   Add **repair_ticket**, **repair_part**, **repair_timeline** with **helpdesk_id** pointing to **helpdesk.id**. Do not add columns to helpdesk, employees, asset, or uploads. New code (e.g. in a new module or namespaced controllers) reads helpdesk, asset (by code), employees (by emp_id), and uploads (by ref) as read-only or via existing APIs.

3. **Optional: ref for uploads**  
   When creating a new ticket, ensure `helpdesk.ref` is set (e.g. to a unique string). All attachment widgets continue to use `FileManagerHelper::FileUpload($this->ref, $name)` so uploads stay linked by ref and name.

4. **Summary**  
   Reuse: **helpdesk**, **helpdesk_detail**, **uploads** (ref+name), **employees**, **tree**, **asset**, **categorise**, **stock_events**. Add only **repair_ticket** (optional), **repair_part**, **repair_timeline** with **helpdesk_id**. All linking is by existing foreign keys or ref; no changes to helpdesk2 table definitions or to asset/employees/organization/upload schemas.
