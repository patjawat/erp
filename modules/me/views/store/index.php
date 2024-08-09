<?php
use yii\helpers\Html;
/** @var yii\web\View $this */
$this->title = "เบิกวัสดุ/อุปกรณ์";
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cart-plus"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>




        <div class="card">
            <div class="card-body">
                <?= Html::a('<i class="fa-solid fa-cart-plus"></i> เบิกวัสดุ ', ['/helpdesk/default/repair-select','title' => '<i class="fa-regular fa-circle-check"></i> เลือกประเภทการซ่อม'], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-md']]) ?>
            </div>
        </div>

