<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;
use app\components\ApproveHelper;
$totalNotification = ApproveHelper::Info()['total'];
$me = UserHelper::GetEmployee();
?>


<div class="card employee-welcome-card flex-fill shadow">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <div class="welcome-content">
                <h6>สวัสดี, <?=$me->fullname?></h6>
              </div>
              <div class="welcome-img">
                <?=Html::img($me->ShowAvatar(), ['class' => 'avatar border border-white'])?>
              </div>
            </div>
            <div class="welcome-btn d-flex justify-content-between align-items-center align-self-center">
              <?=Html::a('<i class="fa-solid fa-clipboard-user"></i> โปรไฟล์',['/profile'],['class' => 'btn btn-primary shadow rounded-pill'])?>
              <?php if($totalNotification >=1):?>
              <a href="<?php echo Url::to(['/approve'])?>" class="mt-3" style="z-index:2;">
               คุณมี <span class="badge rounded-pill text-bg-danger"><?php echo $totalNotification?></span> กิจกรรมที่ต้องทำ
              </a>
              <?php else:?>
                <p class="text-white mt-3" style="z-index: 1;">
                  คุณมี <span class="badge rounded-pill text-bg-danger"><?php echo $totalNotification?></span> กิจกรรมที่ต้องทำ
                </p>
                  <?php endif;?>
                  
        </div>
    </div>
</div>
