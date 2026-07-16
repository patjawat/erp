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
$resultsJs = "function(data,p){p.page=p.page||1;var total=parseInt(data.total_count||0,10);return{results:data.results||[],pagination:{more:total>0&&(p.page*30)<total}};}";

/** @var \app\modules\inventoryV2\models\StockOrder $model */
/** @var array|null $ctx */

// คลังหลัก (ต้นทางที่จ่าย) และคลังย่อย (คลังที่รับของ)
// — คลังย่อยดึงจาก context ที่ controller resolve ให้แล้ว (ตามแผนกของผู้เบิก)
$mainWarehousesList = Warehouse::find()
    ->where(['warehouse_type' => 'MAIN'])
    ->andWhere(['or', ['delete' => null], ['delete' => '']])
    ->orderBy(['warehouse_name' => SORT_ASC])
    ->all();
$mainWarehouses = ArrayHelper::map($mainWarehousesList, 'id', 'warehouse_name');
$warehouseList = ArrayHelper::map(Warehouse::find()->andWhere(['or', ['delete' => null], ['delete' => '']])->orderBy(['warehouse_name' => SORT_ASC])->all(), 'id', 'warehouse_name');

// sub-warehouses: ถ้า controller ส่ง $ctx มา ใช้จาก ctx (auto), ไม่มีก็โหลดเอง (เช่นในหน้า update)
$subWarehousesList = $ctx['sub_warehouses'] ?? null;
if ($subWarehousesList === null) {
    $subWarehousesList = Warehouse::findSubWarehousesForUser(true);
}
$subWarehouses = ArrayHelper::map($subWarehousesList, 'id', 'warehouse_name');

// auto-select คลังย่อยตัวแรก ถ้ายังไม่ได้เลือกไว้
$autoSubId = $model->sub_warehouse_id ?: (!empty($subWarehousesList) ? $subWarehousesList[0]->id : null);

// ผู้เห็นชอบ — ใช้ค่าใน model ก่อน (กรณี post error/update) แล้ว fallback ไป $ctx
$approverSig = $model->getIssueSignature('approver');
$approverEmpId = $model->getIssueSignatureEmpId('approver');
if (!$approverEmpId && !empty($ctx['approver']['emp_id'])) {
    $approverEmpId = (int) $ctx['approver']['emp_id'];
    if (empty($approverSig['name'])) {
        $approverSig['name'] = $ctx['approver']['name'] ?? '';
    }
    if (empty($approverSig['position'])) {
        $approverSig['position'] = $ctx['approver']['position'] ?? '';
    }
}
?>

<div class="requisition-form">
    <?php $form = ActiveForm::begin(['id' => 'requisition-form']); ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-md-3">
                    <?= $form->field($model, 'order_no', ['labelOptions' => ['label' => 'เลขที่ใบขอเบิก']])->textInput(['readonly' => true, 'class' => 'form-control', 'placeholder' => 'REQ-AUTO']) ?>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <?= $form->field($model, 'sub_warehouse_id', ['labelOptions' => ['label' => 'คลังที่รับของ <span class="req-star">*</span>']])->dropDownList($subWarehouses, [
                        'id' => 'sub-warehouse-id',
                        'prompt' => '-- เลือกคลังที่รับของ --',
                        'class' => 'form-select',
                        'required' => true,
                        'options' => $autoSubId ? [$autoSubId => ['selected' => true]] : [],
                    ]) ?>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <?= $form->field($model, 'main_warehouse_id', ['labelOptions' => ['label' => 'คลังที่จ่ายของ <span class="req-star">*</span>']])->dropDownList($mainWarehouses ?: $warehouseList, [
                        'id' => 'main-warehouse-id',
                        'prompt' => '-- เลือกคลังที่จ่ายของ --',
                        'class' => 'form-select',
                        'required' => true,
                    ]) ?>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <?= $form->field($model, 'order_date', ['labelOptions' => ['label' => 'วันที่ขอเบิก']])->widget(\app\widgets\datepicker\DatepickerThai::class, [
                        'options' => [
                            'id' => 'requisition-order_date',
                            'placeholder' => 'เลือกวันที่',
                            'value' => $model->order_date ? AppHelper::convertToThai($model->order_date) : '',
                        ],
                    ]) ?>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <label class="form-label mb-0" for="issue_reason">เหตุผล/วัตถุประสงค์การเบิก <span class="req-star">*</span></label>
                            <div class="dropdown">
                                <button class="btn btn-light border rounded-3 dropdown-toggle btn-sm" type="button" id="dropdownIssueReason" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-list-ul me-1"></i> ตัวเลือก
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownIssueReason">
                                    <?php
                                    $reasonOptions = [
                                        'ใช้ในงานประจำ',
                                        'ซ่อมบำรุง',
                                        'เติมสต็อกคลังย่อย',
                                        'โครงการ/กิจกรรม',
                                        'เบิกทดแทนของเสียหาย',
                                        'อื่นๆ (ระบุเพิ่มเติม)',
                                    ];
                                    foreach ($reasonOptions as $text):
                                    ?>
                                    <li><a class="dropdown-item issue-reason-option" href="#" data-reason="<?= Html::encode($text) ?>"><?= Html::encode($text) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <textarea name="issue_reason" id="issue_reason" class="form-control" rows="2" placeholder="ระบุเหตุผลหรือวัตถุประสงค์ในการเบิกวัสดุ" required><?= Html::encode($model->getIssueReason()) ?></textarea>
                        <div class="form-text">เช่น เบิกเพื่อใช้ในหน่วยงาน, เติมสต็อกคลังย่อย หรือกดตัวเลือกเพื่อเลือกเหตุผลที่ใช้บ่อย</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="approver_emp_id_select">ผู้เห็นชอบ (หัวหน้า) — เลือกพนักงาน (ดึงจากผังโครงสร้างองค์กร) <span class="req-star">*</span></label>
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
                        <div class="form-text">ตำแหน่งจะดึงจากระบบพนักงานอัตโนมัติ — ค่าเริ่มต้นคือหัวหน้าหน่วยงานของผู้เบิก (สามารถเปลี่ยนได้)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm req-items-card">
        <div class="card-header req-items-head py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="req-items-title mb-0 small fw-semibold"><i class="bi bi-box-seam me-1"></i>รายการวัสดุที่ต้องการเบิก</h6>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" id="load-below-max" class="btn btn-outline-secondary btn-sm" disabled title="เลือกคลังที่จ่ายและหน่วยงานที่รับให้ครบก่อน">
                    <i class="bi bi-magic me-1"></i> เติมรายการตามเกณฑ์ Min/Max
                </button>
                <button type="button" id="add-item" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> เพิ่มวัสดุ
                </button>
            </div>
        </div>
            <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="item-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50%">รายการวัสดุ</th>
                        <th style="width: 12%" class="text-center">หน่วยนับ</th>
                        <th style="width: 23%" class="text-center">จำนวนที่ขอเบิก</th>
                        <th style="width: 15%" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                <?php
                if (!$model->isNewRecord && !empty($model->stockDetails)) {
                    foreach ($model->stockDetails as $i => $detail) {
                        $unitText = $detail->item ? ($detail->item->getUnitName() ?: '-') : '-';
                        $itemImg = $detail->item && method_exists($detail->item, 'ShowImg') ? $detail->item->ShowImg() : '';
                        $itemName = $detail->item->item_name ?? $detail->item_code;
                        echo '<tr class="item-row">';
                        echo '<td><input type="hidden" name="StockDetail[' . $i . '][item_code]" value="' . Html::encode($detail->item_code) . '">';
                        echo '<div class="d-flex align-items-center gap-2">';
                        if ($itemImg) {
                            echo '<img src="' . Html::encode($itemImg) . '" alt="" loading="lazy" class="rounded border item-thumb" onerror="this.style.display=\'none\'">';
                        }
                        echo '<div class="min-w-0">';
                        echo '<div class="item-name-display fw-semibold">' . Html::encode($itemName) . '</div>';
                        echo '<div class="small text-muted font-monospace">' . Html::encode($detail->item_code) . '</div>';
                        echo '</div></div></td>';
                        echo '<td class="text-center unit-cell text-muted small align-middle">' . Html::encode($unitText) . '</td>';
                        echo '<td><input type="number" name="StockDetail[' . $i . '][qty]" class="form-control text-center qty-input fw-bold" min="0.01" step="any" inputmode="decimal" value="' . (float)$detail->qty . '" required placeholder="0.00"></td>';
                        echo '<td class="text-center"><button type="button" class="btn btn-outline-danger border-0 remove-item" aria-label="ลบรายการ"><i class="bi bi-trash"></i></button></td>';
                        echo '</tr>';
                    }
                }
                ?>
                </tbody>
            </table>
            </div>
            <div id="item-empty-state" class="item-empty d-none">
                <div class="item-empty__icon"><i class="bi bi-inbox"></i></div>
                <div class="item-empty__title">ยังไม่มีรายการวัสดุ</div>
                <div class="item-empty__hint">กด «เพิ่มวัสดุ» เพื่อเริ่มเลือกของที่ต้องการเบิก</div>
            </div>
    </div>

    <div class="form-group mt-4 mb-5 d-flex justify-content-end gap-2">
        <?= Html::submitButton($model->isNewRecord ? '<i class="bi bi-send-fill me-1"></i> ส่งคำขอเบิก' : '<i class="bi bi-check-lg me-1"></i> บันทึกการแก้ไข', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<table class="d-none">
    <tbody id="row-template">
        <tr class="item-row">
            <td>
                <select name="StockDetail[{idx}][item_code]" class="item-select-ajax form-select" required></select>
            </td>
            <td class="text-center unit-cell text-muted small align-middle"></td>
            <td>
                <input type="number" name="StockDetail[{idx}][qty]" class="form-control text-center qty-input fw-bold" min="0.01" step="any" inputmode="decimal" required placeholder="0.00">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger border-0 remove-item" aria-label="ลบรายการ"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    </tbody>
    <tbody id="row-template-prefilled">
        <tr class="item-row">
            <td>
                <input type="hidden" name="StockDetail[{idx}][item_code]" value="{item_code}">
                <div class="d-flex align-items-center gap-2">
                    {item_img_html}
                    <div class="min-w-0">
                        <div class="item-name-display fw-semibold">{item_name}</div>
                        <div class="small text-muted font-monospace">{item_code}</div>
                    </div>
                </div>
            </td>
            <td class="text-center unit-cell text-muted small align-middle">{unit_name}</td>
            <td><input type="number" name="StockDetail[{idx}][qty]" class="form-control text-center qty-input fw-bold" min="0.01" step="any" inputmode="decimal" value="{qty}" required placeholder="0.00"></td>
            <td class="text-center"><button type="button" class="btn btn-outline-danger border-0 remove-item" aria-label="ลบรายการ"><i class="bi bi-trash"></i></button></td>
        </tr>
    </tbody>
</table>

<?php
\app\assets\TomSelectAsset::register($this);
$this->registerCss(<<<CSS
/* Design tokens — scope กับหน้านี้ (อ้างอิงมาตรฐานกลาง sub-stock/issue.php) */
.requisition-form {
    --ink-1: #1a202c;
    --ink-2: #4a5568;
    --ink-3: #718096;
    --ink-4: #a0aec0;
    --surface: #ffffff;
    --surface-2: #f7f9fc;
    --surface-3: #eef2f7;
    --line: rgba(15, 23, 42, 0.08);
    --line-strong: rgba(15, 23, 42, 0.14);
    --primary: #0d6efd;
    --danger: #b91c1c;
    --radius-sm: 8px;
    --ease: cubic-bezier(0.16, 1, 0.3, 1);
}

/* หัวการ์ดรายการวัสดุ — surface กลาง ไม่ใช้ primary เป็น decoration */
.requisition-form .req-items-head {
    background: var(--surface-2);
    border-bottom: 1px solid var(--line);
}
.requisition-form .req-items-title { color: var(--ink-1); }
.requisition-form .req-star { color: var(--danger); font-weight: 600; }

/* Empty state ของตารางรายการ */
.requisition-form .item-empty {
    text-align: center;
    padding: 3rem 1.5rem;
    color: var(--ink-3);
}
.requisition-form .item-empty__icon {
    width: 52px; height: 52px;
    margin: 0 auto 0.75rem;
    display: flex; align-items: center; justify-content: center;
    background: var(--surface-3);
    border-radius: 14px;
    font-size: 1.5rem;
    color: var(--ink-4);
}
.requisition-form .item-empty__title { color: var(--ink-2); font-weight: 600; }
.requisition-form .item-empty__hint { font-size: 0.85rem; color: var(--ink-3); margin-top: 0.15rem; }

/* dropdown ถูก append ที่ body จึงใช้ class นี้ (ไม่มี parent .requisition-form) */
.ts-dropdown.requisition-item-dropdown {
    z-index: 1060 !important;
    position: fixed !important;
    border: 1px solid rgba(15, 23, 42, 0.14) !important;
    border-radius: 0.375rem !important;
    background: #ffffff !important;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.10), 0 2px 4px rgba(15, 23, 42, 0.06) !important;
    max-height: 280px;
    overflow-y: auto;
}
.requisition-form #item-table td:first-child { overflow: visible; position: relative; }
.requisition-form #item-table .ts-wrapper { position: relative; }
.requisition-form #item-table .ts-control { min-height: 38px; }
.requisition-form #item-table .item-thumb {
    width: 42px; height: 42px; object-fit: cover; flex-shrink: 0; background: var(--surface-2);
}
.requisition-form #item-table .item-name-display { line-height: 1.25; }
.ts-dropdown.requisition-item-dropdown .ts-option-thumb {
    width: 32px; height: 32px; object-fit: cover; border-radius: 4px;
    border: 1px solid rgba(15, 23, 42, 0.10); flex-shrink: 0; background: #f7f9fc;
}

/* Motion — enter/exit ของแถวสื่อ state ไม่ใช่ decoration */
.requisition-form .item-row--new {
    animation: reqRowIn 180ms var(--ease);
}
.requisition-form .item-row--leaving {
    opacity: 0;
    transform: translateY(-4px);
    transition: opacity 160ms var(--ease), transform 160ms var(--ease);
}
@keyframes reqRowIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}
@media (prefers-reduced-motion: reduce) {
    .requisition-form .item-row--new { animation: none; }
    .requisition-form .item-row--leaving { transition: opacity 80ms linear; transform: none; }
}
CSS
);

$getItemInWhUrl = Url::to(['/inventory-v2/stock-item/get-items-by-warehouse']);
$itemsBelowMaxUrl = Url::to(['/inventory-v2/requisition/items-below-max']);
$checkStockUrl = Url::to(['/inventory-v2/requisition/check-stock-availability']);
$initialIdx = !$model->isNewRecord && !empty($model->stockDetails) ? count($model->stockDetails) : 0;

$script = <<< JS
let idx = {$initialIdx};

function escapeAttr(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// สร้าง HTML ของ td แรก (รูป + ชื่อ + รหัส) — ใช้ทั้ง prefilled และหลังเลือกจาก TomSelect
function buildItemDisplayCellHtml(rowIdx, code, name, img) {
    var imgHtml = img
        ? '<img src="' + escapeAttr(img) + '" alt="" loading="lazy" class="rounded border item-thumb" onerror="this.style.display=\\'none\\'">'
        : '';
    return '<input type="hidden" name="StockDetail[' + rowIdx + '][item_code]" value="' + escapeAttr(code) + '">'
        + '<div class="d-flex align-items-center gap-2">'
        +     imgHtml
        +     '<div class="min-w-0">'
        +         '<div class="item-name-display fw-semibold">' + escapeAttr(name) + '</div>'
        +         '<div class="small text-muted font-monospace">' + escapeAttr(code) + '</div>'
        +     '</div>'
        + '</div>';
}

// รหัสวัสดุที่ถูกเลือกแล้วในแถวอื่น (ไม่รวมแถวที่ส่งเข้า)
// หลังเลือก <select> จะถูกแทนด้วย <input type="hidden"> → ต้องเช็คทั้งคู่
function getSelectedItemCodes(excludeRow) {
    var codes = [];
    $('#item-table tbody tr').each(function() {
        if (excludeRow && this === excludeRow[0]) return;
        var v = $(this).find('select[name*="[item_code]"], input[type="hidden"][name*="[item_code]"]').val();
        if (v) codes.push(v);
    });
    return codes;
}

// 1. ฟังก์ชันสร้าง TomSelect (กรองรายการที่เลือกแล้วในแถวอื่น)
function initItemSelect(elementId) {
    var selfRef = null;
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
            var currentWhId = $('#main-warehouse-id').val();
            var currentSubWhId = $('#sub-warehouse-id').val();
            if (!currentWhId) return callback();
            if (!currentSubWhId) return callback();
            var excludeRow = $(this.input).closest('tr');
            var selectedCodes = getSelectedItemCodes(excludeRow);
            var q = (typeof query === 'string') ? query : '';
            q = q.replace(/^["'\s]+|["'\s]+$/g, '');
            var url = '$getItemInWhUrl' + '?warehouse_id=' + currentWhId + '&sub_warehouse_id=' + currentSubWhId + '&q=' + encodeURIComponent(q);
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
                var unit = (item.unit_name && item.unit_name !== '-') ? ' <span class="text-muted">(' + escape(item.unit_name) + ')</span>' : '';
                var img = item.item_img
                    ? '<img src="' + escape(item.item_img) + '" alt="" loading="lazy" class="ts-option-thumb me-2" onerror="this.style.display=\\'none\\'">'
                    : '';
                return '<div class="d-flex align-items-center py-1">' + img
                    + '<div class="min-w-0"><div class="fw-bold">' + escape(item.item_name) + unit + '</div>'
                    + '<small class="text-muted font-monospace">รหัส: ' + escape(item.item_code) + '</small></div></div>';
            }
        },
        onChange: function(value) {
            if (!value) return;
            var opt = this.options[value];
            var unitText = (opt && opt.unit_name) ? opt.unit_name : '-';
            var name = (opt && opt.item_name) ? opt.item_name : value;
            var img = (opt && opt.item_img) ? opt.item_img : '';
            var \$row = $(this.wrapper).closest('tr');
            \$row.find('.unit-cell').text(unitText);

            // อ่าน idx จาก name ของ select เพื่อใช้สร้าง hidden input ใหม่
            var selName = \$row.find('select[name*="[item_code]"]').attr('name') || '';
            var idxMatch = selName.match(/\\[(\\d+)\\]/);
            var rowIdx = idxMatch ? idxMatch[1] : '0';

            // แทน <select> ด้วย display (ภาพ + ชื่อ + รหัส) — กันชื่อซ้ำสับสน
            this.destroy();
            \$row.find('td:first').html(buildItemDisplayCellHtml(rowIdx, value, name, img));
            \$row.find('.qty-input').focus();
        }
    });
}

// สลับ empty state / thead ตามจำนวนแถวจริง (นับเฉพาะ .item-row)
function refreshItemEmptyState() {
    var hasRows = $('#item-table .item-row').length > 0;
    $('#item-empty-state').toggleClass('d-none', hasRows);
    $('#item-table thead').toggleClass('d-none', !hasRows);
}

// 2. ฟังก์ชันเพิ่มแถว (คืนค่า TomSelect ของแถวใหม่)
function addItem() {
    var whId = $('#main-warehouse-id').val();
    var subWhId = $('#sub-warehouse-id').val();
    if (!whId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('คำเตือน', 'กรุณาเลือกคลังที่จ่ายของก่อนเพิ่มรายการ', 'warning');
        }
        $('#main-warehouse-id').addClass('is-invalid').focus();
        return null;
    }
    if (!subWhId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('คำเตือน', 'กรุณาเลือกคลังที่รับของก่อนเพิ่มรายการ', 'warning');
        }
        $('#sub-warehouse-id').addClass('is-invalid').focus();
        return null;
    }

    var template = $('#row-template').html();
    var rowHtml = template.replace(/{idx}/g, idx);
    var newRow = $(rowHtml);

    $('#item-table tbody').append(newRow);
    newRow.addClass('item-row--new');
    refreshItemEmptyState();

    var selectId = 'select-item-' + idx;
    newRow.find('.item-select-ajax').attr('id', selectId);

    var ts = initItemSelect(selectId);
    idx++;
    return ts;
}

// 3. Event Listeners
$(document).on('click', '#add-item', function(e) {
    e.preventDefault();
    var ts = addItem();
    if (ts) ts.open();
});

// Enter ที่ช่องจำนวน = เพิ่มแถวใหม่ แล้วโฟกัสที่ช่องค้นหาวัสดุ
$(document).on('keydown', '.qty-input', function(e) {
    if (e.which === 13) {
        e.preventDefault();
        var ts = addItem();
        if (ts) {
            ts.open();
        }
    }
});

// เปิด/ปิดปุ่มเติมรายการตามสถานะการเลือกคลัง
function updateLoadBelowMaxButtonState() {
    var ready = !!$('#main-warehouse-id').val() && !!$('#sub-warehouse-id').val();
    $('#load-below-max').prop('disabled', !ready);
}

// โหลดรายการที่ต่ำกว่า Min/Max แล้วเพิ่มเข้าตาราง — เรียกจากปุ่ม manual เท่านั้น
// Policy: รายการที่มีอยู่แล้ว -> ข้าม (ไม่ทับจำนวนที่ผู้ใช้พิมพ์ไว้)
function loadBelowMaxAndAddToTable() {
    var whId = $('#main-warehouse-id').val();
    var subId = $('#sub-warehouse-id').val();
    if (!whId || !subId) {
        Swal.fire('คำเตือน', 'กรุณาเลือกคลังที่จ่ายของและคลังที่รับของก่อน', 'warning');
        return;
    }
    var url = '$itemsBelowMaxUrl'.replace(/\/$/, '') + '?warehouse_id=' + whId + '&sub_warehouse_id=' + encodeURIComponent(subId);

    Swal.fire({
        title: 'กำลังตรวจสอบเกณฑ์ Min/Max...',
        didOpen: function() { Swal.showLoading(); },
        allowOutsideClick: false,
        showConfirmButton: false
    });

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(list) {
            Swal.close();
            if (!list || list.length === 0) {
                Swal.fire('ไม่พบรายการ', 'ไม่มีวัสดุที่ต่ำกว่าเกณฑ์ Min/Max ในขณะนี้', 'info');
                return;
            }
            var added = 0, skipped = 0;
            list.forEach(function(row) {
                var code = (row.item_code || '').toString();
                var existing = $('#item-table tbody tr').filter(function() {
                    var el = $(this).find('input[name*="[item_code]"], select[name*="[item_code]"]');
                    return el.length && (el.val() === code || (el.find('option:selected').val() === code));
                });
                if (existing.length) {
                    skipped++;
                    return;
                }
                var imgHtml = row.item_img
                    ? '<img src="' + escapeAttr(row.item_img) + '" alt="" loading="lazy" class="rounded border item-thumb" onerror="this.style.display=\\'none\\'">'
                    : '';
                var template = $('#row-template-prefilled').html();
                var html = template
                    .replace(/{idx}/g, idx)
                    .replace(/{item_code}/g, escapeAttr(row.item_code || ''))
                    .replace(/{item_name}/g, escapeAttr(row.item_name || ''))
                    .replace(/{item_img_html}/g, imgHtml)
                    .replace(/{unit_name}/g, escapeAttr(row.unit_name || '-'))
                    .replace(/{qty}/g, row.qty_to_reach_max);
                $('#item-table tbody').append(html);
                idx++;
                added++;
            });
            refreshItemEmptyState();
            var parts = [];
            if (added) parts.push('เพิ่มใหม่ ' + added + ' รายการ');
            if (skipped) parts.push('ข้าม ' + skipped + ' รายการ (มีอยู่แล้ว — ไม่ทับจำนวนเดิม)');
            Swal.fire({
                title: added ? 'เติมรายการเสร็จสิ้น' : 'ไม่มีรายการใหม่ให้เพิ่ม',
                text: parts.join(' · '),
                icon: added ? 'success' : 'info'
            });
        })
        .catch(function() {
            Swal.close();
            Swal.fire('ผิดพลาด', 'ดึงข้อมูลไม่สำเร็จ กรุณาลองใหม่อีกครั้ง', 'error');
        });
}

// Probe ว่าคลังนี้ยังมีของให้เบิกหรือไม่ — ถ้าไม่มี แสดงเตือนและล็อกปุ่มเพิ่มวัสดุ
var warehouseHasStock = true;
function checkWarehouseStock(whId) {
    if (!whId) {
        warehouseHasStock = true;
        $('#add-item').prop('disabled', false).removeAttr('title');
        return;
    }
    var url = '$getItemInWhUrl' + '?warehouse_id=' + encodeURIComponent(whId) + '&q=';
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.ok ? r.json() : []; })
        .then(function(json) {
            var list = Array.isArray(json) ? json : (json && (json.results || json.data)) || [];
            if (list.length === 0) {
                warehouseHasStock = false;
                $('#add-item').prop('disabled', true).attr('title', 'คลังนี้ยังไม่มีของให้เบิก');
                Swal.fire({
                    title: 'คลังนี้ยังไม่มีของให้เบิก',
                    text: 'ยอดคงเหลือของคลังที่จ่ายของที่เลือกเป็น 0 ทุกรายการ กรุณาเลือกคลังอื่น หรือติดต่อผู้ดูแลคลังให้รับเข้าก่อน',
                    icon: 'warning',
                    confirmButtonText: 'รับทราบ'
                });
            } else {
                warehouseHasStock = true;
                $('#add-item').prop('disabled', false).removeAttr('title');
            }
        })
        .catch(function() {
            warehouseHasStock = true;
            $('#add-item').prop('disabled', false).removeAttr('title');
        });
}

// เปลี่ยนคลังหลัก: ยังคง confirm-then-clear แต่ไม่ auto-reload — ให้ผู้ใช้กดปุ่มเอง
$('#main-warehouse-id').on('change', function() {
    $(this).removeClass('is-invalid');
    updateLoadBelowMaxButtonState();
    var whId = $(this).val();
    checkWarehouseStock(whId);
    if ($('#item-table tbody tr').length > 0) {
        Swal.fire({
            title: 'ยืนยันการเปลี่ยนคลัง?',
            text: "รายการวัสดุเดิมจะถูกล้างออกทั้งหมด เนื่องจากวัสดุถูกจำกัดตามคลัง",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ตกลง',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#item-table tbody').empty();
                idx = 0;
                refreshItemEmptyState();
            }
        });
    }
});

// init: ถ้ามีคลังถูก preselect ไว้ตั้งแต่โหลดหน้า → probe ทันที
if ($('#main-warehouse-id').val()) {
    checkWarehouseStock($('#main-warehouse-id').val());
}

// เปลี่ยนคลังที่รับของ: แค่อัปเดตสถานะปุ่ม ไม่ trigger reload
$('#sub-warehouse-id').on('change', function() {
    $(this).removeClass('is-invalid');
    updateLoadBelowMaxButtonState();
});

$(document).on('click', '#load-below-max', function(e) {
    e.preventDefault();
    loadBelowMaxAndAddToTable();
});

// init เมื่อโหลดหน้า
updateLoadBelowMaxButtonState();
refreshItemEmptyState();

$(document).on('click', '.remove-item', function() {
    var row = $(this).closest('tr');
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) {
        row.remove();
        refreshItemEmptyState();
        return;
    }
    row.addClass('item-row--leaving');
    setTimeout(function() {
        row.remove();
        refreshItemEmptyState();
    }, 160);
});

// เลือกเหตุผลจาก dropdown แล้วใส่ในช่องเหตุผล/วัตถุประสงค์การเบิก
$(document).on('click', '.issue-reason-option', function(e) {
    e.preventDefault();
    var reason = $(this).data('reason');
    if (reason) $('#issue_reason').val(reason);
});

function escapeHtmlPreflight(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function submitRequisitionForm(form) {
    Swal.fire({
        title: 'กำลังบันทึก...',
        didOpen: function() { Swal.showLoading(); },
        allowOutsideClick: false,
        showConfirmButton: false
    });
    $.post(form.attr('action'), form.serialize())
        .done(function(res) {
            if (res && res.success) {
                Swal.fire('สำเร็จ', 'บันทึกข้อมูลเรียบร้อย', 'success')
                    .then(function() { window.location.href = res.redirect; });
            } else {
                Swal.fire('ผิดพลาด', (res && res.message) || 'บันทึกไม่สำเร็จ', 'error');
            }
        })
        .fail(function() {
            Swal.fire('ผิดพลาด', 'เชื่อมต่อระบบไม่ได้ กรุณาลองใหม่', 'error');
        });
}

// Submit Form ด้วย AJAX — preflight ตรวจยอดคลังก่อน เพื่อกัน user ส่งใบเบิกที่จ่ายไม่ได้
$('#requisition-form').on('beforeSubmit', function(e) {
    var form = $(this);

    // ตรวจช่องบังคับ 4 ช่อง: คลังที่รับของ, คลังที่จ่ายของ, เหตุผล, ผู้เห็นชอบ
    var requiredFields = [
        { sel: '#sub-warehouse-id',       label: 'คลังที่รับของ' },
        { sel: '#main-warehouse-id',      label: 'คลังที่จ่ายของ' },
        { sel: '#issue_reason',           label: 'เหตุผล/วัตถุประสงค์การเบิก' },
        { sel: '#approver_emp_id_select', label: 'ผู้เห็นชอบ (หัวหน้า)' }
    ];
    $('.is-invalid').removeClass('is-invalid');
    var missing = [];
    requiredFields.forEach(function(f) {
        var el = $(f.sel);
        var v = (el.val() || '').toString().trim();
        if (!v) {
            missing.push(f.label);
            el.addClass('is-invalid');
        }
    });
    if (missing.length) {
        Swal.fire('กรุณากรอกข้อมูลให้ครบ', 'ยังไม่ได้กรอก: ' + missing.join(', '), 'warning');
        return false;
    }

    if ($('#item-table tbody tr').length === 0) {
        Swal.fire('คำเตือน', 'กรุณาเพิ่มรายการวัสดุอย่างน้อย 1 รายการ', 'warning');
        return false;
    }

    var whId = $('#main-warehouse-id').val();
    var items = [];
    $('#item-table tbody tr').each(function() {
        var code = $(this).find('input[name*="[item_code]"], select[name*="[item_code]"]').val();
        var qty = parseFloat($(this).find('.qty-input').val()) || 0;
        if (code && qty > 0) {
            items.push({ item_code: code, qty: qty });
        }
    });

    if (!whId || items.length === 0) {
        Swal.fire('คำเตือน', 'กรุณาเลือกคลังที่จ่ายและกรอกจำนวนให้ครบ', 'warning');
        return false;
    }

    Swal.fire({
        title: 'กำลังตรวจสอบยอดคลัง...',
        didOpen: function() { Swal.showLoading(); },
        allowOutsideClick: false,
        showConfirmButton: false
    });

    $.post('$checkStockUrl', { main_warehouse_id: whId, items: items })
        .done(function(checkRes) {
            Swal.close();
            if (!checkRes || !checkRes.success) {
                Swal.fire('ผิดพลาด', (checkRes && checkRes.message) || 'ตรวจสอบยอดคลังไม่สำเร็จ', 'error');
                return;
            }

            if (!checkRes.has_issue) {
                Swal.fire({
                    title: 'ส่งใบขอเบิก?',
                    text: 'ยอดคลังพอจ่ายทุกรายการ — ยืนยันการส่ง',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยัน',
                    cancelButtonText: 'ยกเลิก'
                }).then(function(result) {
                    if (result.isConfirmed) submitRequisitionForm(form);
                });
                return;
            }

            // มีรายการที่ติด — แสดงตารางสรุป
            var statusBadge = {
                ok: '<span class="badge" style="background:rgba(21,128,61,.10);color:#15803d">พอจ่าย</span>',
                balance_only: '<span class="badge" style="background:rgba(180,83,9,.10);color:#b45309">ยอดคลังพอ Lot ไม่พร้อม</span>',
                insufficient: '<span class="badge" style="background:rgba(185,28,28,.10);color:#b91c1c">คงเหลือไม่พอ</span>',
                not_in_warehouse: '<span class="badge" style="background:rgba(185,28,28,.10);color:#b91c1c">ไม่มีในคลัง</span>'
            };
            var rows = checkRes.items.map(function(it) {
                var badge = statusBadge[it.status] || '';
                var rowClass = it.status === 'ok' ? '' : 'table-warning';
                return '<tr class="' + rowClass + '">'
                    + '<td class="text-start small">' + escapeHtmlPreflight(it.item_name) + '<div style="font-size:12px;color:#718096">' + escapeHtmlPreflight(it.item_code) + '</div></td>'
                    + '<td class="text-end small">' + it.qty_requested + '</td>'
                    + '<td class="text-end small">' + it.balance + '</td>'
                    + '<td class="text-end small">' + it.fifo_remain + '</td>'
                    + '<td class="text-center">' + badge + '</td>'
                    + '</tr>';
            }).join('');

            var html = '<div class="small text-muted mb-2 text-start">'
                + 'คลังที่จ่ายของมียอดไม่ครบ หรือ Lot ใน FIFO ไม่พอจ่าย ตรวจสอบก่อนยืนยัน:'
                + '</div>'
                + '<div class="table-responsive">'
                + '<table class="table table-sm table-bordered align-middle mb-0">'
                + '<thead class="table-light"><tr>'
                + '<th class="text-start small">รายการ</th>'
                + '<th class="text-end small">ขอเบิก</th>'
                + '<th class="text-end small">ยอดคลัง</th>'
                + '<th class="text-end small">FIFO ใช้ได้</th>'
                + '<th class="text-center small">สถานะ</th>'
                + '</tr></thead><tbody>' + rows + '</tbody></table></div>'
                + '<div class="small text-muted mt-2 text-start">'
                + '<strong>หมายเหตุ:</strong> "ยอดคลัง" คือยอดที่เห็นในรายงาน "FIFO ใช้ได้" คือยอดที่ระบบจ่ายได้จริง '
                + 'ถ้าไม่เท่ากัน อาจมีใบรับเข้าฉบับร่างที่ยังไม่ได้ confirm'
                + '</div>';

            Swal.fire({
                title: 'พบรายการที่อาจจ่ายไม่ได้',
                html: html,
                icon: 'warning',
                width: 760,
                showCancelButton: true,
                confirmButtonText: 'ยืนยันส่งต่อ',
                cancelButtonText: 'แก้ไขก่อน',
                reverseButtons: true,
                customClass: { confirmButton: 'btn btn-warning', cancelButton: 'btn btn-light' }
            }).then(function(result) {
                if (result.isConfirmed) submitRequisitionForm(form);
            });
        })
        .fail(function() {
            Swal.fire('ผิดพลาด', 'เชื่อมต่อระบบไม่ได้ กรุณาลองใหม่', 'error');
        });

    return false;
});
JS;
$this->registerJs($script);
?>
