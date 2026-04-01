<?php
use yii\bootstrap5\Html;
use app\components\AppHelper;
use app\modules\leave\models\Leave;
?>
<table class="table table-striped mt-3">
    <thead>
        <tr class="table-secondary">
            <th scope="col">ปีงบประมาณ</th>
            <th scope="col">ผู้ขออนุมัติการลา</th>
            <th scope="col">ประเภทการลา</th>
            <th scope="col">วันที่</th>
            <th class="text-center" scope="col">เป็นเวลา/วัน</th>
            <th scope="col">เหตุผล</th>
        </tr>
    </thead>
    <tbody class="align-middle table-group-divider">
        <?php foreach ($model as $item): ?>
        <tr>
            <td class="text-center"><?= Html::encode($item->thai_year) ?></td>
            <td class="text-truncate" style="max-width: 230px;"><?= $item->getAvatar(false)['avatar'] ?? '-' ?></td>
            <td><?= $item->leaveType ? Html::encode($item->leaveType->title) : '-' ?></td>
            <td><?= $item->showLeaveDate() ?></td>
            <td class="text-center"><?php
                $d = (float) $item->total_days;
                echo $d == (int) $d ? (string) (int) $d : number_format($d, 1, '.', '');
            ?></td>
            <td class="text-start"><?= Html::encode($item->data_json['reason'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
