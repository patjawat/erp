<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'นโยบายการลา';
$this->params['breadcrumbs'][] = ['label' => 'การลางาน', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0"><i class="bi bi-gear"></i> <?= $this->title ?></h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/leave/views/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(); ?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6>
                <i class="bi bi-ui-checks"></i> นโยบายการลา
                <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?></span> รายการ
            </h6>
        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col" class="fw-semibold">ประเภทตำแหน่ง</th>
                    <th scope="col" class="text-center">อายุงาน</th>
                    <th scope="col" class="text-center">สิทธลา</th>
                    <th scope="col" class="text-center">สะสมวันลา</th>
                    <th scope="col" class="text-center">สะสมวันลาสูงสุด</th>
                    <th scope="col" class="text-start">เพิ่มเติม</th>
                    <th scope="col" class="text-center">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <?php foreach ($dataProvider->getModels() as $item): ?>
                <tr>
                    <td scope="row"><?= Html::encode($item->positionType->title ?? '') ?></td>
                    <td class="text-center"><?= Html::encode($item->year_of_service) ?></td>
                    <td class="text-center"><?= Html::encode($item->days) ?></td>
                    <td class="text-center"><?= $item->accumulation == 1 ? '<i class="bi bi-check-circle text-primary"></i>' : '<i class="bi bi-dash-circle text-danger"></i>' ?></td>
                    <td class="text-center"><?= Html::encode($item->max_days) ?></td>
                    <td class="text-start"><?= Html::encode($item->additional_rules) ?></td>
                    <td class="text-center"><?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/leave/leave-policies/update', 'id' => $item->id, 'title' => 'แก้ไข'], ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-md']]) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => ['class' => 'pagination pagination-sm'],
            ]) ?>
        </div>
    </div>
</div>
<?php Pjax::end(); ?>
