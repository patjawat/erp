<?php

use yii\helpers\Url;
use yii\helpers\Html;
?>

<table class="table">
    <thead>
        <tr>
            <th class="text-center" style="width:30px">ลำดับ</th>
            <th>เรื่อง</th>
            <th>ประเภท</th>
            <th style="width: 200px;">วันที่</th>
            <th scope="col">ผู้ขอ</th>
            <th scope="col" style="width: 200px;">ผอ.</th>
            <th class="fw-semibold text-center" scope="col">สถานะ</th>
            <th class="fw-semibold text-end" style="width:100px">ดำเนินการ</th>
        </tr>
    </thead>
    <tbody class="align-middle table-group-divider">
        <?php foreach ($dataProvider->getModels() as $key => $item): ?>
            <tr>
                <td class="text-center">
                    <?php echo (($dataProvider->pagination->offset + 1) + $key) ?>
                </td>
                <td>
                    <p class="mb-0"><?= $item->topic ?></p>
                    <p class="mb-0">สถานที่ <span class="fw-semibold"><?= $item->data_json['location'] ?? 'ไม่ระบุ' ?></span>
                        <?= $item->StackMember() ?>
                    </p>
                </td>
                <td>
                    <?= $item->developmentType?->title ?? '-' ?>
                </td>
                <td>
                    <p class="mb-0"> <?= $item->showDateRange() ?></p>
                </td>
                <td>
                    <?php
                    try {
                        echo $item->createdByEmp->getAvatar(false) ?? '';
                    } catch (\Throwable $th) {
                    } ?>
                </td>
                <td><?php echo $item->stackChecker() ?></td>
                <td class="text-center"><?= $item->getStatus($item->status)['view'] ?? '-' ?></td>
                <td class="text-end">
                    <div class="dropdown dev-row-actions">
                        <button class="btn btn-sm btn-outline-secondary fw-medium rounded-3 dropdown-toggle d-inline-flex align-items-center gap-1"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            จัดการ
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0 dev-actions-menu py-2">
                            <li><h6 class="dropdown-header text-uppercase small text-secondary-emphasis fw-semibold px-3 mb-1">รายการ</h6></li>
                            <li><?= Html::a('<i class="fa-regular fa-pen-to-square fa-fw me-2 text-primary"></i> แก้ไข', ['update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item py-2']) ?>
                            </li>
                            <li><?= Html::a('<i class="fa-solid fa-eye fa-fw me-2 text-secondary"></i> แสดงรายละเอียด', ['view', 'id' => $item->id], ['class' => 'dropdown-item py-2']) ?>
                            </li>
                            <?php if ($item->development_type_id == 'dev3'): ?>
                                <li><?= Html::a('<i class="fa-solid fa-user-check fa-fw me-2 text-success"></i> ตอบรับเป็นวิทยากร', ['/me/development/response-dev', 'id' => $item->id, 'title' => '<i class="fa-solid fa-user-check"></i> การตอบรับเป็นวิทยากร'], ['class' => 'dropdown-item py-2 open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                </li>
                            <?php endif; ?>

                            <li><hr class="dropdown-divider my-2"></li>
                            <li><h6 class="dropdown-header text-uppercase small text-secondary-emphasis fw-semibold px-3 mb-1">เอกสารพิมพ์</h6></li>
                            <li>
                                <?= Html::a(
                                    '<i class="fa-solid fa-file-pdf fa-fw me-2 text-danger"></i> พิมพ์ใบขอไปราชการ (PDF)',
                                    ['/hr/development/print', 'id' => $item->id],
                                    [
                                        'class' => 'dropdown-item py-2',
                                        'target' => '_blank',
                                    ]
                                ) ?>
                            </li>
                            <li>
                                <?= Html::a(
                                    '<i class="fa-solid fa-car-side fa-fw me-2 text-primary"></i> พิมพ์ใบขอใช้รถยนต์ส่วนตัว',
                                    ['/hr/development/print-personal-vehicle', 'id' => $item->id],
                                    [
                                        'class' => 'dropdown-item py-2',
                                        'target' => '_blank',
                                        'title' => 'ใช้เทมเพลตที่ตั้งค่าไว้สำหรับ «ขอใช้รถยนต์ส่วนตัวเดินทางไปราชการ»',
                                    ]
                                ) ?>
                            </li>
                            <li><?= Html::a('<i class="fa-solid fa-file-lines fa-fw me-2 text-secondary"></i> พิมพ์ใบขออนุญาต', ['/me/development/permit-request', 'id' => $item->id], ['class' => 'dropdown-item py-2 open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                            </li>
                            <li><?= Html::a('<i class="fa-solid fa-chalkboard-user fa-fw me-2 text-secondary"></i> พิมพ์ใบตอบรับเป็นวิทยากร', ['/me/development/form-academic', 'id' => $item->id], ['class' => 'dropdown-item py-2 open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                            </li>

                            <li><hr class="dropdown-divider my-2"></li>
                            <li>
                                <?= Html::a(
                                    '<i class="fa-solid fa-gear fa-fw me-2"></i> ตั้งค่าเทมเพลตพิมพ์',
                                    ['/pdf-template'],
                                    [
                                        'class' => 'dropdown-item py-2 text-body-secondary small',
                                        'target' => '_blank',
                                        'title' => 'เปิดหน้าตั้งค่าเทมเพลต PDF ทุกประเภท (booking.vehicle.official ฯลฯ)',
                                    ]
                                ) ?>
                            </li>
                            <li><?= Html::a('<i class="fa-solid fa-triangle-exclamation fa-fw me-2"></i> ยกเลิก', ['cancel', 'id' => $item->id], ['class' => 'dropdown-item py-2 text-danger cancel-order']) ?>
                            </li>
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

<?php
$css = <<<CSS
.dev-actions-menu {
    min-width: 268px;
}
.dev-actions-menu .dropdown-header {
    letter-spacing: 0.06em;
    font-size: 0.7rem;
}
.dev-actions-menu .dropdown-item {
    border-radius: 0.5rem;
    margin: 0 0.5rem;
    width: calc(100% - 1rem);
    transition: background-color 140ms cubic-bezier(0.22, 1, 0.36, 1), color 140ms cubic-bezier(0.22, 1, 0.36, 1);
}
.dev-actions-menu .dropdown-item:hover,
.dev-actions-menu .dropdown-item:focus-visible {
    background-color: rgba(13, 110, 253, 0.08);
}
.dev-actions-menu .dropdown-item.text-danger:hover,
.dev-actions-menu .dropdown-item.text-danger:focus-visible {
    background-color: rgba(220, 53, 69, 0.10);
}
.dev-actions-menu .dropdown-divider {
    margin-left: 0.75rem;
    margin-right: 0.75rem;
    opacity: 0.5;
}
@keyframes devActionsItemIn {
    from { opacity: 0; transform: translateY(-4px); }
    to   { opacity: 1; transform: translateY(0); }
}
.dev-actions-menu.show > li {
    animation: devActionsItemIn 200ms cubic-bezier(0.22, 1, 0.36, 1) both;
}
.dev-actions-menu.show > li:nth-child(1)  { animation-delay: 10ms; }
.dev-actions-menu.show > li:nth-child(2)  { animation-delay: 25ms; }
.dev-actions-menu.show > li:nth-child(3)  { animation-delay: 40ms; }
.dev-actions-menu.show > li:nth-child(4)  { animation-delay: 55ms; }
.dev-actions-menu.show > li:nth-child(5)  { animation-delay: 70ms; }
.dev-actions-menu.show > li:nth-child(6)  { animation-delay: 85ms; }
.dev-actions-menu.show > li:nth-child(7)  { animation-delay: 100ms; }
.dev-actions-menu.show > li:nth-child(8)  { animation-delay: 115ms; }
.dev-actions-menu.show > li:nth-child(9)  { animation-delay: 130ms; }
.dev-actions-menu.show > li:nth-child(n+10) { animation-delay: 145ms; }
@media (prefers-reduced-motion: reduce) {
    .dev-actions-menu.show > li { animation: none; }
    .dev-actions-menu .dropdown-item { transition: none; }
}
CSS;
$this->registerCss($css);
