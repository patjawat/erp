<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model yii\base\Model */

// 1. ตั้งชื่อ Title เป็น "เพิ่มรายการใหม่"
$this->title = 'เพิ่มรายการใหม่';

// 2. ตั้งค่า Breadcrumb (เหลือแค่ชื่อหน้าปัจจุบัน)
$this->params['breadcrumbs'] = [
    $this->title, // แสดงคำว่า "เพิ่มรายการใหม่"
];
?>

<div class="fade-in">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>