<?php

use app\models\Categorise;
use yii\widgets\Pjax;
use app\modules\leave\models\LeaveEntitlements;
use app\modules\leave\models\LeavePermission;
use app\modules\leave\models\LeaveTypes;
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = "ตั้งค่าระเบียบสิทธิการลา";

$listLeaveType = LeaveTypes::find()
    ->andWhere(['active' => 1])
    ->andWhere(['NOT IN', 'name', ['ลาออก']])->all();
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar-day"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'leave']); ?>
<div class="card">
    <div class="card-body">

        <div class="table-container">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width:50px;">รหัส</th>
                        <th>รายการ</th>
                        <?php foreach ($listLeaveType as $leaveType): ?>
                            <th class="text-center" style="width:150px;"><?= $leaveType->name ?></th>
                        <?php endforeach; ?>
                    <tr>
                </thead>
                <tbody>
                    <?php foreach (Categorise::find()->where(['name' => 'position_type'])->all() as $item): ?>
                        <tr>
                            <td><?= $item->code ?></td>
                            <td><?= $item->title ?></td>
                            <?php foreach ($listLeaveType as $leaveItem): ?>
                                <td class="text-center">
                                <?=Html::a($leaveType->viewDay($leaveItem->id,$item->code),['/leave/leave-permission/update','position_type_id' => $item->code,'leave_type_id' => $leaveItem->id,'title' => '<i class="bi bi-ui-checks"></i> กำหนดวัน'.$leaveItem->name],['class' => 'open-modal','data' => ['size' => 'modal-md']]);?>    
                            </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>


    </div>
</div>

<div class="d-flex justify-content-center">
            <?=Html::a('<i class="bi bi-arrow-left-circle"></i> ย้อนกลับ',['/leave/leave-types'],['class' => 'btn btn-primary shadow rounded-pill text-center'])?>
        </div>
<?php Pjax::end(); ?>