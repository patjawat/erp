<?php

use app\models\Categorise;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Product $model */
$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<!-- <div class="card">
    <div class="card-body">
        <?= Html::a('Update', ['update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square text-warning"></i> แก้ไข'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </div>
</div> -->


<div class="row">
    <div class="col-4">
        <?= Html::img($model->ShowImg(), ['class' => ' card-img-top', 'style' => 'max-width:100%']) ?>
    </div>
    <div class="col-8">
        <div class="card">
            <div class="card-body">

                <?=DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'label' => 'ชื่อรายการ',
                        'value' => function ($model) {
                            return $model->title;
                        },
                    ],
                    [
                        'label' => 'หมวดหมู่',
                        'value' => function ($model) {
                            return isset($model->productType->title) ? $model->productType->title : '-';
                        },
                    ],
                    [
                        'label' => 'รหัส',
                        'value' => function ($model) {
                            return $model->code;
                        },
                    ],
                    [
                        'label' => 'หน่วยนับ',
                        'value' => function ($model) {
                            return isset($model->data_json['unit']) ? $model->data_json['unit'] : '-';
                        },
                    ],
                    // [
                    //     'label' => 'จำนวนสูงสุด',
                    //     'value' => function ($model) {
                    //         return isset($model->data_json['unit']) ? $model->data_json['unit'] : '-';
                    //     },
                    // ],
                    // [
                    //     'label' => 'จำนวนต่ำสุด',
                    //     'value' => function ($model) {
                    //         return isset($model->data_json['unit']) ? $model->data_json['unit'] : '-';
                    //     },
                    // ],
                ],
            ])?>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center gap-2">
    <?= Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['/sm/product/update','id' => $model->id,'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> แก้ไข'], ['class' => 'btn btn-warning open-modal', 'data' => ['size' => 'modal-lg']]) ?>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
    <i class="fa-regular fa-circle-xmark"></i>  ปิด
    </button>

</div>