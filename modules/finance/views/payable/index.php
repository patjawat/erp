<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\finance\components\ThaiDate;
use app\modules\finance\models\FinancePayable;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $q */
/** @var string $sent */
/** @var string $payment */
/** @var string $billing */

$this->title = 'ทะเบียนเจ้าหนี้';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo 'บิลที่การเงินรับเอกสารแล้ว — ส่งบัญชี / วางบิล / จ่ายชำระ';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'payable']);
$this->endBlock();

$operate = Yii::$app->user->can('financeOperate');
$select = static function (string $name, string $value, array $options): string {
    return Html::dropDownList($name, $value, ['' => 'ทั้งหมด'] + $options, ['class' => 'form-select form-select-sm', 'style' => 'min-width:130px']);
};
?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<form method="get" class="card border mb-3">
    <div class="card-body d-flex flex-wrap gap-2 align-items-end">
        <div class="flex-grow-1" style="min-width:220px">
            <label class="form-label small mb-1">ค้นหา</label>
            <input type="text" name="q" value="<?= Html::encode($q) ?>" class="form-control form-control-sm" placeholder="เลขทะเบียน / บริษัท / เลขใบแจ้งหนี้">
        </div>
        <div><label class="form-label small mb-1">ส่งบัญชี</label><?= $select('sent', $sent, ['no' => 'ยังไม่ส่ง', 'yes' => 'ส่งแล้ว']) ?></div>
        <div><label class="form-label small mb-1">วางบิล</label><?= $select('billing', $billing, ['unbilled' => 'รอวางบิล', 'billed' => 'วางบิลแล้ว']) ?></div>
        <div><label class="form-label small mb-1">การจ่าย</label><?= $select('payment', $payment, ['unpaid' => 'ยังไม่จ่าย', 'partial' => 'จ่ายบางส่วน', 'paid' => 'จ่ายครบ']) ?></div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>ค้นหา</button>
            <a href="<?= Url::to(['index']) ?>" class="btn btn-sm btn-outline-secondary">ล้าง</a>
        </div>
    </div>
</form>

<?= Html::beginForm(['send-accounting-bulk'], 'post', ['id' => 'bulk-accounting-form']) ?>
<section class="card border shadow-sm">
    <div class="card-header bg-body d-flex justify-content-between align-items-center gap-2 flex-wrap">
        <span class="text-body-secondary small"><?= number_format($dataProvider->getTotalCount()) ?> รายการ</span>
        <?php if ($operate): ?>
            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('ส่งบัญชีรายการที่เลือก?');">
                <i class="bi bi-send-check me-1"></i>ส่งบัญชีที่เลือก
            </button>
        <?php endif; ?>
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
                    'visible' => $operate,
                    'checkboxOptions' => static fn(FinancePayable $m) => ['value' => $m->id, 'disabled' => $m->isSentAccounting()],
                    'headerOptions' => ['style' => 'width:36px'],
                ],
                [
                    'label' => 'เลขทะเบียน',
                    'format' => 'raw',
                    'value' => static fn(FinancePayable $m) => Html::a(Html::encode($m->payable_no), ['view', 'id' => $m->id], ['class' => 'fw-semibold']),
                    'contentOptions' => ['class' => 'text-nowrap'],
                ],
                ['label' => 'บริษัท', 'attribute' => 'vendor_name_snapshot'],
                ['label' => 'ใบแจ้งหนี้', 'value' => static fn(FinancePayable $m) => $m->invoice_no ?: '-'],
                ['label' => 'ครบกำหนด', 'value' => static fn(FinancePayable $m) => ThaiDate::date($m->due_date), 'contentOptions' => ['class' => 'text-nowrap']],
                [
                    'label' => 'ยอดเงิน',
                    'format' => ['decimal', 2],
                    'value' => 'net_amount',
                    'contentOptions' => ['class' => 'text-end text-nowrap'],
                    'headerOptions' => ['class' => 'text-end'],
                ],
                [
                    'label' => 'คงค้าง',
                    'format' => ['decimal', 2],
                    'value' => static fn(FinancePayable $m) => $m->getOutstanding(),
                    'contentOptions' => ['class' => 'text-end text-nowrap fw-semibold'],
                    'headerOptions' => ['class' => 'text-end'],
                ],
                [
                    'label' => 'สถานะ',
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'text-nowrap'],
                    'value' => static function (FinancePayable $m) {
                        $pay = $m->paymentStatus();
                        return ($m->isBilled()
                                ? '<span class="badge bg-success-subtle text-success-emphasis">วางบิลแล้ว</span>'
                                : '<span class="badge bg-warning-subtle text-warning-emphasis">รอวางบิล</span>')
                            . ' <span class="badge ' . FinancePayable::paymentStatusBadgeClass($pay) . '">' . Html::encode(FinancePayable::paymentStatusLabel($pay)) . '</span>'
                            . ($m->isSentAccounting() ? ' <span class="badge text-bg-success">ส่งบัญชีแล้ว</span>' : '');
                    },
                ],
                [
                    'label' => '',
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'text-end text-nowrap'],
                    'value' => static function (FinancePayable $m) use ($operate) {
                        $btn = static fn(string $icon, string $title, $url, string $cls = 'btn-outline-secondary', array $opt = []) =>
                            Html::a('<i class="bi ' . $icon . '"></i>', $url, array_merge(
                                ['class' => 'btn btn-sm ' . $cls, 'title' => $title, 'aria-label' => $title], $opt));
                        $html = $btn('bi-eye', 'ดูรายละเอียด', ['view', 'id' => $m->id]);
                        if ($operate && $m->getOutstanding() > 0.005) {
                            $html .= $btn('bi-cash-stack', 'จ่ายชำระ', ['pay', 'vendor' => $m->vendor_name_snapshot], 'btn-outline-primary');
                        }
                        if ($operate && !$m->isSentAccounting()) {
                            $html .= $btn('bi-send', 'ส่งบัญชี', ['send-accounting', 'id' => $m->id], 'btn-outline-success',
                                ['data-method' => 'post', 'data-confirm' => 'ส่งบิลนี้ให้บัญชี?']);
                        }
                        return '<div class="btn-group">' . $html . '</div>';
                    },
                ],
            ],
            'emptyText' => 'ยังไม่มีบิล — รับเอกสารจากกล่องรอรับก่อน',
            'emptyTextOptions' => ['class' => 'text-center text-body-secondary py-5'],
        ]) ?>
    </div>
</section>
<?= Html::endForm() ?>
