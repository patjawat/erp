<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\WarehouseSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>
<div class="row">
    <div class="col-lg-7 col-md-7 col-sm-12">
        <?= $form->field($model, 'warehouse_name')->textInput(['placeholder' => 'ระบุชื่อคลังที่ต้องการค้นหา...'])->label(false) ?>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-12">

        <?php
        echo $form->field($model, 'warehouse_type')->widget(Select2::classname(), [
            'data' => ['MAIN' => 'คลังหลัก', 'SUB' => 'คลังย่อย', 'BRANCH' => 'รพ.สต.'],
            'options' => ['placeholder' => 'ประเภทคลังทั้หมด'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>

    </div>
    <div class="col-1">
        <div class="d-flex flex-row align-items-center gap-2">
            <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']) ?>
            <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
                aria-expanded="false" aria-controls="collapseFilter">
                <i class="fa-solid fa-filter"></i>
            </button>
        </div>
    </div>
</div>

<div class="collapse mt-3" id="collapseFilter">
</div>


<?php ActiveForm::end(); ?>