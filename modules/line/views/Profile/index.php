<?php
use yii\web\View;
/** @var yii\web\View $this */
use yii\helpers\Html;
?>

<?php $this->beginBlock('page-title'); ?>
ข้อมูลส่วนบุคคล | โปรไฟล์
<?php $this->endBlock(); ?>


<?php
try {
    if (!Yii::$app->user->isGuest){
        try {
            echo $this->render('avatar',['model' => $model]);
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
} catch (\Throwable $th) {
    //throw $th;
}
?>



<div class="card">
    <div class="card-body">

        <div id="signup-container" class="row justify-content-center mt-5">
            <div class="sign-in-from">
                <h4 class="text-center mb-3 text-primary"><?=$this->title?></h4>
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
                <!-- <button onclick="return logOut()">Logout</button>


                <img id="pictureUrl" width="25%">
                <p id="userId"></p>
                <p id="displayName"></p>
                <p id="statusMessage"></p>
                <p id="getDecodedIDToken"></p> -->

                <?php
use yii\helpers\Url;
$urlCheckProfile = Url::to(['/line/auth/check-profile']);
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
                // location.replace("https://liff.line.me/2005893839-9qRwwMWG");
            }
        }
    });
    console.log('check profile');
}

function runApp() {
      liff.getProfile().then(profile => {
        // document.getElementById("pictureUrl").src = profile.pictureUrl;
        // document.getElementById("userId").innerHTML = '<b>UserId:</b> ' + profile.userId;
        // document.getElementById("displayName").innerHTML = '<b>DisplayName:</b> ' + profile.displayName;
        // document.getElementById("statusMessage").innerHTML = '<b>StatusMessage:</b> ' + profile.statusMessage;
        // document.getElementById("getDecodedIDToken").innerHTML = '<b>Email:</b> ' + liff.getDecodedIDToken().email;
        checkProfile()
      }).catch(err => console.error(err));
    }
    liff.init({ liffId: "2005893839-1vEqqXoQ" }, () => {
      if (liff.isLoggedIn()) {
        runApp()
      } else {
        liff.login();
      }
    }, err => console.error(err.code, error.message));

JS;
$this->registerJs($js,View::POS_END);
?>