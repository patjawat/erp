<?php

use app\modules\purchase\models\Order;
use yii\helpers\Html;


$prCount = Order::countPrOrder();
$pqCount = Order::countPqOrder();
$poCount = Order::countPoOrder();
$prSum = Order::SumOrderPrice();
?>
<div class="row">
    <div class="col-3">
        <div class="text-bg-light p-3 rounded-2">
            <div class="d-flex justify-content-between gap-1 mb-0">
                <span class="h5 fw-semibold"><i class="bi bi-currency-dollar"></i><?=$prSum;?>K</span>
                <i class="bi bi-plus-circle-fill text-black-50"></i>
            </div>
            <div class="d-flex justify-content-between gap-1 mb-0">
                <?=Html::a('ขอซื้อ-ขอจ้าง',['/purchase/pr-order'])?>
                <span class="text-black bg-primary-subtle badge rounded-pill fw-ligh fs-13"><?=$prCount;?></span>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="text-bg-light p-3 rounded-2">
            <div class="d-flex justify-content-between gap-1 mb-0">
                <span class="h5 fw-semibold"><i class="bi bi-currency-dollar"></i>64K</span>
                <i class="fa-solid fa-user-check text-black-50"></i>
            </div>
            <div class="d-flex justify-content-between gap-1 mb-0">
                <?=Html::a('ทะเบียนคุม',['/purchase/pq-order'])?>
                <span class="text-black bg-primary-subtle badge rounded-pill fw-ligh fs-13"><?=$pqCount;?></span>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="text-bg-light p-3 rounded-2">
            <div class="d-flex justify-content-between gap-1 mb-0">
                <span class="h5 fw-semibold"><i class="bi bi-currency-dollar"></i>30K</span>
                <i class="bi bi-rocket text-black-50"></i>
            </div>
            <div class="d-flex justify-content-between gap-1 mb-0">
            <?=Html::a('สั่งซื้อ',['/purchase/po-order'])?>
                <span class="text-black bg-primary-subtle badge rounded-pill fw-ligh fs-13"><?=$poCount;?></span>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="text-bg-light p-3 rounded-2">
            <div class="d-flex justify-content-between gap-1 mb-0">
                <span class="h5 fw-semibold"><i class="bi bi-currency-dollar"></i>120K</span>
                <i class="bi bi-bag-check-fill text-black-50"></i>
            </div>
            <div class="d-flex justify-content-between gap-1 mb-0">
                <span>ดำเนินการแล้ว</span>
                <span class="text-black bg-primary-subtle badge rounded-pill fw-ligh fs-13">1200</span>
            </div>
        </div>
    </div>
</div>