<?php

use app\modules\plan\models\PlanOrder;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanOrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'แผนคำขอพัสดุ';
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-dolly me-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'parcel']) ?>
<?php $this->endBlock(); ?>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="text-white mt-2"><i class="bi bi-ui-checks"></i> ทะเบียน<?= $this->title ?> <span class="badge text-bg-light">
                    <?= $dataProvider->getTotalCount() ?></span> รายการ</h6>
            <div>
                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['create'], ['class' => 'btn btn-light']) ?>
            </div>

        </div>
    </div>
    <div class="card-body">

        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                    <th scope="col">หมวดพัสดุ</th>
                    <th scope="col">ประเภท</th>
                    <th scope="col">วัตถุประสงค์</th>
                    <th scope="col">วงเงิน</th>
                    <th scope="col">แหล่งงบประมาณ</th>
                    <th scope="col">หน่วยงาน</th>
                    <th scope="col">สถานะ</th>
                    <th class="fw-semibold text-center" scope="col" style="width: 100px;">จัดการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr class="">
                    <tr>
                        <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?>
                        </td>
                        <td><?= $item->planType?->title ?></td>
                        <td><?= $item->assetType?->title ?></td>
                        <td><?= $item->description ?></td>
                        <td><?=$item->order_price?></td>
                        <td><?=$item->budge?->title ?? '-'?></td>
                        <td><?= $item->departmentName() ?></td>
                        <td><?= $item->viewStatus()['view'] ?></td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    จัดการ
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    <li><?= Html::a('<i class="bi bi-eye me-2"></i>แสดง', ['view', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                                    <li><?= Html::a('<i class="fa-solid fa-pen-to-square me-2"></i> แก้ไข', ['update', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
            <?= yii\bootstrap5\LinkPager::widget([
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