<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductSearch $model */
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
        <div class="col-ุlg-6 col-lg-6 col-sm-12">
            <?php echo $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหา...'])->label(false) ?>
        </div>
        <div class="col-lg-5 col-lg-5 col-sm-12">
            <?php
                    echo $form->field($model, 'category_id')->widget(Select2::classname(), [
                        'data' => $model->ListProductType(),
                        'options' => ['placeholder' => 'กรุณาเลือก'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                        'pluginEvents' => [
                            "select2:unselect" => "function() { $(this).submit(); }",
                            'select2:select' => "function(result) { 
                                var data = \$(this).select2('data')[0].text;
                                \$('#order-data_json-product_type_name').val(data)
                                $(this).submit();
                                }",
                                ]
                                ])->label(false);
                                ?>
        </div>
        <div class="col-lg-1 col-md-1 col-sm-12">
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
