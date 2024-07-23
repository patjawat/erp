<?php
/** @var yii\web\View $this */
use yii\helpers\Url;
use yii\helpers\Html;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-ui-checks-grid"></i> Application | บริการต่างๆ
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-12">
        <a href="<?= Url::to(['/helpdesk/default/repair-select', 'title' => '<i class="fa-regular fa-circle-check"></i> เลือกประเภทการซ่อม']); ?>"
            class="open-modal shadow" data-title="xxx">
            <div class="card">
                <div class="card-body">
                <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">แจ้งซ่อม</h4>
                            <p class="card-text">ระบบแจ้งซ่อมทั่วไปและทรัพย์สินย์</p>
                        </div>
                        <div>
                            <?=Html::img('@web/img/customer-service.png',['class' => 'avatar'])?>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12">
        <a href="<?= Url::to(['/helpdesk/default/repair-select', 'title' => '<i class="fa-regular fa-circle-check"></i> เลือกประเภทการซ่อม']); ?>"
            class="open-modal shadow" data-title="xxx">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">ขอใช้ยานพาหนะ</h4>
                            <p class="card-text">ระบบการขอใช้ยานพาหนะในราชการ</p>
                        </div>
                        <div>
                            <?=Html::img('@web/img/customer-service.png',['class' => 'avatar'])?>
                        </div>
                    </div>

                </div>
            </div>
        </a>
    </div>

    <div class="col-12">
        <a href="<?= Url::to(['/helpdesk/default/repair-select', 'title' => '<i class="fa-regular fa-circle-check"></i> เลือกประเภทการซ่อม']); ?>"
            class="open-modal shadow" data-title="xxx">
            <div class="card">
                <div class="card-body">
                <div class="d-flex justify-content-between">
                        <div>
                        <h4 class="card-title">ข้อใช้ห้องประชุม</h4>
                        <p class="card-text">ระบบจองห้องประชุม</p>
                        </div>
                        <div>
                            <?=Html::img('@web/img/customer-service.png',['class' => 'avatar'])?>
                        </div>
                    </div>
                  
                </div>
            </div>
        </a>
    </div>


</div>

<?php
use app\components\SiteHelper;

$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffApp = SiteHelper::getInfo()['line_liff_app'];
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
    liff.init({ liffId: "$liffApp"}, () => {
      if (liff.isLoggedIn()) {
        runApp()
      } else {
        liff.login();
      }
    }, err => console.error(err.code, error.message));

JS;
$this->registerJs($js,View::POS_END);
?>
