<?php

use yii\helpers\Html;
?>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th class="text-center" style="width:30px">ลำดับ</th>
            <th>ผู้ขอ</th>
            <th>ห้องประชุม</th>
            <th>วันที่ต้องการใช้</th>
            <th>หัวข้อการประชุม</th>
            <th>หน่วยงาน</th>
            <th class="fw-semibold text-center">สถานะ</th>
            <th class="fw-semibold text-center">ดำเนินการ</th>
        </tr>
    </thead>
    <tbody class="table-group-divider align-middle">
        <!-- Row 1 -->
        <?php foreach ($dataProvider->getModels() as $key => $item): ?>
            <tr>
                <td class="text-center">
                    <?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                    <td><?= $item->getUserReq()['avatar'] ?></td>
                    <td class="fs-13"><?= $item->room?->title ?? '-' ?></td>
                    <td>
                    <?= $item->viewMeetingDate() ?>
                    <p class="text-muted mb-0 fs-13">
                        เริ่มเวลา <?= $item->viewTime()['full'] ?>
                    </p>
                </td>
                <td class="fs-13">
                    <?= $item->title ?>
                </td>
                <td><?= $item->getUserReq()['department'] ?></td>
                <td class="text-center"><?= $item->viewStatus()['view'] ?>
                </td>
                <td class="text-center">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                            id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            จัดการ
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li><?= Html::a('<i class="fa-solid fa-eye me-2"></i>แสดง', [$url . 'view', 'id' => $item->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                            <li><?= Html::a('<i class="fa-solid fa-pen-to-square me-2"></i>แก้ไข', [$url . 'view', 'id' => $item->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                            <li><?= Html::a('<i class="fa-regular fa-circle-xmark me-2"></i> ยกเลิก', ['/booking/vehicle/cancel', 'id' => $item->id], ['class' => 'dropdown-item', 'data' => ['size' => 'modal-lg']]) ?></li>
                        </ul>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>