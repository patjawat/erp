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
                            'placeholder' => 'วัน/เดือน/พ.ศ.',
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
                $inconsistentCount = 0;
                $issueItemCodes = array_values(array_unique(array_map(static fn($d) => (string) $d->item_code, $model->stockDetails)));
                $balanceByItem = [];
                if (!empty($issueItemCodes)) {
                    foreach ((new \yii\db\Query())
                        ->select(['item_code', 'qty' => new \yii\db\Expression('SUM(balance_qty)')])
                        ->from(\app\modules\inventoryV2\models\StockBalance::tableName())
                        ->where(['warehouse_id' => $model->main_warehouse_id, 'item_code' => $issueItemCodes])
                        ->groupBy('item_code')
                        ->all() as $balanceRow) {
                        $balanceByItem[(string) $balanceRow['item_code']] = (float) $balanceRow['qty'];
                    }
                }
                foreach ($model->stockDetails as $index => $detail) {
                    $availableLots = \app\modules\inventoryV2\models\StockDetail::find()
                        ->joinWith('stockOrder')
                        ->where(['stock_detail.item_code' => $detail->item_code])
                        ->andWhere(['stock_order.status' => \app\modules\inventoryV2\models\StockOrder::STATUS_CONFIRMED])
                        ->andWhere(['or',
                            ['and',
                                ['stock_order.main_warehouse_id' => $model->main_warehouse_id],
                                ['or',
                                    ['stock_order.order_type' => \app\modules\inventoryV2\models\StockOrder::ORDER_TYPE_IN],
                                    ['and',
                                        ['stock_order.order_type' => \app\modules\inventoryV2\models\StockOrder::ORDER_TYPE_ADJUST],
                                        ['>', 'stock_detail.qty', 0],
                                    ],
                                ],
                            ],
                            ['and',
                                ['stock_order.order_type' => [
                                    \app\modules\inventoryV2\models\StockOrder::ORDER_TYPE_TRANSFER,
                                    \app\modules\inventoryV2\models\StockOrder::ORDER_TYPE_OUT,
                                ]],
                                ['stock_order.sub_warehouse_id' => $model->main_warehouse_id],
                                ['>', 'stock_detail.qty', 0],
                            ],
                        ])
                        ->andWhere(['>', 'stock_detail.remain_qty', 0])
                        ->orderBy(['stock_order.order_date' => SORT_ASC, 'stock_detail.id' => SORT_ASC])
                        ->all();
                    $totalAvailable = 0;
                    foreach ($availableLots as $lotIn) {
                        $totalAvailable += (float) $lotIn->remain_qty;
                    }
                    $noLotInWarehouse = empty($availableLots);
                    $balanceQty = (float) ($balanceByItem[(string) $detail->item_code] ?? 0);
                    $isDataInconsistent = $balanceQty > 0.0001 && $totalAvailable <= 0.0001;
                    $notEnoughQty = !$noLotInWarehouse && $totalAvailable < (float) $detail->qty;
                    $isInsufficient = $noLotInWarehouse || $notEnoughQty;
                    if ($isDataInconsistent) {
                        $inconsistentCount++;
                    }
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
                        'balanceQty' => $balanceQty,
                        'isDataInconsistent' => $isDataInconsistent,
                    ];
                }
                ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-1"></i> รายการพัสดุ</h6>
                    <?php if ($insufficientCount > 0): ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>รายการที่ไม่พอจ่าย: <?= $insufficientCount ?> รายการ</span>
                    <?php endif; ?>
                    <?php if ($inconsistentCount > 0): ?>
                    <span class="badge bg-danger-subtle text-danger-emphasis"><i class="bi bi-database-exclamation me-1"></i>ข้อมูลสต็อกไม่ตรงกัน: <?= $inconsistentCount ?> รายการ</span>
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
                                <th width="25%">FIFO อัตโนมัติ (คลังหลัก)</th>
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
                                    $isDataInconsistent = $row['isDataInconsistent'] ?? false;
                                    $balanceQty = $row['balanceQty'] ?? 0;
                                    $fifoTotalQty = 0.0;
                                    $fifoTotalValue = 0.0;
                                    $fifoLotSummary = [];
                                    $fifoLotValues = [];
                                    foreach ($availableLots as $lotIn) {
                                        $lotNo = (string) $lotIn->lot_number;
                                        $lotQty = (float) $lotIn->remain_qty;
                                        $fifoTotalQty += $lotQty;
                                        $fifoTotalValue += $lotQty * (float) $lotIn->unit_price;
                                        $fifoLotSummary[$lotNo] = ($fifoLotSummary[$lotNo] ?? 0) + $lotQty;
                                        $fifoLotValues[$lotNo] = ($fifoLotValues[$lotNo] ?? 0) + ($lotQty * (float) $lotIn->unit_price);
                                    }
                                    $fifoAvgPrice = $fifoTotalQty > 0 ? $fifoTotalValue / $fifoTotalQty : 0;
                                    $fifoPlan = [];
                                    foreach ($fifoLotSummary as $lotNo => $lotQty) {
                                        $fifoPlan[] = [
                                            'lot_number' => $lotNo,
                                            'remain_qty' => $lotQty,
                                            'unit_price' => $lotQty > 0 ? $fifoLotValues[$lotNo] / $lotQty : 0,
                                        ];
                                    }
                                    $firstLotQty = !empty($fifoPlan) ? (float) $fifoPlan[0]['remain_qty'] : 0.0;
                                    $requiresMultiLot = !$isInsufficient && (float) $detail->qty > $firstLotQty + 0.000001;
                                ?>
                                <tr class="item-row <?= $isInsufficient ? 'table-warning insufficient-row' : ($requiresMultiLot ? 'table-info multi-lot-row' : '') ?>" data-index="<?= $index ?>" data-insufficient="<?= $isInsufficient ? '1' : '0' ?>" data-inconsistent="<?= $isDataInconsistent ? '1' : '0' ?>" data-first-lot-stock="<?= $firstLotQty ?>" data-total-stock="<?= $fifoTotalQty ?>">
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
                                               value="<?= $detail->qty ?>" min="0" step="1" <?= $canProcess ? '' : 'readonly' ?>>
                                    </td>
                                    <td>
                                        <?php if ($isDataInconsistent): ?>
                                        <a class="badge bg-danger-subtle text-danger-emphasis text-decoration-none me-1" href="<?= \yii\helpers\Url::to(['/inventory-v2/stock-health/index', 'warehouse_id' => $model->main_warehouse_id, 'search' => $detail->item_code]) ?>" target="_blank" title="ยอดสรุปมี <?= number_format($balanceQty, 2) ?> แต่ไม่พบ Lot ที่จ่ายได้ เปิดผลตรวจสุขภาพสต็อก">
                                            <i class="bi bi-database-exclamation me-1"></i>ข้อมูลสต็อกไม่ตรงกัน
                                        </a>
                                        <?php endif; ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis mb-1 insufficient-badge <?= $isInsufficient ? '' : 'd-none' ?>"><i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>ยอดรวมทุก Lot ไม่พอจ่าย</span>
                                        <span class="badge bg-info-subtle text-info-emphasis mb-1 multi-lot-badge <?= $requiresMultiLot ? '' : 'd-none' ?>">
                                            <i class="bi bi-layers me-1" aria-hidden="true"></i>Lot แรกไม่พอ · ตัดต่อ Lot ถัดไป
                                        </span>
                                        <select name="Issue[<?= $index ?>][lot_number]" class="form-select <?= $isInsufficient ? 'border-danger' : 'border-success' ?> lot-selector" <?= $canProcess ? '' : 'disabled' ?>>
                                            <?php if (empty($availableLots)): ?>
                                                <option value="">— ไม่มีในคลังหลัก / ไม่มี Lot ให้จ่าย —</option>
                                            <?php else: ?>
                                                <option value="AUTO_FIFO" data-stock="<?= $fifoTotalQty ?>" data-price="<?= $fifoAvgPrice ?>" data-fifo-plan="<?= Html::encode(json_encode($fifoPlan, JSON_UNESCAPED_UNICODE)) ?>">
                                                    FIFO อัตโนมัติ · <?= count($fifoLotSummary) ?> Lot · รวม <?= number_format($fifoTotalQty, 2) ?>
                                                </option>
                                            <?php endif; ?>
                                        </select>
                                        <?php if ($fifoLotSummary): ?>
                                            <small class="text-muted d-block mt-1"><?= Html::encode(implode(' → ', array_map(static fn($lot, $qty) => $lot . ' (' . number_format($qty, 2) . ')', array_keys($fifoLotSummary), $fifoLotSummary))) ?></small>
                                        <?php endif; ?>
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
// URL สำหรับดึง Lot เมื่อเลือกสินค้าใหม่
$getLotUrl = Url::to(['get-available-lots', 'warehouse_id' => $model->main_warehouse_id]);
$indexUrlJson = json_encode(Url::to(['index']));
// เตรียมข้อมูลสินค้าสำหรับ Tom-select (categorise table ใช้ code/title — alias ให้ JS ใช้ value/text)
$items = \app\modules\inventoryV2\models\StockItem::find()
    ->select(['code as value', 'title as text'])
    ->asArray()
    ->all();

$itemsJson = json_encode($items);
$js = <<< JS
$(document).ready(function() {
    let itemIndex = $('.item-row').length;
    const issueIndexUrl = $indexUrlJson;
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
                    const total = data.reduce((sum, lot) => sum + Number(lot.remain_qty || 0), 0);
                    const value = data.reduce((sum, lot) => sum + Number(lot.remain_qty || 0) * Number(lot.unit_price || 0), 0);
                    const lots = new Set(data.map(lot => lot.lot_number)).size;
                    const firstLotNumber = String(data[0].lot_number || '');
                    const firstLotStock = data.filter(lot => String(lot.lot_number || '') === firstLotNumber)
                        .reduce((sum, lot) => sum + Number(lot.remain_qty || 0), 0);
                    lotSelect.closest('.item-row').attr('data-first-lot-stock', firstLotStock);
                    lotSelect.closest('.item-row').attr('data-total-stock', total);
                    const plan = JSON.stringify(data.map(lot => ({lot_number: lot.lot_number, remain_qty: Number(lot.remain_qty || 0), unit_price: Number(lot.unit_price || 0)})));
                    lotSelect.append($('<option>', {value: 'AUTO_FIFO', text: `FIFO อัตโนมัติ · \${lots} Lot · รวม \${total}`})
                        .attr({'data-stock': total, 'data-price': total > 0 ? value / total : 0, 'data-fifo-plan': plan}));
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
                <input type="number" name="Issue[\${itemIndex}][qty_issued]" class="form-control text-center fw-bold border-primary qty-issued" value="1" min="0" step="1">
            </td>
            <td>
                <span class="badge bg-warning-subtle text-warning-emphasis mb-1 insufficient-badge d-none"><i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>ยอดรวมทุก Lot ไม่พอจ่าย</span>
                <span class="badge bg-info-subtle text-info-emphasis mb-1 multi-lot-badge d-none"><i class="bi bi-layers me-1" aria-hidden="true"></i>Lot แรกไม่พอ · ตัดต่อ Lot ถัดไป</span>
                <select id="lot-select-\${itemIndex}" name="Issue[\${itemIndex}][lot_number]" class="form-select border-success lot-selector" required>
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
                if (row.attr('data-inconsistent') !== '1') {
                    const firstLotStock = Number(row.attr('data-first-lot-stock') || 0);
                    const totalStock = Number(row.attr('data-total-stock') || option.data('stock') || 0);
                    const exceedsStock = qty > totalStock + 0.000001;
                    const needsMultipleLots = !exceedsStock && qty > firstLotStock + 0.000001;
                    row.removeClass('table-warning insufficient-row table-info multi-lot-row');
                    row.toggleClass('table-warning insufficient-row', exceedsStock);
                    row.toggleClass('table-info multi-lot-row', needsMultipleLots);
                    row.find('.insufficient-badge').toggleClass('d-none', !exceedsStock);
                    row.find('.multi-lot-badge').toggleClass('d-none', !needsMultipleLots);
                    row.attr('data-invalid-issue', exceedsStock ? '1' : '0');
                    row.find('.lot-selector').toggleClass('border-danger', exceedsStock)
                        .toggleClass('border-success', !exceedsStock);
                }
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
                    const total = data.reduce((sum, lot) => sum + Number(lot.remain_qty || 0), 0);
                    const value = data.reduce((sum, lot) => sum + Number(lot.remain_qty || 0) * Number(lot.unit_price || 0), 0);
                    const lots = new Set(data.map(lot => lot.lot_number)).size;
                    const firstLotNumber = String(data[0].lot_number || '');
                    const firstLotStock = data.filter(lot => String(lot.lot_number || '') === firstLotNumber)
                        .reduce((sum, lot) => sum + Number(lot.remain_qty || 0), 0);
                    lotSelect.closest('.item-row').attr('data-first-lot-stock', firstLotStock);
                    lotSelect.closest('.item-row').attr('data-total-stock', total);
                    const plan = JSON.stringify(data.map(lot => ({lot_number: lot.lot_number, remain_qty: Number(lot.remain_qty || 0), unit_price: Number(lot.unit_price || 0)})));
                    lotSelect.append($('<option>', {value: 'AUTO_FIFO', text: `FIFO อัตโนมัติ · \${lots} Lot · รวม \${total}`})
                        .attr({'data-stock': total, 'data-price': total > 0 ? value / total : 0, 'data-fifo-plan': plan}));
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

    function safeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function buildFifoPreview(rows) {
        const previews = [];
        rows.each(function() {
            const row = $(this);
            let remaining = Number(row.find('.qty-issued').val() || 0);
            const option = row.find('.lot-selector option:selected');
            let plan = [];
            try { plan = JSON.parse(option.attr('data-fifo-plan') || '[]'); } catch (e) { plan = []; }
            const grouped = [];
            plan.forEach(function(source) {
                const lot = String(source.lot_number || '-');
                let target = grouped.find(entry => entry.lot_number === lot);
                if (!target) {
                    target = {lot_number: lot, remain_qty: 0};
                    grouped.push(target);
                }
                target.remain_qty += Number(source.remain_qty || 0);
            });
            const slices = [];
            grouped.forEach(function(source) {
                if (remaining <= 0) return;
                const take = Math.min(remaining, source.remain_qty);
                if (take > 0) slices.push({lot_number: source.lot_number, qty: take});
                remaining -= take;
            });
            const itemName = $.trim(row.find('td:nth-child(2) strong').first().text())
                || $.trim(row.find('.ts-select option:selected').text()) || 'รายการพัสดุ';
            previews.push({item_name: itemName, slices: slices, shortage: Math.max(0, remaining)});
        });
        return previews;
    }

    function fifoPreviewHtml(previews, confirmed) {
        const rows = previews.map(function(item) {
            const lots = (item.lots || item.slices || []).map(function(lot) {
                return '<span class="badge bg-body-tertiary text-body border me-1">Lot ' + safeHtml(lot.lot_number) + ' = ' + safeHtml(Number(lot.qty).toLocaleString()) + '</span>';
            }).join('');
            const shortage = item.shortage > 0 ? '<div class="text-danger small mt-1">ขาดอีก ' + safeHtml(item.shortage) + '</div>' : '';
            return '<div class="text-start border rounded p-2 mb-2"><strong>' + safeHtml(item.item_name || item.item_code) + '</strong><div class="mt-1">' + lots + '</div>' + shortage + '</div>';
        }).join('');
        return '<div class="small text-muted mb-2">' + (confirmed ? 'ผลการตัด Lot จริง' : 'แผนการตัด Lot ตาม FIFO') + '</div>' + rows;
    }

    $('#btnSubmitIssue').click(function() {
        const activeRows = $('#issueTable .item-row').filter(function() {
            const qtyInput = $(this).find('.qty-issued');
            return !qtyInput.prop('disabled') && ((parseFloat(qtyInput.val()) || 0) > 0);
        });

        if (!activeRows.length) {
            Swal.fire('แจ้งเตือน', 'กรุณาระบุรายการที่ต้องการจ่ายอย่างน้อย 1 รายการ', 'warning');
            return;
        }

        calculateTotal();
        const invalidRows = activeRows.filter(function() { return $(this).attr('data-invalid-issue') === '1'; });
        if (invalidRows.length) {
            Swal.fire('ยังบันทึกไม่ได้', 'มีรายการที่จำนวนจ่ายเกินยอดรวมทุก Lot กรุณาแก้แถวสีเหลืองก่อน', 'warning');
            invalidRows.first().find('.qty-issued').trigger('focus');
            return;
        }

        let missingLot = false;
        activeRows.each(function() {
            const lotSelect = $(this).find('.lot-selector');
            if (!lotSelect.val()) {
                missingLot = true;
                return false;
            }
        });

        if (missingLot) {
            Swal.fire('แจ้งเตือน', 'กรุณาเลือก Lot สำหรับรายการที่ต้องการจ่ายให้ครบ', 'warning');
            return;
        }

        const fifoPreview = buildFifoPreview(activeRows);
        const multiLotPreview = fifoPreview.filter(item => item.slices.length > 1);
        Swal.fire({
            title: 'ยืนยันการบันทึกการจ่าย?',
            html: multiLotPreview.length
                ? '<div class="alert alert-warning text-start py-2"><strong>พบรายการที่ต้องตัดมากกว่า 1 Lot</strong></div>' + fifoPreviewHtml(multiLotPreview, false) + '<div class="small text-muted">ระบบจะคำนวณใหม่ภายใน Transaction และ Rollback ทั้งใบหากยอดไม่พอ</div>'
                : '<div>รายการทั้งหมดตัดจาก Lot เดียวต่อรายการ</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                const submitBtn = $('#btnSubmitIssue').prop('disabled', true);
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: $('#issue-process-form').serialize(),
                    dataType: 'json'
                }).done(function(res) {
                    if (res && res.success) {
                        const multiLotResult = (res.fifo_allocations || []).filter(item => (item.lots || []).length > 1);
                        const successOptions = multiLotResult.length ? {
                            icon: 'success',
                            title: 'บันทึกการจ่ายสำเร็จ',
                            html: '<div class="alert alert-info text-start py-2"><strong>ผลการตัดข้าม Lot</strong></div>' + fifoPreviewHtml(multiLotResult, true),
                            confirmButtonText: 'ตกลง'
                        } : {
                            icon: 'success',
                            title: 'บันทึกการจ่ายสำเร็จ',
                            text: res.message || 'บันทึกการจ่ายเรียบร้อยแล้ว',
                            confirmButtonText: 'ตกลง'
                        };
                        Swal.fire(successOptions).then(() => {
                            window.location.href = issueIndexUrl;
                        });
                    } else {
                        Swal.fire('ผิดพลาด', (res && res.message) || 'บันทึกไม่สำเร็จ', 'error');
                    }
                }).fail(function(xhr) {
                    let message = (xhr.responseJSON && xhr.responseJSON.message)
                        || xhr.responseText
                        || 'เกิดข้อผิดพลาดในการบันทึก';
                    if (message.length > 400) {
                        message = 'เกิดข้อผิดพลาดในการบันทึก กรุณาลองใหม่อีกครั้งหรือติดต่อผู้ดูแลระบบ';
                    }
                    Swal.fire('ผิดพลาด', message, 'error');
                }).always(function() {
                    submitBtn.prop('disabled', false);
                });
            }
        });
    });
});
JS;
$this->registerJS($js, View::POS_READY);
?>
