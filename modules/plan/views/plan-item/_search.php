<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\DepDrop;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\modules\plan\models\PlanType;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanItemSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="plan-item-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    <div class="row">

        
        <div class="col-lg-3">
    <?php
    echo $form->field($model, 'plan_type_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(PlanType::find()->where(['name' => 'plan_type'])->all(), 'code', 'title'),
        'options' => [
            'id' => 'plan_type_id',
            'placeholder' => 'ประเภททั้งหมด',
        ],
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ])->label(false);
    ?>

</div>
        <div class="col-lg-4">

    <?php
    echo $form->field($model, 'category_id')->widget(DepDrop::classname(), [
        'options' => [
            'placeholder' => 'หมวดหมู่',
        ],
        'type' => DepDrop::TYPE_SELECT2,
        'select2Options' => ['pluginOptions' => ['allowClear' => true]],
        'pluginOptions' => [
            'depends' => ['plan_type_id'],
            'url' => Url::to(['get-plan-category']),
            'loadingText' => 'กำลังโหลด ...',
            'params' => ['depdrop_all_params' => 'plan_type_id'],
            'initDepends' => ['plan_type_id'],
            'initialize' => true,
        ],
        'pluginEvents' => [
            "select2:select" => "function() { 

                        }",
        ],

    ])->label(false); ?>
    </div>
            <div class="col-lg-4">
            <?= $form->field($model, 'title')->textInput()->label(false) ?>
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

    <?php ActiveForm::end(); ?>

</div>