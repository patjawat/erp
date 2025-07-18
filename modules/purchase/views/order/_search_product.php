<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="product-search">

    <?php $form = ActiveForm::begin([
        'action' => ['product-list', 'order_id' => $model->id],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
<div>

<div class="row">
<div class="col-4">
    <?php echo $form->field($searchModel, 'title')->textInput(['placeholder' => 'ค้นหา...'])->label('คำค้นหา') ?>
</div>
<div class="col-4">
<?php
  
        echo $form->field($searchModel, 'category_id')->widget(Select2::classname(), [
            'data' => $model->ListProductType(),
            'options' => ['placeholder' => ($model->category_id ?  $model->data_json['order_type_name'] : 'ระบุประเภท'),
            'disabled' => ($model->category_id ?  true : false)
        ],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
            ],
            'pluginEvents' => [
                'select2:select' => "function(result) { 
                          $(this).submit()
                        }",
            ]
        ])->label('ประเภท');
        ?>
</div>
<div class="col-2">

<?php if($dataProvider->getTotalCount() == 0 ):?>
    <div class="form-group mt-4">
        <?= Html::submitButton('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php endif;?>

</div>
</div>

    <?php
        // echo $form->field($model, 'q_category')->checkboxList(
        //     $model->ListProductType(),
        //     ['custom' => true, 'id' => 'custom-checkbox-list']
        // )->label(false);
    ?>
</div>
    <?php ActiveForm::end(); ?>

</div>
