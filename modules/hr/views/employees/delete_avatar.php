<?php
use yii\bootstrap5\Html;
use yii\helpers\Url;
?>
<style>
    .profile-user-wid {
    margin-top: -30px;
}

.avatar-md > img{
    height: 6.5rem;
    width: 6.5rem;
    max-width:6.5rem;
    max-height:6.5rem;
}
</style>
<?php
// print_r($model->UpdateFormDetail());
// echo Html::img($model->showAvatar(),['style' =>'margin-top: 25px;max-width: 135px;max-height: 135px;']);
// print_r($model->showAvatar());
?>
<div class="overflow-hidden card rounded-4 shadow-none border border-3 border-primary border-end-0 border-bottom-0 border-start-0 mb-4 custom-card hrm-main-card primary">
            <div class="bg-<?=$model->status == 'ลาออก' ? 'danger' : 'primary'?>-subtle">
                <div class="row">
                    <div class="col-7">
               
                        <div class="text-primary p-3">
                            <h5 class="text-primary"><?=isset($model->statusName->title) ? $model->statusName->title : '-'?> <i class="fa-solid fa-user-check"></i></h5>
                            <p class="text-truncate">อายุราชการ 1 ปี 8 เดือน 8 วัน</p>
                        </div>
                    </div>
                    <div class="align-self-end col-5">
                        <?=Html::img('@web/img/profile-bg.png',['class' => 'img-fluid'])?>    
                       </div>
                </div>
            </div>
            <div class="pt-0 card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="avatar-md profile-user-wid mb-4">
                        <?php // Html::img($model->showAvatar(),['class' => 'object-fit-cover img-thumbnail rounded-circle shadow'])?>    
                        <?=Html::img($model->showAvatar(),['class' => 'object-fit-cover rounded shadow'])?>    
                            </div>
                        <h5 class="font-size-15 text-truncate"><?=$model->fullname;?></h5>
                        <p class="mb-0 text-truncate"><?=isset($model->positionName->title) ? $model->positionName->title : '-'?></p>
                    </div>
                    <div class="col-6">
                        <div class="pt-4">
                            <div class="row">
                                <div class="col-12">
                                    <?php if($model->position_type == 'ข้าราชการ'):?>
                                        <h5 class="font-size-15 text-truncate">เกษียณอายุราชการ</h5>
                                    <p class="mb-0"><i class="fa-regular fa-clock"></i> <?=$model->date_end?></p>
                                    <?php else:?>
                                    <h5 class="font-size-15 text-truncate">วันครบกำหนดสัญญาจ้าง</h5>
                                    <p class="mb-0"><i class="fa-regular fa-clock"></i> <?=$model->date_end?></p>
                                    <?php endif;?>
                                </div>
                               
                            </div>
                            <div class="mt-4">
                                <?php if(isset($list)):?>
                                    <?=Html::a('<i class="fa-solid fa-eye"></i> เพิ่มเติม',['/hr/employees/view','id' => $model->id],['class' => 'btn btn-primary'])?>
                            <?php else:?>
                                <?=Html::a('<i class="fa-solid fa-print"></i> หนังสือรับรอง ',['/hr/employees/view','id' => $model->id,'detail' => 'certi'],['class' => 'btn btn-primary rounded-pill shadow'])?>    
                            
                            <?php endif;?>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>