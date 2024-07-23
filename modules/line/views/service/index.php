<?php
use yii\web\View;
/** @var yii\web\View $this */
?>
<div class="card">
    <div class="card-body">
        <h4 class="card-title">อยู่ระหว่างดำเนินการ</h4>
    </div>
</div>


<?php
use app\components\SiteHelper;
use yii\helpers\Url;
$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffService = SiteHelper::getInfo()['line_liff_service'];
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
            
            liff.init({ liffId: "$liffService"}, () => {
            if (liff.isLoggedIn()) {
                runApp()
            } else {
                liff.login();
            }
            }, err => console.error(err.code, error.message));

JS;
$this->registerJs($js,View::POS_END);
?>
