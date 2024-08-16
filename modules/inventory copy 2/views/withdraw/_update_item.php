<?php
use app\models\Categorise;
use app\modules\inventory\models\warehouse;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\modules\inventory\models\Store;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Stock $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="stock-movement-form">

    <?php $form = ActiveForm::begin(['id' => 'form']); ?>
    <div class="card border border-primary mt-3">
    <div class="card-body">
        <?php echo  $model->product->Avatar()?>
    </div>
</div>

    <div class="row">
         <div class="col-12">
         <?= $form->field($model, 'data_json[amount_withdrawal]')->textInput()->label('จำนวน'); ?>
         </div>
    </div>


    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'movement_type')->hiddenInput()->label(false) ?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<< JS

        \$('#form').on('beforeSubmit', function (e) {
                var form = \$(this);
                \$.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success: async function (response) {
                        form.yiiActiveForm('updateMessages', response, true);
                        if(response.status == 'success') {
                            closeModal()
                            success()
                            try {
                                // loadRepairHostory()
                            } catch (error) {
                                
                            }
                            await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                        }
                    }
                });
                return false;
            });


    JS;
$this->registerJS($js,View::POS_END)
?>
