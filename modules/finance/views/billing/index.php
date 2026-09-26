<?php

use app\modules\finance\components\ThaiDate;
use app\modules\finance\models\FinancePayableBilling;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $q */
/** @var array $openVendors [บริษัท => จำนวนบิลรอวาง] */

$this->title = 'ทะเบียนรับวางบิล';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo 'บริษัทมาวางบิล — บันทึกวันที่ บริษัท บิลที่วาง ผู้วาง ผู้รับวาง แล้วพิมพ์ใบรับวางบิล';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'billing']);
$this->endBlock();

$canCreate = Yii::$app->user->can('financeOperate');
?>

<?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
    <form method="get" class="d-flex gap-2 flex-grow-1" style="max-width:520px">
        <input type="text" name="q" value="<?= Html::encode($q) ?>" class="form-control" placeholder="ค้นหา บริษัท / เลขที่ใบรับวางบิล">
        <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
    </form>
    <?php if ($canCreate): ?>
        <a href="<?= Url::to(['create']) ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>เพิ่มการรับวางบิล</a>
    <?php endif; ?>
</div>

<?php if ($openVendors): ?>
    <div class="alert alert-light border small">
        <i class="bi bi-receipt me-1"></i>บิลที่การเงินรับแล้วแต่ยังไม่วางบิล <?= number_format(array_sum($openVendors)) ?> บิล จาก <?= count($openVendors) ?> บริษัท
    </div>
<?php endif; ?>

<section class="card border shadow-sm">
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n<div class=\"card-footer bg-body d-flex justify-content-between align-items-center flex-wrap gap-2\">{summary}{pager}</div>",
            'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
            'rowOptions' => static fn(FinancePayableBilling $m) => $m->isCancelled() ? ['class' => 'text-body-secondary'] : [],
            'columns' => [
                ['label' => 'วันที่รับวาง', 'value' => static fn($m) => ThaiDate::date($m->billing_date), 'contentOptions' => ['class' => 'text-nowrap']],
                [
                    'label' => 'เลขที่',
                    'format' => 'raw',
                    'value' => static fn(FinancePayableBilling $m) => Html::a(Html::encode($m->billing_no), ['view', 'id' => $m->id], ['class' => 'fw-semibold']),
                ],
                ['label' => 'บริษัท', 'attribute' => 'vendor_name'],
                ['label' => 'จำนวนบิล', 'attribute' => 'bill_count', 'contentOptions' => ['class' => 'text-center'], 'headerOptions' => ['class' => 'text-center']],
                ['label' => 'ยอดรวม', 'attribute' => 'total_amount', 'format' => ['decimal', 2], 'contentOptions' => ['class' => 'text-end text-nowrap'], 'headerOptions' => ['class' => 'text-end']],
                ['label' => 'ผู้วาง', 'value' => static fn($m) => $m->deliverer_name ?: '-'],
                ['label' => 'ผู้รับวาง', 'value' => static fn($m) => $m->receiver_name ?: '-'],
                [
                    'label' => '',
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'text-end text-nowrap'],
                    'value' => static fn(FinancePayableBilling $m) => $m->isCancelled()
                        ? '<span class="badge bg-danger-subtle text-danger-emphasis">ยกเลิก</span>'
                        : Html::a('<i class="bi bi-printer"></i> พิมพ์', ['print', 'id' => $m->id], ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank']),
                ],
            ],
            'emptyText' => 'ยังไม่มีการรับวางบิล — กด "เพิ่มการรับวางบิล" เมื่อบริษัทมาวางบิล',
            'emptyTextOptions' => ['class' => 'text-center text-body-secondary py-5'],
        ]) ?>
    </div>
</section>
