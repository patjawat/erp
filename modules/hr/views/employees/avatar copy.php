<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\AppHelper;

?>
<div class="card">
    <div class="card-body">
        <div class="d-flex">
            <div class="position-relative">
                <?=Html::img($model->showAvatar(),['class' => 'avatar avatar-xl border border-primary-subtl border-1','data-aos' => 'zoom-in'])?>
                <span class="contact-status offline"></span>
            </div>
            <div class="flex-grow-1 w-50">
                <div class="row">
                    <div class="col-lg-8 col-md-12 col-sm-12 fw-semibold mb-1 d-inline-block text-truncate">
                        <h5>
                            <a href="<?=Url::to(['/hr/employees/view','id' => $model->id])?>"
                                class="text-dark"><?=$model->fullname?></a>
                        </h5>
                    </div>
                    <div class="col-lg-4 col-md-12 col-sm-12">

                        <div class="dropdown float-end">
                            <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-sliders"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" style="">
                                <?=AppHelper::Btn([
                                'type' => 'update',
                'url' => ['/hr/employees/update', 'id' => $model->id],
                'modal' => true,
                'size' => 'lg',
                'class' => 'dropdown-item'
                ])?>

                                <?= Html::a('<i class="fa-solid fa-user-gear me-1"></i> ตั้งค่า', ['/usermanager/user/update-employee', 'id' => $model->user_id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']])?>

                                <?php Html::a('<i
                                class="bi bi-database-fill-gear me-1"></i>ตั้งค่า', ['/hr/categorise', 'id' => $model->id,'title' => ''], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']])?>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="row">

                    <div class="col-12 text-truncate">
                        <p class="text-muted mb-0">
                            <?php if($model->positionName()):?>
                            <i class="bi bi-check2-circle text-primary"></i>
                            <?=$model->positionName()?>
                            <?php else:?>
                            <i class="fa-solid fa-circle-exclamation text-warning"></i> -
                            <?php endif;?>
                        </p>
                        <p class="text-muted mb-0"><i class="bi bi-check2-circle text-primary"></i> เริ่มงาน
                            <code><?=Yii::$app->thaiFormatter->asDate($model->join_date,'medium')?> </code>
                        </p>
                        <p class="text-muted mb-0"><i class="bi bi-check2-circle text-primary"></i> อายุราชการ
                            <?=$model->age_join_date?> </p>

                    </div>

                </div>

            </div>

        </div>


    </div>
    
    <div class="card-footer bg-transparent border-top">
        <?php if(isset($leave)):?>
        <?= $this->render('sum_leave_style2')?>
        <hr>
        <?php endif;?>
        <div class="row mt-2">
            <div class="col-6 text-truncate">

                <span class="float-start">

                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-sm rounded-circle bg-light">
                            <i class="bi bi-tag avatar-title text-primary"></i>
                        </div>

                        <div class="flex-grow-1 text-end">
                            <span class="text-primary fw-semibold"><?=$model->positionTypeName()?></span>
                        </div>
                </span>

            </div>
        </div>

    </div>
    <div class="col-6 text-truncate">
        <label class="badge rounded-pill text-primary-emphasis bg-success-subtle p-2 fs-6 text-truncate float-end">
            <i class="bi bi-clipboard-check"></i> <?=$model->statusName()?>
        </label>
    </div>


</div>

</div>
<!-- </div> -->