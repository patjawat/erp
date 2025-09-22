<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use app\components\AppHelper;
use app\modules\am\models\Asset;

?>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th class="fw-semibold" scope="col" style="text-align: center;">ลำดับ</th>
                    <th class="fw-semibold" scope="col" style="width:70px;">รูปภาพ</th>
                    <th class="fw-semibold" scope="col" tyle="width:280px;">ชื่อครุภัณฑ์</th>
                    <th class="fw-semibold" scope="col">หมายเลขทะเบียน(รถยนต์)</th>
                    <th class="fw-semibold" scope="col">ครุภัณฑ์</th>
                    <th class="fw-semibold" scope="col">ประเภทครุภัณฑ์</th>
                    <th class="fw-semibold" scope="col">หมวดหมู่</th>
                    <th class="fw-semibold" scope="col">วันที่รับเข้า</th>
                    <th class="fw-semibold" scope="col" style="width:115px;">วิธีได้มา</th>
                    <th class="fw-semibold" scope="col" style="width:115px;">ประเภทเงิน</th>
                    <th class="fw-semibold text-end" scope="col" style="width:115px;">ราคาแรกรับ</th>
                    <th class="fw-semibold text-center" scope="col" style="width:115px;">สถานะ</th>
                    <th class="fw-semibold text-center" scope="col" style="width: 100px;">จัดการ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider align-middle">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                        <td style="width:70px;">
                            <?= Html::a(
                                Html::img(
                                    $item->showImg()['image'],
                                    [
                                        'class' => 'rounded mx-auto d-block text-white lazyautosizes ls-is-cached lazyloaded',
                                        'style' => 'max-width:60px; max-height:60px; object-fit:cover;',
                                        'alt' => $item->asset_name
                                    ]
                                ),
                                ['view', 'id' => $item->id],
                                ['class' => '']
                            ) ?>
                        </td>
                        <td class="align-middle">
                            <div class="d-inline-block text-truncate" style="max-width: 280px;">

                          
                            <span class="mb-0">
                                <?= $item->asset_name ?>
                            </span>
                            <div class="d-flex flex-row gap-1 fs-12">
                                <?php if (isset($item->data_json['brand'])): ?>
                                    <span class="mb-0">ยี่ห้อ : <span class="fw-semibold"><?= $item->data_json['brand'] ?></span></span>
                                <?php endif ?>
                                <?php if (isset($item->data_json['asset_model'])): ?>
                                    <span class="mb-0">รุ่น : <span class="fw-semibold"><?= $item->data_json['asset_model'] ?></span></span>
                                <?php endif ?>
                            </div>
  </div>
                            <?php // $this->render('item_list',['model' => $item])
                            ?>
                        </td>
                        <td class="fw-semibold text-primary"><?= $item->license_plate ?></td>
                        <td class="fw-semibold text-primary"><?= $item->code ?></td>
                        <td class="align-middle"><?= $item->assetType->title ?? '' ?></td>
                        <td class="align-middle"><?= $item->assetCategory?->title ?? '' ?></td>
                        <td class="align-middle"><?= $item->viewReceiveDate() ?></td>
                        <td class="align-middle"><?= $item->purchaseName?->title ?? '' ?></td>
                        <td class="align-middle"><?= $item->budgetTypeName() ?></td>

                        <td class="align-middle text-end fw-semibold"><?= number_format($item->price, 0) ?></td>
                        <td class="text-center"><?= $item->statusName() ?></td>

                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    จัดการ
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    <li><?= Html::a('<i class="fa-solid fa-eye me-1"></i>แสดง', ['view', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                                    <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['update', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                                    <li><?= Html::a('<i class="fa-solid fa-copy me-2"></i> สร้างใหม่จากสำเนานี้', ['create', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>

                                </ul>
                            </div>
                        </td>


                        <!-- <td class="align-middle text-center">
                            <div class="d-flex gap-3">
                                <?= Html::a('<i class="fa-solid fa-eye fa-2x"></i>', ['view', 'id' => $item->id]) ?>
                                <?= Html::a('<i class="fa-solid fa-pen-to-square fa fa-2x text-warning"></i>', ['update', 'id' => $item->id]) ?>
                                <?= Html::a('<i class="fa-solid fa-trash fa-2x text-danger"></i>', ['delete', 'id' => $item->id], ['class' => 'delete-asset']) ?>
                            </div>
                        </td> -->
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>

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