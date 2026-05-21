<?php

use yii\helpers\Html;
use yii\web\View;
use kartik\select2\Select2;
use kartik\form\ActiveForm;
use app\widgets\datepicker\DatepickerThai;
use app\modules\filemanager\components\FileManagerHelper;
use app\components\UserHelper;
use app\modules\purchaseV2\models\PurchaseRequest;
use app\modules\purchaseV2\models\PurchaseRequestItem;
use app\modules\purchaseV2\models\PurchaseRequestApproval;
use app\modules\purchaseV2\services\PurchaseWorkflowService;

/** @var PurchaseRequest $model */
$title = $title ?? 'ฟอร์มคำขอ';

$rows = [];
foreach ($items as $item) {
    if ($item instanceof PurchaseRequestItem) {
        $rows[] = [
            'item_type' => $item->item_type,
            'item_code' => $item->item_code,
            'item_name' => $item->item_name,
            'detail' => $item->detail,
            'unit_name' => $item->unit_name,
            'qty' => $item->qty,
            'unit_price' => $item->unit_price,
        ];
    } elseif (is_array($item)) {
        $rows[] = $item;
    }
}
if (empty($rows)) {
    $rows = [[]];
}

$workflowPreview = PurchaseWorkflowService::previewApprovers($model, UserHelper::GetEmployee());
$approvalProgress = (int) $model->approvalProgress();
$budgetUsage = (int) $model->budgetUsagePercent();

?>

<?php $form = ActiveForm::begin([
    'id' => 'purchase-request-form',
    'options' => ['class' => 'd-grid gap-4'],
]); ?>

<?= Html::hiddenInput('PurchaseRequest[ref]', $model->ref) ?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
            <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                        <?= Html::encode($model->getDisplayReference()) ?>
                    </span>
                    <?= $model->statusBadge() ?>
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-semibold">
                        <?= Html::encode($model->requestTypeLabel()) ?>
                    </span>
                    <?php if (!empty($model->legacy_ref)): ?>
                        <span class="badge bg-light text-secondary border rounded-pill px-3 py-2">
                            นำเข้า: <?= Html::encode($model->legacy_ref) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <h3 class="fw-bold mb-2"><?= Html::encode($title) ?></h3>
                <p class="text-muted mb-0">จัดข้อมูลคำขอแบบ one-page workflow เพื่อให้กรอกเร็ว อ่านง่าย และส่งต่ออนุมัติได้ทันที</p>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <span class="badge rounded-pill border bg-light text-muted px-3 py-2">
                        <i data-lucide="notepad-text" class="me-1"></i> สร้างร่างในหน้าเดียว
                    </span>
                    <span class="badge rounded-pill border bg-light text-muted px-3 py-2">
                        <i data-lucide="badge-check" class="me-1"></i> ส่งอนุมัติจากปุ่มเดียว
                    </span>
                    <span class="badge rounded-pill border bg-light text-muted px-3 py-2">
                        <i data-lucide="file-up" class="me-1"></i> แนบไฟล์ได้ทันที
                    </span>
                </div>

                <div class="mt-4">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>ความคืบหน้ากระบวนการ</span>
                        <span><?= number_format($approvalProgress, 0) ?>%</span>
                    </div>
                    <div class="progress rounded-pill" style="height: .65rem;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $approvalProgress ?>%;" aria-valuenow="<?= $approvalProgress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>

            <div class="text-lg-end">
                <div class="p-4 rounded-4 bg-body-tertiary">
                    <div class="text-muted small">ยอดรวมสุทธิ</div>
                    <div class="display-6 fw-bold text-primary mb-1" id="summary-grand-total"><?= number_format((float) $model->grand_total, 2) ?></div>
                    <div class="text-muted small">งบประมาณ <?= number_format((float) $model->budget_amount, 2) ?></div>
                </div>
                <div class="row g-2 mt-3">
                    <div class="col-12 col-sm-6 col-lg-12">
                        <div class="p-3 rounded-4 bg-body-tertiary h-100">
                            <div class="text-muted small">รายการ</div>
                            <div class="fw-semibold"><span id="summary-item-count"><?= number_format(count($rows), 0) ?></span> รายการ</div>
                            <div class="text-muted small">งบใช้ไป <span id="summary-budget-usage"><?= number_format($budgetUsage, 0) ?></span>%</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-12">
                        <div class="p-3 rounded-4 bg-body-tertiary h-100">
                            <div class="text-muted small">สถานะปัจจุบัน</div>
                            <div class="fw-semibold"><?= Html::encode($model->statusMeta()['label'] ?? 'แบบร่าง') ?></div>
                            <div class="text-muted small">บันทึกเป็นร่างหรือส่งอนุมัติได้</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($model->legacy_ref)): ?>
            <div class="alert alert-info bg-primary bg-opacity-10 border-0 rounded-4 mt-4 mb-0">
                เอกสารนี้มีการเชื่อมโยงกับข้อมูลจากระบบเดิม สามารถ trace กลับไปยังแหล่งที่มาได้
            </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <a href="#req-form-info" class="btn btn-sm btn-outline-primary rounded-3 fw-semibold">ข้อมูลคำขอ</a>
            <a href="#req-form-requester" class="btn btn-sm btn-outline-primary rounded-3 fw-semibold">ผู้ขอ / หน่วยงาน</a>
            <a href="#req-form-budget" class="btn btn-sm btn-outline-primary rounded-3 fw-semibold">งบประมาณ</a>
            <a href="#req-form-doc-flow" class="btn btn-sm btn-outline-primary rounded-3 fw-semibold">เลขที่อ้างอิง</a>
            <a href="#req-form-items" class="btn btn-sm btn-outline-primary rounded-3 fw-semibold">รายการ</a>
            <a href="#req-form-files" class="btn btn-sm btn-outline-primary rounded-3 fw-semibold">ไฟล์แนบ</a>
            <a href="#req-form-approval" class="btn btn-sm btn-outline-primary rounded-3 fw-semibold">อนุมัติ</a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4" id="req-form-info">
            <div class="card-header bg-white border-bottom px-4 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <div>
                    <h5 class="mb-0 fw-bold">ข้อมูลคำขอ</h5>
                    <div class="text-muted small">เลขที่เอกสาร เรื่อง วันที่ และภาพรวมของคำขอ</div>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                    <?= Html::encode($model->getDisplayReference()) ?>
                </span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <?= $form->field($model, 'request_no')->textInput([
                            'class' => 'form-control rounded-3',
                            'readonly' => true,
                        ])->label('เลขที่คำขอ') ?>
                    </div>
                    <div class="col-12 col-md-3">
                        <?= $form->field($model, 'request_date')->widget(DatepickerThai::class, [
                            'options' => ['placeholder' => 'เลือกวันที่'],
                        ])->label('วันที่คำขอ') ?>
                    </div>
                    <div class="col-12 col-md-3">
                        <?= $form->field($model, 'request_type')->dropDownList(PurchaseRequest::requestTypeOptions(), [
                            'class' => 'form-select rounded-3',
                        ])->label('ประเภทจัดซื้อ') ?>
                    </div>
                    <div class="col-12 col-md-3">
                        <?= $form->field($model, 'budget_year')->textInput([
                            'class' => 'form-control rounded-3',
                            'placeholder' => 'ปีงบประมาณ',
                        ])->label('ปีงบประมาณ') ?>
                    </div>
                    <div class="col-12">
                        <?= $form->field($model, 'request_title')->textInput([
                            'class' => 'form-control rounded-3',
                            'placeholder' => 'เช่น ขอซื้อครุภัณฑ์ / ขอจ้างบริการ',
                        ])->label('เรื่อง') ?>
                    </div>
                    <div class="col-12">
                        <?= $form->field($model, 'summary')->textarea([
                            'rows' => 4,
                            'class' => 'form-control rounded-3',
                            'placeholder' => 'อธิบายความจำเป็นโดยย่อ',
                        ])->label('รายละเอียด / ความจำเป็น') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4" id="req-form-requester">
            <div class="card-header bg-white border-bottom px-4 py-3">
                <h5 class="mb-0 fw-bold">ผู้ขอ / หน่วยงาน / คู่ค้า</h5>
                <div class="text-muted small">ข้อมูลบุคคลและหน่วยงานที่เกี่ยวข้องกับคำขอ</div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-lg-4">
                        <?= $form->field($model, 'requester_emp_id')->widget(Select2::class, [
                            'data' => PurchaseRequest::listRequesters(),
                            'theme' => Select2::THEME_KRAJEE_BS5,
                            'options' => ['placeholder' => 'เลือกผู้ขอ'],
                            'pluginOptions' => ['allowClear' => true, 'dropdownParent' => '#main-modal'],
                        ])->label('ผู้ขอ') ?>
                    </div>
                    <div class="col-12 col-lg-4">
                        <?= $form->field($model, 'department_id')->widget(Select2::class, [
                            'data' => PurchaseRequest::listDepartments(),
                            'theme' => Select2::THEME_KRAJEE_BS5,
                            'options' => ['placeholder' => 'เลือกหน่วยงาน'],
                            'pluginOptions' => ['allowClear' => true, 'dropdownParent' => '#main-modal'],
                        ])->label('หน่วยงาน') ?>
                    </div>
                    <div class="col-12 col-lg-4">
                        <?= $form->field($model, 'vendor_id')->widget(Select2::class, [
                            'data' => PurchaseRequest::listVendors(),
                            'theme' => Select2::THEME_KRAJEE_BS5,
                            'options' => ['placeholder' => 'เลือกผู้ขาย/ผู้รับจ้าง'],
                            'pluginOptions' => ['allowClear' => true, 'dropdownParent' => '#main-modal'],
                        ])->label('ผู้ขาย / ผู้รับจ้าง') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4" id="req-form-budget">
            <div class="card-header bg-white border-bottom px-4 py-3">
                <h5 class="mb-0 fw-bold">งบประมาณ</h5>
                <div class="text-muted small">กำหนดวงเงิน ประเภทงบ และรูปแบบ VAT</div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-lg-3">
                        <?= $form->field($model, 'budget_type_code')->widget(Select2::class, [
                            'data' => PurchaseRequest::listBudgetTypes(),
                            'theme' => Select2::THEME_KRAJEE_BS5,
                            'options' => ['placeholder' => 'ประเภทงบ'],
                            'pluginOptions' => ['allowClear' => true, 'dropdownParent' => '#main-modal'],
                        ])->label('ประเภทงบ') ?>
                    </div>
                    <div class="col-12 col-lg-3">
                        <?= $form->field($model, 'budget_amount')->textInput([
                            'class' => 'form-control rounded-3',
                            'placeholder' => 'วงเงินงบประมาณ',
                            'inputmode' => 'decimal',
                        ])->label('วงเงินงบประมาณ') ?>
                    </div>
                    <div class="col-12 col-lg-3">
                        <?= $form->field($model, 'vat_type')->dropDownList(PurchaseRequest::vatTypeOptions(), [
                            'class' => 'form-select rounded-3',
                        ])->label('VAT') ?>
                    </div>
                    <div class="col-12 col-lg-3">
                        <?= $form->field($model, 'discount_amount')->textInput([
                            'class' => 'form-control rounded-3',
                            'placeholder' => 'ส่วนลด',
                            'inputmode' => 'decimal',
                        ])->label('ส่วนลด') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4" id="req-form-doc-flow">
            <div class="card-header bg-white border-bottom px-4 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <div>
                    <h5 class="mb-0 fw-bold">เลขที่อ้างอิงในแต่ละขั้น</h5>
                    <div class="text-muted small">ระบุเลขอ้างอิงสำหรับการติดตาม PR / PQ / PO / GR และ trace ย้อนกลับได้</div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <?= $form->field($model, 'pr_number')->textInput([
                            'class' => 'form-control rounded-3',
                            'placeholder' => 'เลขที่ขอซื้อ',
                        ])->label('PR') ?>
                    </div>
                    <div class="col-12 col-md-3">
                        <?= $form->field($model, 'pq_number')->textInput([
                            'class' => 'form-control rounded-3',
                            'placeholder' => 'เลขทะเบียนคุม',
                        ])->label('PQ') ?>
                    </div>
                    <div class="col-12 col-md-3">
                        <?= $form->field($model, 'po_number')->textInput([
                            'class' => 'form-control rounded-3',
                            'placeholder' => 'เลขที่สั่งซื้อ',
                        ])->label('PO') ?>
                    </div>
                    <div class="col-12 col-md-3">
                        <?= $form->field($model, 'gr_number')->textInput([
                            'class' => 'form-control rounded-3',
                            'placeholder' => 'เลขที่ตรวจรับ',
                        ])->label('GR') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4" id="req-form-items">
            <div class="card-header bg-white border-bottom px-4 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <div>
                    <h5 class="mb-0 fw-bold">รายการพัสดุ / ครุภัณฑ์</h5>
                    <div class="text-muted small">เพิ่มทีละรายการแบบการ์ด อ่านง่ายบนมือถือและ desktop</div>
                </div>
                <button type="button" class="btn btn-outline-primary rounded-3 fw-semibold" id="add-item-row">
                    <i data-lucide="circle-plus" class="me-1"></i> เพิ่มรายการ
                </button>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-3" id="purchase-item-body">
                    <?php foreach ($rows as $index => $row): ?>
                        <?php
                        $row = is_array($row) ? $row : [];
                        $itemType = $row['item_type'] ?? 'consumable';
                        ?>
                        <div class="card border rounded-4 shadow-sm purchase-item-row">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                    <div>
                                        <div class="fw-semibold">รายการที่ <?= number_format($index + 1, 0) ?></div>
                                        <div class="text-muted small">กรอกข้อมูลสินค้า/บริการพร้อมราคาต่อหน่วย</div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-3 remove-item-row">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </div>

                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-md-3">
                                        <label class="form-label fw-semibold">ประเภท</label>
                                        <select name="PurchaseRequestItem[<?= $index ?>][item_type]" class="form-select rounded-3">
                                            <?php foreach (PurchaseRequestItem::itemTypeOptions() as $value => $label): ?>
                                                <option value="<?= Html::encode($value) ?>" <?= $itemType === $value ? 'selected' : '' ?>>
                                                    <?= Html::encode($label) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <label class="form-label fw-semibold">รายการ</label>
                                        <input type="text" name="PurchaseRequestItem[<?= $index ?>][item_name]" value="<?= Html::encode($row['item_name'] ?? '') ?>" class="form-control rounded-3" placeholder="ชื่อรายการ">
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label fw-semibold">หน่วย</label>
                                        <input type="text" name="PurchaseRequestItem[<?= $index ?>][unit_name]" value="<?= Html::encode($row['unit_name'] ?? '') ?>" class="form-control rounded-3" placeholder="เช่น ชิ้น">
                                    </div>
                                    <div class="col-6 col-md-1">
                                        <label class="form-label fw-semibold">จำนวน</label>
                                        <input type="number" step="0.01" min="0" data-role="qty" name="PurchaseRequestItem[<?= $index ?>][qty]" value="<?= Html::encode($row['qty'] ?? 0) ?>" class="form-control rounded-3 text-end" placeholder="0">
                                    </div>
                                    <div class="col-6 col-md-1">
                                        <label class="form-label fw-semibold">ราคา</label>
                                        <input type="number" step="0.01" min="0" data-role="unit-price" name="PurchaseRequestItem[<?= $index ?>][unit_price]" value="<?= Html::encode($row['unit_price'] ?? 0) ?>" class="form-control rounded-3 text-end" placeholder="0.00">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">รายละเอียด</label>
                                        <textarea name="PurchaseRequestItem[<?= $index ?>][detail]" rows="2" class="form-control rounded-3" placeholder="รายละเอียดเพิ่มเติม"><?= Html::encode($row['detail'] ?? '') ?></textarea>
                                    </div>
                                    <div class="col-12 col-md-4 ms-md-auto">
                                        <label class="form-label fw-semibold">รวม</label>
                                        <input type="text" data-role="amount" class="form-control rounded-3 text-end bg-body-secondary" value="0.00" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4" id="req-form-files">
            <div class="card-header bg-white border-bottom px-4 py-3">
                <h5 class="mb-0 fw-bold">เอกสารแนบ</h5>
                <div class="text-muted small">อัปโหลดไฟล์ประกอบคำขอโดยใช้ ref เดียวกับเอกสารนี้</div>
            </div>
            <div class="card-body p-4">
                <div class="text-muted small mb-3">ไฟล์ปัจจุบันและเอกสารอ้างอิงจะถูกเก็บแยกตาม ref ของคำขอ</div>
                <?= FileManagerHelper::FileUpload($model->ref, 'purchase_request') ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4 sticky-top">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">สรุปคำขอ</h5>
                        <div class="text-muted small">ตรวจยอดรวมและการใช้งบก่อนส่งอนุมัติ</div>
                    </div>
                    <?= $model->statusBadge() ?>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-sm-6 col-xl-12">
                        <div class="p-3 rounded-4 bg-body-tertiary h-100">
                            <div class="text-muted small">ยอดก่อนส่วนลด</div>
                            <div class="fw-bold text-primary fs-5" id="summary-subtotal"><?= number_format((float) $model->subtotal_amount, 2) ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-12">
                        <div class="p-3 rounded-4 bg-body-tertiary h-100">
                            <div class="text-muted small">ส่วนลด</div>
                            <div class="fw-bold text-warning fs-5" id="summary-discount"><?= number_format((float) $model->discount_amount, 2) ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-12">
                        <div class="p-3 rounded-4 bg-body-tertiary h-100">
                            <div class="text-muted small">VAT</div>
                            <div class="fw-bold text-info fs-5" id="summary-vat"><?= number_format((float) $model->vat_amount, 2) ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-12">
                        <div class="p-3 rounded-4 bg-primary bg-opacity-10 h-100">
                            <div class="text-muted small">ยอดรวมสุทธิ</div>
                            <div class="fw-bold text-primary fs-5" id="summary-grand-total"><?= number_format((float) $model->grand_total, 2) ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-12">
                        <div class="p-3 rounded-4 bg-body-tertiary h-100">
                            <div class="text-muted small">วงเงินงบประมาณ</div>
                            <div class="fw-semibold" id="summary-budget-amount"><?= number_format((float) $model->budget_amount, 2) ?></div>
                            <div class="text-muted small">งบเหลือ <span id="summary-budget-left"><?= number_format(max((float) $model->budget_amount - (float) $model->grand_total, 0), 2) ?></span></div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>การใช้งบประมาณ</span>
                        <span id="summary-budget-usage-text"><?= number_format((int) $budgetUsage, 0) ?>%</span>
                    </div>
                    <div class="progress rounded-pill" style="height: .65rem;">
                        <div class="progress-bar bg-primary" id="summary-budget-progress" role="progressbar" style="width: <?= (int) $budgetUsage ?>%;" aria-valuenow="<?= (int) $budgetUsage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <?= Html::submitButton('<i data-lucide="save" class="me-1"></i> บันทึกร่าง', [
                        'class' => 'btn btn-outline-primary rounded-3 fw-semibold',
                        'name' => 'save_action',
                        'value' => 'draft',
                    ]) ?>
                    <?= Html::submitButton('<i data-lucide="send" class="me-1"></i> ส่งอนุมัติ', [
                        'class' => 'btn btn-primary rounded-3 fw-semibold',
                        'name' => 'save_action',
                        'value' => 'submit',
                    ]) ?>
                </div>

                <div class="alert alert-primary bg-primary bg-opacity-10 border-0 rounded-4 mt-3 mb-0">
                    <div class="fw-semibold mb-1">ขั้นถัดไป</div>
                    <div class="small text-body-secondary">บันทึกร่างได้ทุกครั้ง และกดส่งอนุมัติเมื่อข้อมูลครบถ้วน</div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4" id="req-form-approval">
            <div class="card-header bg-white border-bottom px-4 py-3">
                <h5 class="mb-0 fw-bold">ผู้เกี่ยวข้อง / การอนุมัติ</h5>
                <div class="text-muted small">แสดงผู้อนุมัติที่ระบบคาดการณ์ไว้ล่วงหน้า</div>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-3">
                    <?php foreach ($workflowPreview as $step): ?>
                        <?php
                        $stepStatus = $step['status'] ?? PurchaseRequestApproval::STATUS_NONE;
                        $stepMeta = PurchaseRequestApproval::statusOptions()[$stepStatus] ?? ['label' => ucfirst((string) $stepStatus), 'color' => 'secondary', 'icon' => 'circle'];
                        ?>
                        <div class="border rounded-4 p-3 bg-body-tertiary">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div class="fw-semibold"><?= Html::encode($step['role_name'] ?? 'ขั้นตอน') ?></div>
                                    <div class="text-muted small"><?= Html::encode($step['approver_name'] ?: '-') ?></div>
                                    <div class="text-muted small"><?= Html::encode($step['approver_position'] ?: '-') ?></div>
                                </div>
                                <span class="badge bg-<?= Html::encode($stepMeta['color']) ?> bg-opacity-10 text-<?= Html::encode($stepMeta['color']) ?> border border-<?= Html::encode($stepMeta['color']) ?>-subtle rounded-pill">
                                    <i data-lucide="<?= Html::encode($stepMeta['icon']) ?>" class="me-1"></i>
                                    <?= Html::encode($stepMeta['label']) ?>
                                </span>
                            </div>
                            <?php if (!empty($step['note'])): ?>
                                <div class="text-muted small"><?= Html::encode($step['note']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($workflowPreview)): ?>
                        <div class="text-center text-muted py-4">ยังไม่มีข้อมูลผู้อนุมัติ</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="purchase-item-template">
    <div class="card border rounded-4 shadow-sm purchase-item-row">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <div class="fw-semibold">รายการใหม่</div>
                    <div class="text-muted small">กรอกข้อมูลสินค้า/บริการพร้อมราคาต่อหน่วย</div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-3 remove-item-row">
                    <i data-lucide="trash-2"></i>
                </button>
            </div>

            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">ประเภท</label>
                    <select name="PurchaseRequestItem[__INDEX__][item_type]" class="form-select rounded-3">
                        <?php foreach (PurchaseRequestItem::itemTypeOptions() as $value => $label): ?>
                            <option value="<?= Html::encode($value) ?>"><?= Html::encode($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-5">
                    <label class="form-label fw-semibold">รายการ</label>
                    <input type="text" name="PurchaseRequestItem[__INDEX__][item_name]" class="form-control rounded-3" placeholder="ชื่อรายการ">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label fw-semibold">หน่วย</label>
                    <input type="text" name="PurchaseRequestItem[__INDEX__][unit_name]" class="form-control rounded-3" placeholder="เช่น ชิ้น">
                </div>
                <div class="col-6 col-md-1">
                    <label class="form-label fw-semibold">จำนวน</label>
                    <input type="number" step="0.01" min="0" data-role="qty" name="PurchaseRequestItem[__INDEX__][qty]" class="form-control rounded-3 text-end" placeholder="0">
                </div>
                <div class="col-6 col-md-1">
                    <label class="form-label fw-semibold">ราคา</label>
                    <input type="number" step="0.01" min="0" data-role="unit-price" name="PurchaseRequestItem[__INDEX__][unit_price]" class="form-control rounded-3 text-end" placeholder="0.00">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">รายละเอียด</label>
                    <textarea name="PurchaseRequestItem[__INDEX__][detail]" rows="2" class="form-control rounded-3" placeholder="รายละเอียดเพิ่มเติม"></textarea>
                </div>
                <div class="col-12 col-md-4 ms-md-auto">
                    <label class="form-label fw-semibold">รวม</label>
                    <input type="text" data-role="amount" class="form-control rounded-3 text-end bg-body-secondary" value="0.00" readonly>
                </div>
            </div>
        </div>
    </div>
</template>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
(function () {
    const body = document.getElementById('purchase-item-body');
    const template = document.getElementById('purchase-item-template');
    let itemIndex = body ? body.querySelectorAll('.purchase-item-row').length : 0;

    function money(n) {
        n = parseFloat(n || 0);
        if (isNaN(n)) {
            n = 0;
        }
        return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function parseNumber(v) {
        v = parseFloat(v || 0);
        return isNaN(v) ? 0 : v;
    }

    function fieldValue(name) {
        const field = document.querySelector('#purchase-request-form [name="PurchaseRequest[' + name + ']"]');
        return field ? field.value : '';
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = value;
        }
    }

    function recalcRow(row) {
        const qty = parseNumber(row.querySelector('[data-role="qty"]')?.value);
        const price = parseNumber(row.querySelector('[data-role="unit-price"]')?.value);
        const amountInput = row.querySelector('[data-role="amount"]');
        const amount = qty * price;
        if (amountInput) {
            amountInput.value = money(amount);
        }
        return amount;
    }

    function recalcAll() {
        let subtotal = 0;
        const rows = document.querySelectorAll('.purchase-item-row');
        rows.forEach((row) => {
            subtotal += recalcRow(row);
        });

        const discount = parseNumber(fieldValue('discount_amount'));
        const vatType = fieldValue('vat_type');
        const budgetAmount = parseNumber(fieldValue('budget_amount'));
        let base = Math.max(0, subtotal - discount);
        let vat = 0;
        let grand = base;

        if (vatType === 'EX') {
            vat = base * 0.07;
            grand = base + vat;
        } else if (vatType === 'IN') {
            vat = base - (base / 1.07);
            grand = base;
        }

        const budgetLeft = Math.max(0, budgetAmount - grand);
        const budgetUsage = budgetAmount > 0 ? Math.min(100, Math.round((grand / budgetAmount) * 100)) : 0;

        setText('summary-subtotal', money(subtotal));
        setText('summary-discount', money(discount));
        setText('summary-vat', money(vat));
        setText('summary-grand-total', money(grand));
        setText('summary-budget-amount', money(budgetAmount));
        setText('summary-budget-left', money(budgetLeft));
        setText('summary-budget-usage', budgetUsage.toString());
        setText('summary-budget-usage-text', budgetUsage.toString() + '%');
        setText('summary-item-count', rows.length.toString());

        const progress = document.getElementById('summary-budget-progress');
        if (progress) {
            progress.style.width = budgetUsage + '%';
            progress.setAttribute('aria-valuenow', budgetUsage.toString());
        }
    }

    function addRow() {
        if (!template || !body) {
            return;
        }
        const html = template.innerHTML.replaceAll('__INDEX__', itemIndex);
        itemIndex++;
        body.insertAdjacentHTML('beforeend', html);
        recalcAll();
        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
    }

    document.getElementById('add-item-row')?.addEventListener('click', function () {
        addRow();
    });

    document.addEventListener('input', function (event) {
        if (event.target.closest('#purchase-request-form')) {
            const row = event.target.closest('.purchase-item-row');
            if (row || event.target.name === 'PurchaseRequest[discount_amount]' || event.target.name === 'PurchaseRequest[budget_amount]' || event.target.name === 'PurchaseRequest[vat_type]') {
                recalcAll();
            }
        }
    });

    document.addEventListener('click', function (event) {
        if (event.target.closest('.remove-item-row')) {
            const row = event.target.closest('.purchase-item-row');
            if (row) {
                row.remove();
                recalcAll();
            }
        }
    });

    const form = $('#purchase-request-form');
    if (form.length) {
        form.on('beforeSubmit', function (e) {
            e.preventDefault();
            const currentForm = $(this);
            $.ajax({
                url: currentForm.attr('action'),
                type: 'post',
                data: currentForm.serialize(),
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        if (res.redirect) {
                            window.location.href = res.redirect;
                        }
                        return;
                    }
                    if (window.Swal) {
                        Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: res.message || 'บันทึกไม่สำเร็จ' });
                    } else {
                        alert(res.message || 'บันทึกไม่สำเร็จ');
                    }
                },
                error: function () {
                    if (window.Swal) {
                        Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: 'เกิดข้อผิดพลาดระหว่างบันทึก' });
                    } else {
                        alert('เกิดข้อผิดพลาดระหว่างบันทึก');
                    }
                }
            });
            return false;
        });
    }

    recalcAll();
    if (typeof lucide !== 'undefined' && lucide.createIcons) {
        lucide.createIcons();
    }
})();
JS;
$this->registerJs($js, View::POS_END);
?>
