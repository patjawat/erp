<?php

use app\modules\finance\models\FinancePayablePayment;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $q */

$this->title = 'รอบจ่ายเจ้าหนี้';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'เจ้าหนี้ค้างชำระ', 'url' => ['/finance/payable/aging']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo 'ประวัติการจ่ายชำระรายบริษัท — หนังสือนำส่ง ใบสำคัญจ่าย เช็ค และการยกเลิกรอบจ่าย';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'payments']);
$this->endBlock();

$thDate = function ($d) {
    $t = $d ? date_create($d) : null;
    return $t ? $t->format('d/m/') . ((int) $t->format('Y') + 543) : '–';
};
$canCancel = Yii::$app->user->can('financeOperate');
$canApprove = Yii::$app->user->can('financeApprove');
?>

<?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<form method="get" class="card border mb-3">
    <div class="card-body d-flex flex-wrap gap-2 align-items-end">
        <div class="flex-grow-1" style="min-width:220px">
            <label class="form-label small mb-1">ค้นหา</label>
            <input type="text" name="q" value="<?= Html::encode($q) ?>" class="form-control form-control-sm" placeholder="ชื่อเจ้าหนี้ / เลขที่เช็ค / เลขที่หนังสือ">
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>ค้นหา</button>
            <a href="<?= Url::to(['payments']) ?>" class="btn btn-sm btn-outline-secondary">ล้าง</a>
            <a href="<?= Url::to(['pay']) ?>" class="btn btn-sm btn-success"><i class="bi bi-cash-stack me-1"></i>จ่ายชำระ</a>
        </div>
    </div>
</form>

<section class="card border shadow-sm">
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n<div class=\"card-footer bg-body d-flex justify-content-between align-items-center flex-wrap gap-2\">{summary}{pager}</div>",
            'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
            'rowOptions' => static fn(FinancePayablePayment $m) => $m->isCancelled() ? ['class' => 'text-body-secondary'] : [],
            'columns' => [
                ['label' => '#', 'value' => 'id', 'contentOptions' => ['class' => 'text-nowrap']],
                ['label' => 'วันที่จ่าย', 'value' => static fn($m) => $thDate($m->pay_date), 'contentOptions' => ['class' => 'text-nowrap']],
                ['label' => 'เจ้าหนี้', 'attribute' => 'vendor_name_snapshot'],
                [
                    'label' => 'บิล',
                    'contentOptions' => ['class' => 'text-center'],
                    'headerOptions' => ['class' => 'text-center'],
                    'value' => static fn(FinancePayablePayment $m) => $m->isCancelled() ? count($m->cancelledLines())
                        : ($m->status === 'paid' ? count($m->settlements) : count($m->requestedLines())),
                ],
                [
                    'label' => 'เช็ค',
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'text-nowrap'],
                    'value' => static function (FinancePayablePayment $m) {
                        $c = $m->getCheque();
                        if ($c) {
                            return Html::a(Html::encode($c->cheque_no), ['/finance/cheque/view', 'id' => $c->id])
                                . ' <span class="badge text-bg-light border">' . Html::encode($c->statusLabel()) . '</span>';
                        }
                        return Html::encode($m->cheque_no ?: '–');
                    },
                ],
                [
                    'label' => 'ใบสำคัญจ่าย',
                    'format' => 'raw',
                    'value' => static function (FinancePayablePayment $m) {
                        $v = $m->getVoucher();
                        return $v
                            ? Html::a('#' . $v->id . ($v->doc_no ? ' ' . Html::encode($v->doc_no) : ''), ['/finance/cash/expense', 'fiscal_year' => $v->fiscal_year])
                            : '<span class="text-body-secondary">–</span>';
                    },
                ],
                [
                    'label' => 'ยอดจ่ายสุทธิ',
                    'format' => ['decimal', 2],
                    'value' => 'net_total',
                    'contentOptions' => ['class' => 'text-end text-nowrap fw-semibold'],
                    'headerOptions' => ['class' => 'text-end'],
                ],
                [
                    'label' => 'สถานะ',
                    'format' => 'raw',
                    'value' => static function (FinancePayablePayment $m) {
                        [$label, $cls] = $m->statusBadge();
                        $why = $m->isCancelled() ? $m->cancel_reason : ($m->status === 'rejected' ? $m->reject_reason : '');
                        return '<span class="badge ' . $cls . '" title="' . Html::encode((string) $why) . '">' . $label . '</span>';
                    },
                ],
                [
                    'label' => '',
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'text-end text-nowrap'],
                    'value' => static function (FinancePayablePayment $m) use ($canCancel, $canApprove) {
                        if ($m->isPending()) {
                            if (!$canApprove) {
                                return '<span class="small text-body-secondary">รอผู้อนุมัติ</span>';
                            }
                            return Html::beginForm(['approve-payment', 'id' => $m->id], 'post', ['class' => 'd-inline'])
                                . Html::submitButton('<i class="bi bi-check2-circle"></i> อนุมัติ', ['class' => 'btn btn-sm btn-success',
                                    'onclick' => "return confirm('อนุมัติรอบจ่ายนี้? ระบบจะตัดหนี้และออกใบสำคัญจ่าย/เช็ค');"])
                                . Html::endForm() . ' '
                                . Html::beginForm(['reject-payment', 'id' => $m->id], 'post', ['class' => 'd-inline reject-payment-form'])
                                . Html::hiddenInput('reason', '')
                                . Html::submitButton('ไม่อนุมัติ', ['class' => 'btn btn-sm btn-outline-danger'])
                                . Html::endForm();
                        }
                        if ($m->status === 'rejected') {
                            return '';
                        }
                        $html = Html::a('<i class="bi bi-file-earmark-text"></i> หนังสือนำส่ง', ['letter', 'id' => $m->id],
                            ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank']);
                        if ($canCancel && !$m->isCancelled()) {
                            $html .= ' ' . Html::beginForm(['cancel-payment', 'id' => $m->id], 'post', ['class' => 'd-inline cancel-payment-form'])
                                . Html::hiddenInput('reason', '')
                                . Html::submitButton('<i class="bi bi-x-circle"></i> ยกเลิก', ['class' => 'btn btn-sm btn-outline-danger'])
                                . Html::endForm();
                        }
                        return $html;
                    },
                ],
            ],
            'emptyText' => 'ยังไม่มีรอบจ่ายเจ้าหนี้',
            'emptyTextOptions' => ['class' => 'text-center text-body-secondary py-5'],
        ]) ?>
    </div>
</section>

<?php
$this->registerJs(<<<JS
document.querySelectorAll('.cancel-payment-form').forEach(function(f){
  f.addEventListener('submit', function(e){
    const reason = window.prompt('ยกเลิกรอบจ่ายนี้? ยอดหนี้จะกลับมาค้าง ใบสำคัญจ่ายจะถูกลบ และเช็คจะถูกยกเลิก\\n\\nระบุเหตุผล:');
    if(!reason || !reason.trim()){ e.preventDefault(); return; }
    f.querySelector('input[name=reason]').value = reason.trim();
  });
});
document.querySelectorAll('.reject-payment-form').forEach(function(f){
  f.addEventListener('submit', function(e){
    const reason = window.prompt('ไม่อนุมัติรอบจ่ายนี้ — ระบุเหตุผล:');
    if(!reason || !reason.trim()){ e.preventDefault(); return; }
    f.querySelector('input[name=reason]').value = reason.trim();
  });
});
JS, \yii\web\View::POS_END);
?>
