<?php
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */

$this->title = 'ค่าเสื่อมราคา';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = ['label' => 'ครุภัณฑ์', 'url' => ['/am/equip']];
$this->params['breadcrumbs'][] = ['label' => $model->asset_name ?? $model->code, 'url' => ['view-asset', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
        <i data-lucide="trending-down"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/views/asset/_action_menu', ['model' => $model]) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid px-2 px-md-3 pb-3">
<?= $this->render('@app/modules/am/views/asset/_title', ['model' => $model]) ?>

<div class="row g-3 mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <?= $this->render('@app/modules/am/views/asset/_view_menu', ['model' => $model, 'menu' => 'depreciation']) ?>
            </div>
            <div class="card-body">
                <?php if (isset($model->data_json['service_life']) && (int)$model->data_json['service_life'] > 0): ?>
                    <?= $this->render('depreciation_list', ['model' => $model]) ?>
                <?php else: ?>
                    <div class="alert alert-info mb-0 d-flex align-items-center gap-2">
                        <i data-lucide="info" class="flex-shrink-0"></i>
                        <div>
                            <strong>ยังไม่มีข้อมูลค่าเสื่อม</strong><br>
                            <span class="small">กรุณาระบุอายุการใช้งาน (ปี) และมูลค่าซากในฟอร์มแก้ไขครุภัณฑ์ เพื่อคำนวณค่าเสื่อมราคา</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>
