<?php
use app\components\AppHelper;
use app\modules\hr\models\EmployeeDetail;
use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="card">
    <div class="card-body">
        <div class="d-flex">
            <div class="position-relative">
                <?= Html::img($model->showAvatar(), ['class' => 'avatar avatar-xl border border-primary-subtl border-1', 'data-aos' => 'zoom-in']) ?>
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
                        <h6>
                            <a href="<?= Url::to(['/hr/employees/view', 'id' => $model->id]) ?>"
                                class="text-dark"><?= $model->fullname ?> (<code><?= $model->age_y ?></code> ปี)</a>
                        </h6>
                    </div>
                    <div class="col-lg-3 col-md-12 col-sm-12">

                        <div class="dropdown float-end">
                            <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-sliders"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <?= AppHelper::Btn([
                                    'type' => 'update',
                                    'url' => ['/hr/employees/update', 'id' => $model->id],
                                    'modal' => true,
                                    'size' => 'lg',
                                    'class' => 'dropdown-item',
                                ]) ?>

                                <?= Html::a('<i class="fa-solid fa-user-gear me-1"></i> ตั้งค่า', ['/usermanager/user/update-employee', 'id' => $model->user_id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                <?php // Html::a('<i class="bi bi-database-fill-gear me-1"></i>ตั้งค่า', ['/hr/categorise', 'id' => $model->id, 'title' => ''], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 text-truncate">
                        <p class="text-muted mb-0">
                            <i class="bi bi-check2-circle text-primary me-1"></i><?= $model->departmentName() ?>
                        </p>
                        <p class="text-muted mb-0">
                            <?= $model->positionName(['icon' => true]) ?>
                        </p>
                        <?php if ($model->joinDate()): ?>
                            <p class="text-muted mb-0"><i class="bi bi-check2-circle text-primary"></i> เริ่มงาน
                                <code>
                                        <?php echo Yii::$app->thaiFormatter->asDate($model->joinDate(), 'medium') ?>
                                    </code>
                            </p>
                            <p><i class="fa-solid fa-business-time"></i>
                            อายุราชการ <?= $model->workLife() ?></p>
                        

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
    <div class="card-footer bg-transparent">
        <div class="row">
            <div class="col-6 text-truncate">

                <?php if ($model->positionTypeName()): ?>
                <div class="d-flex gap-2">
                    <i class="bi bi-tag text-primary"></i>
                    <span class="text-primary fw-normal"><?= $model->positionTypeName() ?></span>
                </div>
                <?php else: ?>
                <div class="d-flex gap-2">
                    <i class="fa-solid fa-circle-exclamation text-warning"></i>
                    <span class="text-primary fw-normal">ไม่ระบุประเภท</span>
                </div>
                <?php endif ?>
            </div>
            <!-- End col-6 -->
            <div class="col-6 text-truncate">
                <?php if ($model->positionTypeName()): ?>
                <label
                    class="badge rounded-pill text-primary-emphasis bg-success-subtle p-2 fs-6 text-truncate float-end">
                    <i class="bi bi-clipboard-check"></i> <?= $model->statusName() ?>
                </label>
                <?php else: ?>
                <label
                    class="badge rounded-pill text-primary-emphasis bg-success-subtle p-2 fs-6 text-truncate float-end">
                    <i class="fa-solid fa-circle-exclamation text-warning"></i> ไม่ระบุสถานะ
                </label>
                <?php endif ?>
            </div>
            <!-- End col-6 -->
        </div>
        <!-- End Row -->
    </div>
</div>