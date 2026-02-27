<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEventSearch $model */
?>

<?php $form = ActiveForm::begin([
    'action' => ['/inventory/export-stock/index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1,
    ],
]); ?>

<div class="row align-items-end">
    <div class="col-md-3">
        <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $model]) ?>
    </div>
    <div class="col-md-3">
        <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $model]) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'asset_type_id')->widget(Select2::class, [
            'data' => $model->ListAssetType(),
            'options' => ['placeholder' => 'ประเภทวัสดุ (ทั้งหมด)', 'class' => 'form-control'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false) ?>
    </div>
    <div class="col-md-2">
        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass me-1"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>
