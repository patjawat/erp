<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\usermanager\models\User */

$this->title = 'สร้างผู้ใช้งาน';
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-user-gear"></i> <?=$this->title;?>  
<?php $this->endBlock(); ?>

<div class="card">
  <div class="card-body d-flex justify-content-between align-items-center">
    <h5><i class="fa-solid fa-user-plus"></i> สร้างใหม่</h5>
    <?=$this->render('../default/navlink')?>
  </div>
</div>

<div class="user-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
