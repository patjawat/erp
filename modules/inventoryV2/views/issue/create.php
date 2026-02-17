<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\StockOrder */

$this->title = 'สร้างใบเบิกจ่ายพัสดุ';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลังสินค้า', 'url' => ['/inventoryV2/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'รายการเบิกจ่าย', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="stock-order-create">

    <div class="row mb-3">
        <div class="col-md-12">
            <h1 class="h3 mb-2 text-gray-800">
                <i class="bi bi-box-arrow-up-right text-danger me-2"></i>
                <?= Html::encode($this->title) ?>
            </h1>
            <p class="text-muted">ใช้สำหรับบันทึกการจ่ายวัสดุออกจากคลัง (ระบบจะตัดสต็อกแบบ FIFO อัตโนมัติ)</p>
        </div>
    </div>

    <hr class="mb-4">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<style>
    /* ตกแต่งเล็กน้อยให้ดูทันสมัย */
    .stock-order-create h1 {
        font-weight: 700;
    }
    hr {
        border-top: 2px solid #eee;
    }
</style>