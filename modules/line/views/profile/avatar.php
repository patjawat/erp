<?php
use app\components\AppHelper;
use app\modules\hr\models\EmployeeDetail;
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\hr\models\Employees;
$id = Yii::$app->user->id;
$model = Employees::findOne(['user_id' => $id]);
?>
<style>
    .avatar-xxl {
    height: 10rem;
    width: 10rem;
}
</style>
<?php if($model):?>
<div class="card">
    <div class="card-body">
        <div class="d-flex flex-column mb-3 ">
            <div class="d-flex justify-content-center">
                <?= Html::img($model->showAvatar(), ['class' => 'avatar avatar-xxl border border-primary-subtl border-1 card-img-top mt--45']) ?>
            </div>

            <div class="d-flex justify-content-center">
                <div class="d-flex flex-column">
                    <h2><?=$model->fullname?></h2>
                    
                    <h6> <i class="bi bi-check2-circle text-primary me-1"></i><?= $model->departmentName() ?></h6>
                    <h6>  <?= $model->positionName(['icon' => true]) ?></h6>
                    <h6>
                    <?php if ($model->joinDate()): ?>
                    <p class="text-muted mb-0"><i class="bi bi-check2-circle text-primary"></i> เริ่มงาน
                        <code><?php echo Yii::$app->thaiFormatter->asDate($model->joinDate(), 'medium') ?></code>
                    </p>
                    <?php endif;?>
                    </h6>

                    <p>
                    <i class="fa-solid fa-business-time"></i>
                    อายุราชการ <?= $model->workLife() ?>
                </p>
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
<?php endif;?>