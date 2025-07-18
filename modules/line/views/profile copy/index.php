<?php
use yii\web\View;
use yii\helpers\Url;
/** @var yii\web\View $this */
use yii\helpers\Html;
?>

<?php $this->beginBlock('page-title'); ?>
ข้อมูลส่วนบุคคล | โปรไฟล์
<?php $this->endBlock(); ?>

<div id="avatar"></div>
<div class="card" id="loading">
    <div class="card-body">
        <div class="d-flex justify-content-center"><div class="spinner-border" style="width: 3rem; height: 3rem;" role="status"></div></div><h6 class="text-center mt-3">Loading...</h6>
    </div>
</div>


<?php
use app\components\SiteHelper;
$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffProfile = SiteHelper::getInfo()['line_liff_profile'];
// $liffProfile = SiteHelper::getInfo()['line_liff_dashboard'];
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
        
        liff.init({ liffId: "$liffProfile"}, () => {
          if (liff.isLoggedIn()) {
            runApp()
          } else {
            liff.login();
          }
        }, err => console.error(err.code, err.message));

        $("body").on("click", ".logout", function (e) {
            e.preventDefault();
            $.ajax({
              type: "get",
              url: $(this).attr('href'),
              data: "data",
              dataType: "json",
              success: function (response) {
                // location.replace($liffProfile);
                
              }
            });
            
          });

JS;
$this->registerJs($js,View::POS_END);
?>