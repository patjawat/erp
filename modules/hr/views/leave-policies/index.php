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
$this->params['breadcrumbs'][] = ['label' => 'ระบบลา', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2  text-primary-gradient">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
             <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings-icon lucide-settings">
                <path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
       <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?=$this->render('@app/modules/hr/views/leave/menu',['active' => 'setting'])?>
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
                    <th scope="col" class="text-center">อายุงาน</th>
                    <th scope="col" class="text-center">สิทธลา</th>
                    <th scope="col" class="text-center">สะสมวันลา</th>
                    <th scope="col" class="text-center">สะสมวันลาสูงสุด</th>
                    <th scope="col" class="text-start">เพิ่มเติม</th>
                    <th scope="col" class="text-center">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <?php foreach($dataProvider->getModels() as $item):?>
                <tr class="">
                    <td scope="row"><?php echo $item->positionType->title?></td>
                    <td class="text-center"><?php echo $item->year_of_service;?></td>
                    <td class="text-center"><?php echo $item->days;?></td>
                    <td class="text-center"><?php echo $item->accumulation == 1 ? '<i class="bi bi-check-circle text-primary"></i>' : '<i class="bi bi-dash-circle text-danger"></i>';?></td>
                    <td class="text-center"><?php echo $item->max_days;?></td>
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