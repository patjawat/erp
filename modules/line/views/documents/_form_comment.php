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
$this->title = $model->document->topic;
// use iamsaint\datetimepicker\DateTimePickerAsset::register($this);

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $this->beginBlock('page-title'); ?>

<div class="container-fluid text-white mt-5">

    <div class=" d-flex flex-column" style="max-width:1000px">
        <div class="mt--45">
            <p class="text-truncate fw-semibold fs-5 mb-0">
                <?php if ($model->document->doc_speed == 'ด่วนที่สุด'): ?>
                <span class="badge text-bg-danger fs-13">
                    <i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด
                </span>
                <?php endif; ?>

                <?php if ($model->document->secret == 'ลับที่สุด'): ?>
                <span class="badge text-bg-danger fs-13"><i class="fa-solid fa-lock"></i>
                    ลับที่สุด
                </span>
                <?php endif; ?>
                <?php echo $model->document->topic ?>
            </p>
            <span class="fs-6">เลขรับ</span> : <span
                class="fw-medium"><?php echo $model->document->doc_regis_number ?></span>

            <span class="fs-6">เลขหนังสือ</span> : <span
                class="fs-6 fw-medium"><?php echo $model->document->doc_number ?></span>
            <span class="fs-6">จากหน่วยงาน</span> : <span class="text-primary fw-normal fs-13">
                <i class="fa-solid fa-inbox"></i>
                <?php echo $modelOrg->title ?? '-'; ?>
                <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                    <i class="fa-regular fa-eye"></i> <?php echo $model->document->viewCount() ?>
                </span>
            </span>

        </div>

    </div>
<style>
    .form-control {
    font-size: 1.5rem;
    }
</style>
<div class="card">
    <div class="card-body">

    <?php 
    print_r($model->document_id);
    ?>
    8888
<h4 class="text-center"><i class="fa-regular fa-comments"></i> การลงความเห็น</h4>
<?php $form = ActiveForm::begin([
    'id' => 'form-comment',
    'formConfig' => ['deviceSize' => ActiveForm::SIZE_LARGE],
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/dms/documents/comment-validator']
]); ?>
<!-- ุ้<h6><i class="fa-regular fa-comment"></i> ลงความเห็น</h6> -->
<?= $form->field($model, 'to_id')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'document_id')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'name')->hiddenInput(['value' => 'comment'])->label(false); ?>
<?= $form->field($model, 'data_json[comment]')->textArea([
    'style' => 'height: 160px;'
])->label(false); ?>

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
        <div class="d-grid gap-2">
        <?php echo Html::submitButton('<i class="fa-solid fa-paper-plane"></i> ลงความเห็น', ['class' => 'btn btn-lg btn-primary rounded-pill shadow']) ?>
    </div>

    <?php else: ?>
        <?php echo Html::submitButton('<i class="fa-regular fa-pen-to-square"></i> แก้ไขความเห็น', ['class' => 'btn btn-lg btn-warning rounded-pill shadow']) ?>
<?php endif; ?>
<?php ActiveForm::end(); ?>


</div>
</div>


<div class="listComment"></div>
<?php
$listCommentUrl = Url::to(['/me/documents/list-comment', 'id' => $model->document_id]);
$url = Url::to(['/dms/documents/get-items']);
$js = <<<JS
    listComment()
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
    

    async function listComment()
            {
             
                await \$.ajax({
                    type: "get",
                    url: "$listCommentUrl",
                    dataType: "json",
                    success: async function (res) {
                        \$('.listComment').html(res.content)
                    }
                });
            }
            
            

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

                    if (res.status === 'success') {
                       // รีเซ็ตฟอร์ม
                       form[0].reset();
                       success('ลงความเห็นสำเร็จ')
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
$this->registerJS($js, View::POS_LOAD);
?>