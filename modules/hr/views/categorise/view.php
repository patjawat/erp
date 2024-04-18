<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Categorise $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Categorises', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="categorise-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'code',
            'name',
            'title',
            'description'
        ],
    ]) ?>

</div>

<div class="d-flex justify-content-center">
    <?=Html::a('ย้อนกลับ',['/hr/categorise','name' => 'position_name','title' => 'ตำแหน่ง'],['class' => 'btn btn-primary open-modal','data' => ['size' => 'modal-xl']])?>
</div>