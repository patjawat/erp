<?php

use app\components\widgets\DataSummaryWidget;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetItemSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
?>

<div class="card">
    <div class="card-header d-flex justify-content-between text-white">
        <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2 text-body">
            <div class="bg-primary bg-opacity-10 text-primary rounded-pill">
            </div>
            <i data-lucide="file-text"></i>
            <?= $this->title?>
        </h6>
        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/sm/asset-item/create', 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เพิ่มวัสดุใหม่'], ['class' => 'btn btn-light open-modal', 'data' => ['size' => 'modal-lg']]) ?>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" scope="col" style="width: 5%;">#</th>
                        <th scope="col" style="width: 28%;">รายการครุภัณฑ์</th>
                        <th scope="col" style="width: 12%;">รหัส</th>
                        <th scope="col" style="width: 16%;">ประเภท</th>
                        <th scope="col" style="width: 16%;">กลุ่ม</th>
                        <th scope="col" style="width: 10%;">หน่วยนับ</th>
                        <th scope="col" style="width: 90px;" class="text-center">สถานะ</th>
                        <th class="text-center" scope="col" style="width: 120px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($dataProvider->getModels() as $key => $model): ?>
                        <tr>
                            <td class="text-center">
                                <?= $dataProvider->pagination ? (($dataProvider->pagination->offset + 1) + $key) : ($key + 1) ?>
                            </td>
                            <td>
                                <?= $this->render('item', ['model' => $model]) ?>
                            </td>
                            <td class="fw-semibold text-primary"><?= Html::encode($model->code ?: '-') ?></td>
                            <td><?= Html::encode($model->assetTypeTitle) ?></td>
                            <td><?= Html::encode($model->assetGroupTitle) ?></td>
                            <td><?= Html::encode($model->unitName) ?></td>
                            <td class="text-center">
                                <span class="badge rounded-pill <?= $model->active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>">
                                    <?= $model->active ? 'ใช้งาน' : 'ไม่ใช้งาน' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center flex-wrap gap-1">
                                    <?= Html::a('<i class="bi bi-eye"></i>', ['view', 'id' => $model->id, 'title' => '<i class="fa-solid fa-eye"></i> แสดงข้อมูลครุภัณฑ์'], ['class' => 'btn btn-sm btn-info open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                    <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $model->id, 'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไขรหัสทรัพย์สิน'], ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                    <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $model->id], ['class' => 'btn btn-sm btn-danger delete-item']) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer bg-body border-top py-3 px-4">
        <?= DataSummaryWidget::widget([
            'dataProvider' => $dataProvider,
            'pagerOptions' => [],
        ]) ?>
    </div>
</div>
