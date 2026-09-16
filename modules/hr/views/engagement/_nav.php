<?php
use yii\helpers\Html;
$can=static fn($name)=>Yii::$app->user->can($name)||Yii::$app->user->can('admin');
?>
<nav class="d-flex flex-wrap gap-2 mb-4" aria-label="เมนูความผูกพัน">
<?= Html::a('แบบสำรวจของฉัน',['mine'],['class'=>'btn btn-outline-primary']) ?>
<?php if($can('engagementManageRound')): ?><?= Html::a('รอบสำรวจ',['index'],['class'=>'btn btn-outline-primary']) ?><?php endif ?>
<?php if($can('engagementManageTemplate')): ?><?= Html::a('แบบสอบถาม',['templates'],['class'=>'btn btn-outline-primary']) ?><?php endif ?>
<?php if($can('engagementViewAnalytics')): ?><?= Html::a('ผลวิเคราะห์',['report'],['class'=>'btn btn-outline-primary']) ?><?php endif ?>
<?php if($can('engagementManageAction')): ?><?= Html::a('แผนปรับปรุง',['actions'],['class'=>'btn btn-outline-primary']) ?><?php endif ?>
<?= Html::a('กลับภาพรวม HR',['/hr/default/dashboard'],['class'=>'btn btn-outline-secondary']) ?>
</nav>
