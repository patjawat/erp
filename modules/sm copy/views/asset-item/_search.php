<?php

use app\models\Categorise;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetItemSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="asset-item-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1,
        ],
    ]); ?>

    <div class="row g-3 align-items-end">
        <div class="col-lg-4 col-md-6 col-sm-12">
            <?= $form->field($model, 'title')->textInput([
                'placeholder' => 'ค้นหาชื่อรายการ...',
            ])->label(false) ?>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-12">
            <?= $form->field($model, 'code')->textInput([
                'placeholder' => 'ค้นหารหัส...',
            ])->label(false) ?>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-12">
            <?= $form->field($model, 'category_id')->widget(Select2::class, [
                'data' => ArrayHelper::map(
                    Categorise::find()->where(['name' => 'asset_type'])->orderBy(['title' => SORT_ASC])->all(),
                    'code',
                    'title'
                ),
                'options' => [
                    'placeholder' => 'เลือกประเภททรัพย์สิน...',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])->label(false) ?>
        </div>

        <div class="col-lg-2 col-md-6 col-sm-12">
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary w-100']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
