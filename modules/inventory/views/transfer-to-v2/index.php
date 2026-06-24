<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'ส่งออกไป Inventory V2';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="share-2"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventory/menu_dashbroad', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= Yii::$app->session->getFlash('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-end mb-2">
    <?= Html::a('<i class="fa-solid fa-clipboard-list me-1"></i> ย้ายใบเบิกค้างจ่าย → V2', ['requisition-index'], [
        'class' => 'btn btn-outline-primary btn-sm',
    ]) ?>
</div>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2 mb-0"><i class="fa-solid fa-filter"></i> เงื่อนไข</h6>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            เลือก <strong>ประเภทวัสดุ</strong> (บังคับ) และ <strong>ช่วงวันที่</strong> เพื่อใช้ยอด "คงเหลือสิ้นเดือน" จากรายงาน แล้วเลือก <strong>คลังหลักใน Inventory V2</strong> ที่จะสร้างใบรับเข้า (สถานะฉบับร่าง)
        </p>
        <?= $this->render('_form', [
            'model' => $searchModel,
            'listWarehouses' => $listWarehouses,
            'mainWarehouseId' => $mainWarehouseId,
        ]) ?>
    </div>
</div>

<?php if (!empty($searchModel->asset_type_id) && !empty($rowsForV2)): ?>
<div class="card">
    <div class="card-header bg-primary-gradient text-white d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h6 class="text-white mt-2 mb-0">
            รายการคงเหลือสิ้นเดือนที่จะสร้างเป็นใบรับเข้า (ฉบับร่าง) ใน V2
            <span class="badge text-white bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">
                <?= count($rowsForV2) ?> รายการ
            </span>
        </h6>
        <?php if ($mainWarehouseId !== ''): ?>
        <?php
        $form = \yii\widgets\ActiveForm::begin([
            'action' => ['/inventory/transfer-to-v2/create-receive'],
            'method' => 'post',
            'options' => ['class' => 'd-inline'],
        ]);
        echo Html::hiddenInput('StockEventSearch[date_start]', $searchModel->date_start);
        echo Html::hiddenInput('StockEventSearch[date_end]', $searchModel->date_end);
        echo Html::hiddenInput('StockEventSearch[asset_type_id]', $searchModel->asset_type_id);
        echo Html::hiddenInput('main_warehouse_id', $mainWarehouseId);
        echo Html::submitButton('<i class="fa-solid fa-file-import me-1"></i> สร้างใบรับเข้าใน V2 (ฉบับร่าง)', [
            'class' => 'btn btn-light btn-sm',
            'data' => ['confirm' => 'ยืนยันสร้างใบรับเข้าใน Inventory V2 เป็นฉบับร่าง?'],
        ]);
        \yii\widgets\ActiveForm::end();
        ?>
        <?php else: ?>
        <span class="text-white-50 small">เลือกคลัง V2 ด้านบน แล้วกด ดูรายการ เพื่อแสดงปุ่มสร้างใบรับเข้า</span>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 48px;">ลำดับ</th>
                        <th>รหัสสินค้า</th>
                        <th>รายการสินค้า</th>
                        <th class="text-center">ประเภทวัสดุ</th>
                        <th class="text-end">จำนวนคงเหลือ</th>
                        <th class="text-end">มูลค่าคงเหลือ</th>
                        <th class="text-end">ราคา/หน่วย</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php $n = 1; foreach ($rowsForV2 as $r): ?>
                        <tr>
                            <td class="text-center"><?= $n++ ?></td>
                            <td><?= Html::encode($r['asset_item']) ?></td>
                            <td><?= Html::encode($r['asset_name']) ?></td>
                            <td class="text-center"><?= Html::encode($r['asset_type_name']) ?></td>
                            <td class="text-end"><?= number_format($r['end_qty'], 5) ?></td>
                            <td class="text-end"><?= number_format($r['end_price'], 5) ?></td>
                            <td class="text-end"><?= number_format($r['unit_price'], 5) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php elseif (Yii::$app->request->get() && $searchModel->asset_type_id !== '' && empty($rowsForV2)): ?>
    <div class="alert alert-info">
        <i class="fa-solid fa-info-circle me-1"></i>
        ไม่มีรายการคงเหลือสิ้นเดือนที่ตรงกับประเภทวัสดุที่เลือก หรือรายการไม่มีในระบบ Inventory V2
    </div>
<?php endif; ?>
