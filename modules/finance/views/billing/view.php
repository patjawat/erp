<?php

use app\modules\finance\components\ThaiDate;
use app\modules\finance\models\FinancePayableBilling;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var FinancePayableBilling $model */

$this->title = 'ใบรับวางบิล ' . $model->billing_no;
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนรับวางบิล', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->billing_no;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'billing']);
$this->endBlock();

$fmt = fn($v) => number_format((float) $v, 2);
// บิลของใบที่ยกเลิกแล้วถูกปลดออก — แสดงจาก payables ปัจจุบันเท่านั้น
$bills = $model->payables;
?>

<?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<?php if ($model->isCancelled()): ?>
    <div class="alert alert-danger"><i class="bi bi-x-circle me-1"></i>ยกเลิกแล้ว เมื่อ <?= ThaiDate::datetime($model->cancelled_at) ?> — <?= Html::encode($model->cancel_reason) ?></div>
<?php endif; ?>

<div class="card border shadow-sm mb-3">
    <div class="card-header bg-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0"><?= Html::encode($model->vendor_name) ?></h5>
        <div class="d-flex gap-2">
            <a href="<?= Url::to(['index']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>ทะเบียน</a>
            <?php if (!$model->isCancelled()): ?>
                <a href="<?= Url::to(['print', 'id' => $model->id]) ?>" target="_blank" class="btn btn-sm btn-primary"><i class="bi bi-printer me-1"></i>พิมพ์ใบรับวางบิล</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3"><div class="small text-body-secondary">เลขที่</div><div class="fw-semibold"><?= Html::encode($model->billing_no) ?></div></div>
            <div class="col-md-3"><div class="small text-body-secondary">วันที่รับวางบิล</div><div class="fw-semibold"><?= ThaiDate::date($model->billing_date) ?></div></div>
            <div class="col-md-3"><div class="small text-body-secondary">เลขที่ใบวางบิลของบริษัท</div><div><?= Html::encode($model->vendor_ref ?: '-') ?></div></div>
            <div class="col-md-3"><div class="small text-body-secondary">ยอดรวม</div><div class="fw-semibold"><?= $fmt($model->total_amount) ?> บาท</div></div>
            <div class="col-md-3"><div class="small text-body-secondary">ผู้วางบิล</div><div><?= Html::encode($model->deliverer_name ?: '-') ?></div></div>
            <div class="col-md-3"><div class="small text-body-secondary">ผู้รับวางบิล</div><div><?= Html::encode($model->receiver_name ?: '-') ?></div></div>
            <div class="col-md-6"><div class="small text-body-secondary">หมายเหตุ</div><div><?= Html::encode($model->note ?: '-') ?></div></div>
        </div>
    </div>
</div>

<section class="card border shadow-sm mb-3">
    <div class="card-header bg-body"><h5 class="mb-0">บิลที่วาง (<?= $model->isCancelled() ? 0 : count($bills) ?>)</h5></div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th>เลขทะเบียน</th><th>เลขที่ใบแจ้งหนี้</th><th class="text-center">วันที่ใบแจ้งหนี้</th><th class="text-center">ครบกำหนดจ่าย</th><th class="text-end">ยอดเงิน</th></tr></thead>
            <tbody>
                <?php if (!$bills): ?>
                    <tr><td colspan="5" class="text-center text-body-secondary py-4"><?= $model->isCancelled() ? 'ใบนี้ยกเลิกแล้ว บิลกลับเป็นรอวางบิล' : 'ไม่มีบิล' ?></td></tr>
                <?php endif; ?>
                <?php foreach ($bills as $p): ?>
                    <tr>
                        <td><?= Html::a(Html::encode($p->payable_no), ['/finance/payable/view', 'id' => $p->id]) ?></td>
                        <td><?= Html::encode($p->invoice_no ?: '-') ?></td>
                        <td class="text-center"><?= ThaiDate::date($p->invoice_date) ?></td>
                        <td class="text-center"><?= ThaiDate::date($p->due_date) ?></td>
                        <td class="text-end"><?= $fmt($p->net_amount) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php if (!$model->isCancelled() && Yii::$app->user->can('financeOperate')): ?>
    <details>
        <summary class="small text-body-secondary">ยกเลิกใบรับวางบิลนี้</summary>
        <?= Html::beginForm(['cancel', 'id' => $model->id], 'post', ['class' => 'd-flex gap-2 mt-2', 'style' => 'max-width:520px']) ?>
        <input type="text" name="reason" class="form-control form-control-sm" placeholder="เหตุผลที่ยกเลิก" required>
        <button class="btn btn-sm btn-outline-danger text-nowrap" onclick="return confirm('ยกเลิกใบรับวางบิลนี้? บิลจะกลับเป็นรอวางบิล');">ยกเลิก</button>
        <?= Html::endForm() ?>
    </details>
<?php endif; ?>
