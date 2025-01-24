<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
use app\components\AppHelper;
use app\components\UserHelper;
$me = UserHelper::GetEmployee();
/** @var yii\web\View $this */
/** @var app\modules\lm\models\Leave $model */

\yii\web\YiiAsset::register($this);
?>



<div class="bg-white border rounded shadow p-4 max-w-md mx-auto mt-8 mb-3">
<?php echo $model->employee->getAvatar(false)?>
    <!-- <h1 class="font-bold text-2xl my-4 text-center text-primary">ขอ<?php echo ($model->leaveType->title ?? '-') ?></h1> -->
    <hr class="mb-2">
    <div class="d-flex justify-content-between mb-6">
        <h1 class="h6 font-bold">วันที่</h1>
        <div class="text-muted">
            <div><span class="text-pink fw-semibold"><?php echo AppHelper::convertToThai($model->date_start ?? '') ?></span> ถึง <span class="text-pink fw-semibold"><?php echo AppHelper::convertToThai($model->date_end ?? '') ?></span></div>
            <div>รวม <span class="badge rounded-pill badge-soft-danger text-primary fs-13 "><?php echo $model->total_days?></span> วัน</div>
        </div>
    </div>
    <div class="mb-8">
        <div class="mb-2 font-bold">เหตุผล</class=> : <span class="text-muted mb-2"><?php echo $model->data_json['reason'] ?? '-' ?></span></div>
        <!-- <div class="text-muted mb-2">Anytown, USA 12345</div>
        <div class="text-muted">johndoe@example.com</div> -->
    </div>
    <?= $this->render('view_summary', ['model' =>  $model]) ?>

    <div class="d-flex justify-content-between">
        <div class="text-muted mb-2"><?php echo $model->viewStatus() ?></div>
        <div class="text-muted small">เขียนเมื่อ : <?php echo $model->viewCreated() ?></div>
    </div>
</div>