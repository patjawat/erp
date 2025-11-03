<?php

use yii\helpers\Html;
use app\models\Categorise;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

$cart = Yii::$app->cartSub;
$warehouse = Yii::$app->session->get('warehouse');
$item = $warehouse->data_json['item_type'];
$product = ArrayHelper::map(Categorise::find()
->where(['name' => 'asset_type','category_id' => 4])
->andWhere(['IN', 'code', $item])
    ->all(), 'code', 'title');

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="stock-search">

    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    <?= $form->field($model, 'order_id')->hiddenInput()->label(false) ?>

    <fieldset class="border p-3 rounded mb-3">
        <legend class="float-none w-auto px-2 fs-6">ค้นหา</legend>
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12">
                    <?= $form->field($model, 'q',)->textInput(['placeholder' => 'ค้นหาจากชื่อหรือรหัส'])->label(false) ?>
                  
            </div>
            <div class="col-lg-4 col-md-4 col-sm-12">
                <?php

                echo $form->field($model, 'asset_type')->widget(Select2::classname(), [
                    'data' => $product,
                    'options' => ['placeholder' => 'เลือกประเภทวัสดุ', 'class' => 'rounded-pill border-0'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                    'pluginEvents' => [
                        'select2:select' => "function(result) { 
                $(this).submit()
                }",
                        "select2:unselect" => "function() { 
                    $(this).submit()
                     }",
                    ],
                ])->label(false);
                ?>
            </div>
            <div class="col-2">
  <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', [
                        'class' => 'btn btn-light'
                    ]) ?>
            </div>
        </div>

    </fieldset>

    <?php ActiveForm::end(); ?>

</div>