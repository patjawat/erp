<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\usermanager\models\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ระบบจัดการผู้ใช้งาน';
$this->params['breadcrumbs'][] = ['label' => 'ภาพรวม', 'url' => ['/usermanager/default/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-bold text-body mb-0 d-flex align-items-center gap-2">
    <span class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
        <i class="bi bi-people fs-4"></i>
    </span>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex flex-wrap gap-2 align-items-center">
    <?= Html::a('<i class="bi bi-plus-lg me-1"></i> สร้างผู้ใช้', ['create'], ['class' => 'btn btn-primary rounded-3 link-loading']) ?>
    <?= $this->render('../default/navlink') ?>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('@app/modules/settings/views/menu', ['active' => 'user']) ?>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <?= $this->render('_list', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]) ?>
    </div>
</div>
