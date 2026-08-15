<?php

use yii\grid\GridView;
use yii\helpers\Html;
use app\modules\finance\models\FinancePayable;

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
echo $this->render('@app/modules/finance/menu', ['active' => 'payable']);
$this->endBlock();
?>

<div class="alert alert-info d-flex gap-2 align-items-start">
    <i class="bi bi-journal-check" aria-hidden="true"></i>
    <span>ทะเบียนนี้ครอบคลุมตั้งแต่ร่างจนถึงอนุมัติเข้าทะเบียน แต่ยังไม่สร้างรายการบัญชี ฎีกา หรือแผนจ่ายเงิน</span>
</div>

<section class="card border shadow-sm" aria-labelledby="payable-list-heading">
    <div class="card-header bg-body d-flex justify-content-between align-items-center gap-2">
        <h5 class="mb-0" id="payable-list-heading">รายการเจ้าหนี้</h5>
        <span class="text-body-secondary small"><?= number_format($dataProvider->getTotalCount()) ?> รายการ</span>
    </div>
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n<div class=\"card-footer bg-body d-flex justify-content-between align-items-center flex-wrap gap-2\">{summary}{pager}</div>",
            'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
            'columns' => [
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
                    'attribute' => 'status',
                    'label' => 'สถานะ',
                    'format' => 'raw',
                    'value' => static fn(FinancePayable $model) => Html::tag(
                        'span',
                        Html::encode(FinancePayable::statusOptions()[$model->status] ?? $model->status),
                        ['class' => 'badge ' . FinancePayable::statusBadgeClass($model->status)]
                    ),
                ],
            ],
            'emptyText' => 'ยังไม่มีร่างทะเบียนเจ้าหนี้ ให้เริ่มจากรับรองรายการในกล่องรับงานบัญชี',
            'emptyTextOptions' => ['class' => 'text-center text-body-secondary py-5'],
        ]) ?>
    </div>
</section>
