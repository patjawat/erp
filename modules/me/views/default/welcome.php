<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;
use app\components\NotificationHelper;
$totalNotification = NotificationHelper::Info()['total'];
?>
<style>
.employee-welcome-card {
    margin-bottom: 24px;
    position: relative;
    background: linear-gradient(90.31deg, #d2ebff -1.02%, #0866ad 132.59%);
}

.employee-welcome-card::before {
    content: "";
    position: absolute;
    top: 0;
    right: 20px;
    border-radius: 0px 0px 10px 0px;
    width: 100px;
    height: 100%;
    transform: skew(12deg);
    background: linear-gradient(90.31deg, #5ca1d4 -1.02%, #0866ad 132.59%);
    animation: fadeIn 1s ease-in-out;
}

.welcome-img {
    z-index: 2;
}

@keyframes fadeInLeft {
    from {
    opacity: 0;
    transform: translateX(-50px);
  }
  to {
    opacity: 1;
  }
}   
@keyframes fadeInRight {
  from {
    opacity: 50;
    transform: translateX(100px);
  }
  to {
    opacity: 1;
  }
}

@keyframes fadeIn {
  0% { opacity: 0; }
  100% { opacity: 1; }
}
</style>

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