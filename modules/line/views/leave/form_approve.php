<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\DetailView;
use kartik\form\ActiveField;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="row">
    <div class="col-8">
        <?= $this->render('@app/modules/hr/views/leave/view_detail', ['model' =>  $model->leave]) ?>
    </div>
    <div class="col-4">
    <?=$this->render('@app/modules/hr/views/leave/view_summary',['model' => $model->leave])?>
    
    <?php $form = ActiveForm::begin([
    'id' => 'form',
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/me/leave/approve-validator'],
])
?>
<div class="d-flex justify-content-center flex-column">
    <div class="d-flex justify-content-center">
        <?php  echo $form->field($model, 'status')->radioList(['Approve' => $model->data_json['topic'], 'Reject' => 'ไม่'.$model->data_json['topic']],['custom' => true, 'inline' => true])->label(false)?>
    </div>
    <div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
</div>
</div>



<?php ActiveForm::end(); ?>


    </div>

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
                            location.reload()
                            // try {
                            //     loadRepairHostory()
                            // } catch (error) {
                                
                            // }
                            // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                        }
                    }
                });
                return false;
            });


    JS;
$this->registerJS($js)
?>