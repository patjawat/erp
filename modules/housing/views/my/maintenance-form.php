<?php

use yii\helpers\Html;

$this->title = 'แจ้งปัญหาบ้านพัก/ห้องพัก';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
?>
<div class="container-fluid py-3" style="max-width:900px">
    <?= Html::a('กลับไปบ้านพักของฉัน', ['/profile', 'name' => 'housing'], ['class' => 'btn btn-outline-secondary mb-3']) ?>
    <div class="card border-0 shadow-sm"><div class="card-body">
        <?= $this->render('_maintenance_modal', ['model' => $model, 'occupancy' => $occupancy]) ?>
    </div></div>
</div>
