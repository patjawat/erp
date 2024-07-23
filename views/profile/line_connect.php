<?php
use app\modules\employees\models\Employees;
// use app\themes\assets\AppAsset;
use yii\bootstrap5\Html;
use kartik\widgets\ActiveForm;
use yii\web\View;
use yii\widgets\Pjax;
use yii\widgets\MaskedInput;
// $assets = AppAsset::register($this);
$this->title = "ระบบลงทะเบียน";
?>

<img id="pictureUrl" width="25%">
  <p id="userId"></p>
  <p id="displayName"></p>
  <p id="statusMessage"></p>
  <p id="getDecodedIDToken"></p>
<button class="btn btn-success" onclick="return logout()">Lofout</button>

<?=Html::a('Lgin',['/profile/line-connect'],['class' => 'btn btn-primary','target' => '_blank'])?>
<?php
use yii\helpers\Url;
$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$js = <<< JS

\$('#form-register').on('beforeSubmit', function (e) {
                var form = \$(this);
                \$.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success: async function (response) {
                        location.replace("https://liff.line.me/2005893839-1vEqqXoQ");
                        form.yiiActiveForm('updateMessages', response.validation, true);
                        if(response.status == true) {
                            $('#welcome').show()
                            console.log('register Success');
                        }
                    }
                });
                return false;
            });

// async function checkProfile(){
//     const {userId} = await liff.getProfile()
   
//     await $.ajax({
//         type: "post",
//         url: "$urlCheckProfile",
//         data:{
//             line_id:userId
//         },
//         dataType: "json",
//         success: function (res) {
//             console.log(res);
//             if(res.status == true){
//                 location.replace("https://liff.line.me/2005893839-1vEqqXoQ");
//             }
//         }
//     });
//     console.log('check profile');
// }
function logout(){
    if (liff.isLoggedIn()) {
  liff.logout();
  $("#main-modal").modal("toggle");
}
}

function runApp() {
      liff.getProfile().then(profile => {
        // checkProfile()
        // $('#signupform-line_id').val(profile.userId)
        document.getElementById("pictureUrl").src = profile.pictureUrl;
        document.getElementById("userId").innerHTML = '<b>UserId:</b> ' + profile.userId;
        document.getElementById("displayName").innerHTML = '<b>DisplayName:</b> ' + profile.displayName;
        document.getElementById("statusMessage").innerHTML = '<b>StatusMessage:</b> ' + profile.statusMessage;
        document.getElementById("getDecodedIDToken").innerHTML = '<b>Email:</b> ' + liff.getDecodedIDToken().email;
      }).catch(err => console.error(err));
    }
    liff.init({ liffId: "2005893839-9qRwwMWG" }, () => {
      if (liff.isLoggedIn()) {
        runApp()
     
    } else {
        // liff.login();
      }
    }, err => console.error(err.code, error.message));

JS;
$this->registerJs($js,View::POS_END);
?>