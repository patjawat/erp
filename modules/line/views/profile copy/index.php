<?php
use yii\web\View;
/** @var yii\web\View $this */
use yii\helpers\Html;
?>
    <?= $this->render('avatar',['model' => $model])?>
    <?php
    echo Yii::$app->user->id;
    ?>
     <?php if(!Yii::$app->user->isGuest):?>
     <?php
                     echo Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex'])
                     . Html::submitButton(
                         '<i class="bx bx-power-off me-2"></i> ออกจากระบบ (' . Yii::$app->user->identity->username . ')',
                         ['class' => 'dropdown-item']
                     )
                     . Html::endForm();
                    ?>
                    <?php endif; ?>




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
    
<button onclick="return logOut()">Logout</button>

<?php
use yii\helpers\Url;
$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$js = <<< JS




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
       console.log('isInClient');

      } else {
        if (liff.isLoggedIn()) {
            getUserProfile()
            const profile = await liff.getProfile()
            
         

          //ตรวจสอล ID ว่าเคยมี
        //   checkProfile()

        //   $('#title').text(profile)
        //   document.getElementById("btnLogIn").style.display = "none"
        //   document.getElementById("btnLogOut").style.display = "block"
        } else {
        //   document.getElementById("btnLogIn").style.display = "block"
        //   document.getElementById("btnLogOut").style.display = "none"
        console.log('Not InClient');
        logIn()

        }
      }
    }
    main()


JS;
$this->registerJs($js,View::POS_END);
?>

