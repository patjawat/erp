<?php
use yii\helpers\Html;
$this->title = $title;
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'unit']) ?><?php $this->endBlock(); ?>
<div class="container-fluid py-3">
    <div class="mb-3"><?= Html::a('<i class="bi bi-arrow-left"></i> ย้อนกลับ', Yii::$app->request->referrer ?: ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?></div>
    <div class="card border-0 shadow-sm"><div class="card-body"><?= $this->render('_form', compact('model', 'buildingOptions', 'floorOptions', 'floorBuildingMap')) ?></div></div>
</div>
