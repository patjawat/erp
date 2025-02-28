<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\AppHelper;
use app\modules\hr\models\EmployeeDetail;
?>

<div class="card hover-card">
    <div class="card-body">

    <div class="dropdown-menu dropdown-menu-right">
                                <?= AppHelper::Btn([
                                    'type' => 'update',
                                    'url' => ['/hr/employees/update', 'id' => $model->id],
                                    'modal' => true,
                                    'size' => 'lg',
                                    'class' => 'dropdown-item',
                                ]) ?>

                                <?= Html::a('<i class="fa-solid fa-user-gear me-1"></i> ตั้งค่า', ['/usermanager/user/update-employee', 'id' => $model->user_id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                            </div>
                           
        <div class="d-flex">
            <div class="position-relative">
                <?= Html::img($model->showAvatar(), ['class' => 'avatar avatar-xl border border-primary-subtl border-0 shadow  lazyload',
                    'data' => [
                        'expand' => '-20',
                        'sizes' => 'auto',
                        'src' =>$model->showAvatar()
                        ]
                        ]) ?>
                <div class="position-absolute top-0 start-500 translate-middle">
                <?php if($model->user_id == 0):?>
                    <i class="bi bi-exclamation-circle-fill text-warning fs-4"></i>
                    <?php else:?>    
                <i class="bi bi-check-circle-fill text-primary fs-4"></i>
        <?php endif?>
            </div>
                <!-- <span class="contact-status offline"></span> -->
            </div>
            <div class="flex-grow-1 w-50">
                <div class="row">
                    <div class="col-lg-9 col-md-12 col-sm-12 fw-semibold mb-1 d-inline-block text-truncate">
                        <a href="<?= Url::to(['/hr/employees/view', 'id' => $model->id]) ?>" class="text-dark">       
                        <h6 class="mb-0">
                            <?= $model->fullname ?> (<code><?= $model->age_y ?></code> ปี)

                        </h6>
                    </a>
                    </div>
                    <div class="col-lg-3 col-md-12 col-sm-12">

                        <div class="dropdown float-end">
                            <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-sliders"></i>
                            </a>
                            
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 text-truncate">
                        <p class="text-muted mb-0">
                            <?= $model->positionName(['icon' => true]) ?>
                        </p>
                        <?php if ($model->joinDate()): ?>
                            <p class="text-muted mb-0"><i class="bi bi-check2-circle text-primary"></i> เริ่มงาน
                                <code>
                                <?php 
                                        try {
                                            echo Yii::$app->thaiFormatter->asDate($model->joinDate(), 'medium');
                                        } catch (\Throwable $th) {

                                        }
                                        ?>
                                    </code>
                            </p>
                            <p><i class="bi bi-check2-circle text-primary"></i>
                            อายุราชการ <?= $model->workLife()['full'] ?></p>
                        

                        <?php endif; ?>
                        <?php if (isset($showAge) && $showAge == true): ?>
                        <p class="text-muted mb-0"><i class="bi bi-check2-circle text-primary"></i>
                            อายุราชการ<?= $model->age_join_date ?> </p>
                        <?php endif; ?>
                    </div>
                </div>


            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-6 text-truncate">

                <?php if ($model->departmentName()): ?>
                <div class="d-flex">
                <i class="bi bi-tags-fill me-1"></i>
                    <span class="text-primary fw-normal"><?= $model->departmentName() ?></span>
                </div>
                <?php else: ?>
                <div class="d-flex gap-2">
                    <i class="fa-solid fa-circle-exclamation text-warning"></i>
                    <span class="text-primary fw-normal">ไม่ระบุ</span>
                </div>
                <?php endif ?>
            </div>
            <!-- End col-6 -->
            <div class="col-6 text-truncate">
                <?php if ($model->positionTypeName()): ?>
                <label
                    class="badge rounded-pill text-primary-emphasis bg-primary-subtle p-2  text-truncate float-end">
                    <i class="bi bi-clipboard-check"></i> <?= $model->statusName() ?>
                </label>
                <?php else: ?>
                <label
                    class="badge rounded-pill text-primary-emphasis bg-warning-subtle p-2 text-truncate float-end">
                    <i class="fa-solid fa-circle-exclamation text-warning"></i> ไม่ระบุสถานะ
                </label>
                <?php endif ?>
            </div>
            <!-- End col-6 -->
        </div>
        <!-- End Row -->
    </div>
</div>