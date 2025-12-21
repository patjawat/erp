<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\components\widgets\DataSummaryWidget;
?>

<div class="card border border-light-subtle shadow-sm" style="border-radius: 12px; border-color: #e5e7eb !important;">
    <div class="card-header bg-white border-bottom py-3 px-4">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <?= $this->render('@app/modules/am/components/tab_menu', [
                'tabs' => $tabs
            ]) ?>

            <div class="d-flex align-items-center gap-2">
                <div class="input-group">

                    <span class="input-group-text bg-white border-end-0 text-muted ps-3">

                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>

                    </span>

                    <input type="text" class="form-control border-start-0 ps-2 shadow-none text-secondary" placeholder="ค้นหารหัส, ชื่อทรัพย์สิน..." style="font-size: 0.9rem; width: 220px;">

                </div>
                <button class="btn btn-primary d-flex align-items-center gap-2 text-nowrap px-3 shadow-sm">

                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>

                    เพิ่มรายการ

                </button>

                <div class="dropdown">
                <button class="btn btn-success shadow dropdown-toggle" type="button"
                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                    <li><?= Html::a('<i class="fa-solid fa-file-csv me-2"></i>นำเข้าด้วย CSV', ['/am/import', 'title' => 'นำเข้าไฟล์ CSV'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                    <li><?= Html::a('<i class="fa-solid fa-file me-2"></i> ตัวอย่างไฟล์นำเข้า', 'https://docs.google.com/spreadsheets/d/1YjAwT8Qklc6gEx30T_fXa_XkfncrCRe3pt9FwC6QYok/edit?usp=sharing', ['class' => 'dropdown-item', 'target' => '_blank']) ?></li>
                    <li>
                        <?= Html::a(
                            '<i class="fa-solid fa-file-excel me-2"></i> ส่งออก Excel',
                            '#',
                            ['class' => 'dropdown-item delete-all-item', 'data-order-id' => 1]
                        ) ?>

                    </li>
                </ul>
            </div>

            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center py-2">#</th>
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
                <?php foreach ($dataProvider->getModels() as $item): ?>
                    <tr>
                        <td class="text-center text-muted fw-medium py-2" style="font-size: 1rem;">1</td>
                        <td>
                            <div class="fw-bold" style="font-size: 1rem;"><?= $item->code ?></div>
                            <div class="text-muted" style="font-size: 0.9rem;"><?= $item->asset_name ?></div>
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
                            <div class="d-flex justify-content-center gap-1">
                                <a href="<?= Url::to(['view', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary" title="ดูรายละเอียด">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>
                               <a href="<?= Url::to(['update', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary" title="ดูรายละเอียด">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path>
                                    </svg>
                            </a>
                                <a href="<?= Url::to(['qrcode', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary" title="ดูรายละเอียด">
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
                               <a href="<?= Url::to(['deltet', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary" title="ดูรายละเอียด">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10 11v6"></path>
                                        <path d="M14 11v6"></path>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path>
                                        <path d="M3 6h18"></path>
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                            </a>
                            </div>
                        </td>
                    </tr>
                    <!-- <td>
                            <div class="d-flex justify-content-center gap-1">
                                <a href="<?= Url::to(['view', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary" title="ดูรายละเอียด">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>
                                <a href="<?= Url::to(['update', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary" title="แก้ไข">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path>
                                    </svg>
                                </a>
                                <a href="<?= Url::to(['qrcode', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary" title="QR Code">
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
                                    </button>
                                    <a href="<?= Url::to(['delete', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary" title="ลบ">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M10 11v6"></path>
                                            <path d="M14 11v6"></path>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path>
                                            <path d="M3 6h18"></path>
                                            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                        </button>
                            </div>
                        </td> -->
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
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