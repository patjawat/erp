<?php

use yii\helpers\Html;
use app\modules\finance\components\ThaiDate;
use app\modules\finance\models\FinancePayable;
use app\modules\finance\models\FinancePayablePayment;

/** @var yii\web\View $this */
/** @var FinancePayable $model */

$this->title = $model->payable_no;
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนเจ้าหนี้', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'payable']);
$this->endBlock();

$fmt = fn($v) => number_format((float) $v, 2);
$operate = Yii::$app->user->can('financeOperate');
$outstanding = $model->getOutstanding();
$payState = $model->paymentStatus();
$canEdit = !$model->isSentAccounting() && $model->getPaidAmount() <= 0.005;
?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="row g-3">
    <div class="col-xl-8">
        <section class="card border shadow-sm">
            <div class="card-header bg-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0"><?= Html::encode($model->vendor_name_snapshot) ?></h5>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($operate && $canEdit): ?>
                        <?= Html::a('<i class="bi bi-pencil me-1"></i>แก้ข้อมูลบิล', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                    <?php endif; ?>
                    <?= Html::a('<i class="bi bi-printer me-1"></i>พิมพ์ใบอนุมัติจ่าย',
                        ['/finance/payable-doc/open', 'payable_id' => $model->id],
                        ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                    <?php if ($operate && !$model->isSentAccounting()): ?>
                        <?= Html::beginForm(['send-accounting', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?>
                        <?= Html::submitButton('<i class="bi bi-send me-1"></i>ส่งบัญชี', ['class' => 'btn btn-sm btn-success', 'data' => ['confirm' => 'ส่งบิลนี้ให้บัญชี?']]) ?>
                        <?= Html::endForm() ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-body-secondary">เลขที่ใบแจ้งหนี้</dt>
                    <dd class="col-sm-8"><?= $model->invoice_no ? Html::encode($model->invoice_no) : '<span class="text-body-secondary">ยังไม่ระบุ</span>' ?></dd>
                    <dt class="col-sm-4 text-body-secondary">วันที่ใบแจ้งหนี้</dt>
                    <dd class="col-sm-8"><?= ThaiDate::date($model->invoice_date) ?></dd>
                    <dt class="col-sm-4 text-body-secondary">เอกสารต้นทาง</dt>
                    <dd class="col-sm-8"><?= Html::a(Html::encode($model->source_document_no ?: '-'), ['/finance/inbox/view', 'id' => $model->finance_inbox_id]) ?></dd>
                    <dt class="col-sm-4 text-body-secondary">รับวางบิล</dt>
                    <dd class="col-sm-8">
                        <?php if ($model->isBilled() && $model->billing): ?>
                            <?= ThaiDate::date($model->billing_date) ?>
                            <?= Html::a('(' . Html::encode($model->billing->billing_no) . ')', ['/finance/billing/view', 'id' => $model->billing_id], ['class' => 'small ms-1']) ?>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning-emphasis">รอวางบิล</span>
                        <?php endif; ?>
                    </dd>
                    <dt class="col-sm-4 text-body-secondary">เครดิต / ครบกำหนดจ่าย</dt>
                    <dd class="col-sm-8"><?= (int) $model->credit_days ?> วัน · <strong><?= ThaiDate::date($model->due_date) ?></strong></dd>
                    <dt class="col-sm-4 text-body-secondary">ส่งบัญชี</dt>
                    <dd class="col-sm-8">
                        <?php if ($model->isJournalized()): ?>
                            <span class="badge text-bg-success">บัญชีลงบันทึกแล้ว</span>
                        <?php elseif ($model->isSentAccounting()): ?>
                            <span class="badge bg-success-subtle text-success-emphasis">ส่งแล้ว <?= ThaiDate::datetime($model->sent_accounting_at) ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary-emphasis">ยังไม่ส่ง</span>
                        <?php endif; ?>
                    </dd>
                    <?php if ($model->note): ?>
                        <dt class="col-sm-4 text-body-secondary">หมายเหตุ</dt>
                        <dd class="col-sm-8"><?= Html::encode($model->note) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </section>
    </div>

    <div class="col-xl-4">
        <section class="card border shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between py-1"><span class="text-body-secondary">ยอดหนี้</span><span><?= $fmt($model->gross_amount) ?></span></div>
                <div class="d-flex justify-content-between py-1"><span class="text-body-secondary">ภาษีหัก ณ ที่จ่าย</span><span><?= $fmt($model->withholding_tax_amount) ?></span></div>
                <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-body-secondary">ยอดจ่ายสุทธิ</span><strong><?= $fmt($model->net_amount) ?></strong></div>
                <div class="d-flex justify-content-between py-1"><span class="text-body-secondary">จ่ายแล้ว</span><span><?= $fmt($model->getPaidAmount()) ?></span></div>
                <div class="d-flex justify-content-between align-items-center pt-2">
                    <span>คงค้าง <span class="badge ms-1 <?= FinancePayable::paymentStatusBadgeClass($payState) ?>"><?= Html::encode(FinancePayable::paymentStatusLabel($payState)) ?></span></span>
                    <strong class="fs-5 <?= $outstanding > 0.005 ? 'text-danger' : 'text-success' ?>"><?= $fmt($outstanding) ?></strong>
                </div>
                <?php if ($operate && $outstanding > 0.005): ?>
                    <?= Html::a('<i class="bi bi-cash-stack me-1"></i>จ่ายชำระ', ['pay', 'vendor' => $model->vendor_name_snapshot], ['class' => 'btn btn-primary w-100 mt-3']) ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="card border shadow-sm mt-3">
            <div class="card-header bg-body"><h6 class="mb-0">ประวัติการจ่าย</h6></div>
            <div class="list-group list-group-flush">
                <?php if (!$model->settlements): ?>
                    <div class="list-group-item py-3 text-center text-body-secondary small">ยังไม่มีการจ่าย</div>
                <?php endif; ?>
                <?php foreach ($model->settlements as $s): ?>
                    <?php $pay = $s->payment_id ? FinancePayablePayment::findOne($s->payment_id) : null; ?>
                    <?php $cheque = $pay ? $pay->getCheque() : null; ?>
                    <div class="list-group-item py-2">
                        <div class="d-flex justify-content-between gap-2">
                            <span><?= ThaiDate::date($s->settle_date) ?></span>
                            <strong><?= $fmt($s->amount) ?></strong>
                        </div>
                        <div class="small text-body-secondary d-flex flex-wrap gap-2">
                            <?php if ($pay): ?><?= Html::a('รอบจ่าย #' . $pay->id, ['letter', 'id' => $pay->id], ['target' => '_blank']) ?><?php endif; ?>
                            <?php if ($s->cash_voucher_id && $s->cashVoucher): ?>
                                <?= Html::a('ใบสำคัญ #' . $s->cash_voucher_id, ['/finance/cash/expense', 'fiscal_year' => $s->cashVoucher->fiscal_year]) ?>
                            <?php endif; ?>
                            <?php if ($cheque): ?>
                                <?= Html::a('เช็ค ' . Html::encode($cheque->cheque_no), ['/finance/cheque/view', 'id' => $cheque->id]) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>
