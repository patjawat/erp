<?php
use yii\helpers\Html;
/** @var yii\web\View $this */
?>
        <div class="card">
            <div class="card-body">
                <?= Html::a('<i class="fa-solid fa-bag-shopping"></i> ขอซื้อ/ขอจ้าง ', ['/purchase/pr-order/create', 'name' => 'order', 'title' => '<i class="bi bi-plus-circle"></i> เพิ่มใบขอซื้อ-ขอจ้าง'], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-md']]) ?>
            </div>
        </div>

