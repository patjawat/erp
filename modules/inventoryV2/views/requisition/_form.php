<?php

use app\components\AppHelper;
use app\modules\inventoryV2\models\Warehouse;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

$formatRepoJs = "var formatRepo=function(repo){if(repo.loading)return repo.avatar;return '<div>'+repo.avatar+'</div>';};";
$this->registerJs($formatRepoJs, View::POS_HEAD);
$resultsJs = "function(data,p){p.page=p.page||1;return{results:data.results,pagination:{more:(p.page*30)<(data.total_count||999)}};}";

// คลังหลัก (ต้นทางที่จ่าย) และคลังย่อย (หน่วยงานที่รับของ)
$mainWarehousesList = Warehouse::find()
    ->where(['warehouse_type' => 'MAIN'])
    ->andWhere(['or', ['delete' => null], ['delete' => '']])
    ->orderBy(['warehouse_name' => SORT_ASC])
    ->all();
$mainWarehouses = ArrayHelper::map($mainWarehousesList, 'id', 'warehouse_name');
$warehouseList = ArrayHelper::map(Warehouse::find()->andWhere(['or', ['delete' => null], ['delete' => '']])->orderBy(['warehouse_name' => SORT_ASC])->all(), 'id', 'warehouse_name');
$subWarehousesList = Warehouse::findSubWarehousesForUser();
$subWarehouses = ArrayHelper::map($subWarehousesList, 'id', 'warehouse_name');

$approverSig = $model->getIssueSignature('approver');
$approverEmpId = '';
$dj = $model->data_json;
if (is_string($dj)) {
    $dj = json_decode($dj, true) ?: [];
}
if (!empty($dj['issue_approver']['emp_id'])) {
    $approverEmpId = $dj['issue_approver']['emp_id'];
}

// ผู้เบิก = ดึงจาก user ที่ล็อกอิน (โชว์อย่างเดียว — controller จะตั้งให้ตอนบันทึก)
$requesterName = '';
$userId = Yii::$app->user->id;
if ($userId) {
    $emp = \app\modules\hr\models\Employees::findOne(['user_id' => $userId]);
    if ($emp) {
        $info = \app\modules\inventoryV2\models\StockOrder::getEmployeeNameAndPosition($emp->id);
        $requesterName = $info['name'] ?? '';
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
.req-scope{
  --primary:#255FAE;--primary-dark:#1a3d6b;--primary-light:#eef5fc;
  --surface:#f8fafc;--card:#fff;--border:#e2e8f0;--border-soft:#f1f5f9;
  --text:#0f172a;--text-2:#475569;--text-3:#94a3b8;
  --green:#16a34a;--green-light:#dcfce7;
  --red:#dc2626;--red-light:#ffe4e6;
  --amber:#d97706;--amber-light:#fef3c7;
  --purple:#7c3aed;--purple-light:#ede9fe;
  --r-sm:8px;--r-md:12px;--r-lg:16px;
  --sh-sm:0 1px 3px rgba(15,23,42,.06);--sh-md:0 4px 16px rgba(15,23,42,.08);
  font-family:'Sarabun',sans-serif;color:var(--text);font-size:15px;
}
.req-scope *{box-sizing:border-box;}
.req-scope .page-wrap{max-width:1500px;margin:0 auto;padding:18px 8px 40px;}

.req-scope .page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:18px;flex-wrap:wrap;}
.req-scope .page-title{font-size:22px;font-weight:700;margin-bottom:4px;display:flex;align-items:center;gap:10px;}
.req-scope .page-title-icon{width:38px;height:38px;border-radius:var(--r-sm);display:flex;align-items:center;justify-content:center;font-size:18px;background:var(--green-light);color:var(--green);}
.req-scope .page-subtitle{font-size:13px;color:var(--text-3);margin-left:48px;}

.req-scope .sw-btn-primary{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:var(--primary);color:#fff;border:none;border-radius:var(--r-sm);font-family:'Sarabun',sans-serif;font-size:13.5px;font-weight:600;cursor:pointer;text-decoration:none;white-space:nowrap;}
.req-scope .sw-btn-primary:hover{background:var(--primary-dark);color:#fff;}
.req-scope .sw-btn-outline{display:inline-flex;align-items:center;gap:7px;padding:8px 14px;background:transparent;color:var(--text-2);border:1px solid var(--border);border-radius:var(--r-sm);font-family:'Sarabun',sans-serif;font-size:13.5px;font-weight:500;cursor:pointer;text-decoration:none;}
.req-scope .sw-btn-outline:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
.req-scope .sw-btn-success{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:var(--green);color:#fff;border:none;border-radius:var(--r-sm);font-family:'Sarabun',sans-serif;font-size:13.5px;font-weight:600;cursor:pointer;}
.req-scope .sw-btn-success:hover{background:#15803d;color:#fff;}

.req-scope .layout{display:grid;grid-template-columns:1fr 320px;gap:18px;align-items:start;}

.req-scope .panel{background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);box-shadow:var(--sh-sm);overflow:hidden;margin-bottom:16px;}
.req-scope .panel-head{display:flex;align-items:center;justify-content:space-between;padding:14px 18px 12px;border-bottom:1px solid var(--border-soft);}
.req-scope .panel-title{font-size:14px;font-weight:600;display:flex;align-items:center;gap:8px;}
.req-scope .panel-title i{color:var(--primary);font-size:16px;}
.req-scope .panel-body{padding:16px 18px;}

.req-scope .fg{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;}
.req-scope .fg.thirds{grid-template-columns:1fr 1fr 1fr;}
.req-scope .fg.full{grid-template-columns:1fr;}
.req-scope .form-group{display:flex;flex-direction:column;gap:4px;margin-bottom:0;}
.req-scope .form-label{font-size:13px;font-weight:600;color:var(--text-2);margin-bottom:0;}
.req-scope .req{color:var(--red);margin-left:2px;}
.req-scope .form-control,
.req-scope .form-select{padding:8px 12px;border:1px solid var(--border);border-radius:var(--r-sm);font-family:'Sarabun',sans-serif;font-size:13.5px;background:var(--surface);outline:none;width:100%;transition:border-color .15s,box-shadow .15s;color:var(--text);}
.req-scope .form-control:focus,
.req-scope .form-select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(37,95,174,.12);background:#fff;}
.req-scope textarea.form-control{resize:vertical;min-height:60px;}
.req-scope .form-text{font-size:11.5px;color:var(--text-3);margin-top:3px;}

.req-scope .fs-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-3);margin:14px 0 10px;padding-bottom:6px;border-bottom:1px solid var(--border-soft);display:flex;align-items:center;gap:6px;}
.req-scope .fs-title:first-child{margin-top:0;}
.req-scope .fs-title i{color:var(--primary);font-size:13px;}

.req-scope .items-table{width:100%;border-collapse:collapse;margin-top:8px;}
.req-scope .items-table th{background:var(--surface);font-size:11px;font-weight:700;color:var(--text-2);text-transform:uppercase;letter-spacing:.4px;padding:9px 8px;text-align:left;border-bottom:1px solid var(--border);}
.req-scope .items-table th.right{text-align:right;}
.req-scope .items-table th.center{text-align:center;}
.req-scope .items-table td{padding:8px 6px;border-bottom:1px solid var(--border-soft);vertical-align:middle;}
.req-scope .items-table td.right{text-align:right;font-variant-numeric:tabular-nums;font-weight:600;}
.req-scope .items-table td.center{text-align:center;}
.req-scope .items-table input[type="number"]{padding:6px 10px;border:1px solid var(--border);border-radius:6px;font-family:'Sarabun',sans-serif;font-size:12.5px;background:var(--surface);outline:none;width:100%;text-align:right;}
.req-scope .items-table input[type="number"]:focus{border-color:var(--primary);background:#fff;}
.req-scope .items-table tr.total-row{background:var(--primary-light);font-weight:700;}
.req-scope .items-table tr.total-row td{padding:11px 8px;font-size:13.5px;color:var(--primary-dark);}
.req-scope .stock-hint{font-size:12px;color:var(--text-3);font-variant-numeric:tabular-nums;}

.req-scope .btn-add{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--primary-light);color:var(--primary);border:1px dashed var(--primary);border-radius:var(--r-sm);font-family:'Sarabun',sans-serif;font-size:12.5px;font-weight:600;cursor:pointer;margin-top:8px;}
.req-scope .btn-add:hover{background:var(--primary);color:#fff;border-style:solid;}
.req-scope .btn-rm{width:28px;height:28px;border-radius:6px;border:none;background:transparent;cursor:pointer;color:var(--red);display:inline-flex;align-items:center;justify-content:center;}
.req-scope .btn-rm:hover{background:var(--red-light);}

.req-scope .info-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;font-size:13px;border-bottom:1px solid var(--border-soft);gap:12px;}
.req-scope .info-row:last-child{border-bottom:none;}
.req-scope .info-row-label{color:var(--text-3);font-size:12px;}
.req-scope .info-row-val{font-weight:600;text-align:right;}

.req-scope .summary-card{background:linear-gradient(135deg,var(--primary-light),#dbeafe);border-radius:var(--r-md);padding:14px 16px;margin-bottom:12px;}
.req-scope .summary-label{font-size:11px;color:var(--primary-dark);text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;}
.req-scope .summary-val{font-size:22px;font-weight:700;color:var(--primary-dark);}

.req-scope .reason-chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;}
.req-scope .reason-chip{padding:4px 12px;border:1px solid var(--border);background:var(--surface);border-radius:14px;font-size:11.5px;color:var(--text-2);cursor:pointer;transition:all .12s;}
.req-scope .reason-chip:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}

.req-scope .action-bar{display:flex;justify-content:space-between;gap:10px;padding:14px 18px;background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);box-shadow:var(--sh-sm);flex-wrap:wrap;}

/* Override Yii widget default styles inside .req-scope */
.req-scope .help-block{font-size:11.5px;color:var(--red);margin-top:3px;}
.req-scope .field-stockorder-order_no,
.req-scope .field-stockorder-sub_warehouse_id,
.req-scope .field-stockorder-main_warehouse_id,
.req-scope .field-stockorder-order_date{margin-bottom:0;}
.req-scope .field-stockorder-order_no .control-label,
.req-scope .field-stockorder-sub_warehouse_id .control-label,
.req-scope .field-stockorder-main_warehouse_id .control-label,
.req-scope .field-stockorder-order_date .control-label{font-size:13px;font-weight:600;color:var(--text-2);margin-bottom:4px;}
.req-scope .has-error .form-control,
.req-scope .has-error .form-select{border-color:var(--red);}
.req-scope .is-invalid{border-color:var(--red) !important;}

@media(max-width:1100px){.req-scope .layout{grid-template-columns:1fr;}}
@media(max-width:768px){
  .req-scope .page-wrap{padding:12px 4px 32px;}
  .req-scope .fg,.req-scope .fg.thirds{grid-template-columns:1fr;}
}

/* TomSelect dropdown — appended to body */
.ts-dropdown.requisition-item-dropdown{
    z-index:1060 !important;position:fixed !important;border:1px solid rgba(0,0,0,.15) !important;
    border-radius:0.375rem !important;background:#fff !important;
    box-shadow:0 0.5rem 1rem rgba(0,0,0,.15) !important;max-height:280px;overflow-y:auto;
}
.req-scope #item-table td:first-child{overflow:visible;position:relative;}
.req-scope #item-table .ts-wrapper{position:relative;}
.req-scope #item-table .ts-control{min-height:38px;border:1px solid var(--border);border-radius:6px;background:var(--surface);}
</style>

<div class="req-scope">

<?php $form = ActiveForm::begin(['id' => 'requisition-form', 'options' => ['class' => 'requisition-form']]); ?>

  <div class="layout">
    <div>
      <div class="panel">
        <div class="panel-head"><div class="panel-title"><i class="bi bi-info-circle-fill"></i> ข้อมูลใบเบิก</div></div>
        <div class="panel-body">

          <div class="fs-title"><i class="bi bi-person-fill"></i> ผู้เบิก / แผนก</div>
          <div class="fg thirds">
            <div class="form-group">
              <label class="form-label">เลขที่ใบ</label>
              <?= Html::activeTextInput($model, 'order_no', ['class' => 'form-control', 'readonly' => true, 'placeholder' => 'REQ-AUTO']) ?>
            </div>
            <div class="form-group">
              <label class="form-label">วันที่ <span class="req">*</span></label>
              <?= \app\widgets\datepicker\DatepickerThai::widget([
                  'model' => $model,
                  'attribute' => 'order_date',
                  'options' => [
                      'id' => 'requisition-order_date',
                      'placeholder' => 'เลือกวันที่',
                      'class' => 'form-control',
                      'value' => $model->order_date ? AppHelper::convertToThai($model->order_date) : '',
                  ],
              ]) ?>
            </div>
            <div class="form-group">
              <label class="form-label">แผนก (คลังย่อย) <span class="req">*</span></label>
              <?= Html::activeDropDownList($model, 'sub_warehouse_id', $subWarehouses, [
                  'id' => 'sub-warehouse-id',
                  'prompt' => '-- เลือกแผนก --',
                  'class' => 'form-select',
              ]) ?>
            </div>
          </div>

          <div class="fg">
            <div class="form-group">
              <label class="form-label">ผู้เบิก</label>
              <input class="form-control" id="req-requester-display" value="<?= Html::encode($requesterName) ?>" placeholder="ดึงจากผู้ใช้ที่ล็อกอินอัตโนมัติ" readonly>
              <div class="form-text">ระบบจะบันทึกผู้เบิกเป็นผู้ใช้ที่ล็อกอินโดยอัตโนมัติ</div>
            </div>
            <div class="form-group">
              <label class="form-label">คลังที่จ่ายของ <span class="req">*</span></label>
              <?= Html::activeDropDownList($model, 'main_warehouse_id', $mainWarehouses ?: $warehouseList, [
                  'id' => 'main-warehouse-id',
                  'prompt' => '-- เลือกคลังที่จ่ายของ --',
                  'class' => 'form-select',
              ]) ?>
            </div>
          </div>

          <div class="fg full">
            <div class="form-group">
              <label class="form-label">ผู้อนุมัติ (หัวหน้า) — ดึงจาก<a href="<?= Url::to(['/hr/organization/diagram']) ?>" target="_blank" style="color:var(--primary);"> ผังโครงสร้างองค์กร <i class="bi bi-box-arrow-up-right"></i></a></label>
              <input type="hidden" name="approver_name" id="approver_name" value="<?= Html::encode($approverSig['name']) ?>">
              <input type="hidden" name="approver_position" id="approver_position" value="<?= Html::encode($approverSig['position']) ?>">
              <?= Select2::widget([
                  'name' => 'approver_emp_id',
                  'value' => $approverEmpId,
                  'initValueText' => $approverSig['name'] ?: '— เลือกพนักงาน —',
                  'options' => ['placeholder' => 'พิมพ์ชื่อเพื่อค้นหา...', 'id' => 'approver_emp_id_select'],
                  'pluginEvents' => [
                      'select2:select' => new JsExpression('function(e) {
                          var d = $(this).select2("data")[0];
                          if (d && d.id) {
                              $("#approver_name").val(d.fullname || "");
                              $("#approver_position").val(d.position_name || d.position_name_text || "");
                          }
                      }'),
                      'select2:unselect' => new JsExpression('function() {
                          $("#approver_name").val("");
                          $("#approver_position").val("");
                      }'),
                  ],
                  'pluginOptions' => [
                      'allowClear' => true,
                      'minimumInputLength' => 0,
                      'ajax' => [
                          'url' => Url::to(['/depdrop/employee-by-id']),
                          'dataType' => 'json',
                          'delay' => 250,
                          'data' => new JsExpression('function(params) { return {q: params.term || "", page: params.page || 1}; }'),
                          'processResults' => new JsExpression($resultsJs),
                          'cache' => true,
                      ],
                      'escapeMarkup' => new JsExpression('function(m) { return m; }'),
                      'templateSelection' => new JsExpression('function(item) { return item.fullname || item.text || item.id || ""; }'),
                      'templateResult' => new JsExpression('formatRepo'),
                  ],
              ]) ?>
              <div class="form-text">ตำแหน่งจะดึงจากระบบพนักงานอัตโนมัติ</div>
            </div>
          </div>

          <div class="fs-title"><i class="bi bi-bullseye"></i> วัตถุประสงค์</div>
          <div class="fg full">
            <div class="form-group">
              <label class="form-label">วัตถุประสงค์ในการเบิก <span class="req">*</span></label>
              <textarea name="issue_reason" id="issue_reason" class="form-control" rows="2" placeholder="เช่น เติมสต็อกประจำเดือน, ใช้บริการผู้ป่วย, ใช้งานซ่อม..."><?= Html::encode($model->getIssueReason()) ?></textarea>
              <div class="reason-chips">
                <?php
                $reasonOptions = [
                    'ใช้ในงานประจำ',
                    'ซ่อมบำรุง',
                    'เติมสต็อกคลังย่อย',
                    'โครงการ/กิจกรรม',
                    'เบิกทดแทนของเสียหาย',
                ];
                foreach ($reasonOptions as $text):
                ?>
                <span class="reason-chip issue-reason-option" data-reason="<?= Html::encode($text) ?>"><?= Html::encode($text) ?></span>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="panel">
        <div class="panel-head">
          <div class="panel-title"><i class="bi bi-list-ul"></i> รายการพัสดุ (เพิ่มได้หลายรายการ)</div>
        </div>
        <div class="panel-body">
          <div style="overflow-x:auto;">
            <table class="items-table" id="item-table">
              <thead>
                <tr>
                  <th style="width:30px;" class="center">#</th>
                  <th>เลือกพัสดุ <span class="req">*</span></th>
                  <th class="right" style="width:90px;">คลังกลาง</th>
                  <th class="right" style="width:100px;">จำนวน</th>
                  <th style="width:70px;">หน่วย</th>
                  <th style="width:36px;"></th>
                </tr>
              </thead>
              <tbody>
              <?php
              if (!$model->isNewRecord && !empty($model->stockDetails)) {
                  foreach ($model->stockDetails as $i => $detail) {
                      $unitText = $detail->item ? ($detail->item->getUnitName() ?: '-') : '-';
                      echo '<tr class="item-row">';
                      echo '<td class="center" style="color:var(--text-3);font-weight:600;">' . ($i + 1) . '</td>';
                      echo '<td><input type="hidden" name="StockDetail[' . $i . '][item_code]" value="' . Html::encode($detail->item_code) . '"><span class="item-name-display">' . Html::encode($detail->item->item_name ?? $detail->item_code) . '</span></td>';
                      echo '<td class="right"><span class="stock-hint balance-cell">—</span></td>';
                      echo '<td><input type="number" name="StockDetail[' . $i . '][qty]" class="qty-input" min="0.01" step="0.01" value="' . (float)$detail->qty . '" required placeholder="0.00"></td>';
                      echo '<td class="unit-cell" style="color:var(--text-3);font-size:12.5px;">' . Html::encode($unitText) . '</td>';
                      echo '<td class="center"><button type="button" class="btn-rm remove-item"><i class="bi bi-trash3"></i></button></td>';
                      echo '</tr>';
                  }
              }
              ?>
              </tbody>
            </table>
          </div>
          <button type="button" class="btn-add" id="add-item"><i class="bi bi-plus-lg"></i> เพิ่มรายการ</button>
        </div>
      </div>

      <div class="action-bar">
        <?= Html::a('ยกเลิก', ['index'], ['class' => 'sw-btn-outline']) ?>
        <div style="display:flex;gap:8px;">
          <?= Html::submitButton('<i class="bi bi-check-lg"></i> ยืนยัน ส่งคำขอเบิก', ['class' => 'sw-btn-success']) ?>
        </div>
      </div>
    </div>

    <aside>
      <div class="panel">
        <div class="panel-head"><div class="panel-title"><i class="bi bi-bar-chart-fill"></i> สรุปใบเบิก</div></div>
        <div class="panel-body">
          <div class="summary-card">
            <div class="summary-label">จำนวนรายการรวม</div>
            <div class="summary-val" id="sum-items-total">0</div>
          </div>
          <div class="info-row"><span class="info-row-label">จำนวนรายการ</span><span class="info-row-val" id="sum-items">0</span></div>
          <div class="info-row"><span class="info-row-label">ผู้เบิก</span><span class="info-row-val" id="sum-by"><?= Html::encode($requesterName ?: '—') ?></span></div>
          <div class="info-row"><span class="info-row-label">แผนก</span><span class="info-row-val" id="sum-dept">—</span></div>
          <div class="info-row"><span class="info-row-label">คลังต้นทาง</span><span class="info-row-val" id="sum-main">—</span></div>
          <div class="info-row"><span class="info-row-label">ประเภท</span><span class="info-row-val">รับเข้า (จากคลังกลาง)</span></div>
        </div>
      </div>
    </aside>
  </div>

<?php ActiveForm::end(); ?>

</div>

<table class="d-none">
    <tbody id="row-template">
        <tr class="item-row">
            <td class="center" style="color:var(--text-3);font-weight:600;"></td>
            <td>
                <select name="StockDetail[{idx}][item_code]" class="item-select-ajax" required></select>
            </td>
            <td class="right"><span class="stock-hint balance-cell">—</span></td>
            <td>
                <input type="number" name="StockDetail[{idx}][qty]" class="qty-input" min="0.01" step="0.01" required placeholder="0.00">
            </td>
            <td class="unit-cell" style="color:var(--text-3);font-size:12.5px;">—</td>
            <td class="center">
                <button type="button" class="btn-rm remove-item"><i class="bi bi-trash3"></i></button>
            </td>
        </tr>
    </tbody>
    <tbody id="row-template-prefilled">
        <tr class="item-row">
            <td class="center" style="color:var(--text-3);font-weight:600;"></td>
            <td><input type="hidden" name="StockDetail[{idx}][item_code]" value="{item_code}"><span class="item-name-display">{item_name}</span></td>
            <td class="right"><span class="stock-hint balance-cell">—</span></td>
            <td><input type="number" name="StockDetail[{idx}][qty]" class="qty-input" min="0.01" step="0.01" value="{qty}" required placeholder="0.00"></td>
            <td class="unit-cell" style="color:var(--text-3);font-size:12.5px;">{unit_name}</td>
            <td class="center"><button type="button" class="btn-rm remove-item"><i class="bi bi-trash3"></i></button></td>
        </tr>
    </tbody>
</table>

<?php
\app\assets\TomSelectAsset::register($this);

$getItemInWhUrl = Url::to(['/inventory-v2/stock-item/get-items-by-warehouse']);
$itemsBelowMaxUrl = Url::to(['/inventory-v2/requisition/items-below-max']);
$initialIdx = !$model->isNewRecord && !empty($model->stockDetails) ? count($model->stockDetails) : 0;

$script = <<< JS
let idx = {$initialIdx};

function reindexRows() {
    \$('#item-table tbody tr').each(function(i){
        \$(this).find('td').first().text(i + 1);
    });
}

function updateSummary() {
    var n = \$('#item-table tbody tr').length;
    \$('#sum-items').text(n);
    \$('#sum-items-total').text(n);
    \$('#sum-dept').text(\$('#sub-warehouse-id option:selected').text().replace(/^--.*--\$/,'') || '—');
    \$('#sum-main').text(\$('#main-warehouse-id option:selected').text().replace(/^--.*--\$/,'') || '—');
}

function getSelectedItemCodes(excludeRow) {
    var codes = [];
    \$('#item-table tbody tr').each(function() {
        if (excludeRow && this !== excludeRow[0]) {
            var v = \$(this).find('select[name*="[item_code]"]').val()
                  || \$(this).find('input[name*="[item_code]"]').val();
            if (v) codes.push(v);
        }
    });
    return codes;
}

function initItemSelect(elementId) {
    return new TomSelect('#' + elementId, {
        dropdownParent: document.body,
        valueField: 'item_code',
        labelField: 'item_name',
        searchField: ['item_name', 'item_code'],
        placeholder: 'พิมพ์ชื่อหรือรหัสวัสดุ...',
        preload: true,
        onDropdownOpen: function() {
            var wrapper = this.wrapper;
            var dropdown = this.dropdown;
            if (!wrapper || !dropdown) return;
            var rect = wrapper.getBoundingClientRect();
            dropdown.style.position = 'fixed';
            dropdown.style.left = rect.left + 'px';
            dropdown.style.top = rect.bottom + 'px';
            dropdown.style.width = Math.max(rect.width, 280) + 'px';
            dropdown.style.minWidth = rect.width + 'px';
            dropdown.classList.add('requisition-item-dropdown');
        },
        load: function(query, callback) {
            var currentWhId = \$('#main-warehouse-id').val();
            if (!currentWhId) return callback();
            var excludeRow = \$(this.input).closest('tr');
            var selectedCodes = getSelectedItemCodes(excludeRow);
            var q = (typeof query === 'string') ? query : '';
            q = q.replace(/^["'\s]+|["'\s]+\$/g, '');
            var url = '$getItemInWhUrl' + '?warehouse_id=' + currentWhId + '&q=' + encodeURIComponent(q);
            fetch(url)
                .then(function(response) {
                    if (!response.ok) return [];
                    var ct = response.headers.get('Content-Type') || '';
                    if (ct.indexOf('json') === -1) return [];
                    return response.json();
                })
                .then(function(json) {
                    var list = Array.isArray(json) ? json : (json && (json.results || json.data)) || [];
                    list = list.filter(function(item) {
                        return item && item.item_code && selectedCodes.indexOf(item.item_code) === -1;
                    });
                    callback(list);
                })
                .catch(function() { callback([]); });
        },
        render: {
            option: function(item, escape) {
                var unit = (item.unit_name && item.unit_name !== '-') ? ' <span style="color:var(--text-3);">(' + escape(item.unit_name) + ')</span>' : '';
                return '<div class="py-1"><div style="font-weight:600;">' + escape(item.item_name) + unit + '</div><small style="color:var(--text-3);">รหัส: ' + escape(item.item_code) + '</small></div>';
            }
        },
        onChange: function(value) {
            if (value) {
                var opt = this.options[value];
                var unitText = (opt && opt.unit_name) ? opt.unit_name : '—';
                var bal = (opt && (opt.balance_qty != null)) ? Number(opt.balance_qty).toLocaleString('th-TH') : '0';
                var row = \$(this.wrapper).closest('tr');
                row.find('.unit-cell').text(unitText);
                row.find('.balance-cell').text(bal);
                row.find('.qty-input').focus();
            }
        }
    });
}

function addItem() {
    var whId = \$('#main-warehouse-id').val();
    if (!whId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('คำเตือน', 'กรุณาเลือกคลังที่จ่ายของก่อนเพิ่มรายการ', 'warning');
        }
        \$('#main-warehouse-id').addClass('is-invalid').focus();
        return null;
    }
    var template = \$('#row-template').html();
    var rowHtml = template.replace(/{idx}/g, idx);
    var newRow = \$(rowHtml);
    \$('#item-table tbody').append(newRow);
    var selectId = 'select-item-' + idx;
    newRow.find('.item-select-ajax').attr('id', selectId);
    var ts = initItemSelect(selectId);
    idx++;
    reindexRows();
    updateSummary();
    return ts;
}

\$(document).on('click', '#add-item', function(e) {
    e.preventDefault();
    var ts = addItem();
    if (ts) ts.open();
});

\$(document).on('keydown', '.qty-input', function(e) {
    if (e.which === 13) {
        e.preventDefault();
        var ts = addItem();
        if (ts) ts.open();
    }
});

function loadBelowMaxAndAddToTable() {
    var whId = \$('#main-warehouse-id').val();
    var subId = \$('#sub-warehouse-id').val();
    if (!whId || !subId) return;
    var url = '$itemsBelowMaxUrl'.replace(/\/\$/, '') + '?warehouse_id=' + whId + '&sub_warehouse_id=' + encodeURIComponent(subId);
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(list) {
            if (!list || list.length === 0) return;
            var added = 0, updated = 0;
            list.forEach(function(row) {
                var code = (row.item_code || '').toString();
                var existing = \$('#item-table tbody tr').filter(function() {
                    var el = \$(this).find('input[name*="[item_code]"], select[name*="[item_code]"]');
                    return el.length && (el.val() === code || (el.find('option:selected').val() === code));
                });
                if (existing.length) {
                    existing.find('.qty-input').val(row.qty_to_reach_max);
                    updated++;
                } else {
                    var template = \$('#row-template-prefilled').html();
                    var html = template
                        .replace(/{idx}/g, idx)
                        .replace(/{item_code}/g, (row.item_code || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'))
                        .replace(/{item_name}/g, (row.item_name || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'))
                        .replace(/{unit_name}/g, (row.unit_name || '—').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'))
                        .replace(/{qty}/g, row.qty_to_reach_max);
                    \$('#item-table tbody').append(html);
                    idx++;
                    added++;
                }
            });
            reindexRows();
            updateSummary();
            if (added || updated) {
                Swal.fire('โหลดรายการต่ำกว่า Max แล้ว', 'เพิ่ม ' + added + ' รายการ, อัปเดตจำนวน ' + updated + ' รายการ', 'success', { timer: 2000 });
            }
        })
        .catch(function() {});
}

\$('#main-warehouse-id, #sub-warehouse-id').on('change', function() {
    \$(this).removeClass('is-invalid');
    updateSummary();
    if (\$(this).attr('id') === 'main-warehouse-id' && \$('#item-table tbody tr').length > 0) {
        Swal.fire({
            title: 'ยืนยันการเปลี่ยนคลัง?',
            text: "รายการวัสดุเดิมจะถูกล้างออกทั้งหมด เนื่องจากวัสดุถูกจำกัดตามคลัง",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ตกลง',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                \$('#item-table tbody').empty();
                idx = 0;
                reindexRows();
                updateSummary();
                loadBelowMaxAndAddToTable();
            }
        });
    } else {
        loadBelowMaxAndAddToTable();
    }
});

\$(document).on('click', '.remove-item', function() {
    \$(this).closest('tr').remove();
    reindexRows();
    updateSummary();
});

\$(document).on('click', '.issue-reason-option', function(e) {
    e.preventDefault();
    var reason = \$(this).data('reason');
    if (reason) \$('#issue_reason').val(reason);
});

\$('#requisition-form').on('beforeSubmit', function(e) {
    let form = \$(this);
    if (\$('#item-table tbody tr').length === 0) {
        Swal.fire('คำเตือน', 'กรุณาเพิ่มรายการวัสดุอย่างน้อย 1 รายการ', 'warning');
        return false;
    }
    Swal.fire({
        title: 'ส่งใบขอเบิก?',
        text: "ยืนยันการส่งข้อมูลไปยังคลังที่เลือก",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ยืนยัน',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return \$.post(form.attr('action'), form.serialize())
                .done(function(res) { return res; });
        }
    }).then((result) => {
        if (result.isConfirmed && result.value && result.value.success) {
            Swal.fire('สำเร็จ', 'บันทึกข้อมูลเรียบร้อย', 'success')
                .then(() => { window.location.href = result.value.redirect; });
        } else if (result.value) {
            Swal.fire('ผิดพลาด', result.value.message, 'error');
        }
    });
    return false;
});

updateSummary();
JS;
$this->registerJs($script);
?>
