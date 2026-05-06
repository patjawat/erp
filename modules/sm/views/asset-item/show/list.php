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
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center" scope="col" style="width: 5%">#</th>
                    <th scope="col" style="width: 8%">รหัส</th>
                    <th scope="col" style="width: 12%">FSN</th>
                    <th scope="col" style="width: 35%">ชื่อทรัพย์สิน</th>
                    <th scope="col" style="width: 5%">หน่วย</th>
                    <th scope="col" style="width: 12%">ประเภท</th>
                    <th class="text-end fw-blod" scope="col" style="width:100px">ราคากลาง</th>
                    <th class="text-center" scope="col" style="width: 120px;">จัดการ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider align-middle">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                        <td><?= $item->code ?></td>
                        <td class="fw-semibold text-primary"><?= $item->data_json['fsn'] ?? '-' ?></td>
                        <td><?= $item->title ?></td>
                        <td><?= $item->data_json['unit'] ?? '-' ?></td>
                        <td><?php echo $item->assetType->title ?? '-';?></td>
                       <td class="text-end fw-bold"><?= number_format((float)($item->data_json['price'] ?? 0)) ?></td>
                        <td class="text-center">
                            <?= Html::a('<i class="bi bi-eye"></i>', ['view', 'id' => $item->id, 'title' => '<i class="fa-solid fa-eye"></i> แสดงข้อมูลครุภัณฑ์'], ['class' => 'btn btn-sm btn-info open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                            <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไขรหัสทรัพย์สิน'], ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                            <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $item->id], ['class' => 'btn btn-sm btn-danger delete-item']) ?>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

    <div class="card-footer bg-body border-top py-3 px-4">
        <?= DataSummaryWidget::widget([
            'dataProvider' => $dataProvider,
            'pagerOptions' => [],
        ]) ?>
    </div>
</div>
