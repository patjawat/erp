<?php

use yii\helpers\Html;
?>

<style>
    .doc-item:hover{
        border: 1px solid #3E7DC0;
    }
</style>
<div class="row">
    <div class="col-6">
        <div class="d-flex flex-column gap-2">
            <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded doc-item">
                <span class="badge rounded-pill bg-primary text-white me-1">1</span>
                <?= Html::a('ขออนุมัติจัดซื้อจัดจ้าง', ['/ms-word/purchase_1', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
            </div>

            <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded doc-item"><span
                    class="badge rounded-pill bg-primary text-white me-1">1</span>
                <?= Html::a('ขออนุมัติจัดซื้อจัดจ้าง', ['/ms-word/purchase_1', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
            </div>

            <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded doc-item"><span
                    class="badge rounded-pill bg-primary text-white me-1">2</span>
                <?= Html::a('ขออนุมัติแต่งตั้ง กก. กำหนดรายละเอียด', ['/ms-word/purchase_1', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
            </div>
            <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded doc-item"><span
                    class="badge rounded-pill bg-primary text-white me-1">3</span>
                <?= Html::a('คำสั่งจังหวัด...', ['/ms-word/purchase_2', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
            </div>
            <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded doc-item"><span
                    class="badge rounded-pill bg-primary text-white me-1">3</span>
                <?= Html::a('ขอความเห็นชอบและรายงานผล', ['/ms-word/purchase_2', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
            </div>
            <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded doc-item"><span
                    class="badge rounded-pill bg-primary text-white me-1">4</span>
                <?= Html::a('ขออนุมัติจัดซื้อจัดจ้าง', ['/ms-word/purchase_3', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
            </div>
            <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded doc-item"><span
                    class="badge rounded-pill bg-primary text-white me-1">5</span>
                <?= Html::a('รายการคุณลักษณะพัสดุ', ['/ms-word/purchase_4', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
            </div>
            <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded doc-item"><span
                    class="badge rounded-pill bg-primary text-white me-1">6</span>
                <?= Html::a('บันทึกข้อความรายงานการขอซื้อ', ['/ms-word/purchase_5', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
            </div>


        </div>
    </div>
    <div class="col-6">

        <div class="d-flex flex-column gap-2">
            <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded doc-item"><span
                    class="badge rounded-pill bg-primary text-white me-1">7</span>
                <?= Html::a('รายงานผลการพิจารณาและขออนุมัติสั่งซื้อสั่งจ้าง', ['/ms-word/purchase_6', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
            </div>

            <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded doc-item"><span
                    class="badge rounded-pill bg-primary text-white me-1">8</span>
                <?= Html::a('ประกาศผู้ชนะการเสนอราคา', ['/ms-word/purchase_7', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
            </div>
            <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded doc-item"><span
                    class="badge rounded-pill bg-primary text-white me-1">9</span>
                <?= Html::a('ใบสั่งซื้อ/สั่งจ้าง', ['/ms-word/purchase_8', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
            </div>
            <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded doc-item"><span
                    class="badge rounded-pill bg-primary text-white me-1">10</span>
                <?= Html::a('ใบตรวจรับการจัดซื้อ/จัดจ้าง', ['/ms-word/purchase_9', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
            </div>
            <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded doc-item"><span
                    class="badge rounded-pill bg-primary text-white me-1">11</span>
                <?= Html::a('รายงานผลการตรวจรับ', ['/ms-word/purchase_10', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
            </div>
            <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded doc-item"><span
                    class="badge rounded-pill bg-primary text-white me-1">12</span>
                <?= Html::a('แบบแสดงความบริสุทธิ์ใจ', ['/ms-word/purchase_11', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
            </div>
            <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded doc-item"><span
                    class="badge rounded-pill bg-primary text-white me-1">13</span>
                <?= Html::a('ขออนุมัติจ่ายเงินบำรุง', ['/ms-word/purchase_12', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
            </div>

        </div>
    </div>
</div>


<div class="d-flex flex-column gap-1">

</div>