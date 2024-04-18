<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/signup-confirm', 'authkey' => 'ttt']);
?>
<div class="password-reset">
    <p>สวัสดี <?= Html::encode('aoao') ?>,</p>

    <p>กรุณาคลิกลิ้งนี้เพื่อยืนยันการสมัครสมาชิกของคุณ :</p>

    <p><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>
</div>