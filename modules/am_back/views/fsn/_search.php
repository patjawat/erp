<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\FsnSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="fsn-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
<div class="d-flex gap-3">
    <?= $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหา...'])->label(false) ?>
    <span class="filter-emp btn btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="top"
        data-bs-custom-class="custom-tooltip" data-bs-title="เลือกเงื่อนไขของการค้นหาเพิ่มเติม...">
        <i class="fa-solid fa-search"></i>
    </span>
</div>


    <?php ActiveForm::end(); ?>

</div>
