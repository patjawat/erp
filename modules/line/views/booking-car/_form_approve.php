<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\DetailView;
use kartik\form\ActiveField;
/**
 * @var yii\web\View $this
 */
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ขออนุญาตใช้รถยนต์';
\yii\web\YiiAsset::register($this);
$this->registerJsFile('https://unpkg.com/vconsole@latest/dist/vconsole.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar-day fs-1"></i> ขอ<?php echo ($model->leave->leaveType->title ?? '-') ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php if ($model->status == 'Pending'): ?>
<div class="mt-5" id="leaveContent">
    <h6 class="text-center text-white"><?php echo $this->title;?></h6>
    <?= $this->render('view', ['model' => $model->bookCar]) ?>
</div>
<?php // if($model->status == 'Pending'): ?>
<div class="d-flex justify-content-center gap-3 mt-4" id="btn-warp">
    <button type="button" class="btn btn-lg btn-primary rounded-pill shadow btn-approve" data-value="Approve"
        data-text='<i class="fa-regular fa-circle-check"></i> เห็นชอบให้ลาได้'><i
            class="fa-regular fa-circle-check"></i> เห็นชอบ</button>
    <button type="button" class="btn btn-lg btn-danger rounded-pill shadow btn-approve" data-value="Reject"
        data-text='<i class="fa-solid fa-xmark"></i> ไม่เห็นชอบให้ลา'><i class="fa-solid fa-xmark"></i>
        ไม่เห็นชอบ</button>
        <?php echo Html::a('ไม่เห็นชอบ',['/line/booking-car/reject','id' => $model->id],['class' => 'btn btn-lg btn-danger rounded-pill shadow'])?>

</div>
<?php endif; ?>



<?php if ($model->status == 'Approve'): ?>

<h1 class="text-center text-white mt-5"><i class="fa-regular fa-circle-check"></i> เห็นชอบ</h1>
<?php endif; ?>

<?php if ($model->status == 'Reject'): ?>
<h1 class="text-center text-white mt-5"><i class="fa-solid fa-xmark"></i> ไม่เห็นชอบ</h1>
<?php endif; ?>

<h1 class="load text-center text-wite mt-5" style="display:none"><i class="fas fa-cog fa-spin"></i> กำลังดำเนินการ</h1>

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
use app\components\SiteHelper;

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