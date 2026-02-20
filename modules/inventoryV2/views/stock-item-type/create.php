<?php
use yii\helpers\Html;

$this->title = 'เพิ่มประเภทวัสดุ';
$this->params['breadcrumbs'][] = ['label' => 'ประเภทวัสดุ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-item-type-create">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-plus-circle text-primary"></i> <?= Html::encode($this->title) ?></h5>
        </div>
        <div class="card-body">
            <?= $this->render('_form', ['model' => $model]) ?>
        </div>
    </div>
</div>
