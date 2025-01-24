use app\components\SiteHelper;
<h1>Leave</h1>
<button id="close-liff-window">Close LIFF Window</button>
<button id="open-window">OPen LIFF Window</button>

<?php
use yii\web\View;
use yii\helpers\Url;
use app\components\SiteHelper;
$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffProfile = SiteHelper::getInfo()['line_liff_profile'];
$liffRegisterUrl = 'https://liff.line.me/'.SiteHelper::getInfo()['line_liff_register'];

$js = <<< JS
    
    liff.init({ liffId: "2005893839-g4J88Xp0"}, () => {
          if (liff.isLoggedIn()) {
                liff.closeWindow()
          } else {
            liff.login();
          }
        }, err => console.error(err.code, err.message));

    JS;
$this->registerJS($js,View::POS_END)
?>

