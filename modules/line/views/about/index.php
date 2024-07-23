<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
/** @var yii\web\View $this */
?>
<div class="card">
    <div class="card-body">
        <h4 class="card-title">ผู้ให้การสนับสนุนระบบ ERP</h4>
        <div class="d-flex justify-content-center gap-5 mt-4">

            <?=Html::img('@web/banner/banner1.png',['style'=> 'width:100px'])?>

            <?=Html::img('@web/banner/banner2.png',['style'=> 'width:90px'])?>

            <?=Html::img('@web/banner/banner3.png',['style'=> 'width:100px'])?>

        </div>
    </div>
</div>


<?php
use app\components\SiteHelper;

$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffAbout = SiteHelper::getInfo()['line_liff_about'];
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
    liff.init({ liffId: "$liffAbout"}, () => {
      if (liff.isLoggedIn()) {
        runApp()
      } else {
        liff.login();
      }
    }, err => console.error(err.code, error.message));

JS;
$this->registerJs($js,View::POS_END);
?>