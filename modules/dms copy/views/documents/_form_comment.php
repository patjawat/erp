<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
// use softark\duallistbox\DualListbox;
use app\modules\hr\models\Organization;
use iamsaint\datetimepicker\Datetimepicker;

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
    // 'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    // 'validationUrl' => ['/dms/documents/validator']
]); ?>
<!-- ุ้<h6><i class="fa-regular fa-comment"></i> ลงความเห็น</h6> -->
<?= $form->field($model, 'tag_id')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'document_id')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'name')->hiddenInput(['value' => 'comment'])->label(false); ?>
<?= $form->field($model, 'data_json[comment]')->textArea()->label(false); ?>
<?php if($model->isNewRecord):?>
<span class="btn btn-primary rounded-pill shadow save-comment"><i class="fa-solid fa-paper-plane"></i> ลงความเห็น</span>
<?php else:?>
    <span class="btn btn-warning rounded-pill shadow save-comment"><i class="fa-regular fa-pen-to-square"></i> แก้ไขความเห็น</span>
<?php endif;?>
<?php ActiveForm::end(); ?>

<?php
$url = Url::to(['/dms/documents/get-items']);
$js = <<< JS



$('.save-comment').click(function (e) { 
    e.preventDefault();

    var form = $('#fullscreen-modal').find("#form-comment");
        $.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serialize(),
            dataType: 'json',
            success: function (res) {
                
                if (res.status === 'success') {
                   // รีเซ็ตฟอร์ม
                   form[0].reset();
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
$this->registerJS($js,View::POS_LOAD);
?>