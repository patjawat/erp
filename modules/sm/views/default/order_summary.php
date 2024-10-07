<?php

use app\modules\purchase\models\Order;
use yii\helpers\Html;


// $pr = Order::prSummery();
$pq = Order::pqSummery();
$po = Order::poSummery();
$success = Order::orderSuccess();


?>
<div class="row">
    <div class="col-3">
        <div class="text-bg-light p-3 rounded-2">
            <div class="d-flex justify-content-between gap-1 mb-0">
                <span class="h5 fw-semibold"><?=$model->prSummery()['price'];?> บาท</span>
                <i class="bi bi-plus-circle-fill text-black-50"></i>
            </div>
            <div class="d-flex justify-content-between gap-1 mb-0">
                <?=Html::a('ขอซื้อ-ขอจ้าง',['/purchase/pr-order'])?>
                <span class="text-black bg-primary-subtle badge rounded-pill fw-ligh fs-13"><?=$model->prSummery()['total'];?></span>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="text-bg-light p-3 rounded-2">
            <div class="d-flex justify-content-between gap-1 mb-0">
                <span class="h5 fw-semibold"><?=number_format($pq['price'],2)?> บาท</span>
                <i class="fa-solid fa-user-check text-black-50"></i>
            </div>
            <div class="d-flex justify-content-between gap-1 mb-0">
                <?=Html::a('ทะเบียนคุม',['/purchase/pq-order'])?>
                <span class="text-black bg-primary-subtle badge rounded-pill fw-ligh fs-13"><?=$pq['total'];?></span>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="text-bg-light p-3 rounded-2">
            <div class="d-flex justify-content-between gap-1 mb-0">
                <span class="h5 fw-semibold"><?=number_format($po['price'],2)?> บาท</span>
                <i class="bi bi-rocket text-black-50"></i>
            </div>
            <div class="d-flex justify-content-between gap-1 mb-0">
            <?=Html::a('ออกใบสั่งซื้อ',['/purchase/po-order'])?>
                <span class="text-black bg-primary-subtle badge rounded-pill fw-ligh fs-13"><?=$po['total'];?></span>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="text-bg-light p-3 rounded-2">
            <div class="d-flex justify-content-between gap-1 mb-0">
                <span class="h5 fw-semibold"><?=number_format($success['price'],2)?></span>
                <i class="bi bi-bag-check-fill text-black-50"></i>
            </div>
            <div class="d-flex justify-content-between gap-1 mb-0">
                <span>ตรวจรับแล้ว</span>
                <span class="text-black bg-primary-subtle badge rounded-pill fw-ligh fs-13"><?=$success['total']?></span>
            </div>
        </div>
    </div>
</div>