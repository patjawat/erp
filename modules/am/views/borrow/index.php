<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetailSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'รายการยืม-คืนทั้งหมด';
$this->params['breadcrumbs'][] = $this->title;
$iconClean = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="calendar-sync" class="lucide lucide-calendar-sync"><path d="M11 10v4h4"></path><path d="m11 14 1.535-1.605a5 5 0 0 1 8 1.5"></path><path d="M16 2v4"></path><path d="m21 18-1.535 1.605a5 5 0 0 1-8-1.5"></path><path d="M21 22v-4h-4"></path><path d="M21 8.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h4.3"></path><path d="M3 10h4"></path><path d="M8 2v4"></path></svg>'
?>
<div class="d-flex justify-content-between">
    <h6><?= Html::encode($this->title) ?></h6>
    <p>
        <?= Html::a('<i class="fa-solid fa-plus"></i> บันทึกการยืม', ['create', 'code' => $searchModel->code, 'title' => $iconClean . ' บันทึกการยืม'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
    </p>
</div>
<div class="table-responsive">
    <table class="table table-hover align-middle border-top">
        <thead class="bg-light">
            <tr>
                <th class="text-center" style="width:30px">ลำดับ</th>
                <th>ผู้ยืม / หน่วยงาน</th>
                <th class="text-center">วันที่ยืม</th>
                <th class="text-center">กำหนดคืน</th>
                <th class="text-center">วันที่คืนจริง</th>
                <th class="">ผู้ดำเนินการ</th>
                <th class="text-center">สถานะ</th>
                <th class="text-end">จัดการ</th>
            </tr>
        </thead>
        <tbody>
        <tbody>
            <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                <?php
                // UX: ถ้ายังไม่คืนให้แถวมีสีเหลืองจางๆ (table-warning-subtle) 
                // ถ้าคืนแล้วให้เป็นสีปกติ เพื่อแยกแยะรายการที่ค้างอยู่
                $isReturned = !empty($item->actual_date);
                $rowClass = $isReturned ? '' : 'table-warning-subtle';
                ?>
                <tr class="<?= $rowClass ?>">
                    <td class="text-center text-muted">
                        <?php echo (($dataProvider->pagination->offset + 1) + $key) ?>
                    </td>
                    <td><?= $item->employee->getAvatar(false) ?></td>
                    <td class="text-center"><?= AppHelper::convertToThai($item->date_start) ?></td>
                    <td class="text-center text-primary fw-bold"><?= AppHelper::convertToThai($item->date_end) ?></td>
                    <td class="text-center">
                        <?= $isReturned ? AppHelper::convertToThai($item->actual_date) : '<span class="text-muted small">-</span>' ?>
                    </td>
                    <td><?= $item->staff?->getAvatar(false) ?? '-' ?></td>
                    <td class="text-center">
                        <?= $item->getBorrowStatusLabel() ?>
                    </td>
                    <td class="text-end">
                        <?= Html::a('view',['view','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?>
                        <?php if (!$isReturned): ?>
                            <?= Html::a(
                                '<i class="bi bi-arrow-return-left me-1"></i> รับคืน',
                                ['borrow-return', 'id' => $item->id, 'title' => 'บันทึกการรับคืน'],
                                ['class' => 'btn btn-sm btn-success rounded-pill px-3 open-modal', 'data' => ['size' => 'modal-lg']]
                            ) ?>
                        <?php else: ?>
                            <?= Html::a('<i class="bi bi-printer"></i>', ['print-receipt', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        </tbody>
    </table>
</div>