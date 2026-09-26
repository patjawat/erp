<?php

use yii\helpers\Html;
use app\components\AppHelper;
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
        <?php if (in_array($model->status, [FinancePayable::STATUS_DRAFT, FinancePayable::STATUS_NEEDS_REVISION], true) && Yii::$app->user->can('financeOperate')): ?>
            <section class="alert alert-info d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" aria-label="ขั้นตอนถัดไป">
                <div class="d-flex gap-2 align-items-start">
                    <i class="bi bi-send-check mt-1" aria-hidden="true"></i>
                    <div><strong><?= $model->accounting_chart_account_id ? 'พร้อมส่งตรวจอนุมัติ' : 'ต้องเลือกบัญชีก่อนส่ง' ?></strong><div>ยืนยันข้อมูลผู้ขาย ใบแจ้งหนี้ ยอดเงิน วันครบกำหนด และบัญชีเดบิตหลักก่อนส่ง</div></div>
                </div>
                <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                    <?= Html::a('<i class="bi bi-pencil me-1" aria-hidden="true"></i>แก้ไขร่าง', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
                    <?= Html::beginForm(['submit', 'id' => $model->id], 'post') ?>
                    <?= Html::submitButton('<i class="bi bi-send me-1" aria-hidden="true"></i>ส่งตรวจอนุมัติ', ['class' => 'btn btn-primary', 'disabled' => !$model->accounting_chart_account_id]) ?>
                    <?= Html::endForm() ?>
                </div>
            </section>
        <?php elseif ($model->status === FinancePayable::STATUS_PENDING_APPROVAL && (Yii::$app->user->can('financeOperate') || Yii::$app->user->can('financeApprove'))): ?>
            <section class="card border shadow-sm mb-3" aria-labelledby="approval-heading">
                <div class="card-header bg-body"><h5 class="mb-0" id="approval-heading">ตรวจอนุมัติรายการ</h5></div>
                <div class="card-body">
                    <p class="text-body-secondary">ตรวจเอกสารต้นทาง ผู้ขาย เลขใบแจ้งหนี้ และยอดสุทธิก่อนตัดสินใจ</p>
                    <?= Html::beginForm(['review', 'id' => $model->id], 'post') ?>
                    <label for="approval-note" class="form-label">หมายเหตุหรือสิ่งที่ต้องแก้ไข</label>
                    <?= Html::textarea('note', '', ['id' => 'approval-note', 'class' => 'form-control', 'rows' => 3, 'placeholder' => 'จำเป็นเมื่อส่งกลับแก้ไข']) ?>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <?php if (Yii::$app->user->can('financeApprove')): ?>
                            <?= Html::submitButton('<i class="bi bi-check-circle me-1" aria-hidden="true"></i>อนุมัติเข้าทะเบียน', [
                                'class' => 'btn btn-success', 'name' => 'decision', 'value' => FinancePayableReview::DECISION_APPROVE,
                            ]) ?>
                        <?php endif; ?>
                        <?php if (Yii::$app->user->can('financeOperate')): ?>
                            <?= Html::submitButton('<i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>ส่งกลับแก้ไข', [
                                'class' => 'btn btn-outline-danger', 'name' => 'decision', 'value' => FinancePayableReview::DECISION_REQUEST_REVISION,
                            ]) ?>
                        <?php endif; ?>
                    </div>
                    <?= Html::endForm() ?>
                </div>
            </section>
        <?php elseif ($model->status === FinancePayable::STATUS_APPROVED): ?>
            <?php if ($model->isJournalized()): ?>
                <div class="alert alert-success d-flex gap-2 align-items-start">
                    <i class="bi bi-check2-all" aria-hidden="true"></i>
                    <div class="flex-grow-1"><strong>บัญชีลงบันทึกแล้ว</strong><div>ผ่านรายการเข้าสู่บัญชีแยกประเภทเรียบร้อย</div></div>
                </div>
            <?php elseif ($model->isSentAccounting()): ?>
                <div class="alert alert-info d-flex gap-2 align-items-start">
                    <i class="bi bi-send-check" aria-hidden="true"></i>
                    <div class="flex-grow-1"><strong>ส่งบัญชีแล้ว — รอบัญชีลงบันทึก</strong>
                        <div>ส่งเมื่อ <?= \app\modules\finance\components\ThaiDate::datetime($model->sent_accounting_at) ?> น.</div></div>
                </div>
            <?php else: ?>
                <div class="alert alert-success d-flex gap-2 align-items-start">
                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                    <div class="flex-grow-1"><strong>อนุมัติเข้าทะเบียนเจ้าหนี้แล้ว</strong><div>ตรวจสอบเอกสารให้ครบถ้วน แล้วส่งให้บัญชีลงบันทึก</div></div>
                    <?php if (Yii::$app->user->can('financeOperate')): ?>
                        <?= Html::beginForm(['send-accounting', 'id' => $model->id], 'post', ['class' => 'flex-shrink-0']) ?>
                        <?= Html::submitButton('<i class="bi bi-send me-1"></i>ส่งบัญชี', ['class' => 'btn btn-primary', 'data' => ['confirm' => 'ยืนยันส่งเจ้าหนี้รายนี้ให้บัญชีลงบันทึก?']]) ?>
                        <?= Html::endForm() ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php elseif ($model->status === FinancePayable::STATUS_PENDING_APPROVAL): ?>
            <div class="alert alert-secondary d-flex gap-2 align-items-start">
                <i class="bi bi-lock" aria-hidden="true"></i><span>รายการอยู่ระหว่างตรวจอนุมัติ คุณมีสิทธิ์ดูข้อมูลแต่ไม่มีสิทธิ์ตัดสินใจ</span>
            </div>
        <?php endif; ?>

        <section class="card border shadow-sm">
            <div class="card-header bg-body d-flex justify-content-between align-items-center gap-2">
                <h5 class="mb-0">รายละเอียดเจ้าหนี้</h5>
                <div class="d-flex align-items-center gap-2">
                    <?= Html::a('<i class="bi bi-printer me-1"></i>พิมพ์ใบอนุมัติจ่าย',
                        ['/finance/payable-doc/open', 'payable_id' => $model->id],
                        ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                    <span class="badge <?= FinancePayable::statusBadgeClass($model->status) ?>"><?= Html::encode(FinancePayable::statusOptions()[$model->status]) ?></span>
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-body-secondary">ผู้แทนจำหน่าย</dt><dd class="col-sm-8"><?= Html::encode($model->vendor_name_snapshot) ?></dd>
                    <dt class="col-sm-4 text-body-secondary">รหัสผู้แทนจำหน่าย</dt><dd class="col-sm-8"><?= Html::encode($model->vendor_code_snapshot ?: '-') ?></dd>
                    <dt class="col-sm-4 text-body-secondary">เลขที่ใบแจ้งหนี้</dt><dd class="col-sm-8"><?= Html::encode($model->invoice_no) ?></dd>
                    <dt class="col-sm-4 text-body-secondary">วันที่ใบแจ้งหนี้</dt><dd class="col-sm-8"><?= AppHelper::convertToThai($model->invoice_date) ?></dd>
                    <dt class="col-sm-4 text-body-secondary">วันที่รับวางบิล</dt><dd class="col-sm-8">
                        <?php if ($model->isBilled()): ?>
                            <?= AppHelper::convertToThai($model->billing_date) ?>
                            <?php if ($model->billing_ref): ?><span class="text-body-secondary small ms-1">(ใบวางบิล <?= Html::encode($model->billing_ref) ?>)</span><?php endif; ?>
                        <?php elseif ($model->status === FinancePayable::STATUS_APPROVED): ?>
                            <span class="badge bg-warning-subtle text-warning-emphasis">รอวางบิล</span>
                            <?php if (Yii::$app->user->can('financeOperate')): ?>
                                <?= Html::a('บันทึกรับวางบิล', ['billing', 'vendor' => $model->vendor_name_snapshot], ['class' => 'btn btn-sm btn-link p-0 ms-1']) ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-body-secondary">ประมาณการ <?= AppHelper::convertToThai($model->billing_date) ?> (ยืนยันตอนรับวางบิล)</span>
                        <?php endif; ?>
                    </dd>
                    <dt class="col-sm-4 text-body-secondary">วันเครดิต</dt><dd class="col-sm-8"><?= number_format($model->credit_days) ?> วัน</dd>
                    <dt class="col-sm-4 text-body-secondary">วันครบกำหนด</dt><dd class="col-sm-8 fw-semibold"><?= AppHelper::convertToThai($model->due_date) ?></dd>
                    <dt class="col-sm-4 text-body-secondary">เอกสารต้นทาง</dt><dd class="col-sm-8"><?= Html::a(Html::encode($model->source_document_no), ['/finance/inbox/view', 'id' => $model->finance_inbox_id]) ?></dd>
                    <dt class="col-sm-4 text-body-secondary">บัญชีเดบิตหลัก</dt><dd class="col-sm-8"><?php if ($model->account_code_snapshot): ?><span class="font-monospace"><?= Html::encode($model->account_code_snapshot) ?></span><div class="small text-body-secondary"><?= Html::encode($model->account_name_snapshot) ?></div><?php else: ?><span class="text-danger">ยังไม่ได้เลือก</span><?php endif; ?></dd>
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
                                <time class="small text-body-secondary"><?= \app\modules\finance\components\ThaiDate::datetime($review->created_at) ?></time>
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
                <div class="d-flex justify-content-between py-2 border-bottom"><span>ยอดสุทธิประมาณการ</span><strong><?= Yii::$app->formatter->asDecimal($model->net_amount, 2) ?></strong></div>
                <?php $paidAmount = $model->getPaidAmount(); $payState = $model->paymentStatus(); ?>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-body-secondary">จ่ายแล้ว</span><span><?= Yii::$app->formatter->asDecimal($paidAmount, 2) ?></span></div>
                <div class="d-flex justify-content-between align-items-center pt-3">
                    <span>คงค้าง <span class="badge ms-1 <?= FinancePayable::paymentStatusBadgeClass($payState) ?>"><?= Html::encode(FinancePayable::paymentStatusLabel($payState)) ?></span></span>
                    <strong class="fs-5 <?= $model->getOutstanding() > 0.005 ? 'text-danger' : 'text-success' ?>"><?= Yii::$app->formatter->asDecimal($model->getOutstanding(), 2) ?></strong>
                </div>
                <?php if ($model->status === FinancePayable::STATUS_APPROVED && $model->isBilled() && $model->getOutstanding() > 0.005 && Yii::$app->user->can('financeOperate')): ?>
                    <?= Html::a('<i class="bi bi-cash-stack me-1"></i>จ่ายชำระ', ['pay', 'vendor' => $model->vendor_name_snapshot], ['class' => 'btn btn-primary w-100 mt-3']) ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="card border shadow-sm mt-3" aria-labelledby="payment-history-heading">
            <div class="card-header bg-body"><h5 class="mb-0" id="payment-history-heading">ประวัติการจ่าย</h5></div>
            <div class="list-group list-group-flush">
                <?php if (!$model->settlements): ?>
                    <div class="list-group-item py-4 text-center text-body-secondary">ยังไม่มีการจ่าย</div>
                <?php endif; ?>
                <?php foreach ($model->settlements as $s): ?>
                    <?php $pay = $s->payment_id ? \app\modules\finance\models\FinancePayablePayment::findOne($s->payment_id) : null; ?>
                    <?php $cheque = $pay ? $pay->getCheque() : null; ?>
                    <div class="list-group-item py-3">
                        <div class="d-flex justify-content-between gap-2">
                            <span><?= AppHelper::convertToThai($s->settle_date) ?></span>
                            <strong><?= Yii::$app->formatter->asDecimal($s->amount, 2) ?></strong>
                        </div>
                        <div class="small text-body-secondary mt-1 d-flex flex-wrap gap-2">
                            <?php if ($pay): ?>
                                <?= Html::a('รอบจ่าย #' . $pay->id, ['letter', 'id' => $pay->id], ['target' => '_blank']) ?>
                            <?php endif; ?>
                            <?php if ($s->cash_voucher_id && $s->cashVoucher): ?>
                                <?= Html::a('ใบสำคัญ #' . $s->cash_voucher_id, ['/finance/cash/expense', 'fiscal_year' => $s->cashVoucher->fiscal_year]) ?>
                            <?php endif; ?>
                            <?php if ($cheque): ?>
                                <?= Html::a('เช็ค ' . Html::encode($cheque->cheque_no), ['/finance/cheque/view', 'id' => $cheque->id]) ?>
                            <?php elseif ($s->note): ?>
                                <span><?= Html::encode($s->note) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>
