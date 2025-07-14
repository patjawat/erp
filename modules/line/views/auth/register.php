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

    /* HTML: <div class="loader"></div> */
.loader {
  width: 100px;
  height: 40px;
  --g: radial-gradient(farthest-side,#0000 calc(95% - 3px),#fff calc(100% - 3px) 98%,#0000 101%) no-repeat;
  background: var(--g), var(--g), var(--g);
  background-size: 30px 30px;
  animation: l9 1s infinite alternate;
}
@keyframes l9 {
  0% {
    background-position: 0 50%, 50% 50%, 100% 50%;
  }
  20% {
    background-position: 0 0, 50% 50%, 100% 50%;
  }
  40% {
    background-position: 0 100%, 50% 0, 100% 50%;
  }
  60% {
    background-position: 0 50%, 50% 100%, 100% 0;
  }
  80% {
    background-position: 0 50%, 50% 50%, 100% 100%;
  }
  100% {
    background-position: 0 50%, 50% 50%, 100% 50%;
  }
}
</style>

<div cid="welcome" style="display: none;">
    <?=$this->render('./welcome')?>
</div>

<div  style="margin-top:40%" id="loading">
        <div class="d-flex justify-content-center">
            <div class="loader"></div>
        </div>
</div>


<!-- <div cid="welcome">
    
<div class="d-flex justify-content-center"><div class="spinner-border" style="width: 3rem; height: 3rem;" role="status"></div></div><h6 class="text-center mt-3">Loading...</h6>
</div> -->

<section class="bg-primary py-3 py-md-5 py-xl-8 h-screen-100 mt-5" id="content" style="display:none">
    <div class="container">
        <div class="row gy-4 align-items-center">
            <div class="col-12 col-md-6 col-xl-7">
                <div class="d-flex justify-content-center text-bg-primary">
                    <div class="col-12 col-xl-9">
                        <h1 class="text-white text-center mb-0">
                            <i class="bi bi-box"></i> ERP Hospital
                        </h1>
                        <hr class="border-primary-subtle">
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-5">
                <?php $form = ActiveForm::begin([
                    'id' => 'form-register',
                ]); ?>
                <div class="p-2">

                    <div class="card rounded-4">
                        <div class="card-body p-3">
                            <h1 class="text-center">ลงทะเบียน</h1>
                            <div class="d-flex justify-content-center mt-1">
                                <img id="pictureUrl" class="avatar avatar-xl border border-3 border-primary shadow">
                            </div>


                            <div class="row justify-content-center">
                                <div class="col-lg-4 col-md-12 col-sm-12">
                                    <?= $form->field($model, 'line_id')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model, 'cid')->textInput(['placeholder' => 'ระบุเลขบัตรประชาชน','autofocus' => true,'class' => 'form-control form-control-lg rounded-pill border-0'])->label('เลขบัตรประชาชน') ?>
                                    <?= $form->field($model, 'email')->textInput(['placeholder' => 'ระบุอีเมล','class' => 'form-control form-control-lg rounded-pill border-0'])->label('อีเมล') ?>
                                    <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'กำหนดรหัสผ่าน','class' => 'form-control form-control-lg rounded-pill border-0'])->label('รหัสผ่าน') ?>

                                </div>
                            </div>


                            <div class="d-grid gap-2 mt-3">

                            <div class="d-inline-block w-100">
                            <div class="d-grid gap-2 mt-3">
                                    <button class="btn btn-lg btn-warning account-btn rounded-pill shadow"
                                        id="btn-regster" type="submit">
                                        <i class="fa-solid fa-circle-check"></i> ลงทะเบียน</button>

                                </div>
                                </div>

                                <button class="btn btn-lg btn-primary account-btn rounded-pill d-none" id="btn-loading"
                                    type="button" disabled="">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"
                                        aria-hidden="true"></span>
                                    Loading...
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>

            </div>

            <div class="text-end mt-0">
                <p class="text-center text-white">ผู้ให้การสนับสนุน</p>
                <div class="d-flex justify-content-center gap-5">
                    <?=Html::img('@web/banner/banner2.png',['style'=> 'width:60px;height:60px'])?>
                    <?=Html::img('@web/banner/banner1.png',['style'=> 'width:130px;height:70px'])?>
                    <?=Html::img('@web/banner/banner3.png',['style'=> 'width:80px;height:70px'])?>

                </div>
            </div>
        </div>

    </div>
</section>

<?php
use app\modules\employees\models\Employees;
$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffProfile = SiteHelper::getInfo()['line_liff_profile'];
$liffProfileUrl = 'https://liff.line.me/'.SiteHelper::getInfo()['line_liff_profile'];
$liffRegister =SiteHelper::getInfo()['line_liff_register'];
$liffRegisterUrl = 'https://liff.line.me/'.SiteHelper::getInfo()['line_liff_register'];

$js = <<< JS

        $('#form-register').on('beforeSubmit', function (e) {
             e.preventDefault();
                var form = $(this);
                \$.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success: async function (response) {
                        form.yiiActiveForm('updateMessages', response.validation, true);
                        if(response.status == true) {
                            $('#welcome').show()
                            location.replace("$liffProfileUrl");
                            console.log('register Success');
                        }
                    }
                });
                return false;
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
        document.getElementById("pictureUrl").src = profile.pictureUrl;
        $('#content').show()
        $('#loading').hide()
        
        //   lineprofile.style.display = "block";
          await checkProfile()
          
        } else {
          liff.login();
        }
  }
  main();
JS;
$this->registerJs($js,View::POS_END);
?>