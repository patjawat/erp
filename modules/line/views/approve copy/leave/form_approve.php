<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\DetailView;
use kartik\form\ActiveField;
/** @var yii\web\View $this */
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ขออนุมัติวันลา';
\yii\web\YiiAsset::register($this);
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar-day fs-1"></i> ขอ<?php echo ($model->leave->leaveType->title ?? '-') ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

    <?= $this->render('view_detail', ['model' =>  $model->leave]) ?>
<div class="d-flex justify-content-center gap-3 mt-4">
<button type="button"class="btn btn-lg btn-primary rounded-pill shadow btn-approve" data-value="Approve" data-text='<i class="fa-regular fa-circle-check"></i> เห็นชอบให้ลาได้'><i class="fa-regular fa-circle-check"></i> เห็นชอบ</button>
<button type="button"class="btn btn-lg btn-danger rounded-pill shadow btn-approve" data-value="Reject" data-text='<i class="fa-solid fa-xmark"></i> ไม่เห็นชอบให้ลา'><i class="fa-solid fa-xmark"></i> ไม่เห็นชอบ</button>

</div>

    <?php $form = ActiveForm::begin([
    'id' => 'form',
    // 'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    // 'validationUrl' => ['/me/leave/approve-validator'],
])
?>
<div class="d-flex justify-content-center flex-column">
    <div class="d-flex justify-content-center">
        <?php echo $form->field($model, 'status')->textInput()->label(false);?>
    </div>
    <div class="form-group mt-3 d-flex justify-content-center">
    <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>



<?php ActiveForm::end(); ?>

    </div>

</div>


<?php
use app\components\SiteHelper;
$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffProfile = SiteHelper::getInfo()['line_liff_profile'];
$liffRegisterUrl = 'https://liff.line.me/'.SiteHelper::getInfo()['line_liff_register'];

$js = <<< JS

      async function checkProfile(){
          const {userId} = await liff.getProfile()
          await $.ajax({
              type: "post",
              url: "$urlCheckProfile",
              data:{
                  line_id:userId
              },
              dataType: "json",
              success: function (res) {
                  console.log(res);
                  if(res.status == false){
                      location.replace("$liffRegisterUrl");
                  }
                  if(res.status == true){
                      $('#avatar').html(res.avatar)
                      $('#loading').hide()
                  }
              }
          });
          console.log('check profile');
      }

    function runApp() {
          liff.getProfile().then(profile => {
            checkProfile()
          }).catch(err => console.error(err));
        }
        
        liff.init({ liffId: "$liffProfile"}, () => {
          if (liff.isLoggedIn()) {
            runApp()
          } else {
            liff.login();
          }
        }, err => console.error(err.code, err.message));


        \$('#form').on('beforeSubmit', function (e) {
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

            $('.btn-approve').click(async function (e) { 
                e.preventDefault();
                var form = $('#form');
                // var title = $(this).data('title');
                var text = $(this).data('text');
                var title = 'ยืนยัน';
                var text = $(this).data('text');
                await Swal.fire({
                title: title,
                html: '<h4>'+$(this).html()+'</h4>',
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "ใช่, ยืนยัน!",
                cancelButtonText: "ยกเลิก",
                }).then(async (result) => {
                    $('#approve-status').val($(this).data('value'))
                    console.log(form.serialize())
                    if (result.value == true) {
                        $.ajax({
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
                    }else{       
                        $('#form').submit()
                }
                });
                                        
            });

    JS;
$this->registerJS($js)
?>

