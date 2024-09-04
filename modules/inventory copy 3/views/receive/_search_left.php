<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="product-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'id' => 'form-search_left',
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<?php
echo $form->field($model, 'q_category')->checkboxList(
    $model->ListProductType(),
    ['custom' => true, 'id' => 'custom-checkbox-list']
)->label(false);
?>

    <?php ActiveForm::end(); ?>

</div>
<?php
$js = <<< JS

    \$('.form-check-input').on('change',function(){
                if(this.checked){
                    // \$('#myH').html('Checked');
                    console.log('checked');
                    \$('#form-search_left').submit();
                }
                else{
                    \$('#form-search_left').submit();
                    console.log('un checked');
                    // \$('#myH').html('Unchecked');
                };
            });
            
    JS;
$this->registerJS($js)
?>