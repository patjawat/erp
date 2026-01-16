<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\components\widgets\DataSummaryWidget;
?>

    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center py-2">#</th>
                     <th class="text-center" style="width:80px;">รูปภาพ</th>
                    <th>รหัสครุภัณฑ์ / ชื่อรายการ</th>
                    <th>หมวดหมู่ / ยี่ห้อ</th>
                    <th>หน่วยงานรับผิดชอบ</th>
                    <th>วันที่รับ</th>
                    <th class="text-end">ราคา</th>
                    <th class="text-center" style="width: 130px;">สถานะ</th>
                    <th class="text-center" style="width:200px;">จัดการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                         <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
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
                        <td>
                            <div class="fw-bold" style="font-size: 1rem;"><?= $item->code ?></div>
                            <div class="text-muted" style="font-size: 0.9rem;"><?= $item->asset_name ?> <?= $item->license_plate  ? 'หมายเลขทะเบียน <span class="fw-bold">'. $item->license_plate.'</span>' : ''  ?></div>
                        </td>
                        <td>
                            <div class="text-dark fw-medium"><?= $item->assetType?->title ?? '-' ?></div>
                            <div class="text-muted" style="font-size: 0.9rem;"><?= $model->data_json['brand'] ?? '-' ?></div>
                        </td>
                        <td class="text-secondary py-2">
                            <?php if (isset($item->data_json['department_name']) && $item->data_json['department_name'] == ''): ?>
                                <?= isset($item->data_json['department_name_old']) ? $item->data_json['department_name_old'] : '' ?>
                            <?php else: ?>
                                <?= isset($item->data_json['department_name']) ? $item->data_json['department_name'] : '' ?>
                            <?php endif; ?>

                        </td>
                        <td class="text-secondary py-2"> <?= Yii::$app->thaiFormatter->asDate($item->receive_date, 'medium') ?></td>
                        <td class="text-end fw-semibold"><?= number_format($item->price ?? 0, 2) ?? 0.00 ?></td>
                        <td class="text-center py-2">
                            <?= $item->viewstatus() ?>
                        </td>

                        <td class="text-center py-2">
                            <div class="d-flex justify-content-center">
                                <a href="<?= Url::to(['view-asset', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary" title="ดูรายละเอียด">
                                    <i class="fa-regular fa-eye"></i></a>
                                <?php if(Yii::$app->user->can('asset')):?>
                                <a href="<?= Url::to(['update', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary" title="ดูรายละเอียด">
                                 <i class="fa-regular fa-pen-to-square"></i></a>
                                <?php endif;?>

                                <a href="<?= Url::to(['/am/asset/qrcode', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary open-modal" title="ดูรายละเอียด" data-size="modal-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="5" height="5" x="3" y="3" rx="1"></rect>
                                        <rect width="5" height="5" x="16" y="3" rx="1"></rect>
                                        <rect width="5" height="5" x="3" y="16" rx="1"></rect>
                                        <path d="M21 16h-3a2 2 0 0 0-2 2v3"></path>
                                        <path d="M21 21v.01"></path>
                                        <path d="M12 7v3a2 2 0 0 1-2 2H7"></path>
                                        <path d="M3 12h.01"></path>
                                        <path d="M12 3h.01"></path>
                                        <path d="M12 16v.01"></path>
                                        <path d="M16 12h1"></path>
                                        <path d="M21 12v.01"></path>
                                        <path d="M12 21v-1"></path>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>

                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    <div class="card-footer bg-white py-3 px-4 border-top">
        <!-- <span class="text-muted small">
            
        </span> -->
        <?php
        // แทนที่ส่วน card-footer ทั้งหมดด้วย Widget
        echo DataSummaryWidget::widget([
            'dataProvider' => $dataProvider,
            'pagerOptions' => [
                // สามารถกำหนดค่าเพิ่มเติมให้กับ LinkPager ได้ที่นี่ เช่น
                // 'options' => ['class' => 'pagination pagination-sm custom-class'],
            ],
            // 'summaryTemplate' => 'แสดงทั้งหมด {totalCount} รายการ ({start} - {end})', // ถ้าต้องการเปลี่ยนรูปแบบ
        ]);
        ?>

    </div>
</div>