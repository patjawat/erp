<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
// use softark\duallistbox\DualListbox;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Organization;
use app\modules\dms\models\DocumentTags;;

// use iamsaint\datetimepicker\DateTimePickerAsset::register($this);

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-journal-text fs-4"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php $this->endBlock(); ?>
<?php $form = ActiveForm::begin([
    'id' => 'form-comment',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/dms/documents/comment-validator']
]); ?>
<!-- ุ้<h6><i class="fa-regular fa-comment"></i> ลงความเห็น</h6> -->
<?= $form->field($model, 'to_id')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'document_id')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'name')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'data_json[comment]')->textArea()->label(false); ?>
<?php

echo $form->field($model, 'tags_employee')->widget(Select2::classname(), [
    'data' => $model->listEmployeeSelectTag(),
    'options' => ['placeholder' => 'เลือกผู้ส่งต่อ ...'],
    'pluginOptions' => [
        'allowClear' => true,
        'multiple' => true,
    ],
])->label('ส่งต่อ');

?>

<?php if ($model->isNewRecord): ?>
    <div class="d-flex justify-content-center">
        <?php echo Html::submitButton('<i class="fa-solid fa-paper-plane"></i> ลงความเห็น', ['class' => 'btn btn-primary rounded-pill shadow']) ?>
    </div>
    <?php else: ?>
        <?php echo Html::submitButton('<i class="fa-regular fa-pen-to-square"></i> แก้ไขความเห็น', ['class' => 'btn btn-warning rounded-pill shadow']) ?>
<?php endif; ?>
<?php ActiveForm::end(); ?>

<?php
$url = Url::to(['/dms/documents/get-items']);
$js = <<<JS

    if(\$('#documentsdetail-data_json-comment').val() == '')
    {
    \$('.save-comment').hide()    
    }

    \$('#documentsdetail-data_json-comment').keypress(function (e) { 
        console.log('press');
        
        if(\$(this).val() == '')
    {
    \$('.save-comment').hide()    
    }else{
        \$('.save-comment').show()    
        
    }
    });
    
    $('#form-comment').on('beforeSubmit', function (e) {
        e.preventDefault();
        
        // var form = \$('#fullscreen-modal').find("#form-comment");
        var form = \$("#form-comment");
        $('#viewFormComment').hide()  
        \$.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serialize(),
            dataType: 'json',
            success: function (res) {
                console.log(res);

                    if (res.status === 'success') {
                       // รีเซ็ตฟอร์ม
                       form[0].reset();
                       success('สำเร็จ')
                       listComment()
                       getComment();
                        // Handle success, such as closing modal or reloading data
                    }
                },
                error: function (xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                }
            });
            return false;

    });

              
    JS;
// $this->registerJS($js);
$this->registerJS($js, View::POS_END);
?>