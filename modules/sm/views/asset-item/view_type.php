<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetItem $model */
/** @var app\modules\sm\models\AssetItemSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var yii\data\ActiveDataProvider $dataProviderGroup */

$this->title = 'การตั้งค่าครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'รายการครุภัณฑ์', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php Pjax::begin(['id' => 'sm-container', 'timeout' => 5000]); ?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
        <i class="bi bi-folder-check"></i>
        <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center align-items-lg-center gap-2">
        <h5 class="mb-0"><?= Html::encode($model->title) ?></h5>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('<i class="bi bi-gear me-1"></i> ตั้งค่ากลุ่มครุภัณฑ์', ['/sm/asset-type', 'title' => 'ตั้งค่าครุภัณฑ์'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::a('<i class="bi bi-gear me-1"></i> ตั้งค่ากลุ่มวัสดุ', ['/sm/asset-type', 'title' => 'ตั้งค่าวัสดุ'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary-gradient text-white py-2 px-3">
        <h6 class="text-white mt-2"><i class="bi bi-search"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?= $this->render('_search', ['model' => $searchModel]) ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-0">
            <div class="card-body">
                <div class="d-flex justify-content-start flex-wrap gap-2 align-items-center">
                    <span class="badge rounded-pill bg-primary-subtle text-primary">
                        <?= Html::encode($model->code) ?>
                    </span>
                    <?= app\components\AppHelper::Btn([
                        'title' => '<i class="bi bi-plus-circle"></i> สร้าง' . $model->title,
                        'url' => ['/sm/asset-item/create-item', 'type_code' => $model->code, 'name' => 'asset_item', 'category_id' => $model->code, 'title' => 'สร้าง' . $model->title, 'id' => Yii::$app->getRequest()->getQueryParam('id')],
                        'modal' => true,
                        'size' => 'lg',
                    ]) ?>
                </div>
            </div>
        </div>

        <?= $this->render('show/list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]); ?>
    </div>
</div>

<?php Pjax::end(); ?>
