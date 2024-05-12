<?php
use yii\helpers\Url;
?>
  <nav class="nav" aria-label="Secondary navigation">
    <a class="nav-link active" aria-current="page" href="<?=Url::to(['/am'])?>"><i class="bi bi-bar-chart fs-5"></i> Dashboard</a>
    <a class="nav-link" href="<?=Url::to(['/am/asset'])?>"><i class="bi bi-boxes fs-5"></i> ทะเบียนทรัพย์สิน</a>
    <a class="nav-link" href="<?=Url::to(['/am/report'])?>"><i class="bi bi-calendar-week-fill fs-5"></i> รายงานค่าเสื่อม</a>
  </nav>