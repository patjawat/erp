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
                            return $model->item_name;
                        },
                    ],
                    [
                        'label' => 'รหัส',
                        'value' => function ($model) {
                            return $model->item_code;
                        },
                    ],
                    [
                        'label' => 'หน่วยนับ',
                        'value' => function ($model) {
                            return $model->unitName ?: '—';
                        },
                    ],
                    [
                        'label' => 'หน่วยบรรจุ',
                        'value' => function ($model) {
                            return $model->packageUnitName ?: '—';
                        },
                    ],
                    [
                        'label' => 'จำนวนต่อบรรจุ',
                        'value' => function ($model) {
                            return $model->packageSize !== null
                                ? number_format($model->packageSize, 2) . ' ' . ($model->unitName ?: '')
                                : '—';
                        },
                    ],
                    [
                        'label' => 'หมวดหมู่',
                        'value' => function ($model) {
                            return isset($model->productType->title) ? $model->productType->title : '-';
                        },
                    ],
                     [
                        'label' => 'ประเภทวัสดุ',
                        'value' => function ($model) {
                            return $model->data_json['metter_type'] ?? '-';
                        },
                    ],
                    
                  
                    [
                        'label' => 'จำนวนสูงสุด',
                        'value' => function ($model) {
                            return $model->max_qty;
                        },
                    ],
                    [
                        'label' => 'จำนวนต่ำสุด',
                        'value' => function ($model) {
                            return $model->min_qty;
                        },
                    ],
                ],
            ])?>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center gap-2">
    <?= Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['/inventory-v2/stock-item/update','id' => $model->id,'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> แก้ไข'], ['class' => 'btn btn-warning open-modal', 'data' => ['size' => 'modal-lg']]) ?>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
    <i class="fa-regular fa-circle-xmark"></i>  ปิด
    </button>

</div>
