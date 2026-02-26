<?php
use app\components\AppHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\web\JsExpression;
use kartik\select2\Select2;

// ลงทะเบียน Tom-select (ถ้ายังไม่ได้ลงใน Asset)
\app\assets\TomSelectAsset::register($this);
\app\widgets\datepicker\Assets::register($this);

$formatRepoJs = "var formatRepo=function(repo){if(repo.loading)return repo.avatar;return '<div>'+repo.avatar+'</div>';};";
$this->registerJs($formatRepoJs, View::POS_HEAD);
$employeeSelectResultsJs = "function(data,p){p.page=p.page||1;var total=data.total_count;var more=total!=null?(p.page*30)<total:false;return{results:data.results||[],pagination:{more:more}};}";
$this->title = 'ดำเนินการจ่ายพัสดุ (Issue Process) - ' . $model->order_no;
$canProcess = ($model->status === \app\modules\inventoryV2\models\StockOrder::STATUS_APPROVED);
$isConfirmed = ($model->status === \app\modules\inventoryV2\models\StockOrder::STATUS_CONFIRMED);
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-box-seam fs-4"></i>
                <h5 class="mb-0 text-white">บันทึกการจ่ายพัสดุ: <?= Html::encode($model->order_no) ?></h5>
            </div>
            <?php if ($isConfirmed): ?>
            <span class="badge text-bg-success fs-6">จ่ายพัสดุแล้ว</span>
            <?php endif; ?>
        </div>

        <div class="card-body">
            <div class="row mb-4 bg-light p-3 rounded mx-0 border">
                <div class="col-md-4">
                    <small class="text-muted d-block fw-bold" style="font-size: 0.7rem;">แผนก/ฝ่ายที่เบิก</small>
                    <strong class="text-primary"><?= Html::encode($model->subWarehouse->warehouse_name ?? '-') ?></strong>
                </div>
                <div class="col-md-4 text-center border-start border-end">
                    <small class="text-muted d-block fw-bold" style="font-size: 0.7rem;">อ้างอิง</small>
                    <strong><?= Html::encode($model->source_type ?? '-') ?></strong>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted d-block fw-bold" style="font-size: 0.7rem;">คลังต้นทาง</small>
                    <span class="badge text-bg-secondary"><?= Html::encode($model->mainWarehouse->warehouse_name ?? 'คลังหลัก') ?></span>
                </div>
            </div>

            <?php
            $sig = function ($r) use ($model) { return $model->getIssueSignature($r); };
            $companyAuthorizer = \app\modules\inventoryV2\models\StockOrder::getCompanyAuthorizer();
            $displaySig = function ($key) use ($sig, $companyAuthorizer) {
                $val = $sig($key);
                if ($key === 'authorizer' && empty($val['name']) && empty($val['position']) && (!empty($companyAuthorizer['name']) || !empty($companyAuthorizer['emp_id']))) {
                    $val = [
                        'name' => $companyAuthorizer['name'],
                        'position' => $companyAuthorizer['position'],
                        'date' => $val['date'],
                        'emp_id' => $companyAuthorizer['emp_id'] ?? null,
                    ];
                }
                return $val;
            };
            $roles = [
                'requester' => 'ผู้เบิก',
                'disbursing' => 'ผู้จ่ายพัสดุ',
                'approver' => 'ผู้เห็นชอบ (หัวหน้า/เจ้าหน้าที่คลังอนุมัติแทนได้)',
                'recipient' => 'ผู้รับวัสดุ (ระบุโดยเจ้าหน้าที่คลังตอนจะพิมพ์ใบเบิก)',
                'authorizer' => 'ผู้สั่งจ่าย (หัวหน้าเจ้าหน้าที่ — ตั้งค่าที่ ตั้งค่าองค์กร)',
            ];
            ?>
            <div class="card mb-4 border-info">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#issue-signatures-block" style="cursor: pointer;">
                    <span class="fw-bold text-dark"><i class="bi bi-person-lines-fill me-1"></i> ข้อมูลผู้ลงนามใบเบิก</span>
                    <i class="bi bi-chevron-down collapse-icon"></i>
                </div>
                <div id="issue-signatures-block" class="collapse show card-body">
                    <p class="small text-muted mb-2"><strong>ผู้รับวัสดุ</strong> — ระบุโดยเจ้าหน้าที่คลังก่อนกดพิมพ์ใบเบิก เลือกจากรายชื่อพนักงานหรือกรอกชื่อ-ตำแหน่งเอง แล้วกดบันทึก</p>
                    <form id="issue-signatures-form" method="post" action="<?= \yii\helpers\Url::to(['save-issue-signatures', 'id' => $model->id]) ?>">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="25%">บทบาท</th>
                                        <th width="25%">เลือกจากข้อมูลพนักงาน</th>
                                        <th width="25%">ชื่อ</th>
                                        <th width="25%">ตำแหน่ง</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roles as $key => $label): ?>
                                    <?php
                                    $d = $displaySig($key);
                                    $roleEmpId = $model->getIssueSignatureEmpId($key);
                                    if ($key === 'authorizer' && !$roleEmpId && !empty($d['emp_id'])) {
                                        $roleEmpId = (int) $d['emp_id'];
                                    }
                                    ?>
                                    <tr class="sig-row <?= $key === 'recipient' ? 'recipient-signature-row' : '' ?>" data-role="<?= $key ?>">
                                        <td class="align-middle">
                                            <input type="hidden" name="IssueSignatures[<?= $key ?>][date]" value="<?= Html::encode($d['date']) ?>">
                                            <input type="hidden" name="IssueSignatures[<?= $key ?>][emp_id]" class="sig-emp-id" id="issue_sig_emp_<?= $key ?>" value="<?= $roleEmpId ? (int)$roleEmpId : '' ?>">
                                            <?= Html::encode($label) ?>
                                        </td>
                                        <td class="align-middle">
                                            <?= Select2::widget([
                                                'name' => 'sig_emp_select_' . $key,
                                                'value' => $roleEmpId ?: '',
                                                'initValueText' => $d['name'] ?: '— เลือกพนักงาน —',
                                                'options' => ['placeholder' => 'พิมพ์ชื่อเพื่อค้นหา...', 'id' => 'issue_sig_select_' . $key, 'class' => 'sig-employee-select'],
                                                'pluginEvents' => [
                                                    'select2:select' => new JsExpression('function(e) {
                                                        var d = $(this).select2("data")[0];
                                                        var role = $(this).closest("tr").data("role");
                                                        if (d && d.id) {
                                                            $("#issue_sig_emp_" + role).val(d.id);
                                                            $(".sig-name[data-role=\"" + role + "\"]").val(d.fullname || "");
                                                            $(".sig-position[data-role=\"" + role + "\"]").val(d.position_name || d.position_name_text || "");
                                                        }
                                                    }'),
                                                    'select2:clear' => new JsExpression('function() {
                                                        var role = $(this).closest("tr").data("role");
                                                        $("#issue_sig_emp_" + role).val("");
                                                        $(".sig-name[data-role=\"" + role + "\"]").val("");
                                                        $(".sig-position[data-role=\"" + role + "\"]").val("");
                                                    }'),
                                                ],
                                                'pluginOptions' => [
                                                    'allowClear' => true,
                                                    'minimumInputLength' => 1,
                                                    'ajax' => [
                                                        'url' => Url::to(['/depdrop/employee-by-id']),
                                                        'dataType' => 'json',
                                                        'delay' => 250,
                                                        'data' => new JsExpression('function(params) { return {q: params.term || "", page: params.page || 1}; }'),
                                                        'processResults' => new JsExpression($employeeSelectResultsJs),
                                                        'cache' => true,
                                                    ],
                                                    'escapeMarkup' => new JsExpression('function(m) { return m; }'),
                                                    'templateSelection' => new JsExpression('function(item) { return item.fullname || item.text || item.id || ""; }'),
                                                    'templateResult' => new JsExpression('formatRepo'),
                                                ],
                                            ]) ?>
                                        </td>
                                        <td><input type="text" name="IssueSignatures[<?= $key ?>][name]" class="form-control form-control-sm sig-name" data-role="<?= $key ?>" value="<?= Html::encode($d['name']) ?>" placeholder="ชื่อ-นามสกุล หรือเลือกจากด้านซ้าย"></td>
                                        <td><input type="text" name="IssueSignatures[<?= $key ?>][position]" class="form-control form-control-sm sig-position" data-role="<?= $key ?>" value="<?= Html::encode($d['position']) ?>" placeholder="ตำแหน่ง"></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <button type="submit" id="btn-save-signatures" class="btn btn-primary btn-sm"><i class="bi bi-check-lg me-1"></i> บันทึกข้อมูลผู้ลงนาม</button>
                        </div>
                    </form>
                </div>
            </div>

            <form id="issue-process-form">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
                <?php
                $confirmedDateValue = '';
                if ($isConfirmed && $model->getDisbursementDate()) {
                    $confirmedDateValue = date('Y-m-d', $model->getDisbursementDate());
                } else {
                    $confirmedDateValue = date('Y-m-d');
                }
                $confirmedDateDisplay = $confirmedDateValue ? AppHelper::convertToThai($confirmedDateValue) : AppHelper::convertToThai(date('Y-m-d'));
                ?>
                <?php if ($canProcess): ?>
                <div class="mb-3">
                    <label for="confirmed_date" class="form-label small text-muted fw-bold">วันที่จ่าย</label>
                    <?= \app\widgets\datepicker\DatepickerThai::widget([
                        'name' => 'confirmed_date',
                        'value' => $confirmedDateDisplay,
                        'options' => [
                            'id' => 'confirmed_date',
                            'class' => 'form-control form-control-sm',
                            'style' => 'max-width: 180px;',
                            'placeholder' => 'วว/ดด/พพพพ',
                        ],
                    ]) ?>
                </div>
                <?php elseif ($isConfirmed && $confirmedDateValue): ?>
                <div class="mb-3">
                    <span class="text-muted small fw-bold">วันที่จ่าย:</span>
                    <span class="ms-2"><?= \app\components\ThaiDateHelper::formatThaiDate($confirmedDateValue) ?></span>
                </div>
                <?php endif; ?>
                <?php
                // ไม่พอจ่าย: ดูจากใบเบิกเป็นหลัก → ตรวจที่คลังหลัก ถ้าไม่มีในคลัง หรือไม่มี Lot ให้จ่าย = ไม่พอจ่าย
                $detailRows = [];
                $insufficientCount = 0;
                foreach ($model->stockDetails as $index => $detail) {
                    $availableLots = \app\modules\inventoryV2\models\StockDetail::find()
                        ->joinWith('stockOrder')
                        ->where([
                            'stock_detail.item_code' => $detail->item_code,
                            'stock_order.main_warehouse_id' => $model->main_warehouse_id,
                            'stock_order.order_type' => 'IN'
                        ])
                        ->andWhere(['>', 'remain_qty', 0])
                        ->all();
                    $totalAvailable = 0;
                    foreach ($availableLots as $lotIn) {
                        $totalAvailable += (float) $lotIn->remain_qty;
                    }
                    $noLotInWarehouse = empty($availableLots);
                    $notEnoughQty = !$noLotInWarehouse && $totalAvailable < (float) $detail->qty;
                    $isInsufficient = $noLotInWarehouse || $notEnoughQty;
                    if ($isInsufficient) {
                        $insufficientCount++;
                    }
                    $detailRows[] = [
                        'detail' => $detail,
                        'index' => $index,
                        'availableLots' => $availableLots,
                        'isInsufficient' => $isInsufficient,
                        'noLotInWarehouse' => $noLotInWarehouse,
                        'notEnoughQty' => $notEnoughQty,
                    ];
                }
                ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-1"></i> รายการพัสดุ</h6>
                    <?php if ($insufficientCount > 0): ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>รายการที่ไม่พอจ่าย: <?= $insufficientCount ?> รายการ</span>
                    <?php endif; ?>
                    <?php if ($canProcess): ?>
                    <button type="button" class="btn btn-primary btn-sm" id="btnAddItem">
                        <i class="bi bi-plus-circle-fill me-1"></i> เพิ่มพัสดุอื่นเพิ่มเติม
                    </button>
                    <?php endif; ?>
                </div>
                <p class="small text-muted mb-2">ใช้รายการจากใบเบิกเป็นหลัก — ตรวจที่คลังหลัก: ถ้าไม่มีในคลัง หรือไม่มี Lot ให้จ่าย แสดงเป็น <span class="badge bg-warning text-dark">ไม่พอจ่าย</span></p>
                <div class="table-responsive">
                    <table class="table table-hover align-middle border" id="issueTable">
                        <thead class="table-dark text-center">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%" class="text-start">รายการพัสดุ</th>
                                <th width="10%">ขอเบิก</th>
                                <th width="12%">จ่ายจริง</th>
                                <th width="25%">ตัดจาก Lot (คลังหลัก)</th>
                                <th width="12%">รวมมูลค่า</th>
                                <th width="8%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detailRows as $row): ?>
                                <?php
                                    $detail = $row['detail'];
                                    $index = $row['index'];
                                    $availableLots = $row['availableLots'];
                                    $isInsufficient = $row['isInsufficient'];
                                    $noLotInWarehouse = $row['noLotInWarehouse'];
                                    $notEnoughQty = $row['notEnoughQty'] ?? false;
                                ?>
                                <tr class="item-row <?= $isInsufficient ? 'table-warning insufficient-row' : '' ?>" data-index="<?= $index ?>" data-insufficient="<?= $isInsufficient ? '1' : '0' ?>">
                                    <td class="text-center text-muted"><?= $index + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-start gap-2">
                                            <?php if ($detail->item): ?>
                                            <div class="flex-shrink-0">
                                                <?= Html::img($detail->item->ShowImg(), [
                                                    'class' => 'rounded border',
                                                    'style' => 'width:56px;height:56px;object-fit:cover',
                                                    'alt' => Html::encode($detail->item->item_name),
                                                ]) ?>
                                            </div>
                                            <?php endif; ?>
                                            <div class="min-w-0">
                                                <strong><?= Html::encode($detail->item->item_name ?? $detail->item_code) ?></strong><br>
                                                <small class="text-muted">Code: <?= Html::encode($detail->item_code) ?></small>
                                                <?php
                                                $categoryTitle = $detail->item && $detail->item->categoryType ? Html::encode($detail->item->categoryType->title) : '—';
                                                ?>
                                                <br><small class="text-muted">ประเภท: <?= $categoryTitle ?></small>
                                            </div>
                                        </div>
                                        <input type="hidden" name="Issue[<?= $index ?>][detail_id]" value="<?= $detail->id ?>">
                                        <input type="hidden" name="Issue[<?= $index ?>][item_code]" value="<?= $detail->item_code ?>">
                                    </td>
                                    <td class="text-center fw-bold text-secondary"><?= number_format($detail->qty, 2) ?></td>
                                    <td>
                                        <input type="number" name="Issue[<?= $index ?>][qty_issued]" 
                                               class="form-control text-center fw-bold border-primary qty-issued" 
                                               value="<?= $detail->qty ?>" min="0" max="<?= $detail->qty ?>" step="0.01" <?= $canProcess ? '' : 'readonly' ?>>
                                    </td>
                                    <td>
                                        <?php if ($isInsufficient): ?>
                                        <span class="badge bg-warning text-dark me-1" title="ตรวจจากใบเบิก แล้วตรวจที่คลัง — <?= $noLotInWarehouse ? 'ไม่มีในคลัง / ไม่มี Lot ให้จ่าย' : 'ยอดในคลังไม่พอ' ?>">
                                            <i class="bi bi-exclamation-triangle me-1"></i>ไม่พอจ่าย
                                        </span>
                                        <?php endif; ?>
                                        <select name="Issue[<?= $index ?>][lot_number]" class="form-select <?= $isInsufficient ? 'border-danger' : 'border-warning' ?> lot-selector" <?= $canProcess ? '' : 'disabled' ?>>
                                            <?php if (empty($availableLots)): ?>
                                                <option value="">— ไม่มีในคลังหลัก / ไม่มี Lot ให้จ่าย —</option>
                                            <?php else: ?>
                                                <?php foreach ($availableLots as $lotIn): ?>
                                                    <option value="<?= $lotIn->lot_number ?>" data-stock="<?= $lotIn->remain_qty ?>" data-price="<?= $lotIn->unit_price ?>">
                                                        LOT: <?= $lotIn->lot_number ?> (เหลือ <?= number_format($lotIn->remain_qty, 2) ?>) [@<?= number_format($lotIn->unit_price, 2) ?>]
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </td>
                                    <td class="text-end fw-bold text-primary"><span class="row-total">0.00</span></td>
                                    <td class="text-center">
                                        <?php if ($canProcess): ?>
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-cancel-item"><i class="bi bi-trash"></i></button>
                                        <button type="button" class="btn btn-link btn-sm btn-restore-item d-none">คืน</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="5" class="text-end fw-bold">รวมทั้งสิ้น:</td>
                                <td class="text-end fw-bold text-danger fs-5" id="grand-total">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </form>

            <div class="text-end mt-4 pt-3 border-top">
                <?= Html::a('กลับ', ['index'], ['class' => 'btn btn-light border px-4 me-2']) ?>
                <?= Html::a('<i class="bi bi-printer me-1"></i> พิมพ์ใบเบิก', ['print', 'id' => $model->id], ['class' => 'btn btn-outline-secondary px-4 me-2', 'target' => '_blank']) ?>
                <?php if ($canProcess): ?>
                <button type="button" class="btn btn-success btn-lg px-5 shadow" id="btnSubmitIssue">
                    <i class="bi bi-check-all"></i> บันทึกการจ่ายพัสดุ
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// URL สำหรับดึง Lot เมื่อเลือกสินค้าใหม่ (ต้องไปสร้าง Action นี้ใน Controller)
$getLotUrl = Url::to(['get-available-lots', 'warehouse_id' => $model->main_warehouse_id]);
// URL สำหรับค้นหาสินค้า (แนะนำใช้ Select2 หรือดึงรายการสินค้ามาพักไว้)
$itemsJson = json_encode(\app\modules\inventoryV2\models\StockItem::find()->select(['item_code', 'item_name'])->asArray()->all());

$getLotUrl = Url::to(['get-available-lots', 'warehouse_id' => $model->main_warehouse_id]);
// เตรียมข้อมูลสินค้าสำหรับ Tom-select
$items = \app\modules\inventoryV2\models\StockItem::find()
    ->select(['item_code as value', 'item_name as text'])
    ->asArray()
    ->all();

$itemsJson = json_encode($items);
$js = <<< JS
$(document).ready(function() {
    let itemIndex = $('.item-row').length;
    const itemList = $itemsJson;

    // บันทึกข้อมูลผู้ลงนามแบบ AJAX + SweetAlert ปิด 2 วินาที
    $('#issue-signatures-form').on('submit', function(e) {
        e.preventDefault();
        var \$btn = $('#btn-save-signatures').prop('disabled', true);
        $.post($(this).attr('action'), $(this).serialize()).done(function(res) {
            if (res.success) {
                Swal.fire({ icon: 'success', title: res.message || 'บันทึกข้อมูลผู้ลงนามเรียบร้อยแล้ว', timer: 2000, timerProgressBar: true, showConfirmButton: false });
            } else {
                Swal.fire('ผิดพลาด', res.message || 'ไม่สามารถบันทึกได้', 'error');
            }
        }).fail(function() {
            Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการบันทึก', 'error');
        }).always(function() {
            \$btn.prop('disabled', false);
        });
    });

// ฟังก์ชันสำหรับสร้าง Tom-select ให้กับแถวใหม่
    function initTomSelect(index) {
        new TomSelect(`#select-item-\${index}`, {
            dropdownParent: document.body,
            options: itemList,
            placeholder: "พิมพ์ชื่อหรือรหัสพัสดุ...",
            allowEmptyOption: true,
            create: false,
            onChange: function(itemCode) {
                if(!itemCode) return;
                loadLots(itemCode, index);
            },
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
            }
        });
    }

    // ฟังก์ชันดึง Lot เมื่อเลือกสินค้า
    function loadLots(itemCode, index) {
        let lotSelect = $(`#lot-select-\${index}`);
        lotSelect.html('<option>กำลังโหลด...</option>').prop('disabled', true);

        $.ajax({
            url: '$getLotUrl',
            data: { item_code: itemCode },
            success: function(data) {
                lotSelect.empty();
                if (data.length > 0) {
                    data.forEach(lot => {
                        lotSelect.append(`<option value="\${lot.lot_number}" data-stock="\${lot.remain_qty}" data-price="\${lot.unit_price}">
                            LOT: \${lot.lot_number} (เหลือ \${lot.remain_qty}) [@\${lot.unit_price}]
                        </option>`);
                    });
                    lotSelect.prop('disabled', false);
                } else {
                    lotSelect.append('<option value="">ของหมดสต็อก</option>');
                }
                calculateTotal();
            }
        });
    }

    // --- กดปุ่มเพิ่มรายการใหม่ ---
    $(document).off('click', '#btnAddItem').on('click', '#btnAddItem', function(e) {
        let newRow = `
        <tr class="item-row table-info" id="row-\${itemIndex}">
            <td class="text-center text-primary fw-bold">NEW</td>
            <td>
                <select id="select-item-\${itemIndex}" name="Issue[\${itemIndex}][item_code]" class="ts-select"></select>
                <input type="hidden" name="Issue[\${itemIndex}][detail_id]" value="new">
            </td>
            <td class="text-center text-muted">-</td>
            <td>
                <input type="number" name="Issue[\${itemIndex}][qty_issued]" class="form-control text-center fw-bold border-primary qty-issued" value="1" min="0.01" step="0.01">
            </td>
            <td>
                <select id="lot-select-\${itemIndex}" name="Issue[\${itemIndex}][lot_number]" class="form-select border-warning lot-selector" required>
                    <option value="">-- เลือกพัสดุก่อน --</option>
                </select>
            </td>
            <td class="text-end fw-bold text-primary"><span class="row-total">0.00</span></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
        
        $('#issueTable tbody').append(newRow);
        initTomSelect(itemIndex); // เรียกใช้ Tom-select ทันทีที่สร้างแถว
        itemIndex++;
        calculateTotal();
    });

    // การลบแถว
    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
        calculateTotal();
    });

    function calculateTotal() {
        let grandTotal = 0;
        $('.item-row').each(function() {
            let row = $(this);
            let qtyInput = row.find('.qty-issued');
            if (!qtyInput.prop('disabled')) {
                let qty = parseFloat(qtyInput.val()) || 0;
                let option = row.find('.lot-selector option:selected');
                let price = parseFloat(option.data('price')) || 0;
                let total = qty * price;
                row.find('.row-total').text(total.toLocaleString(undefined, {minimumFractionDigits: 2}));
                grandTotal += total;
            } else {
                row.find('.row-total').text('0.00');
            }
        });
        $('#grand-total').text(grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2}));
    }

    calculateTotal();
    $(document).on('input', '.qty-issued', calculateTotal);
    $(document).on('change', '.lot-selector', calculateTotal);

    // --- เมื่อเลือกพัสดุใหม่ ให้ไปดึง Lot มาโชว์ ---
    $(document).on('change', '.new-item-select', function() {
        let itemCode = $(this).val();
        let row = $(this).closest('tr');
        let lotSelect = row.find('.lot-selector');

        if (!itemCode) return;

        $.ajax({
            url: '$getLotUrl',
            data: { item_code: itemCode },
            success: function(data) {
                lotSelect.empty();
                if (data.length > 0) {
                    data.forEach(lot => {
                        lotSelect.append(`<option value="\${lot.lot_number}" data-stock="\${lot.remain_qty}" data-price="\${lot.unit_price}">
                            LOT: \${lot.lot_number} (เหลือ \${lot.remain_qty}) [@\${lot.unit_price}]
                        </option>`);
                    });
                    lotSelect.prop('disabled', false);
                } else {
                    lotSelect.append('<option value="">ของหมดสต็อก</option>').prop('disabled', true);
                }
                calculateTotal();
            }
        });
    });

    $(document).on('click', '.btn-remove-row', function() { $(this).closest('tr').remove(); calculateTotal(); });

    $(document).on('click', '.btn-cancel-item', function() {
        let row = $(this).closest('tr');
        row.addClass('table-danger opacity-50').find('td').css('text-decoration', 'line-through');
        row.find('.qty-issued, .lot-selector').prop('disabled', true).val(0);
        $(this).addClass('d-none');
        row.find('.btn-restore-item').removeClass('d-none');
        calculateTotal();
    });

    $(document).on('click', '.btn-restore-item', function() {
        let row = $(this).closest('tr');
        row.removeClass('table-danger opacity-50').find('td').css('text-decoration', 'none');
        row.find('.qty-issued, .lot-selector').prop('disabled', false);
        $(this).addClass('d-none');
        row.find('.btn-cancel-item').removeClass('d-none');
        calculateTotal();
    });

    $('#btnSubmitIssue').click(function() {
        Swal.fire({
            title: 'ยืนยันการบันทึกการจ่าย?',
            text: "ระบบจะตัดสต็อกและบันทึกรายการพัสดุตามหน้าจอนี้",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(window.location.href, $('#issue-process-form').serialize(), function(res) {
                    if (res.success) {
                        Swal.fire('สำเร็จ', 'บันทึกการจ่ายเรียบร้อยแล้ว', 'success').then(() => { window.location.href = 'index'; });
                    } else {
                        Swal.fire('ผิดพลาด', res.message, 'error');
                    }
                });
            }
        });
    });
});
JS;
$this->registerJS($js, View::POS_READY);
?>