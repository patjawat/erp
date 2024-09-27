<?php

use app\components\UserHelper;
use yii\helpers\Html;

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
}

.welcome-img {
    z-index: 2;
}
</style>

<div class="card employee-welcome-card flex-fill shadow">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <div class="welcome-content">
                <h6>สวัสดี, <?=UserHelper::GetEmployee()->fullname?></h6>
                <p>คุณมี <span class="badge rounded-pill text-bg-danger">2 </span> กิจกรรมที่ต้องทำ</p>
            </div>
            <div class="welcome-img">
                <?=Html::img(UserHelper::GetEmployee()->ShowAvatar(), ['class' => 'avatar border border-white'])?>
            </div>
        </div>
        <div class="welcome-btn">
            <?=Html::a('<i class="fa-solid fa-clipboard-user"></i> โปรไฟล์',['/profile'],['class' => 'btn btn-primary shadow rounded-pill'])?>
        </div>
    </div>
</div>