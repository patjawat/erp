<?php
use yii\helpers\Html;
?>
<style>
    .employee-welcome-card {
    margin-bottom: 24px;
    position: relative;
    background: linear-gradient(90.31deg, #FF902F -1.02%, #FF2D3D 132.59%);
}
</style>

<div class="card employee-welcome-card flex-fill">
<div class="card-body">
<div class="welcome-info">
<div class="welcome-content">
<h4>Welcome Back, Darlee</h4>
<p>You have <span>4 meetings</span> today,</p>
</div>
<div class="welcome-img">

<?=Html::img('@web/img/patjwat2.png',['class' => 'avatar'])?>
</div>
</div>
<div class="welcome-btn">
<a href="profile.html" class="btn">View Profile</a>
</div>
</div>
</div>