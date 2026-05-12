<?php
use yii\helpers\Html;

?>
<div class="card">
    <div class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <div class="d-flex gap-3 justify-content-start">
            <div class="btn-group">
            <?= Html::a('<i class="fa-solid fa-business-time"></i> ใบขอซื้อ', ['/sm/pr-order', 'name' => 'pr'], ['class' => 'btn btn-light']) ?>
                <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                    <i class="bi bi-caret-down-fill"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><?= Html::a('<i class="fa-solid fa-circle-plus text-primary me-1"></i> สร้างใบขอซื้อ', ['/sm/order/create', 'name' => 'pr', 'title' => '<i class="bi bi-plus-circle"></i> เพิ่มขอซื้อ-ขอจ้าง (PR)'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                    </li>
                </ul>
            </div>
            <div class="btn-group">
            <?= Html::a('<i class="fa-solid fa-basket-shopping"></i> ใบสั่งซื้อ', ['/sm/po-order', 'name' => 'po'], ['class' => 'btn btn-light']) ?>
                <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                    <i class="bi bi-caret-down-fill"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><?= Html::a('<i class="fa-solid fa-circle-plus text-primary me-1"></i> สร้างใบสั่งซื้อ', ['/sm/order/create', 'name' => 'po', 'status' => 5, 'title' => '<i class="bi bi-plus-circle"></i> สร้างใบสั่งซื้อ (PO)'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                    </li>
                </ul>
            </div>

        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-list-ul"></i>', ['#', 'view' => 'list'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="bi bi-grid"></i>', ['#', 'view' => 'grid'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="fa-solid fa-gear"></i>', ['#', 'title' => 'การตั้งค่าบุคลากร'], ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>
        </div>

    </div>
</div>