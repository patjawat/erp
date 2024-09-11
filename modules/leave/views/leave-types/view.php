<?php

use app\models\Categorise;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\leave\models\LeaveTypes $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Leave Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar-day"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<div class="row d-flex justify-content-center">
<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">

<div class="card">
  <div class="card-body">

<div class="table-container">
    <table class="table table-striped">
    <thead>
        <tr>
            <th style="width:50px;">รหัส</th>
            <th>รายการ</th>
            <th class="text-center" style="width:100px">ลาได้</th>
            <th class="text-center" style="width:100px">ลาได้สูงสุด</th>
        <tr>
    </thead>
    <tbody>
        <?php foreach(Categorise::find()->where(['name' => 'position_type'])->all() as $item):?>
        <tr>
            <td><?=$item->code?></td>
            <td><?=$item->title?></td>
            <td class="text-center"><span class="badge rounded-pill badge-soft-primary text-primary fs-13"> 0 </span></td>
            <td class="text-center"><span class="badge rounded-pill badge-soft-primary text-primary fs-13"> 0 </span></td>

        </tr>
        <?php endforeach;?>
    </tbody>
    </table>
</div>


</div>
</div>
</div>
</div>