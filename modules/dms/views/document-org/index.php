<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'หน่วยงาน';

$this->params['breadcrumbs'][] = ['label' => 'งานสารบรรณ', 'url' => ['/dms/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div
    class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
       <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings-icon lucide-settings">
                <path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915" />
                <circle cx="12" cy="12" r="3" />
            </svg>
        <?= $this->title; ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/dms/menu', ['model' => $searchModel, 'active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(); ?>
<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?= $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white"> <i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title ?>
                <span class="badge text-bg-light"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                รายการ
            </h6>
            <div class="d-flex gap-3">
                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['create','title' => '<i class="fa-solid fa-circle-plus"></i> สร้างใหม่'], ['class' => 'btn btn-light shadow open-modal', 'data' => ['size' => 'modal-md']]) ?>
                <span class="btn btn-success shadow export-document"><i
                        class="fa-regular fa-file-excel me-1"></i>ส่งออก</span>
            </div>
        </div>
    </div>
    <div class="card-body">

        <table class="table table-striped table-fixed">
            <thead>
                <tr>
                    <th class="text-center" style="width:30px;">ลำดับ</th>
                    <th class="text-start">ชื่อหน่วยงาน</th>
                    <th class="text-start">WebHook URL</th>
                    <th style="width:70px;">แก้ไข</th>
                </tr>
            </thead>
            <tbody class="align-middle  table-group-divider table-hover">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?>
                </td>
                <td> <?= $item->title ?? '-' ?></td>
                <td> <?= $item->data_json['url'] ?? '-' ?></td>
                <td>
                    <?php echo Html::a('<i class="fa-regular fa-pen-to-square fa-2x"></i>', ['update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'open-modal', 'data' => ['size' => 'modal-md']]) ?>
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
<?php Pjax::end(); ?>