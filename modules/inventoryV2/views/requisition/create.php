<?php
use yii\helpers\Html;

$this->title = 'สร้างใบขอเบิกวัสดุ';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2']];
$this->params['breadcrumbs'][] = ['label' => 'รายการใบขอเบิก', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 15V3" /><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><path d="m7 10 5 5 5-5" />
        </svg>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted small mb-0">สร้างคำขอเบิกไปยังคลังหลักเพื่อรอการอนุมัติจ่ายสินค้า</p>
</div>
<?php $this->endBlock(); ?>

<div class="requisition-create">
    <?= $this->render('_form', [
        'model' => $model,
        'ctx' => $ctx ?? null,
    ]) ?>
</div>
