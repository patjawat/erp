<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetailSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ประวัติการสอบเทียบ';
$this->params['breadcrumbs'][] = $this->title;
$iconClean = '<i data-lucide="circle-plus"></i> '
?>
<div class="asset-detail-index">
    <div class="d-flex justify-content-between">
        <h6><?= Html::encode($this->title) ?></h6>

        <p>
            <?= Html::a('<i data-lucide="circle-plus"></i> สร้างใหม่', ['create', 'code' => $searchModel->code, 'title' => $iconClean . ' แบบฟอร์มบันทึกข้อมูลการสอบเทียบ'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
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
                    <th>ผู้ให้บริการ</th>
                    <th>วันที่ตามแผน</th>
                    <th>วันที่ดำเนินการ</th>
                    <th>ผู้ดำเนินการ</th>
                    <th>ผลการสอบเทียบ</th>
                    <th class="text-center" style="width:130px">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                        <td><?= $item->provider_type == 'external' ? 'หน่วยงานภายนอก (Outsource)' : 'ดำเนินการเอง (In-house)'?></td>
                        <td><?= Yii::$app->thaiDate->toThaiDate($item->plan_date, false, false); ?></td>
                        <td><?= Yii::$app->thaiDate->toThaiDate($item->actual_date, false, false); ?></td>
                        <td><?= $item->createdBy->employees->fullname ?? '-' ?></td>
                        <td><?= $item->viewResult() ?></td>
                        <td class="text-center py-2">
                            <div class="d-flex justify-content-center">
                                <a href="<?= Url::to(['/am/calibration/view', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary open-modal" data-size="modal-lg" title="ดูรายละเอียด">
                                    <i class="fa-regular fa-eye"></i></a>
                                <a href="<?= Url::to(['/am/calibration/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข']) ?>" class="btn btn-icon btn-ghost-secondary open-modal" data-size="modal-lg" title="ดูรายละเอียด">
                                 <i class="fa-regular fa-pen-to-square"></i></a>

                                <a href="<?= Url::to(['/am/calibration/delete', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary delete-item" title="ดูรายละเอียด">
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