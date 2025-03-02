<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\DetailView;
use kartik\form\ActiveField;
use yii\helpers\ArrayHelper;
/**
 * @var yii\web\View $this
 */
use app\components\SiteHelper;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ขออนุญาติใช้รถยนต์';
\yii\web\YiiAsset::register($this);
$this->registerJsFile('https://unpkg.com/vconsole@latest/dist/vconsole.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar-day fs-1"></i> ขอ<?php echo ($model->leave->leaveType->title ?? '-') ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php if ($model->status == 'Pending'): ?>

<?php // if($model->status == 'Pending'): ?>
<?php
    $form = ActiveForm::begin([
        'id' => 'form',
    ])
    ?>
<div class="d-flex justify-content-center flex-column">
    <div class="d-flex justify-content-center">
        <?php echo $form->field($model, 'status')->hiddenInput()->label(false); ?>
    </div>
    <div class="form-group mt-3 d-flex justify-content-center">

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php


$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffProfile = SiteHelper::getInfo()['line_liff_profile'];
$liffProfileUrl = 'https://liff.line.me/' . $liffProfile;
$liffRegisterUrl = 'https://liff.line.me/' . SiteHelper::getInfo()['line_liff_register'];

$js = <<<JS

       
    \$('#form').on('beforeSubmit', function (e) {
                var form = \$('#form');
                \$.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success: async function (response) {
                        location.reload();
                        // if(response.status == 'success'){
                        //     \$('.btn-approve').hide();
                        //     \$('.load').hide()
                        //     Swal.fire({
                        //     title: "บันทึกสำเร็จ!",
                        //     text: "ดำเนินการลงความเห็นเรียบร้อยแล้ว",
                        //     icon: "success"
                        //     });
                        // }
                            }
                        });
                        return false;
                    });

            
    \$('.btn-approve').click(async function (e) { 

                e.preventDefault();
                

                var form = \$('#form');
                // var title = \$(this).data('title');
                var text = \$(this).data('text');
                var title = 'ยืนยัน';
                var text = \$(this).data('text');
                await Swal.fire({
                title: title,
                html: '<h4>'+\$(this).html()+'</h4>',
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "ใช่, ยืนยัน!",
                cancelButtonText: "ยกเลิก",
                }).then(async (result) => {
                    \$('#approve-status').val(\$(this).data('value'))
                    if (result.value == true) {
                    \$('#btn-warp').hide()
                    \$('#leaveContent').hide()
                    \$('.load').show()
                       \$('#form').submit()
                     
                    //    location.replace("$liffProfileUrl");
                    //    liff.closeWindow()
                        
                    }
                });
                                        
            });
            
      

JS;
$this->registerJS($js, View::POS_END)
?>