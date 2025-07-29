<?php

use yii\helpers\Html;
/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ทะเบียนการขอใช้รถยนต์';
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานยานพาหนะ', 'url' => ['/booking/vehicle/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
    <i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('page-action'); ?>
    <?= $this->render('@app/modules/booking/views/vehicle/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
    <?= $this->render('@app/modules/booking/views/vehicle/menu', ['active' => 'asset']) ?>
<?php $this->endBlock(); ?>

<?=$this->render('@app/modules/am/views/asset/view',[
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ])?>