<?php
use yii\helpers\Html;
?>
<ul class="list-inline">
    <li><span class="badge rounded-pill bg-primary text-white">1</span> <?= Html::a('ขออนุมัติแต่งตั้ง กก. กำหนดรายละเอียด', ['/ms-word/purchase_1', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><span class="badge rounded-pill bg-primary text-white">2</span> <?= Html::a('ขอความเห็นชอบและรายงานผล', ['/ms-word/purchase_2', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><span class="badge rounded-pill bg-primary text-white">3</span> <?= Html::a('ขออนุมัติจัดซื้อจัดจ้าง', ['/ms-word/purchase_3', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><span class="badge rounded-pill bg-primary text-white">4</span> <?= Html::a('รายการคุณลักษณะพัสดุ', ['/ms-word/purchase_4', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><span class="badge rounded-pill bg-primary text-white">5</span> <?= Html::a('บันทึกข้อความรายงานการขอซื้อ', ['/ms-word/purchase_5', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><span class="badge rounded-pill bg-primary text-white">6</span> <?= Html::a('รายงานผลการพิจารณาและขออนุมัติสั่งซื้อสั่งจ้าง', ['/ms-word/purchase_6', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><span class="badge rounded-pill bg-primary text-white">7</span> <?= Html::a('ประกาศผู้ชนะการเสนอราคา', ['/ms-word/purchase_7', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><span class="badge rounded-pill bg-primary text-white">8</span> <?= Html::a('ใบสั่งซื้อ/สั่งจ้าง', ['/ms-word/purchase_8', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><span class="badge rounded-pill bg-primary text-white">9</span> <?= Html::a('ใบตรวจรับการจัดซื้อ/จัดจ้าง', ['/ms-word/purchase_9', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><span class="badge rounded-pill bg-primary text-white">10</span> <?= Html::a('รายงานผลการตรวจรับ', ['/ms-word/purchase_10', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><span class="badge rounded-pill bg-primary text-white">11</span> <?= Html::a('แบบแสดงความบริสุทธิ์ใจ', ['/ms-word/purchase_11', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><span class="badge rounded-pill bg-primary text-white">12</span> <?= Html::a('ขออนุมัติจ่ายเงินบำรุง', ['/ms-word/purchase_12', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
</ul>