<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeaveEntitlementsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'กำหนดเวร 8';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('@app/modules/hr/views/leave/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'leave']); ?>
<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body ">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>
<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between  align-top align-items-center">
            <h6 class="text-white">
                <i class="bi bi-ui-checks"></i> <?= $this->title ?>
                <span class="badge rounded-pill text-bg-primary"><?php echo $dataProvider->getTotalCount() ?></span>
                รายการ
            </h6>
        </div>

    </div>
    <div class="card-body">

        <div class="d-flex justify-content-between">

        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th rowspan="2">ลำดับ</th>
                    <th scope="col" class="fw-semibold">ชื่อ-นามสกุล</th>
                    <th scope="col" class="fw-semibold">ประเภท</th>
                    <th scope="col" class="fw-semibold text-center">แผนก/ฝ่าย</th>
                    <th scope="col" class="text-start fw-semibold" style="width: 100px;">ใช้เวร 8</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                        <td><?php echo $item->getAvatar(false) ?></td>
                        <td><?= $item->positionType->title ?></td>
                        <td class="text-center fw-semibold"><?= $item->departmentName() ?></td>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch">
                                <?= Html::checkbox('work_shift', $item->work_shift == 'shift', [
                                    'class' => 'form-check-input use-shift8',
                                    'data' => ['id' => $item->id],
                                ]) ?>
                            </div>

                        </td>
                    </tr>
                <?php endforeach; ?>


            </tbody>
        </table>
        <div class="d-flex justify-content-center">

            <?php echo  yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
        </div>

    </div>
</div>

<?php
$url = Url::to(['/hr/leave-entitlements/export']);
$createAllUrl = Url::to(['/hr/leave-entitlements/create-all']);
$js = <<< JS


$("body").on("change", ".use-shift8", function () {
    let checkbox = $(this);
    let id = checkbox.data("id");
    let value = checkbox.is(":checked") ? "shift" : "normal";
    console.log(id);
    

    $.ajax({
        url: "/hr/work-shift/update-shift", // <-- route ไปยัง action
        type: "POST",
        data: {
            id: id,
            work_shift: value,
        },
        success: function (response) {
            success("บันทึกสำเร็จ");
        },
        error: function () {
            error("เกิดข้อผิดพลาดในการบันทึก");
        }
    });
});





JS;
$this->registerJS($js, View::POS_END);
?>

<?php Pjax::end(); ?>