<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\hr\models\LeavePolicies;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeavePoliciesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'นโยบายการลา';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/hr/views/leave/menu_settings') ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(); ?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6>
                <i class="bi bi-ui-checks"></i> นโยบายการลา
                <span class="badge rounded-pill text-bg-primary"><?php echo $dataProvider->getTotalCount() ?></span>
                รายการ
            </h6>
        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr >
                    <th scope="col" class="fw-semibold">ประเภทตำแหน่ง</th>
                    <th scope="col" class="text-center fw-semibold">อายุงาน</th>
                    <th scope="col" class="text-center fw-semibold">สิทธลา</th>
                    <th scope="col" class="text-center fw-semibold">สะสมวันลา</th>
                    <th scope="col" class="text-center fw-semibold">สะสมวันลาสูงสุด</th>
                    <th scope="col" class="text-start fw-semibold">เพิ่มเติม</th>
                    <th scope="col" class="text-center fw-semibold">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <?php foreach($dataProvider->getModels() as $item):?>
                <tr class="">
                    <td scope="row"><?php echo $item->positionType->title?></td>
                    <td class="text-center fw-semibold"><?php echo $item->year_of_service;?></td>
                    <td class="text-center fw-semibold"><?php echo $item->days;?></td>
                    <td class="text-center fw-semibold"><?php echo $item->accumulation == 1 ? '<i class="bi bi-check-circle text-primary"></i>' : '<i class="bi bi-dash-circle text-danger"></i>';?></td>
                    <td class="text-center fw-semibold"><?php echo $item->max_days;?></td>
                    <td class="text-start"><?php echo $item->additional_rules;?></td>
                    <td class="text-center"><?php echo Html::a('<i class="fa-regular fa-pen-to-square"></i>',['/hr/leave-policies/update','id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],['class' => 'btn btn-sm btn-warning open-modal','data' => ['size' => 'modal-md']])?></td>
                </tr>
                <?php endforeach;?>
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
        <?php Pjax::end(); ?>
    </div>
</div>