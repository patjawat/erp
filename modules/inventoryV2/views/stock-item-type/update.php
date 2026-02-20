<?php
use yii\helpers\Html;

$this->title = 'แก้ไขประเภทวัสดุ: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'ประเภทวัสดุ', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="stock-item-type-update">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-pencil text-primary"></i> แก้ไขประเภทวัสดุ</h5>
        </div>
        <div class="card-body">
            <?= $this->render('_form', ['model' => $model]) ?>
        </div>
    </div>
</div>
