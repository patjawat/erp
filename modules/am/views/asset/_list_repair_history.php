<?php

use app\modules\helpdesk2\models\Helpdesk;

$repairHistorys = Helpdesk::find()->where(['asset_number' => $model->code])->all();
?>


<table class="table table-hover align-middle mb-0">
    <thead class="table-light text-secondary small">
        <tr>
            <th class="px-4 py-3 fw-medium">รหัสงานซ่อม/วันที่แจ้ง</th>
            <th class="px-4 py-3 fw-medium">อุปกรณ์ / อาการ</th>
            <th class="px-4 py-3 fw-medium">ผู้แจ้ง</th>
            <th scope="col">ความเร่งด่วน</th>
            <th class="px-4 py-3 fw-medium text-end">ค่าใช้จ่าย</th>
            <th class="px-4 py-3 fw-medium text-center">สถานะ</th>
        </tr>
    </thead>
    <tbody class="border-top-0">
        <?php foreach ($repairHistorys as $key => $item): ?>
            <tr>
                <td class="px-4 py-3 text-dark fw-medium">
                    <div class="fw-medium text-dark"><?php echo $item->repair_number ?></div>
                    <div class="text-muted small"><?= $item->viewCreateDateTime() ?></div>
                </td>
                <td class="px-4 py-3">
                    <div class="fw-medium text-dark"><?= $item->deviceType->title ?? '-' ?></div>
                    <div class="text-muted small"><?= $item->title ?></div>
                </td>
                <td class="px-4 py-3 text-secondary"><?= $item->emp->getInfo()['avatar'] ?></td>
                <td><?= $item->viewUrgent()['view'] ?></td>
                <td class="px-4 py-3 text-end fw-medium">0.00</td>
                <td class="px-4 py-3 text-center">
                    <?= $item->viewStatus() ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
