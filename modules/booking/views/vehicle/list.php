<?php

use yii\web\View;
use yii\helpers\Html;
?>
<div class="card shadow-sm">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white mt-2">
                <i class="bi bi-ui-checks"></i> ทะเบียนการขอใช้รถยนต์
                <span class="badge text-bg-light">
                    <?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
            </h6>
            <div class="d-flex justify-content-between">
                <button class="btn btn-success export-leave"><i class="fa-solid fa-file-excel"></i> Excel</button>
            </div>
        </div>
    </div>

    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                    <th>รหัสการจอง</th>
                    <th>ผู้จอง</th>
                    <th>วันที่</th>
                    <th>เวลา</th>
                    <th>สถานที่ไป</th>
                    <th class="text-center">พขร</th>
                    <th class="text-center">ความเร่งด่วน</th>
                    <th class="text-center">สถานะ</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr data-date_start="<?= $item->date_start ?>" data-location="<?= $item->locationOrg?->title ?? '-' ?>">
                        <td class="text-center fw-semibold">
                            <?= (($dataProvider->pagination->offset + 1) + $key) ?>
                        </td>
                        <td><?= $item->code ?></td>
                        <td><?= $item->userRequest()['avatar'] ?></td>
                        <td>
                            <p class="mb-0 fw-semibold"><?= $item->showDateRange() ?> </p>
                        </td>
                        <td>
                            <p class="mb-0 fw-semibold"><?= $item->viewTime()['full'] ?></p>
                        </td>
                        <td>
                            <p class="mb-0 fs-11"><?= $item->locationOrg?->title ?? '-' ?></p>
                        </td>
                        <td class="text-center"><?=$item->StackDriver()?></td>
                        <td class="text-center"><?= $item->viewUrgent() ?></td>
                        <td class="text-center">
                            <?php if ($item->is_shared == 1): ?>
                                <i class="fa-solid fa-user-group"></i> จัดสรรร่วม
                                <?php else: ?>
                                    <?= $item->viewStatus()['view'] ?? '-' ?>
                                <?php endif; ?>
                        </td>

                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    จัดการ
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1" style="">
                                    <li> <?= Html::a(
                                                '<i class="fa-solid fa-user-tag me-1"></i> จัดสรร',
                                                ['/booking/vehicle/approve', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไขข้มูลขอใช้รถ'],
                                                ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]
                                            ) ?></li>
                                    <li><?= Html::a('<i class="fa-solid fa-eye me-2"></i>แสดง', ['view', 'id' => $item->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                                    <li><?= Html::a('<i class="fa-solid fa-pen-to-square me-2"></i> แก้ไข', ['update', 'id' => $item->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                                    <!-- <li><?php // Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ใบขอรถยนต์', ['/booking/vehicle/print', 'id' => $item->id, 'title' => 'ใบขอใช้รถยนต์'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?></li> -->
                                    <li><?= Html::a(
                                        '<i class="fa-solid fa-print me-1"></i> พิมพ์ใบขอรถยนต์',
                                        ['/booking/vehicle/print', 'id' => $item->id, 'title' => 'ใบขอใช้รถยนต์'],
                                        ['class' => 'dropdown-item', 'target' => '_blank']
                                    ) ?></li>
                                    
                                    <li><?= Html::a('<i class="fa-regular fa-circle-xmark me-2"></i> ยกเลิก', ['/booking/vehicle/cancel', 'id' => $item->id], ['class' => 'dropdown-item cancel-order', 'data' => ['size' => 'modal-lg']]) ?></li>

                                    <li>
</li>
                                </ul>
                            </div>
                        </td>

                       
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="body-footer">
            <div class="d-flex justify-content-center">
                <?= yii\bootstrap5\LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'firstPageLabel' => 'หน้าแรก',
                    'lastPageLabel' => 'หน้าสุดท้าย',
                    'options' => [
                        'class' => 'pagination pagination-sm',
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>




<?php
$js = <<< JS


JS;
$this->registerJS($js, View::POS_END);
?>