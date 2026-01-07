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
        <div class="table-responsive" style="max-height: 600px;max-height: 600px;min-height:300px; overflow: auto;">
        <table class="table table-striped table-hover mb-0">
        <thead style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th class="text-center" style="width:30px">ลำดับ</th>
                        <th class="ps-4" style="width: 300px">รหัส/ผู้จอง</th>
                        <th style="width:200px">วันที่และเวลา</th>
                        <th>วัตถุประสงค์</th>
                        <th style="width: 25%;">สถานที่ไป</th>
                        <th class="text-center">พขร</th>
                        <th class="text-center" style="width:100px">ความเร่งด่วน</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-end pe-4" style="width:120px;">จัดการ</th>
                    </tr>
                </thead>
               <tbody class="align-middle table-group-divider">
                    <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                        <tr>
                            <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <?php
                                    echo Html::img('@web/img/loading.gif', [
                                    'class' => 'rounded-4 me-3 shadow lazyload',
                                    'width' => '40', 'height' => '40',
                                    'data' => [
                                        'expand' => '-20',
                                        'sizes' => 'auto',
                                        'src' => $item->userRequest()['photo']
                                    ]
                                    ]);?>
                                    <div>
                                        <div class="fw-bold mb-0"><?= $item->userRequest()['fullname'] ?></div>
                                        <small class="text-primary" style="font-size: 0.75rem;"><?= $item->userRequest()['department'] ?></small>
                                        <small class="text-muted d-block"><?= $item->code ?></small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="fw-medium text-dark"><?= $item->showDateRange() ?></div>
                                <div class="badge bg-light text-dark fw-normal border">
                                    <i class="bi bi-clock me-1"></i> <?= $item->viewTime()['full'] ?>
                                </div>
                            </td>
                            <td class="fw-bold"><?= $item->reason ?></td>

                            <td>
                                <div class="text-truncate" style="max-width: 200px;">
                                    <i class="bi bi-geo-alt text-danger me-1"></i><?= $item->locationOrg?->title ?? '-' ?>
                                </div>
                            </td>

                            <td class="text-center"><?php echo $item->StackDriver() ?></td>
                            <td class="text-center"><?php echo $item->viewUrgent() ?></td>
                            <td class="text-center">
                                <?php if ($item->is_shared == 1): ?>
                                    <i class="fa-solid fa-user-group"></i> จัดสรรร่วม
                                <?php else: ?>
                                    <?php
                                        echo $item->status;
                                        ?>
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

                                        <li><?= Html::a('<i class="fa-solid fa-eye me-2"></i>แสดง', ['/booking/vehicle/view', 'id' => $item->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                                        <li><?= Html::a('<i class="fa-solid fa-pen-to-square me-2"></i> แก้ไข', ['/booking/vehicle/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขการจงรถ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>

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
        </div>

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