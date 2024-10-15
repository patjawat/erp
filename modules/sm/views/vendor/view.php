<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\SupVendor $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Sup Vendors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="sup-vendor-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => 'เลขที่ผู้เสียภาษี',
                'value' => function ($model) {
                    return $model->code;
                },
            ],
            'address',
            'phone',
            'email',
            'contact_name',
            'account_name',
            'account_number',
            'bank_name',
            
        ],
    ]) ?>

<div class="d-flex gap-2  justify-content-center">
    <?=Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข', ['/sm/vendor/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-warning  open-modal', 'data' => ['size' => 'modal-lg']])?>
    <?=Html::a('<i class="fa-solid fa-trash me-1"></i>ลบ', ['/sm/vendor/delete', 'id' => $model->id], [
    'class' => 'btn btn-danger  delete-item',
])?>
</div>

</div>
