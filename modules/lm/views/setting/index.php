<?php
use yii\helpers\Url;
use app\models\Categorise;
use yii\widgets\Pjax;
use app\modules\lm\models\LeaveEntitlements;
use app\modules\lm\models\LeavePermission;
use app\modules\lm\models\LeaveType;
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = "ตั้งค่าระเบียบสิทธิการลา";

$listLeaveType = LeaveType::find()
    ->andWhere(['title' => 'ลากพักผ่อน'])->all();
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


<div class="row d-flex justify-content-center">

    <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
        <a href="<?=Url::to(['/lm/leave-type'])?>">

            <div class="card zoom-in">
                <div class="card-body d-flex justify-content-center">
                    <i class="fa-solid fa-file-pen fs-1 text-dark"></i>
                </div>
                <div class="card-footer">
                    <i class="bi bi-gear"></i> ประเภทการลา
                </div>
            </div>
        </a>

    </div>
    <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
        <a href="<?=Url::to(['/lm/holiday'])?>">
            <div class="card zoom-in">
                <div class="card-body d-flex justify-content-center">
                    <i class="fa-solid fa-business-time fs-1 text-dark"></i>
                </div>
                <div class="card-footer">
                    <i class="bi bi-gear"></i> ปฏิทินวันหยุด
                </div>
            </div>
            </a>
    </div>

</div>

<?php Pjax::end(); ?>