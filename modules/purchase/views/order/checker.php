<?php
use yii\helpers\Html;
use app\modules\approve\models\Approve;
//เจ้าหน้าที่พัสดุ
$checker = Approve::find()->where(['name' => 'purchase', 'from_id' => $model->id,'level' => 2])->andWhere(['IN','status',['Approve','Reject','Pending']])->one();
$checkerStatus = Approve::find()->where(['name' => 'purchase', 'from_id' => $model->id,'level' => 3])->one();
?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-person-circle"></i> รายการอนุมัติความเห็น</h6>

        </div>
        <?php echo $model->StackApprove()?>
    </div>
    <div class="card-footer d-flex justify-content-between">
        <a class="open-modal" href="">รายการ</a>
        <div>
            <?php if($checker):?>
            <?php if($checker->status == 'Pending' &&  Yii::$app->user->can('purchase')):?>
            <!-- <a class="btn btn-sm btn-primary open-modal" data-size="modal-md"><i class="bi bi-plus-circle me-1"></i> เจ้าหน้าที่พัสดุตรวจสอบ</a> -->
            <?=Html::a('<i class="bi bi-clock"></i> เจ้าหน้าที่พัสดุตรวจสอบ',['/purchase/pr-order/checker-confirm','id' => $checker->id,'title' => 'เจ้าหน้าที่พัสดุตรวจสอบ'],
                                ['class' => 'btn btn-sm btn-warning open-modal','data' => ['size' => 'modal-md']])?>
            <?php  endif;?>

            <?php if($checkerStatus->level == 3):?>
            <?php if($checkerStatus->status == 'Pending'):?>
            <i class="bi bi-hourglass-split"></i> รอผู้อำนวยการอนุมัติ
            <?php endif?>
            <?php if($checkerStatus->status == 'Approve'):?>
            <i class="bi bi-check-circle"></i> ผู้อำนวยการอนุมัติ
            <?php endif?>

            <?php if($checkerStatus->status == 'Reject'):?>
            <i class="bi bi-x-circle"></i> ผู้อำนวยการอนุมัติไม่อนุมัติ
            <?php endif?>
            <?php endif?>
            <?php endif?>
        </div>
    </div>
</div>