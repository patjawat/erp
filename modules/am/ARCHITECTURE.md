# Government Asset Management ERP — Architecture Summary

## STEP 1 — Project Analysis (Summary)

### 1.1 Project structure

- **Module:** `app\modules\am` — Asset Management (ครุภัณฑ์/ทรัพย์สิน).
- **Related:** `app\modules\amSurvey` — Asset survey campaigns (QR/Web/CSV); tables `am_asset_surveys`, `am_asset_survey_items`, `am_asset_survey_logs`.
- **Config:** AM registered in `config/add_modules.php`; URL rules in `config/web.php`:
  - `am/asset/bulk-create` → `am/asset-bulk/bulk-create`
  - `am/asset/transfer|repair|dispose|print-qr` → `am/asset-lifecycle/*`
  - `am/dashboard` → `am/default/index`

### 1.2 Database schema (relevant)

| Table | Purpose |
|-------|--------|
| `asset` | Core register: code, fsn_number, receive_date, price, department, data_json, useful_life, residual_value, depreciation_method, lifecycle_status, qr_code_path, deleted_at. |
| `asset_detail` | Lifecycle history (name = 'lifecycle'), calibration, MA; data_json holds transaction_type (RECEIVE/TRANSFER/REPAIR/RETURN/DISPOSE). |
| `am_asset_transactions` | New transaction log: asset_id, transaction_type, from/to location/department, remark, data_json, created_at. |
| `am_asset_surveys` | Survey campaigns: survey_name, survey_year, department_id, status (draft/active/closed). |
| `am_asset_survey_items` | Per-scan/import: survey_id, asset_id, scanned_asset_number, found_status (FOUND/NOT_FOUND/NEW_ASSET), location_match, department_match, survey_method (WEB/CSV/QRCODE). |
| `am_asset_survey_logs` | Audit of location/department changes from survey. |
| `am_asset_depreciations` | *(To be added)* Yearly depreciation records per asset. |
| `am_depreciation_closings` | *(To be added)* Fiscal year closing; locks depreciation for closed years. |

**Category / Department / Location:**

- **Category:** `asset_group_id`, `asset_type_id`, `asset_category_id`, `asset_item_id`; lookup via `Categorise` (name = asset_type, asset_category, etc.) and `AssetItem`.
- **Department:** `asset.department` → FK to `tree` (Organization) in HR module; `Organization::find()` for name.
- **Location:** Stored in `asset.data_json['location']` (no dedicated column).
- **Warehouse:** Not present as a first-class entity; can be represented in `data_json` or a future table.

### 1.3 Existing models

- **Asset** (`app\modules\am\models\Asset`): code (unique), price, receive_date, useful_life, residual_value, depreciation_method, lifecycle_status (received|active|repair|disposed), qr_code_path; `calculateDepreciation()` straight-line; `getTransactions()` / `getRepairTransactions()` from AssetDetail; QR encodes `code` (asset_number).
- **AssetDetail:** Lifecycle records (name = NAME_LIFECYCLE), data_json.transaction_type.
- **AssetSearch:** List filters for AM.
- **AssetItem, AssetGroup, AssetType, AssetCategory:** Supporting masters.

### 1.4 Asset workflows (existing)

1. **Create/Update:** EquipController (view-asset, create, update), AssetController; bulk via AssetBulkController + AssetBulkCreateService (template + quantity, serial paste or CSV).
2. **Lifecycle:** AssetLifecycleController: transfer, repair, dispose — writes to **AssetDetail** (lifecycle); **am_asset_transactions** exists but is not yet written by this controller (optional dual-write for new flow).
3. **QR:** AssetLifecycleController::actionPrintQr() — IDs → print sheet; Asset generates QR from `code` (and can persist to qr_code_path).
4. **Depreciation (legacy):** view_depreciation / depreciation_list use data_json service_life/depreciation; **new path** uses useful_life, residual_value, DepreciationScheduleService, depreciation_v2 view (no change to legacy).
5. **Dashboard:** DefaultController::actionIndex() uses DashboardDataService (KPIs, health, replacement forecast, category/department/age distribution, recent activities); cache 5 min.
6. **Survey:** amSurvey module — SurveyController, ScanController, ImportController; AssetSurveyItem (FOUND/NOT_FOUND/NEW_ASSET, location_match, survey_method).

### 1.5 Mapping to requirements

| Requirement | Status | Notes |
|-------------|--------|--------|
| asset_number | ✅ | `asset.code` |
| asset_name | ✅ | data_json['asset_name'] / Assetitem title |
| category | ✅ | asset_type_id, asset_category_id, asset_item_id |
| serial_number | ✅ | data_json['serial_number'] |
| purchase_price | ✅ | asset.price |
| purchase_date | ✅ | asset.receive_date |
| useful_life, residual_value | ✅ | Columns + DepreciationScheduleService |
| department | ✅ | asset.department → Organization |
| location | ✅ | data_json['location'] |
| status | ✅ | lifecycle_status (active/repair/disposed) + asset_status |
| Bulk receiving | ✅ | /am/asset/bulk-create, serial paste + CSV |
| QR (asset_number) | ✅ | QR content = code; print-qr page exists |
| Lifecycle (received/active/repair/disposed) | ✅ | AssetDetail + lifecycle_status; am_asset_transactions table ready |
| Survey (QR/Web/CSV; found/not_found/location_mismatch) | ✅ | amSurvey; location_match = false ⇒ location_mismatch |
| Depreciation (straight line) | ✅ | Asset::calculateDepreciation(); DepreciationScheduleService; yearly table TBD |
| Fiscal closing | 🔲 | Table + service to be added |
| Reports (register, depreciation, survey, movement) | ⚠️ | ReportController exists (legacy depreciation report); new report actions + export TBD |
| Dashboard | ✅ | /am/dashboard, KPIs + charts + replacement forecast |
| Budget/replacement forecast | ✅ | DashboardDataService::getReplacementForecast() |
| Performance (50k+ assets) | ⚠️ | Indexes and aggregation in place; additional indexes TBD |

---

## Implementation (delivered)

1. **Migrations**
   - `m260318_100000_create_am_asset_depreciations_table.php` — yearly depreciation rows (asset_id, fiscal_year, opening_value, depreciation_amount, accumulated_depreciation, closing_value, is_locked).
   - `m260318_100001_create_am_depreciation_closings_table.php` — fiscal year closing (fiscal_year, closed_at, closed_by, remark).
   - `m260318_100002_asset_performance_indexes.php` — indexes on asset (deleted_at, lifecycle_status, department, receive_date, asset_group_id) for 50k+ assets.

2. **Lifecycle**
   - `AssetTransactionLogService::log()` writes to `am_asset_transactions` for TRANSFER, REPAIR, RETURN, DISPOSE.
   - `AssetLifecycleController` dual-writes to this table after saving `AssetDetail` (existing behaviour unchanged).

3. **Depreciation**
   - `DepreciationScheduleService` (existing) — on-the-fly schedule from useful_life / residual_value.
   - `AmAssetDepreciation` model and `DepreciationClosingService::closeYear($fiscalYear)` — create and lock yearly records; `AmDepreciationClosing` records the closing.

4. **Reports**
   - `ReportController::actionRegister()` — asset register; `format=csv` → CSV export.
   - `ReportController::actionDepreciationReport()` — depreciation (new method); `format=csv` → CSV export.
   - `ReportController::actionMovementReport()` — from `am_asset_transactions`; `format=csv` → CSV export.
   - `ReportController::actionSurveyReport()` — from `am_asset_survey_items`; `format=csv` → CSV export.
   - Views: `report/register`, `report/depreciation-report`, `report/movement-report`, `report/survey-report` (table + foreach, no GridView).
   - PDF: use browser Print or integrate mpdf in a separate action if required.

5. **Dashboard**
   - `/am/dashboard` → `DefaultController::actionIndex()` + `DashboardDataService` (KPIs, health, replacement forecast, category/department/age distribution). Cache 5 min.

6. **Bulk / QR / Survey**
   - Bulk: `/am/asset/bulk-create` → `AssetBulkController::actionBulkCreate()` (template + quantity, serial paste or CSV).
   - QR: `/am/asset/print-qr` → `AssetLifecycleController::actionPrintQr()`; QR content = asset `code`.
   - Survey: `amSurvey` module (QR scan, Web, CSV); `found_status` / `location_match` / `department_match`.

All extensions use **services and new code paths**; existing views and legacy depreciation logic are **not modified**.
