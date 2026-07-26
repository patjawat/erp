<?php
use yii\helpers\Html;
$this->title = $model->isNewRecord ? 'แจ้งปัญหาบ้านพัก' : 'ปรับปรุงรายการแจ้งซ่อม';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'maintenance']) ?><?php $this->endBlock();
?>
<div class="container-fluid py-3"><div class="card border-0 shadow-sm"><div class="card-body"><?= $this->render('_form', compact('model', 'buildingOptions', 'employeeOptions')) ?></div></div></div>
