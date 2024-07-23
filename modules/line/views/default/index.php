<?php
use app\components\SiteHelper;
use yii\web\View;
/** @var yii\web\View $this */
?>
<div class="row">
    <div class="col-12">
    <?= $this->render('@app/modules/hr/views/default/gender_pie_chart', [
            'dataProviderPositionType' => $dataProviderPositionType,
            'dataProviderGenderM' => $dataProviderGenderM,
            'dataProviderGenderW' => $dataProviderGenderW,
        ]) ?>
       
        <?= $this->render('@app/modules/hr/views/default/gender_chart', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'dataProviderGender' => $dataProviderGender,
           
        ]) ?>


    </div>
    <div class="col-12">
 
    </div>
</div>
<?= $this->render('@app/modules/hr/views/default/generation_chart', [
            'dataProviderGenB' => $dataProviderGenB,
            'dataProviderGenX' => $dataProviderGenX,
            'dataProviderGenY' => $dataProviderGenY,
            'dataProviderGenZ' => $dataProviderGenZ,
            'dataProviderGenA' => $dataProviderGenA,
        ]) ?>

        <?php $this->render('@app/modules/hr/views/default/position_name', [
            'dataProviderPositionName' => $dataProviderPositionName
        ]) ?>



          <?= $this->render('@app/modules/hr/views/default/position_type_chart', [
            'dataProviderPositionType' => $dataProviderPositionType
        ]) ?>

<?php
use yii\helpers\Url;
$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffDashboard = SiteHelper::getInfo()['line_liff_dashboard'];
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
    liff.init({ liffId: "$liffDashboard"}, () => {
      if (liff.isLoggedIn()) {
        runApp()
      } else {
        liff.login();
      }
    }, err => console.error(err.code, error.message));

JS;
$this->registerJs($js,View::POS_END);
?>
