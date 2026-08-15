<?php

use yii\helpers\Html;
use app\modules\finance\models\FinancePayable;
use app\modules\finance\models\FinancePayableReview;

$this->title = $model->payable_no;
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนคุมเจ้าหนี้', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo 'ตรวจสอบข้อมูลและประวัติการอนุมัติก่อนเข้าสู่ขั้นตอนจ่ายเงิน';
$this->endBlock();
$this->beginBlock('page-action');
echo Html::a('<i class="bi bi-list me-1" aria-hidden="true"></i>ทะเบียนเจ้าหนี้', ['index'], ['class' => 'btn btn-outline-secondary']);
$this->endBlock();
?>

<div class="row g-3">
    <div class="col-xl-8">
        <?php if (in_array($model->status, [FinancePayable::STATUS_DRAFT, FinancePayable::STATUS_NEEDS_REVISION], true)): ?>
            <section class="alert alert-info d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" aria-label="ขั้นตอนถัดไป">
                <div class="d-flex gap-2 align-items-start">
                    <i class="bi bi-send-check mt-1" aria-hidden="true"></i>
                    <div><strong>พร้อมส่งตรวจอนุมัติ</strong><div>ยืนยันข้อมูลผู้ขาย ใบแจ้งหนี้ ยอดเงิน และวันครบกำหนดก่อนส่ง</div></div>
                </div>
                <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                    <?= Html::a('<i class="bi bi-pencil me-1" aria-hidden="true"></i>แก้ไขร่าง', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
                    <?= Html::beginForm(['submit', 'id' => $model->id], 'post') ?>
                    <?= Html::submitButton('<i class="bi bi-send me-1" aria-hidden="true"></i>ส่งตรวจอนุมัติ', ['class' => 'btn btn-primary']) ?>
                    <?= Html::endForm() ?>
                </div>
            </section>
        <?php elseif ($model->status === FinancePayable::STATUS_PENDING_APPROVAL): ?>
            <section class="card border shadow-sm mb-3" aria-labelledby="approval-heading">
                <div class="card-header bg-body"><h5 class="mb-0" id="approval-heading">ตรวจอนุมัติรายการ</h5></div>
                <div class="card-body">
                    <p class="text-body-secondary">ตรวจเอกสารต้นทาง ผู้ขาย เลขใบแจ้งหนี้ และยอดสุทธิก่อนตัดสินใจ</p>
                    <?= Html::beginForm(['review', 'id' => $model->id], 'post') ?>
                    <label for="approval-note" class="form-label">หมายเหตุหรือสิ่งที่ต้องแก้ไข</label>
                    <?= Html::textarea('note', '', ['id' => 'approval-note', 'class' => 'form-control', 'rows' => 3, 'placeholder' => 'จำเป็นเมื่อส่งกลับแก้ไข']) ?>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <?= Html::submitButton('<i class="bi bi-check-circle me-1" aria-hidden="true"></i>อนุมัติเข้าทะเบียน', [
                            'class' => 'btn btn-success',
                            'name' => 'decision',
                            'value' => FinancePayableReview::DECISION_APPROVE,
                        ]) ?>
                        <?= Html::submitButton('<i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>ส่งกลับแก้ไข', [
                            'class' => 'btn btn-outline-danger',
                            'name' => 'decision',
                            'value' => FinancePayableReview::DECISION_REQUEST_REVISION,
                        ]) ?>
                    </div>
                    <?= Html::endForm() ?>
                </div>
            </section>
        <?php elseif ($model->status === FinancePayable::STATUS_APPROVED): ?>
            <div class="alert alert-success d-flex gap-2 align-items-start">
                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                <span>รายการนี้อนุมัติเข้าทะเบียนเจ้าหนี้แล้ว แต่ยังไม่สร้างรายการบัญชี ฎีกา หรือแผนจ่ายเงิน</span>
            </div>
        <?php endif; ?>

        <section class="card border shadow-sm">
            <div class="card-header bg-body d-flex justify-content-between align-items-center gap-2">
                <h5 class="mb-0">รายละเอียดเจ้าหนี้</h5>
                <span class="badge <?= FinancePayable::statusBadgeClass($model->status) ?>"><?= Html::encode(FinancePayable::statusOptions()[$model->status]) ?></span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-body-secondary">ผู้แทนจำหน่าย</dt><dd class="col-sm-8"><?= Html::encode($model->vendor_name_snapshot) ?></dd>
                    <dt class="col-sm-4 text-body-secondary">รหัสผู้แทนจำหน่าย</dt><dd class="col-sm-8"><?= Html::encode($model->vendor_code_snapshot ?: '-') ?></dd>
                    <dt class="col-sm-4 text-body-secondary">เลขที่ใบแจ้งหนี้</dt><dd class="col-sm-8"><?= Html::encode($model->invoice_no) ?></dd>
                    <dt class="col-sm-4 text-body-secondary">วันที่ใบแจ้งหนี้</dt><dd class="col-sm-8"><?= Yii::$app->formatter->asDate($model->invoice_date, 'php:d/m/Y') ?></dd>
                    <dt class="col-sm-4 text-body-secondary">วันที่รับวางบิล</dt><dd class="col-sm-8"><?= Yii::$app->formatter->asDate($model->billing_date, 'php:d/m/Y') ?></dd>
                    <dt class="col-sm-4 text-body-secondary">วันเครดิต</dt><dd class="col-sm-8"><?= number_format($model->credit_days) ?> วัน</dd>
                    <dt class="col-sm-4 text-body-secondary">วันครบกำหนด</dt><dd class="col-sm-8 fw-semibold"><?= Yii::$app->formatter->asDate($model->due_date, 'php:d/m/Y') ?></dd>
                    <dt class="col-sm-4 text-body-secondary">เอกสารต้นทาง</dt><dd class="col-sm-8"><?= Html::a(Html::encode($model->source_document_no), ['/finance/inbox/view', 'id' => $model->finance_inbox_id]) ?></dd>
                </dl>
            </div>
        </section>

        <section class="card border shadow-sm mt-3" aria-labelledby="review-history-heading">
            <div class="card-header bg-body"><h5 class="mb-0" id="review-history-heading">ประวัติการตรวจอนุมัติ</h5></div>
            <div class="list-group list-group-flush">
                <?php if (!$model->reviews): ?>
                    <div class="list-group-item py-4 text-center text-body-secondary">ยังไม่มีการส่งตรวจอนุมัติ</div>
                <?php else: ?>
                    <?php foreach ($model->reviews as $review): ?>
                        <div class="list-group-item py-3">
                            <div class="d-flex justify-content-between gap-3 flex-wrap">
                                <strong><?= Html::encode(FinancePayableReview::decisionOptions()[$review->decision] ?? $review->decision) ?></strong>
                                <time class="small text-body-secondary"><?= Yii::$app->formatter->asDatetime($review->created_at, 'php:d/m/Y H:i') ?></time>
                            </div>
                            <div class="small text-body-secondary mt-1"><?= Html::encode($review->note ?: 'ไม่มีหมายเหตุ') ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <div class="col-xl-4">
        <section class="card border shadow-sm">
            <div class="card-header bg-body"><h5 class="mb-0">สรุปยอด</h5></div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-body-secondary">ยอดหนี้</span><strong><?= Yii::$app->formatter->asDecimal($model->gross_amount, 2) ?></strong></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-body-secondary">ภาษีหัก ณ ที่จ่าย</span><span><?= Yii::$app->formatter->asDecimal($model->withholding_tax_amount, 2) ?></span></div>
                <div class="d-flex justify-content-between pt-3"><span>ยอดสุทธิประมาณการ</span><strong><?= Yii::$app->formatter->asDecimal($model->net_amount, 2) ?></strong></div>
            </div>
        </section>
    </div>
</div>
