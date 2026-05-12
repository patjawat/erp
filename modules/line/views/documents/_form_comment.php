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


<div class="page-title-box">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-5 col-xl-4">
                <div class="page-title">
                </div>
            </div>
            <div class="col-sm-7 col-xl-8">
				<div class="d-flex justify-content-sm-end">

			</div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid text-white">

    <div class=" d-flex flex-column" style="max-width:1000px">
        <div class="mt--45">
            <p class="text-truncate fs-5 mb-0">
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

<h4 class="text-center"><i class="fa-regular fa-comments"></i> การลงความเห็น</h4>
<?php $form = ActiveForm::begin([
    'id' => 'form-comment',
    'formConfig' => ['deviceSize' => ActiveForm::SIZE_LARGE],
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/dms/documents/comment-validator']
]); ?>
<?php $testNotifyUrl = Url::to(['test-notification', 'id' => $model->document_id]); ?>
<?php $canTestNotification = \Yii::$app->user->can('admin'); ?>
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

<?php
$submitLabel = $model->isNewRecord ? '<i class="fa-solid fa-paper-plane"></i> ลงความเห็น' : '<i class="fa-regular fa-pen-to-square"></i> แก้ไขความเห็น';
$submitClass = $model->isNewRecord ? 'btn btn-lg btn-primary rounded-pill shadow' : 'btn btn-lg btn-warning rounded-pill shadow';
?>
<div class="d-grid gap-2">
    <div class="d-flex flex-wrap gap-2 justify-content-end">
        <?php if ($canTestNotification): ?>
            <?= Html::button('<i class="fa-solid fa-vial me-1"></i> ทดสอบแจ้งเตือน', [
                'type' => 'button',
                'class' => 'btn btn-outline-secondary rounded-pill shadow btn-test-notification',
                'data-test-notify-url' => $testNotifyUrl,
            ]) ?>
        <?php endif; ?>
        <?= Html::submitButton($submitLabel, ['class' => $submitClass]) ?>
    </div>
</div>
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

    $(document).off('click.testNotify', '.btn-test-notification').on('click.testNotify', '.btn-test-notification', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var form = $btn.closest('form');
        var url = $btn.data('test-notify-url');
        if (!url || !form.length) {
            return;
        }

        $btn.prop('disabled', true);
        $.ajax({
            url: url,
            type: 'post',
            data: form.serialize(),
            dataType: 'json',
            success: function (res) {
                if (res && (res.status === 'success' || res.status === 'warning')) {
                    var safeMessage = $('<div>').text(res.message || 'ส่งการแจ้งเตือนทดสอบแล้ว').html();
                    var swalOptions = {
                        icon: res.status === 'success' ? 'success' : 'warning',
                        title: res.message || 'ส่งการแจ้งเตือนทดสอบแล้ว',
                    };
                    if (res.status === 'warning' && res.details_html) {
                        swalOptions.html = '<div class="text-start">' + safeMessage + '<hr class="my-2">' + res.details_html + '</div>';
                    } else {
                        swalOptions.text = 'LINE: ' + (res.line_sent || 0) + ' | Telegram: ' + (res.telegram_sent || 0);
                    }
                    Swal.fire(swalOptions);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: (res && res.message) || 'ทดสอบไม่สำเร็จ',
                    });
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'เกิดข้อผิดพลาด';
                Swal.fire({ icon: 'error', title: 'ทดสอบไม่สำเร็จ', text: msg });
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

              
    JS;
// $this->registerJS($js);
$this->registerJS($js, View::POS_LOAD);
?>
