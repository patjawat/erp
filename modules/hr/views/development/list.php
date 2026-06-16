<?php

use yii\helpers\Url;
use yii\helpers\Html;

$models = $dataProvider->getModels();
$offset = $dataProvider->pagination ? $dataProvider->pagination->offset : 0;
?>

<div class="table-responsive dev-list-wrap">
    <table class="table dev-list-table align-middle">
        <thead>
            <tr>
                <th scope="col" class="dev-col-requester">ผู้ขอ</th>
                <th scope="col" class="dev-col-topic">เรื่อง / ประเภท</th>
                <th scope="col" class="dev-col-location d-none d-lg-table-cell">สถานที่</th>
                <th scope="col" class="dev-col-date d-none d-md-table-cell">ช่วงวันที่</th>
                <th scope="col" class="dev-col-approver d-none d-xl-table-cell" style="width: 147px;">ผู้อนุมัติ</th>
                <th scope="col" class="dev-col-status text-center">สถานะ</th>
                <th scope="col" class="dev-col-actions text-end">จัดการ</th>
            </tr>
        </thead>
        <tbody class="align-middle">
            <?php if (empty($models)): ?>
                <tr>
                    <td colspan="7">
                        <div class="dev-empty py-5 text-center">
                            <i class="bi bi-inbox dev-empty__icon" aria-hidden="true"></i>
                            <p class="mt-3 mb-1 text-body fw-semibold">ไม่พบรายการตามตัวกรอง</p>
                            <p class="mb-0 small text-body-secondary">ลองปรับเงื่อนไขการค้นหา หรือล้างตัวกรองเพื่อดูทั้งหมด</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($models as $key => $item):
                    $location = trim((string)($item->data_json['location'] ?? ''));
                    $typeTitle = $item->developmentType?->title ?? '';
                    $statusHtml = $item->getStatus($item->status)['view'] ?? '-';
                    $dateRange = $item->showDateRange();
                ?>
                    <tr>
                        <!-- ผู้ขอ -->
                        <td class="dev-col-requester">
                            <div class="dev-requester">
                                <?php
                                try {
                                    echo $item->createdByEmp?->getAvatar(false) ?? '';
                                } catch (\Throwable $th) {
                                }
                                ?>
                            </div>
                        </td>

                        <!-- เรื่อง / ประเภท -->
                        <td class="dev-col-topic">
                            <p class="mb-1 fw-semibold text-body dev-topic"><?= Html::encode($item->topic) ?></p>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <?php if ($typeTitle !== ''): ?>
                                    <span class="dev-type-pill"><?= Html::encode($typeTitle) ?></span>
                                <?php endif; ?>
                                <?= $item->StackMember() ?>
                            </div>

                            <!-- mobile-only inline metadata (จุดเดียวที่มือถือเห็น) -->
                            <div class="dev-mobile-meta d-md-none mt-2">
                                <span class="dev-mobile-meta__item">
                                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                                    <?= $dateRange ?>
                                </span>
                                <?php if ($location !== ''): ?>
                                    <span class="dev-mobile-meta__item">
                                        <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                        <?= Html::encode($location) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="dev-mobile-meta d-none d-md-block d-lg-none mt-2">
                                <?php if ($location !== ''): ?>
                                    <span class="dev-mobile-meta__item">
                                        <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                        <?= Html::encode($location) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- สถานที่ -->
                        <td class="dev-col-location d-none d-lg-table-cell">
                            <?php if ($location !== ''): ?>
                                <span class="dev-location">
                                    <i class="bi bi-geo-alt dev-location__icon" aria-hidden="true"></i><?= Html::encode($location) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-body-tertiary small">ไม่ระบุ</span>
                            <?php endif; ?>
                        </td>

                        <!-- ช่วงวันที่ -->
                        <td class="dev-col-date d-none d-md-table-cell">
                            <span class="dev-date"><?= $dateRange ?></span>
                        </td>

                        <!-- ผู้อนุมัติ -->
                        <td class="dev-col-approver d-none d-xl-table-cell">
                            <?= $item->stackChecker() ?>
                        </td>

                        <!-- สถานะ -->
                        <td class="dev-col-status text-center">
                            <?= $statusHtml ?>
                        </td>

                        <!-- จัดการ -->
                        <td class="dev-col-actions text-end">
                            <div class="dropdown dev-row-actions">
                                <button type="button"
                                    class="btn btn-sm btn-outline-secondary fw-medium rounded-3 dropdown-toggle d-inline-flex align-items-center gap-1 dev-action-trigger"
                                    data-bs-toggle="dropdown"
                                    data-bs-strategy="fixed"
                                    aria-expanded="false">
                                    จัดการ
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0 dev-actions-menu py-2">
                                    <li class="dev-actions-menu__group-label">รายการ</li>
                                    <li>
                                        <?= Html::a(
                                            '<i class="fa-solid fa-pen-to-square fa-fw me-2 text-primary" aria-hidden="true"></i> แก้ไข',
                                            ['update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข'],
                                            ['class' => 'dropdown-item dev-actions-menu__item']
                                        ) ?>
                                    </li>
                                    <li>
                                        <?= Html::a(
                                            '<i class="fa-solid fa-eye fa-fw me-2 text-body-secondary" aria-hidden="true"></i> แสดงรายละเอียด',
                                            ['view', 'id' => $item->id],
                                            ['class' => 'dropdown-item dev-actions-menu__item']
                                        ) ?>
                                    </li>
                                    <?php if ($item->development_type_id == 'dev3'): ?>
                                        <li>
                                            <?= Html::a(
                                                '<i class="fa-solid fa-user-check fa-fw me-2 text-success" aria-hidden="true"></i> ตอบรับเป็นวิทยากร',
                                                ['/me/development/response-dev', 'id' => $item->id, 'title' => '<i class="fa-solid fa-user-check"></i> การตอบรับเป็นวิทยากร'],
                                                ['class' => 'dropdown-item dev-actions-menu__item open-modal', 'data' => ['size' => 'modal-lg']]
                                            ) ?>
                                        </li>
                                    <?php endif; ?>

                                    <li><hr class="dropdown-divider dev-actions-menu__divider"></li>
                                    <li class="dev-actions-menu__group-label">เอกสารพิมพ์</li>
                                    <li>
                                        <?= Html::a(
                                            '<i class="fa-solid fa-file-pdf fa-fw me-2 text-danger" aria-hidden="true"></i> พิมพ์ใบขอไปราชการ (PDF)',
                                            ['/hr/development/print', 'id' => $item->id],
                                            [
                                                'class' => 'dropdown-item dev-actions-menu__item',
                                                'target' => '_blank',
                                            ]
                                        ) ?>
                                    </li>
                                    <li>
                                        <?= Html::a(
                                            '<i class="fa-solid fa-car-side fa-fw me-2 text-primary" aria-hidden="true"></i> พิมพ์ใบขอใช้รถยนต์ส่วนตัว',
                                            ['/hr/development/print-personal-vehicle', 'id' => $item->id],
                                            [
                                                'class' => 'dropdown-item dev-actions-menu__item',
                                                'target' => '_blank',
                                                'title' => 'ใช้เทมเพลตที่ตั้งค่าไว้สำหรับ «ขอใช้รถยนต์ส่วนตัวเดินทางไปราชการ»',
                                            ]
                                        ) ?>
                                    </li>
                                    <li>
                                        <?= Html::a(
                                            '<i class="fa-solid fa-file-lines fa-fw me-2 text-body-secondary" aria-hidden="true"></i> พิมพ์ใบขออนุญาต',
                                            ['/me/development/permit-request', 'id' => $item->id],
                                            ['class' => 'dropdown-item dev-actions-menu__item open-modal', 'data' => ['size' => 'modal-xl']]
                                        ) ?>
                                    </li>
                                    <li>
                                        <?= Html::a(
                                            '<i class="fa-solid fa-chalkboard-user fa-fw me-2 text-body-secondary" aria-hidden="true"></i> พิมพ์ใบตอบรับเป็นวิทยากร',
                                            ['/me/development/form-academic', 'id' => $item->id],
                                            ['class' => 'dropdown-item dev-actions-menu__item open-modal', 'data' => ['size' => 'modal-xl']]
                                        ) ?>
                                    </li>

                                    <li><hr class="dropdown-divider dev-actions-menu__divider"></li>
                                    <li>
                                        <?= Html::a(
                                            '<i class="fa-solid fa-triangle-exclamation fa-fw me-2" aria-hidden="true"></i> ยกเลิก',
                                            ['cancel', 'id' => $item->id],
                                            ['class' => 'dropdown-item dev-actions-menu__item text-danger cancel-order']
                                        ) ?>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($models)): ?>
    <div class="d-flex justify-content-center mt-4">
        <?= yii\bootstrap5\LinkPager::widget([
            'pagination' => $dataProvider->pagination,
            'options' => ['class' => 'pagination pagination-sm mb-0'],
        ]); ?>
    </div>
<?php endif; ?>

<?php
$css = <<<CSS
/* === Table layout: subject-first reading "ใคร → อะไร → ที่ไหน → เมื่อไหร่ → อย่างไร" === */
.dev-list-wrap {
    border-radius: 0.75rem;
}

.dev-list-table {
    margin-bottom: 0;
}

.dev-list-table thead th {
    font-weight: 600;
    font-size: 0.8125rem;
    color: var(--bs-body-secondary);
    background-color: var(--bs-tertiary-bg);
    border-bottom: 1px solid var(--bs-border-color);
    padding: 0.75rem 1rem;
    white-space: nowrap;
}

.dev-list-table tbody td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--bs-border-color-translucent);
}

.dev-list-table tbody tr {
    transition: background-color 120ms cubic-bezier(0.22, 1, 0.36, 1);
}

.dev-list-table tbody tr:hover {
    background-color: var(--bs-tertiary-bg);
}

.dev-list-table tbody tr:last-child td {
    border-bottom: 0;
}

/* === คอลัมน์: WHO === */
.dev-col-requester {
    min-width: 13rem;
    max-width: 18rem;
}

/* getAvatar() คืน h6.fs-14 + tooltip — ปรับให้ดูเรียบขึ้นในตาราง */
.dev-col-requester .avatar-detail h6 {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--bs-body);
}

.dev-col-requester .avatar-detail .text-muted {
    font-size: 0.78rem;
}

/* === คอลัมน์: WHAT (เรื่อง+ประเภท) — focal point ของตา === */
.dev-col-topic {
    min-width: 16rem;
}

.dev-topic {
    font-size: 0.9375rem;
    line-height: 1.45;
    color: var(--bs-body);
}

.dev-type-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.125rem 0.625rem;
    border-radius: 999px;
    background-color: rgba(var(--bs-primary-rgb), 0.08);
    color: var(--bs-primary);
    font-size: 0.75rem;
    font-weight: 500;
    line-height: 1.6;
    white-space: nowrap;
}

/* StackMember inline กับ pill */
.dev-col-topic .avatar-stack {
    display: inline-flex;
    gap: 0.25rem;
}

.dev-col-topic .avatar-stack .avatar-sm {
    width: 1.5rem;
    height: 1.5rem;
}

/* === คอลัมน์: WHERE === */
.dev-col-location {
    min-width: 9rem;
    max-width: 14rem;
}

.dev-location {
    color: var(--bs-body);
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.dev-location__icon {
    color: var(--bs-secondary-color);
}

/* === คอลัมน์: WHEN === */
.dev-col-date {
    min-width: 11rem;
    color: var(--bs-body);
    font-size: 0.875rem;
    white-space: nowrap;
}

/* === คอลัมน์: APPROVERS === */
.dev-col-approver {
    min-width: 7rem;
}

.dev-col-approver .avatar-stack {
    display: flex;
    gap: 0.25rem;
}

/* === คอลัมน์: STATUS === */
.dev-col-status {
    width: 7rem;
}

/* === คอลัมน์: ACTIONS === */
.dev-col-actions {
    width: 9rem;
}

.dev-action-trigger {
    min-height: 2.25rem;
    padding-inline: 0.875rem;
}

/* === Mobile-only inline metadata (md และต่ำกว่า) === */
.dev-mobile-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 1rem;
    font-size: 0.8125rem;
    color: var(--bs-body-secondary);
}

.dev-mobile-meta__item {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.dev-mobile-meta__item i {
    color: var(--bs-secondary-color);
}

/* === Dropdown menu — ลบ stagger reflex, ใช้ container fade ทีเดียว === */
.dev-actions-menu {
    min-width: 16rem;
    padding-block: 0.5rem;
    animation: devMenuFadeIn 140ms cubic-bezier(0.22, 1, 0.36, 1) both;
}

.dev-actions-menu__group-label {
    padding: 0.5rem 1rem 0.25rem;
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--bs-secondary-color);
    /* sentence-case, ไม่ใช่ eyebrow uppercase tracked */
    letter-spacing: 0;
    text-transform: none;
}

.dev-actions-menu__item {
    border-radius: 0.5rem;
    margin: 0 0.5rem;
    padding: 0.5rem 0.75rem;
    min-height: 2.5rem;
    display: flex;
    align-items: center;
    width: calc(100% - 1rem);
    transition: background-color 120ms cubic-bezier(0.22, 1, 0.36, 1);
}

.dev-actions-menu__item:hover,
.dev-actions-menu__item:focus-visible {
    background-color: rgba(var(--bs-primary-rgb), 0.08);
}

.dev-actions-menu__item.text-danger:hover,
.dev-actions-menu__item.text-danger:focus-visible {
    background-color: rgba(var(--bs-danger-rgb), 0.10);
}

.dev-actions-menu__divider {
    margin: 0.375rem 0.75rem;
    opacity: 0.4;
}

/* === Empty state === */
.dev-empty__icon {
    font-size: 2.5rem;
    color: var(--bs-secondary-color);
    opacity: 0.45;
}

/* === Animation === */
@keyframes devMenuFadeIn {
    from { opacity: 0; transform: translateY(-2px); }
    to   { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
    .dev-actions-menu,
    .dev-actions-menu__item,
    .dev-list-table tbody tr {
        animation: none;
        transition: none;
    }
}

/* === Mobile fine-tune === */
@media (max-width: 575.98px) {
    .dev-list-table thead th {
        padding: 0.5rem 0.75rem;
    }
    .dev-list-table tbody td {
        padding: 0.75rem;
    }
    .dev-col-requester {
        min-width: 11rem;
    }
    .dev-col-topic {
        min-width: 0;
    }
    .dev-action-trigger {
        padding-inline: 0.625rem;
    }
}
CSS;
$this->registerCss($css);
