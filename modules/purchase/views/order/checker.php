<?php
use yii\helpers\Html;
use app\models\Approve;
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
            <!-- <a class="btn btn-sm btn-primary rounded-pill open-modal" data-size="modal-md"><i class="fa-solid fa-circle-plus me-1"></i> เจ้าหน้าที่พัสดุตรวจสอบ</a> -->
            <?=Html::a('<i class="fa-regular fa-clock"></i> เจ้าหน้าที่พัสดุตรวจสอบ',['/purchase/pr-order/checker-confirm','id' => $checker->id],
                                ['class' => 'btn btn-sm btn-warning rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
            <?php  endif;?>

            <?php if($checkerStatus->level == 3):?>
            <?php if($checkerStatus->status == 'Pending'):?>
            <i class="fa-regular fa-hourglass-half"></i> รอผู้อำนวยการอนุมัติ
            <?php endif?>
            <?php if($checkerStatus->status == 'Approve'):?>
            <i class="fa-regular fa-circle-check"></i> ผู้อำนวยการอนุมัติ
            <?php endif?>

            <?php if($checkerStatus->status == 'Reject'):?>
            <i class="fa-regular fa-circle-xmark"></i> ผู้อำนวยการอนุมัติไม่อนุมัติ
            <?php endif?>
            <?php endif?>
            <?php endif?>
        </div>
    </div>
</div>