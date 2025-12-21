<?php

/* @var $this yii\web\View */
/* @var $model yii\base\Model */
/* @var $type string */

$this->title = 'เพิ่มทรัพย์สิน';
?>

<div class="fade-in">
    <?= $this->render('_form', [
        'model' => $model,
        'type' => $type, // ส่งค่า type ไปให้ form
    ]) ?>
</div>