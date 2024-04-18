<?php
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $user common\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/signup-confirm', 'authkey' =>'aaa']);
?>
สวัสดี patjawtxxx,

กรุณาคลิกลิ้งนี้เพื่อยืนยันการสมัครสมาชิกของคุณ :

<?= Html::encode($resetLink) ?>