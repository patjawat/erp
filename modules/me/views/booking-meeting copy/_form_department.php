<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\file\FileInput;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
// use softark\duallistbox\DualListbox;
use app\modules\hr\models\Organization;
use app\modules\dms\models\DocumentsDetail;
// use iamsaint\datetimepicker\Datetimepicker;
use app\modules\filemanager\components\FileManagerHelper;

?>

<?php $form = ActiveForm::begin([
    'id' => 'tagDepartment',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation

]); ?>
<div style="height: 400px; overflow-y: auto;">

    <?php
                                                echo $form->field($model, 'tags_department')->widget(\kartik\tree\TreeViewInput::className(), [
                                                    'query' => Organization::find()->addOrderBy('root, lft'),
                                                    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                                                    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                                                    'fontAwesome' => true,
                                                    'asDropdown' => true,
                                                    'multiple' => true,
                                                    'options' => ['disabled' => false],
                                                    ])->label('ส่งหน่วยงาน');
                                                    ?>
                                                    </div>

                                                    <div class="form-group mt-3 d-flex justify-content-center gap-3">
    <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i
            class="fa-regular fa-circle-xmark"></i> ปิด</button>
</div>


<?php ActiveForm::end(); ?>


<?php

$js = <<<JS
        

      \$('#tagDepartment').on('beforeSubmit', function (e) {
        var form = \$(this);

        Swal.fire({
        title: "ยืนยัน?",
        text: "ขอใช้ห้องประชุม!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก!",
        confirmButtonText: "ใช่, ยืนยัน!"
        }).then((result) => {
        if (result.isConfirmed) {
            
            \$.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                boforeSubmit: function(){
                    beforLoadModal()
                },
                success: function (response) {
                    // form.yiiActiveForm('updateMessages', response, true);
                    if(response.status == 'success') {
                        closeModal()
                        location.reload(true)
                        // success()
                        // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                    }
                }
            });
        }
        });
        return false;
    });

    
    JS;
$this->registerJS($js, View::POS_END);

?>