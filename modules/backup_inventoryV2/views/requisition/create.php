<?php
use yii\helpers\Html;

$this->title = 'สร้างใบขอเบิกวัสดุ (จากคลังหลัก)';
$this->params['breadcrumbs'][] = ['label' => 'รายการใบขอเบิก', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="requisition-create">
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-cart-plus fs-1 text-primary me-3"></i>
        <div>
            <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
            <small class="text-muted">สร้างคำขอเบิกไปยังคลังหลักเพื่อรอการอนุมัติจ่ายสินค้า</small>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>