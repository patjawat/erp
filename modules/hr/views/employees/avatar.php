<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\components\AppHelper;

$avatar = $model->showAvatar();
?>

<div class="card border border-1">
    <div class="card-body">
        <div class="d-flex">
            <div class="position-relative">
                <?= Html::img('@web/img/loading.gif', [
                    'class' => 'avatar avatar-xl border border-primary-subtl border-0 shadow  lazyload',
                    'data' => [
                        'expand' => '-20',
                        'sizes' => 'auto',
                        'src' => $avatar
                    ]
                ]) ?>
                <div class="position-absolute top-0 start-500 translate-middle">
                    <?php if (!$model->positionTypeName()): ?>
                        <i class="bi bi-exclamation-circle-fill text-warning fs-4"></i>
                    <?php else: ?>
                        <i class="bi bi-check-circle-fill text-primary fs-4"></i>
                    <?php endif ?>
                </div>
            </div>
            <div class="flex-grow-1 w-50">
                <div class="row">
                    <div class="col-lg-9 col-md-12 col-sm-12 mb-1 d-inline-block text-truncate">
                        <h6>
                            <a href="<?= Url::to(['/hr/employees/view', 'id' => $model->id]) ?>"
                                class="text-dark"><?= $model->fullname ?> (<code><?= $model->age_y ?></code> ปี)</a>
                        </h6>
                    </div>
                    <div class="col-lg-3 col-md-12 col-sm-12">

                        <div class="dropdown flex-grow-1 flex-sm-grow-0">
                            <button class="btn btn-outline-primary dropdown-toggle w-100 w-sm-auto" type="button"
                                id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-angle-down"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
                                <li><?= Html::a('<i class="fa-solid fa-eye me-2"></i> แสดง',['/hr/employees/view', 'id' => $model->id],['class' => 'dropdown-item'])?></li>
                                <li>
                                    <?= AppHelper::Btn([
                                        'type' => 'update',
                                        'url' => ['/hr/employees/update', 'id' => $model->id],
                                        'modal' => true,
                                        'size' => 'lg',
                                        'class' => 'dropdown-item',
                                    ]) ?>
                                </li>
                                <li>
                                    <?= Html::a(($model->status == 'CANCEL' ? '<i class="fa-solid fa-arrow-rotate-left me-1"></i> ใช้งาน' : '<i class="fa-solid fa-user-xmark me-1"></i> ยกเลิก'), ['/hr/employees/cancel', 'id' => $model->id], ['class' => 'dropdown-item a-action', 'data' => ['size' => 'modal-md']]) ?>
                                </li>
                                <li>
                                    <?= Html::a('<i class="fa-solid fa-user-gear me-1"></i> ตั้งค่า', ['/usermanager/user/update-employee', 'id' => $model->user_id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 text-truncate">
                        <p class="text-muted mb-0">
                            <?= $model->positionName(['icon' => true]) ?>
                        </p>

                            <p class="text-muted mb-0">
                                <i class="bi bi-check2-circle text-primary"></i>
                               <?=$model->positionTypeName();?>
                            </p>

                        <p class="text-muted mb-0">
                            <i class="bi bi-check2-circle text-primary"></i>
                            อายุราชการ <?= $model->workLife()['full'] ?>
                        </p>

                        <?php if (isset($showAge) && $showAge == true): ?>
                            <p class="text-muted mb-0"><i class="bi bi-check2-circle text-primary"></i>
                                อายุราชการ<?= $model->age_join_date ?> </p>
                        <?php endif; ?>

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
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-6 text-truncate">
                <p class="mb-0"><i class="fa-regular fa-clock"></i> ประเภทเวร : <?= $model->viewWorkType() ?></p>
            </div>
            <!-- End col-6 -->
            <div class="col-6 text-truncate">
                <?php if ($model->positionTypeName()): ?>
                    <label class="badge rounded-pill text-primary-emphasis bg-primary-subtle p-2  text-truncate float-end">
                        <i class="bi bi-clipboard-check"></i> <?= $model->statusName() ?>
                    </label>
                <?php else: ?>
                    <label class="badge rounded-pill text-primary-emphasis bg-warning-subtle p-2 text-truncate float-end">
                        <i class="fa-solid fa-circle-exclamation text-warning"></i> <?= $model->statusName() ?>
                    </label>
                <?php endif ?>
            </div>
            <!-- End col-6 -->
        </div>
        <!-- End Row -->

    </div>
</div>