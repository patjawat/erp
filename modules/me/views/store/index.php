<?php
use yii\helpers\Html;
/** @var yii\web\View $this */
?>
        <div class="card">
            <div class="card-body">
                <?= Html::a('<i class="fa-solid fa-cart-plus"></i> เบิกวัสดุ ', ['/helpdesk/default/repair-select','title' => '<i class="fa-regular fa-circle-check"></i> เลือกประเภทการซ่อม'], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-md']]) ?>
            </div>
        </div>

