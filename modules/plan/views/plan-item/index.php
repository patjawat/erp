<?php

use app\modules\plan\models\PlanItem;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanItemSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'รายการ';
$this->params['breadcrumbs'][] = ['label' => 'แผนงาน', 'url' => ['/plan/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.106 5.553a2 2 0 0 0 1.788 0l3.659-1.83A1 1 0 0 1 21 4.619v12.764a1 1 0 0 1-.553.894l-4.553 2.277a2 2 0 0 1-1.788 0l-4.212-2.106a2 2 0 0 0-1.788 0l-3.659 1.83A1 1 0 0 1 3 19.381V6.618a1 1 0 0 1 .553-.894l4.553-2.277a2 2 0 0 1 1.788 0z"></path>
            <path d="M15 5.764v15"></path>
            <path d="M9 3.236v15"></path>
        </svg>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'setting']) ?>
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
                   <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['create','title' => 'สร้างใหม่'], ['class' => 'btn btn-light open-modal', 'data' => ['size' => 'modal-md']]) ?>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center" style="width:30px">ลำดับ</th>
                    <th scope="col">ประเภท</th>
                    <th scope="col">หมวดหมู</th>
                    <th scope="col">ชื่อรายการ</th>
                    <th class="fw-semibold text-center" scope="col" style="width: 100px;">จัดการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr class="">
                    <tr>
                        <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?>
                        </td>
                        <td><?= $item->planCategory->planType?->title ?? '-' ?></td>
                        <td><?= $item->planCategory?->title ?? '-' ?></td>
                        <td><?= $item->title ?></td>
                        <td class="fw-light text-center align-middle">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    จัดการ
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    <li><?= Html::a('<i class="fa-solid fa-pen-to-square me-1"></i> แก้ไข', ['update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?></li>
                                    <li><?php echo Html::a('<i class="fa-solid fa-trash me-1"></i> ลบทิ้ง', ['delete', 'id' => $item->id], ['class' => 'dropdown-item delete-item']) ?></li>
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