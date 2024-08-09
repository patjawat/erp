<?php
use yii\helpers\Html;
/** @var yii\web\View $this */
?>

<div class="card">
            <div class="card-body">
                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> รับวัสดุ', ['/inventory/receive/create', 'receive_type' => 'receive', 'title' => '<i class="fa-solid fa-cubes-stacked"></i> ใบรับสินค้า'], ['id' => 'btn-add1', 'class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>
            </div>
        </div>
