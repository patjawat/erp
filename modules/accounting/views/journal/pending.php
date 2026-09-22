<?php

use yii\grid\GridView;
use yii\helpers\Html;
use app\modules\finance\models\FinancePayable;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'เจ้าหนี้รอลงบัญชี';
$this->params['breadcrumbs'][] = ['label' => 'บัญชี', 'url' => ['/accounting/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo 'รายการที่การเงินส่งมาแล้ว รอตรวจสอบเอกสารและลงบันทึกบัญชี (สร้างสมุดรายวัน)';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/accounting/menu', ['active' => 'pending']);
$this->endBlock();

$canPrepare = Yii::$app->user->can('accountingPrepare');
?>

<div class="alert alert-info d-flex gap-2 align-items-start">
    <i class="bi bi-inbox" aria-hidden="true"></i>
    <span>ตรวจสอบสำเนาใบส่งของ/ใบตรวจรับกับเอกสารจริง แล้วกด "สร้างรายการบัญชี" เพื่อลงสมุดรายวัน จากนั้นผ่านรายการเข้าบัญชีแยกประเภท</span>
</div>

<section class="card border shadow-sm">
    <div class="card-header bg-body d-flex justify-content-between align-items-center gap-2">
        <h5 class="mb-0">คิวรอลงบัญชี</h5>
        <span class="text-body-secondary small"><?= number_format($dataProvider->getTotalCount()) ?> รายการ</span>
    </div>
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n<div class=\"card-footer bg-body d-flex justify-content-between align-items-center flex-wrap gap-2\">{summary}{pager}</div>",
            'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
            'columns' => [
                ['attribute' => 'payable_no', 'label' => 'เลขทะเบียน', 'contentOptions' => ['class' => 'fw-semibold text-nowrap']],
                ['attribute' => 'vendor_name_snapshot', 'label' => 'เจ้าหนี้'],
                ['attribute' => 'invoice_no', 'label' => 'ใบแจ้งหนี้'],
                ['attribute' => 'source_document_no', 'label' => 'เลขที่ใบสั่งซื้อ', 'value' => fn(FinancePayable $m) => $m->source_document_no ?: '-'],
                [
                    'attribute' => 'account_code_snapshot',
                    'label' => 'บัญชีเดบิต',
                    'value' => fn(FinancePayable $m) => $m->account_code_snapshot ?: 'ยังไม่เลือก',
                    'contentOptions' => ['class' => 'font-monospace text-nowrap'],
                ],
                [
                    'attribute' => 'net_amount',
                    'label' => 'ยอดสุทธิ',
                    'format' => ['decimal', 2],
                    'contentOptions' => ['class' => 'text-end text-nowrap'],
                    'headerOptions' => ['class' => 'text-end'],
                ],
                [
                    'attribute' => 'sent_accounting_at',
                    'label' => 'ส่งเมื่อ',
                    'format' => ['datetime', 'php:d/m/Y H:i'],
                    'contentOptions' => ['class' => 'text-nowrap'],
                ],
                [
                    'label' => '',
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'text-end text-nowrap'],
                    'value' => function (FinancePayable $m) use ($canPrepare) {
                        if (!$canPrepare) {
                            return '';
                        }
                        return Html::beginForm(['create', 'payable_id' => $m->id], 'post')
                            . Html::submitButton('<i class="bi bi-journal-plus me-1"></i>สร้างรายการบัญชี', ['class' => 'btn btn-sm btn-success'])
                            . Html::endForm();
                    },
                ],
            ],
            'emptyText' => 'ยังไม่มีเจ้าหนี้รอลงบัญชี',
            'emptyTextOptions' => ['class' => 'text-center text-body-secondary py-5'],
        ]) ?>
    </div>
</section>
