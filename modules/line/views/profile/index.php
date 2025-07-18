<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\SiteHelper;
$this->registerJsFile('https://unpkg.com/vconsole@latest/dist/vconsole.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>


<div class="page-title-box">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-5 col-xl-4">
                <div class="page-title">
                </div>
            </div>
            <div class="col-sm-7 col-xl-8">
				<div class="d-flex justify-content-sm-end">

			</div>
            </div>
        </div>
    </div>
</div>



<div id="avatar"></div>

<div  style="margin-top:40%" id="loading">
        <div class="d-flex justify-content-center">
            <div class="loader"></div>
        </div>
</div>




<?php


$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffProfile = SiteHelper::getInfo()['line_liff_profile'];
$liffLoginUrl = 'https://liff.line.me/' . SiteHelper::getInfo()['line_liff_login'];

$js = <<< JS


// ฟังก์ชันตรวจสอบสิทธิ์และยืนยันตัวตน
async function checkProfile(userId) {
    try {
        let response = await $.ajax({
            type: "POST",
            url: "$urlCheckProfile",
            data: { line_id: userId },
            dataType: "json"
        });

        if (response.status === false) {
            location.replace("$liffLoginUrl");
        } else {
            $('#avatar').html(response.avatar);
            $('#loading').hide();
        }
    } catch (error) {
        console.error("Error checking profile:", error);
    }
}

// ฟังก์ชันเริ่มต้น LIFF
async function main() {
    try {
        await liff.init({ liffId: "$liffProfile", withLoginOnExternalBrowser: true });

        if (!liff.isLoggedIn()) {
            return liff.login();
        }

        // ดึงข้อมูลโปรไฟล์
        let profile = await liff.getProfile();
        $('#loginform-line_id').val(profile.userId);
        $("#pictureUrl").attr("src", profile.pictureUrl);
        console.log("User Profile:", profile);

        // ตรวจสอบสิทธิ์
        await checkProfile(profile.userId);
    } catch (error) {
        console.error("LIFF initialization failed:", error);
    }
}

// เรียกใช้ฟังก์ชันหลัก
main();
JS;

$this->registerJs($js, View::POS_END);
?>
