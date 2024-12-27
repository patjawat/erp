<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;


?>

<div class="order-form">

<?php $form = ActiveForm::begin([
    'id' => 'formDocumentTags',
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/dms/document-tags/comment-validator'],
]); ?>

<?php

                        echo $form->field($model, 'data_json[comment]')->widget(Select2::classname(), [
                            'data' => $model->listCommentTags(),
                            'options' => ['placeholder' => 'ประเภทหนังสือ'],
                            'pluginOptions' => [
                                'dropdownParent' => '#main-modal',
                                'allowClear' => true,
                                'tags' => true,
                            ],
                            'pluginEvents' => [
                                'select2:select' => 'function(result) { 
                                            }',
                                'select2:unselecting' => 'function() {

                                            }',
                            ]
                        ])->label('ลงความเห็น');
                        ?>

    <div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
</div>

    <?php ActiveForm::end(); ?>

</div>

<?php

$js = <<< JS

    \$('#formDocumentTags').on('beforeSubmit', function (e) {
        var form = \$(this);
        \$.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serialize(),
            dataType: 'json',
            success: async function (response) {
                console.log('submit');
                
                form.yiiActiveForm('updateMessages', response, true);
                if(response.status == 'success') {
                    $("#main-modal").modal("toggle");
                    success()
                    // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});      
                    await $.pjax.reload({container:response.container, history:false,timeout: false});                         
                }
            }
        });
        return false;
    });

    JS;
$this->registerJS($js, View::POS_END)
?>

