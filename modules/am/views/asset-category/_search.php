<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetItemSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
.field-assetitemsearch-asset_category_id {
    width: 300px;
}
</style>
<?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<div class="row">
    <div class="col-lg-7 col-md-7 col-sm-12">
        <?= $form->field($model, 'title')->textInput(['placeholder' => 'ค้นหาชื่อ,ชื่อทรัพย์สิน...'])->label(false) ?>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-12">
        <?php
                echo $form->field($model, 'category_id')->widget(Select2::classname(), [
                    'data' => $model->listAssetType(),
                    'options' => ['placeholder' => 'ระบุประเภท...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],])->label(false);
                        ?>
    </div>

    <div class="col-lg-1 col-md-1 col-sm-12">
        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btn-primary']) ?>
    </div>

</div>
<?php ActiveForm::end(); ?>