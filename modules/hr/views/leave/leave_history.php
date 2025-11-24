<?php
use yii\bootstrap5\Html;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\hr\models\Leave;

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
        <?php foreach($model as $item):?>
            <tr class="">
            <td class="text-center"><?php echo $item->thai_year?></td>
            <td class="text-truncate" style="max-width: 230px;"><?=$item->getAvatar(false)['avatar']?></td>
            <td><?=$item->leaveType?->title ?? '-'?></td>
            <td><?=$item->showLeaveDate()?></td>
            <td class="text-center"><?php echo $item->total_days?></td>
            <td class="text-start"><?php echo $item->data_json['reason']?></td>
            
        </tr>
        <?php endforeach;?>
    </tbody>
</table>

