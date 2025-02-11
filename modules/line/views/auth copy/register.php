<?php
use yii\web\View;
// use app\themes\assets\AppAsset;
use yii\helpers\Url;
use yii\bootstrap5\Html;
use app\components\SiteHelper;
use kartik\widgets\ActiveForm;
$this->title = "ระบบลงทะเบียน";
?>
<?php $this->beginBlock('page-title'); ?>
<?php echo $this->title;?>
<?php $this->endBlock(); ?>
<style>
input,
input::placeholder {
    font-weight: 200;
}

.form-control {
    background-color: #eee;
}

.form-control:focus {
    background-color: #eee;
}
</style>
<div cid="welcome" style="display: none;">
    <?=$this->render('./welcome')?>
</div>


<!-- <div cid="welcome">
    
<div class="d-flex justify-content-center"><div class="spinner-border" style="width: 3rem; height: 3rem;" role="status"></div></div><h6 class="text-center mt-3">Loading...</h6>
</div> -->
<h1 class="text-center text-white">ยืนยันตัวตน</h1>
<?php $form = ActiveForm::begin([
        'id' => 'form-register',
    ]); ?>
<div class="p-2">


    <div class="card rounded-4">
        <div class="card-body p-3">



            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-12 col-sm-12">
                    <?= $form->field($model, 'line_id')->hiddenInput()->label(false) ?>
                    <?= $form->field($model, 'cid')->textInput(['placeholder' => 'ระบุเลขบัตรประชาชน','autofocus' => true,'class' => 'form-control form-control-lg rounded-pill border-0'])->label('เลขบัตรประชาชน') ?>
                    <?= $form->field($model, 'email')->textInput(['placeholder' => 'ระบุอีเมล','class' => 'form-control form-control-lg rounded-pill border-0'])->label('อีเมล') ?>
                    <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'กำหนดรหัสผ่าน','class' => 'form-control form-control-lg rounded-pill border-0'])->label('รหัสผ่าน') ?>
                    <div class="d-inline-block w-100">



                    </div>

                </div>
            </div>


            <div class="d-grid gap-2 mt-3">

<div class="d-flex justify-content-center gap-3">
    <button class="btn btn-lg btn-secondary account-btn rounded-pill shadow" id="btn-regster" type="submit">
        <i class="fa-solid fa-circle-check"></i> ลงทะเบียน</button>
   
</div>

<button class="btn btn-lg btn-primary account-btn rounded-pill d-none" id="btn-loading" type="button" disabled="">
    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
    Loading...
</button>
</div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center flex-column">
    <p class="text-white"><span class="text-danger">*</span> หากมีทะเบียนแล้วให้คลิกเข้าสู่ระบบ</p>
    <span class="btn btn-lg btn-warning account-btn rounded-pill" id="userConnect"><i
    class="fa-solid fa-address-card"></i> เข้าสู่ระบบ</span>
</div>

<?php ActiveForm::end(); ?>

<?php
use app\modules\employees\models\Employees;
$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffUserConnect = SiteHelper::getInfo()['line_liff_user_connect'];
$liffUserConnectUrl = 'https://liff.line.me/'.SiteHelper::getInfo()['line_liff_user_connect'];
$liffProfile = SiteHelper::getInfo()['line_liff_profile'];
$liffProfileUrl = 'https://liff.line.me/'.SiteHelper::getInfo()['line_liff_profile'];
$liffRegister =SiteHelper::getInfo()['line_liff_register'];
$liffRegisterUrl = 'https://liff.line.me/'.SiteHelper::getInfo()['line_liff_register'];

$js = <<< JS

\$('#form-register').on('beforeSubmit', function (e) {
                var form = \$(this);
                \$.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success: async function (response) {
                        location.replace("$liffProfileUrl");
                        form.yiiActiveForm('updateMessages', response.validation, true);
                        if(response.status == true) {
                            $('#welcome').show()
                            console.log('register Success');
                        }
                    }
                });
                return false;
            });

            $('#userConnect').click(function (e) { 
                e.preventDefault();
                location.replace("$liffUserConnectUrl");
                
            });

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
            if(res.status == true){
                location.replace("$liffProfileUrl");
            }
        }
    });
    console.log('check profile');
}


async function main(){
  await liff.init({ liffId: "$liffRegister",withLoginOnExternalBrowser:true});
  if (liff.isLoggedIn()) {
          const profile = await liff.getProfile();
          $('#signupform-line_id').val(profile.userId)
        
        //   lineprofile.style.display = "block";
          await checkProfile()
          
        } else {
          liff.login();
        }
  }
  main();

  
function runApp() {
      liff.getProfile().then(profile => {
        checkProfile()
        $('#signupform-line_id').val(profile.userId)
      }).catch(err => console.error(err));
    }
    liff.init({ liffId: "$liffRegister" }, () => {
      if (liff.isLoggedIn()) {
        runApp()
     
    } else {
        liff.login();
      }
    }, err => console.error(err.code, error.message));

JS;
$this->registerJs($js,View::POS_END);
?>