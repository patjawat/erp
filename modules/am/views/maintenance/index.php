<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetailSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'รายการบำรุงรักษา';
$this->params['breadcrumbs'][] = $this->title;
$iconClean = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-brush-cleaning-icon lucide-brush-cleaning">
            <path d="m16 22-1-4"></path>
            <path d="M19 14a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2h-3a1 1 0 0 1-1-1V4a2 2 0 0 0-4 0v5a1 1 0 0 1-1 1H6a2 2 0 0 0-2 2v1a1 1 0 0 0 1 1"></path>
            <path d="M19 14H5l-1.973 6.767A1 1 0 0 0 4 22h16a1 1 0 0 0 .973-1.233z"></path>
            <path d="m8 22 1-4"></path>
        </svg>'
?>
<div class="asset-detail-index">
    <div class="d-flex justify-content-between">
        <h6><?= Html::encode($this->title) ?></h6>

        <p>
            <?= Html::a('<i data-lucide="circle-plus"></i> สร้างใหม่', ['create', 'code' => $searchModel->code, 'title' => $iconClean . ' การบำรุงรักษา'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        </p>
    </div>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center" style="width:30px">ลำดับ</th>
                    <th>ชื่อรายการ</th>
                    <th>วันที่ตามแผน</th>
                    <th>วันที่ดำเนินการ</th>
                    <th>ผู้ดำเนินการ</th>
                    <th class="text-center" style="width:130px">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                        <td><?= $item->data_json['title'] ?? '-' ?></td>
                        <td><?= Yii::$app->thaiDate->toThaiDate($item->date_start, false, false); ?></td>
                        <td><?= Yii::$app->thaiDate->toThaiDate($item->date_end, false, false); ?></td>
                        <td><?= $item->createdBy->employees->fullname ?? '-' ?></td>
                        <td class="text-center py-2">
                            <div class="d-flex justify-content-center">
                                <a href="<?= Url::to(['/am/maintenance/view', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary open-modal" data-size="modal-lg" title="ดูรายละเอียด">
                                    <i class="fa-regular fa-eye"></i></a>
                                <a href="<?= Url::to(['/am/maintenance/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข']) ?>" class="btn btn-icon btn-ghost-secondary open-modal" data-size="modal-lg" title="ดูรายละเอียด">
                                 <i class="fa-regular fa-pen-to-square"></i></a>

                                <a href="<?= Url::to(['/am/maintenance/delete', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary delete-item" title="ดูรายละเอียด">
                                   <i class="fa-regular fa-trash-can"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php Pjax::end(); ?>

</div>