<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;
use app\components\NotificationHelper;
$totalNotification = NotificationHelper::Info()['total'];

?>


<div class="card employee-welcome-card flex-fill shadow">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <div class="welcome-content">
                <h6>สวัสดี, <?=UserHelper::GetEmployee()->fullname?></h6>
              </div>
              <div class="welcome-img">
                <?=Html::img(UserHelper::GetEmployee()->ShowAvatar(), ['class' => 'avatar border border-white'])?>
              </div>
            </div>
            <div class="welcome-btn d-flex justify-content-between align-items-center align-self-center">
              <?=Html::a('<i class="fa-solid fa-clipboard-user"></i> โปรไฟล์',['/profile'],['class' => 'btn btn-primary shadow rounded-pill'])?>
              <a href="<?php echo Url::to(['/me/notification'])?>" class="text-white mt-3" style="z-index: 100;">
               คุณมี <span class="badge rounded-pill text-bg-danger"><?php echo $totalNotification?></span> กิจกรรมที่ต้องทำ
              </a>
        </div>
    </div>
</div>