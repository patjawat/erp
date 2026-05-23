<?php

/** @var yii\web\View $this */
/** @var \app\modules\inventory\models\StockMonthlyReport $model */

$this->title = 'ปรับยอดคงเหลือ — ' . $model->item_code;
$this->params['breadcrumbs'][] = [
    'label' => 'รายงานสรุปคงคลังรายเดือน',
    'url' => ['index'],
];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="edit-3"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventory/menu_dashbroad', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-12 col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header bg-primary-gradient text-white">
                <h6 class="mb-0 text-white"><i class="fa-solid fa-edit"></i> แก้ไขยอดคงเหลือ (closing)</h6>
            </div>
            <div class="card-body">
                <?= $this->render('_adjust_form', ['model' => $model]) ?>
            </div>
        </div>
    </div>
</div>
