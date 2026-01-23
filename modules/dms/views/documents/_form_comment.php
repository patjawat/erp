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
<?= $form->field($model, 'data_json[comment]')->textArea(['rows' => 8,'placeholder' => 'พิมพ์ข้อความเกษียนหรือเลือกจากแม่แบบด้านบน...'])->label(false); ?>
<div class="d-flex justify-content-between mt-2 px-1 mb-3">
                    <div class="d-flex gap-3">
                        <span id="clear-text" class="btn btn-link btn-sm text-muted text-decoration-none p-0"><i
                                class="fas fa-trash-alt me-1"></i> ล้างข้อความ</span>
                                
                    </div>
                   <span id="char-count" class="badge bg-secondary opacity-50 rounded-pill small text-white">0 ตัวอักษร</span>
                </div>
<?php

echo $form->field($model, 'tags_employee')->widget(Select2::classname(), [
    'data' => $model->listEmployeeSelectTag(),
    'options' => ['placeholder' => 'เลือกผู้ส่งต่อ ...'],
    'pluginOptions' => [
        'allowClear' => true,
        'multiple' => true,
    ],
])->label('<i class="fa-solid fa-user-tag text-primary me-2"></i> ส่งต่อถึง');

?>

<?php if ($model->isNewRecord): ?>
    <div class="d-flex justify-content-center">
        <?php echo Html::submitButton('<i class="fa-solid fa-paper-plane"></i> ลงความเห็น', ['class' => 'btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm']) ?>
    </div>
    <?php else: ?>
        <?php echo Html::submitButton('<i class="fa-regular fa-pen-to-square"></i> แก้ไขความเห็น', ['class' => 'btn btn-warning w-100 py-3 fw-bold rounded-3 shadow-sm']) ?>
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
    var form = $(this);

    // เรียกใช้ SweetAlert2 Confirm
    Swal.fire({
        title: 'ยืนยันการบันทึก?',
        text: "คุณต้องการบันทึกความเห็นนี้ใช่หรือไม่!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ตกลง, บันทึกเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            // --- เริ่มต้นส่วนการทำงานเดิมของคุณหลังจากยืนยัน ---
            
           

            // 3. ส่ง Ajax
            $.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: async function (res) {
                    if (res.status === 'success') {
                        form[0].reset();
                        
                        // ใช้ SweetAlert แสดงความสำเร็จแทนการเรียก success() แบบเดิม (ถ้าต้องการ)
                       await Swal.fire({
                            title: 'สำเร็จ!',
                            text: 'บันทึกข้อมูลของคุณเรียบร้อยแล้ว.',
                            icon: 'success',
                            timer: 2000, // หน่วยเป็นมิลลิวินาที (2000 = 2 วินาที)
                            timerProgressBar: true, // แสดงแถบถอยหลัง (ใส่หรือไม่ก็ได้)
                            showConfirmButton: false // ซ่อนปุ่ม "ตกลง" เพื่อให้ดูสวยงามขณะปิดอัตโนมัติ
                        });

                        await listComment(); // โหลดรายการ comment ใหม่
                        await getComment();  // ฟังก์ชันอื่นๆ ของคุณ

                         // 1. จัดการ UI Tabs (สลับไปที่ Tab Comment ตามที่คุณต้องการ)
                        let homeTab = $('a[href="#comment"]');
                        $('.nav-link, .tab-pane').removeClass('active show');
                        homeTab.addClass('active').attr('aria-selected', 'true');
                        $('#comment').addClass('active show');

                        // 2. ซ่อน Form หรือแสดง Loading
                        $('#viewFormComment').hide();

                    } else {
                        // กรณี Error จากฝั่ง Server
                        Swal.fire('เกิดข้อผิดพลาด', res.message || 'ไม่สามารถบันทึกได้', 'error');
                        $('#viewFormComment').show(); 
                    }
                },
                error: function (xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                    Swal.fire('Error', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                    $('#viewFormComment').show();
                }
            });
        }
    });

    return false; // ป้องกันการ Submit แบบปกติของ HTML Form
});

    $('#clear-text').click(function (e) { 
        e.preventDefault();
        $('#documentsdetail-data_json-comment').val('');
        
    });
              
JS;
$this->registerJS($js, View::POS_END);
?>