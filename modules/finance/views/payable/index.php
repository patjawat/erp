<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\finance\models\FinancePayable;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $q */
/** @var string $status */
/** @var string $payment */

$this->title = 'ทะเบียนคุมเจ้าหนี้';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo 'รายการเจ้าหนี้ที่สร้างจากเอกสารผ่านการตรวจสอบแล้ว';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'payable']);
$this->endBlock();
?>

<form method="get" class="card border mb-3">
    <div class="card-body d-flex flex-wrap gap-2 align-items-end">
        <div class="flex-grow-1" style="min-width:220px">
            <label class="form-label small mb-1">ค้นหา</label>
            <input type="text" name="q" value="<?= Html::encode($q) ?>" class="form-control form-control-sm" placeholder="เลขทะเบียน / ชื่อเจ้าหนี้ / เลขใบแจ้งหนี้">
        </div>
        <div>
            <label class="form-label small mb-1">สถานะทะเบียน</label>
            <select name="status" class="form-select form-select-sm" style="min-width:150px">
                <option value="">ทั้งหมด</option>
                <?php foreach (FinancePayable::statusOptions() as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= Html::encode($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label small mb-1">สถานะการจ่าย</label>
            <select name="payment" class="form-select form-select-sm" style="min-width:130px">
                <option value="">ทั้งหมด</option>
                <option value="unpaid" <?= $payment === 'unpaid' ? 'selected' : '' ?>>ยังไม่จ่าย</option>
                <option value="partial" <?= $payment === 'partial' ? 'selected' : '' ?>>จ่ายบางส่วน</option>
                <option value="paid" <?= $payment === 'paid' ? 'selected' : '' ?>>จ่ายครบ</option>
            </select>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>ค้นหา</button>
            <a href="<?= Url::to(['index']) ?>" class="btn btn-sm btn-outline-secondary">ล้าง</a>
        </div>
    </div>
</form>

<?= Html::beginForm(['send-accounting-bulk'], 'post', ['id' => 'bulk-accounting-form']) ?>
<section class="card border shadow-sm" aria-labelledby="payable-list-heading">
    <div class="card-header bg-body d-flex justify-content-between align-items-center gap-2 flex-wrap">
        <h5 class="mb-0" id="payable-list-heading">รายการเจ้าหนี้</h5>
        <div class="d-flex align-items-center gap-2">
            <span class="text-body-secondary small"><?= number_format($dataProvider->getTotalCount()) ?> รายการ</span>
            <?php if (Yii::$app->user->can('financeOperate')): ?>
                <button type="submit" class="btn btn-sm btn-success"
                        onclick="return confirm('ยืนยันส่งบัญชีรายการที่เลือก?');">
                    <i class="bi bi-send-check me-1" aria-hidden="true"></i>ส่งบัญชีที่เลือก
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n<div class=\"card-footer bg-body d-flex justify-content-between align-items-center flex-wrap gap-2\">{summary}{pager}</div>",
            'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
            'columns' => [
                [
                    'class' => 'yii\grid\CheckboxColumn',
                    'name' => 'ids',
                    'checkboxOptions' => static function (FinancePayable $model) {
                        $eligible = $model->status === FinancePayable::STATUS_APPROVED && !$model->isSentAccounting();
                        return ['value' => $model->id, 'disabled' => !$eligible];
                    },
                    'headerOptions' => ['style' => 'width:36px'],
                ],
                [
                    'attribute' => 'payable_no',
                    'label' => 'เลขทะเบียน',
                    'format' => 'raw',
                    'value' => static fn(FinancePayable $model) => Html::a(
                        Html::encode($model->payable_no),
                        ['view', 'id' => $model->id],
                        ['class' => 'fw-semibold']
                    ),
                ],
                ['attribute' => 'vendor_name_snapshot', 'label' => 'เจ้าหนี้'],
                ['attribute' => 'invoice_no', 'label' => 'ใบแจ้งหนี้'],
                [
                    'attribute' => 'account_code_snapshot',
                    'label' => 'บัญชีเดบิตหลัก',
                    'value' => static fn(FinancePayable $model) => $model->account_code_snapshot ?: 'ยังไม่เลือก',
                    'contentOptions' => ['class' => 'font-monospace text-nowrap'],
                ],
                [
                    'attribute' => 'due_date',
                    'label' => 'วันครบกำหนด',
                    'format' => ['date', 'php:d/m/Y'],
                    'contentOptions' => ['class' => 'text-nowrap'],
                ],
                [
                    'attribute' => 'net_amount',
                    'label' => 'ยอดสุทธิ',
                    'format' => ['decimal', 2],
                    'contentOptions' => ['class' => 'text-end text-nowrap'],
                    'headerOptions' => ['class' => 'text-end'],
                ],
                [
                    'label' => 'คงค้าง',
                    'format' => ['decimal', 2],
                    'value' => static fn(FinancePayable $model) => $model->getOutstanding(),
                    'contentOptions' => ['class' => 'text-end text-nowrap fw-semibold'],
                    'headerOptions' => ['class' => 'text-end'],
                ],
                [
                    'label' => 'การจ่าย',
                    'format' => 'raw',
                    'value' => static fn(FinancePayable $model) => Html::tag(
                        'span',
                        Html::encode(FinancePayable::paymentStatusLabel($model->paymentStatus())),
                        ['class' => 'badge ' . FinancePayable::paymentStatusBadgeClass($model->paymentStatus())]
                    ),
                ],
                [
                    'attribute' => 'status',
                    'label' => 'สถานะ',
                    'format' => 'raw',
                    'value' => static fn(FinancePayable $model) => Html::tag(
                        'span',
                        Html::encode(FinancePayable::statusOptions()[$model->status] ?? $model->status),
                        ['class' => 'badge ' . FinancePayable::statusBadgeClass($model->status)]
                    ),
                ],
                [
                    'label' => 'ส่งบัญชี',
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'text-nowrap'],
                    'value' => static function (FinancePayable $model) {
                        if ($model->isSentAccounting()) {
                            return '<span class="badge text-bg-success">ส่งแล้ว</span>';
                        }
                        return $model->status === FinancePayable::STATUS_APPROVED
                            ? '<span class="badge text-bg-warning">รอส่ง</span>'
                            : '<span class="text-body-secondary">—</span>';
                    },
                ],
            ],
            'emptyText' => 'ยังไม่มีร่างทะเบียนเจ้าหนี้ ให้เริ่มจากรับรองรายการในกล่องรับงานบัญชี',
            'emptyTextOptions' => ['class' => 'text-center text-body-secondary py-5'],
        ]) ?>
    </div>
</section>
<?= Html::endForm() ?>
