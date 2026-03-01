<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\leave\models\Leave $model */
?>
<div class="d-flex align-items-center gap-2 mb-4">
    <div class="p-2 bg-primary bg-opacity-10 rounded-circle text-primary"><i class="bi bi-file-text fs-5"></i></div>
    <h6 class="fw-bold mb-0 text-body">ประวัติการลา</h6>
</div>
<table class="table table-hover align-middle mb-0">
    <thead class="table-light">
        <tr>
            <th scope="col">ผู้ขออนุมัติการลา</th>
            <th scope="col">ประเภทการลา</th>
            <th scope="col">เหตุผล</th>
            <th class="text-center" scope="col">เป็นเวลา/วัน</th>
            <th scope="col">วันที่</th>
            <th scope="col">ปีงบประมาณ</th>
        </tr>
    </thead>
    <tbody class="align-middle table-group-divider">
        <?php foreach ($model->listHistory() as $item): ?>
            <?php $avatarData = $item->getAvatar($item->emp_id, ''); $avatarHtml = isset($avatarData['avatar']) ? $avatarData['avatar'] : ''; ?>
            <tr>
                <td class="text-truncate" style="max-width: 230px;">
                    <?= $avatarHtml ?>
                </td>
                <td class="text-start"><?= $item->leaveType ? Html::encode($item->leaveType->title) : '-' ?></td>
                <td class="text-start"><?= Html::encode($item->data_json['reason'] ?? '-') ?></td>
                <td class="text-center"><?= (float)$item->total_days ?></td>
                <td><?= $item->showLeaveDate() ?></td>
                <td class="text-center"><?= (int)$item->thai_year ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
