<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetItemSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var yii\data\ActiveDataProvider $dataProviderGroup */

$this->title = 'การตั้งค่าครุภัณฑ์';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
        <i class="fa-solid fa-gear"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('@app/modules/sm/views/default/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'sm-container', 'enablePushState' => false, 'timeout' => 5000]); ?>

<div class="card">
    <div class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center align-items-lg-center gap-2">
        <?= Html::a(
            '<i class="fa-solid fa-circle-plus"></i> สร้างใหม่',
            [
                '/sm/asset-item/create',
                'category_id' => $searchModel->category_id,
                'title' => '<i class="fa-solid fa-circle-plus"></i> สร้างครุภัณฑ์',
            ],
            ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-lg']]
        ) ?>

        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('<i class="fa-solid fa-gear me-1"></i> ตั้งค่ากลุ่มทรัพย์สิน', ['/sm/asset-type'], ['class' => 'btn btn-light']) ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="tmt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?= $this->render('_search', ['model' => $searchModel]) ?>
    </div>
</div>

<?= $this->render('show/list', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProviderGroup,
]) ?>

<?php Pjax::end(); ?>
