<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductType $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Product Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="product-type-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
          'title'
        ],
    ]) ?>

<div class="d-flex justify-content-center gap-2">
    <?= Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['/sm/product-type/update','id' => $model->id,'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> แก้ไข'], ['class' => 'btn btn-warning open-modal', 'data' => ['size' => 'modal-md']]) ?>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
    <i class="fa-regular fa-circle-xmark"></i>  ปิด
    </button>

</div>

</div>
