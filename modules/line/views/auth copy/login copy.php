<?php
use app\modules\employees\models\Employees;
// use app\themes\assets\AppAsset;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\web\View;
use yii\widgets\MaskedInput;
// $assets = AppAsset::register($this);
$this->title = "Authentication";
?>


<div class="card border border-danger">
    <div class="card-body">

<div id="signup-container" class="row justify-content-center mt-5">
    <div class="sign-in-from">
        <h4 class="text-center mb-3 text-primary" id="title"><?=$this->title?></h4>
        <div class="line-profile">
    <div class="d-flex justify-content-center align-items-center">
        <div class="round-image">
            <img id="pictureUrl" class="rounded-circle" width="200">
        </div>
    </div>
    <div class="text-center">
        <h4 class="mt-3" id="displayName"></h4>
    </div>
</div>
    <p id="debug"></p>

        <?php $form = ActiveForm::begin(['id' => 'blank-form','enableAjaxValidation' => false,]); ?>
        <div class="row justify-content-center">
            <div class="col-lg-4 col-md-12 col-sm-12">
                <?= $form->field($model, 'line_id')->hiddenInput()->label(false) ?>
                <div class="d-inline-block w-100">
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-lg btn-primary account-btn rounded-pill" id="btn-regster" type="submit">
                            <i class="fa-solid fa-circle-check"></i> ลงทะเบียน</button>

                        <button class="btn btn-lg btn-primary account-btn rounded-pill d-none" id="btn-loading"
                            type="button" disabled="">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Loading...
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>



</div>
</div>



<?php
use yii\helpers\Url;
$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$js = <<< JS





$('#blank-form').on('beforeSubmit', function () {


    var yiiform = $(this);
    // $('#btn-regster').hide();
    // $('#btn-loading').show();
    $.ajax({
            type: yiiform.attr('method'),
            url: yiiform.attr('action'),
            data: yiiform.serializeArray(),
        }
    )
        .done(function(data) {
            if(data.success) {
                // data is saved
                $('#success-container').html(data.content);
                $('#signup-container').hide();
                success()
            } else if (data.validation) {
                // server validation failed
                yiiform.yiiActiveForm('updateMessages', data.validation, true); // renders validation messages at appropriate places
                $('#btn-regster').show();
                $('#btn-loading').hide();
            } else {
                // incorrect server response
            }
        })
        .fail(function () {
            // request failed
        })

    // }
    return false; // prevent default form submission
})





function logOut() {
      liff.logout()
      window.location.reload()
    }

    function logIn() {
      liff.login({ redirectUri: window.location.href })
    }

    async function getUserProfile() {
      const profile = await liff.getProfile()
    //   document.getElementById("pictureUrl").style.display = "block"
      document.getElementById("pictureUrl").src = profile.pictureUrl
      $('#displayName').text(profile.displayName)
      $('#signupform-line_id').val(profile.userId)
      console.log(profile)
      $('#line_id').val(profile.userId)
    //   $('#profile').src = profile.pictureUrl;

    }



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
            if(!res){
                location.replace("https://liff.line.me/2005893839-9qRwwMWG");
            }
        }
    });
    console.log('check profile');
}



    async function main() {
      await liff.init({ liffId: "2005893839-JAYvvA6G" })
      if (liff.isInClient()) {
        getUserProfile()

      } else {
        if (liff.isLoggedIn()) {
            const profile = await liff.getProfile()
          getUserProfile()
          //ตรวจสอล ID ว่าเคยมี
          checkProfile()
          alert(profile)

          $('#title').text(profile)
        //   document.getElementById("btnLogIn").style.display = "none"
        //   document.getElementById("btnLogOut").style.display = "block"
        } else {
        //   document.getElementById("btnLogIn").style.display = "block"
        //   document.getElementById("btnLogOut").style.display = "none"
        console.log('no');
        logIn()

        }
      }
    }
    main()

    
    



JS;
$this->registerJs($js,View::POS_END);
?>