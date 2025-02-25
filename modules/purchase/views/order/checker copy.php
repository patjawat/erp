<?php
use yii\helpers\Html;
use app\components\SiteHelper;
?>

<p>list approve</p>
<?php foreach($model->listApprove() as $item):?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <h6 class="mb-0"><span class="badge bg-primary rounded-pill text-white"><?php echo $item->level?></span>
            <?php echo $item->title;?></h6>
        <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $item->id?>"
            aria-expanded="true" aria-controls="collapseCard">
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>

    <div class="card-body collapse <?=$model->data_json['pr_director_confirm'] == '' ? 'show' : null?>" id="<?php echo $item->id?>">
        <!-- Start Flex Contriler -->
        <div class="d-flex justify-content-between align-items-start">
            <div class="text-truncate">
                <?php
try {
    echo $item->employee->getAvatar(false);
} catch (\Throwable $th) {
    //throw $th;
}
?>
            </div>
        </div>
        <!-- End Flex Contriler -->
    </div>

   
    <div class="card-footer d-flex justify-content-between">
        <h6>การอนุมัติ</h6>
        <div>
<?php echo Html::a('ดำเนินการ',['/approve']);?>
        </div>
    </div>

</div>
<?php endforeach;?>
<p>End approve</p>

<!-- ผู้อำนวยการอนุมัติ -->
<?php if($model->data_json['pr_officer_checker'] == 'Y'):?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <h6 class="mb-0"><span class="badge bg-primary rounded-pill text-white">3</span> ผู้อำนวยการอนุมัติ</h6>
        <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#Director"
            aria-expanded="true" aria-controls="collapseCard">
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>

    <div class="card-body collapse <?=$model->data_json['pr_director_confirm'] == '' ? 'show' : null?>" id="Director">
        <!-- Start Flex Contriler -->
        <div class="d-flex justify-content-between align-items-start">
            <div class="text-truncate">
                <?= SiteHelper::viewDirector()['avatar'] ?>
            </div>
        </div>
        <!-- End Flex Contriler -->
    </div>

    <div class="card-footer d-flex justify-content-between">
        <h6>การอนุมัติ</h6>
        <div>
            <?php if($model->data_json['pr_director_confirm'] == 'Y'):?>
            <?=Html::a('<i class="bi bi-check2-circle"></i> อนุมัติ',['/purchase/pr-order/director-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-success rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
            <?php elseif($model->data_json['pr_director_confirm'] == 'N'):?>
            <?=Html::a('<i class="fa-solid fa-user-slash"></i> ไม่อนุมัติ',['/purchase/pr-order/director-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-danger rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
            <?php else:?>
            <?=Html::a('<i class="fa-regular fa-clock"></i> รออนุมัติ',['/purchase/pr-order/director-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-warning rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
            <?php endif?>
        </div>
    </div>

</div>
<?php endif?>
<!-- จบส่วนผู้อำนวยการอนุมัติ -->


<!-- ผู้ตรวจสอบ -->
<?php if($model->data_json['pr_leader_confirm'] == 'Y'):?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">

        <h6 class="mb-0"><span class="badge bg-primary rounded-pill text-white">2</span> ผู้ตรวจสอบ</h6>
        <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#me"
            aria-expanded="true" aria-controls="collapseCard">
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>
    <div class="card-body collapse  <?=$model->data_json['pr_officer_checker'] == '' ? 'show' : null?>" id="me">
        <!-- Start Flex Contriler -->
        <div class="d-flex justify-content-between align-items-start">
            <div class="text-truncate">
                <?= $model->getMe()['avatar'] ?>
            </div>
        </div>
        <!-- End Flex Contriler -->
    </div>
    <div class="card-footer d-flex justify-content-between">
        <h6>จนท.พัสดุตรวจสอบ</h6>
        <div>
            <?php if($model->data_json['pr_officer_checker'] == 'Y'):?>
            <?=Html::a('<i class="bi bi-check2-circle"></i> ผ่าน',['/purchase/pr-order/checker-confirm','id' => $model->id],
                                ['class' => 'btn btn-sm btn-success rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
            <?php elseif($model->data_json['pr_officer_checker'] == 'N'):?>
            <?=Html::a('<i class="fa-solid fa-user-slash"></i> ไม่ผ่าน',['/purchase/pr-order/checker-confirm','id' => $model->id],
                                ['class' => 'btn btn-sm btn-danger rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
            <?php else:?>
            <?=Html::a('<i class="fa-regular fa-clock"></i> ตรวจสอบ',['/purchase/pr-order/checker-confirm','id' => $model->id],
                                ['class' => 'btn btn-sm btn-warning rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
            <?php endif?>
        </div>
    </div>
</div>
<?php endif?>
<!-- จบส่วนผู้ตรวจสอบ -->


<!-- ผู้เห็นชอบ -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <h6 class="mb-0"><span class="badge bg-primary rounded-pill text-white">1</span> ผู้เห็นชอบ</h6>
        <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#leader"
            aria-expanded="true" aria-controls="collapseCard">
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>
    <div class="card-body collapse <?=$model->data_json['pr_leader_confirm'] == '' ? 'show' : null?>" id="leader">
        <!-- Start Flex Contriler -->
        <div class="d-flex justify-content-between align-items-start">
            <div class="text-truncate">
                <?= $model->viewLeaderUser()['avatar'] ?>
            </div>
        </div>
        <!-- End Flex Contriler -->
    </div>
    <div class="card-footer d-flex justify-content-between">

        <h6>อนุมัติ/เห็นชอบ</h6>
        <div>
            <?php if($model->pr_number != ''):?>
            <?php if($model->data_json['pr_leader_confirm'] == 'Y'):?>
            <?=Html::a('<i class="bi bi-check2-circle"></i> เห็นชอบ',['/purchase/pr-order/leader-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-success rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
            <?php elseif($model->data_json['pr_leader_confirm'] == 'N'):?>
            <?=Html::a('<i class="fa-solid fa-user-slash"></i> ไม่เห็นชอบ',['/purchase/pr-order/leader-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-danger rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
            <?php else:?>
            <?=Html::a('<i class="fa-regular fa-clock"></i> รอเห็นชอบ',['/purchase/pr-order/leader-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-warning rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
            <?php endif?>
            <?php endif?>
        </div>
    </div>
</div>
<!-- จบส่วนผู้เห็นชอบ -->