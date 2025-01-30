<?php

use yii\web\View;
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
        <?php echo $this->render('@app/modules/hr/views/leave/view_detail', ['model' =>  $model->leave]) ?>
        <?php $form = ActiveForm::begin([
                    'id' => 'formApprove',
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
    <div class="col-4">
    <?php echo $this->render('@app/modules/hr/views/leave/view_summary',['model' => $model->leave])?>
    <div class="d-flex justify-content-center">

        <button class="btn btn-primary rounded-pill shadow" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
            <i class="bi bi-clock-history"></i> ดูประวัติเพิ่มเติม
        </button>  
    </div>
    


    </div>

</div>
<div class="collapse" id="collapseExample">
        <div id="viewHistory"></div>
        <?php echo $this->render('@app/modules/hr/views/leave/history',['model' => $model->leave])?>
</div>


<?php
$js = <<< JS

        $('#formApprove').on('beforeSubmit', function (e) {
                e.preventDefault();
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
$this->registerJS($js,View::POS_END)
?>