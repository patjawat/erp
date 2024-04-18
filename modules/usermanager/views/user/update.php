<?php

use yii\helpers\Html;

$this->title = 'ระบบจัดการผู้ใช้งาน';
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->fullname, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-user-gear"></i> <?=$this->title;?>  
<?php $this->endBlock(); ?>

<div class="card">
  <div class="card-body d-flex justify-content-between align-items-center">
    <h5><i class="fa-solid fa-user-pen"></i> แก้ไขข้อมูลผู้ใช้งาน</h5>
    <?=$this->render('../default/navlink')?>
  </div>
</div>

<div class="user-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
