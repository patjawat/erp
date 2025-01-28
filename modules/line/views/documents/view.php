<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
use app\components\SiteHelper;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */

$this->title = $model->topic;
$this->params['breadcrumbs'][] = ['label' => 'Documents', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
$this->registerJsFile('https://unpkg.com/vconsole@latest/dist/vconsole.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>
<iframe id="myIframe" src="<?= Url::to(['/dms/documents/show','ref' => $model->ref]);?>&embedded=true"
frameborder="0" style="width: 100%; height: 500px; border: none;"></iframe>


<?php
use app\components\UserHelper;
$urlCheckProfile = Url::to(['/line/auth/check-profile']);
// $liffDocument = SiteHelper::getInfo()['line_liff_document'];
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

JS;
$this->registerJs($js,View::POS_END);
?>