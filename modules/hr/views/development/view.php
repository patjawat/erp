<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;

$me = UserHelper::GetEmployee();
/** @var yii\web\View $this */
/** @var app\modules\hr\models\Development $model */

$this->title = 'รายละเอียดการพัฒนาบุคลากร';
$this->params['breadcrumbs'][] = ['label' => 'การพัฒนาบุคลากร', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// ตัวอักษรย่อสำหรับ avatar fallback
$requesterInitials = '?';
if ($model->createdByEmp) {
    $fname = trim((string) ($model->createdByEmp->fname ?? ''));
    $lname = trim((string) ($model->createdByEmp->lname ?? ''));
    if ($fname !== '' || $lname !== '') {
        $requesterInitials = mb_substr($fname !== '' ? $fname : $lname, 0, 1, 'UTF-8');
    }
}

// helper สำหรับแสดงค่า data_json พร้อม fallback "ไม่ระบุ" รูปแบบเดียวกันทั้งไฟล์
$dv = function ($value, string $fallback = 'ไม่ระบุ') {
    $value = is_string($value) ? trim($value) : $value;
    if ($value === null || $value === '' || $value === false) {
        return '<span class="text-body-tertiary">' . Html::encode($fallback) . '</span>';
    }
    return Html::encode((string) $value);
};

// badge color ของประเภทสถานที่
$locationType = $model->data_json['location_org_type'] ?? '';
$locationBadge = [
    'ในจังหวัด' => 'success',
    'ต่างจังหวัด' => 'info',
    'ต่างประเทศ' => 'warning',
][$locationType] ?? 'secondary';

// สถานะของใบ — ใช้ HTML badge จาก model
$statusHtml = $model->getStatus($model->status)['view'] ?? '';
$statusColor = $model->getStatus($model->status)['color'] ?? 'secondary';
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary" aria-hidden="true">
            <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
            <path d="m9 14 2 2 4-4" />
        </svg>
        <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex gap-2">
    <?= $this->render('@app/components/ui/btnReturn') ?>
</div>
<?php $this->endBlock(); ?>

<div class="dv-page container-fluid py-3" data-status="<?= Html::encode($statusColor) ?>">
    <div class="row g-4">

        <!-- คอลัมน์ซ้าย: เนื้อหา -->
        <div class="col-12 col-lg-8">
            <div class="dv-stack">

                <!-- Hero: หัวเรื่อง + สถานะ + ผู้ขอ + ปุ่มแก้ไข -->
                <header class="dv-hero bg-white p-3 rounded-3 ">
                    <div class="dv-hero__top">
                        <div class="dv-hero__meta">
                            <?php if (!empty($model->document_id)): ?>
                                <span class="dv-hero__doc">
                                    <i class="fa-solid fa-hashtag" aria-hidden="true"></i>
                                    <?= Html::encode($model->document_id) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($statusHtml !== ''): ?>
                                <span class="dv-hero__status"><?= $statusHtml ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="dv-hero__action">
                            <?= Html::a(
                                '<i class="fa-solid fa-pen-to-square me-1" aria-hidden="true"></i> แก้ไข',
                                ['/me/development/update', 'id' => $model->id, 'title' => '<i class="bi bi-mortarboard-fill me-2"></i>แบบฟอร์มบันทึกข้อมูลการพัฒนาบุคลากร'],
                                ['class' => 'btn btn-sm btn-primary rounded-3 open-modal-x', 'data' => ['size' => 'modal-xl']]
                            ) ?>
                        </div>
                    </div>

                    <h1 class="dv-hero__topic">
                        <?= Html::encode($model->topic ?? '-') ?>
                    </h1>

                    <div class="dv-hero__requester">
                        <div class="dv-avatar-wrap flex-shrink-0">
                            <?php if ($model->createdByEmp): ?>
                                <?= Html::img($model->createdByEmp->showAvatar(), [
                                    'class' => 'dv-avatar lazyload',
                                    'alt' => $model->createdByEmp->fullname ?? '',
                                    'data' => ['sizes' => 'auto', 'src' => $model->createdByEmp->showAvatar()],
                                ]) ?>
                            <?php else: ?>
                                <span class="dv-avatar dv-avatar--fallback" aria-label="ไม่มีรูปประจำตัว">
                                    <?= Html::encode($requesterInitials) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="dv-hero__requester-info">
                            <p class="dv-name mb-1">
                                <span class="text-body-tertiary small me-1">ผู้ขอ</span>
                                <span class="fw-semibold text-body"><?= Html::encode($model->createdByEmp->fullname ?? 'ไม่ระบุ') ?></span>
                            </p>
                            <p class="mb-0 small text-body-secondary">
                                <?= Html::encode($model->createdByEmp->position->name ?? 'ไม่ระบุตำแหน่ง') ?>
                                <span class="dv-dot" aria-hidden="true">·</span>
                                <?= Html::encode($model->createdByEmp->department->name ?? 'ไม่ระบุแผนก') ?>
                            </p>
                            <?php if ($model->viewCreated()['full'] ?? null): ?>
                                <p class="mb-0 small text-body-tertiary mt-1">
                                    <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>ผู้บันทึก: <?= $model->viewCreated()['full'] ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </header>

                <!-- Section: รายละเอียดการพัฒนา -->
                <section class="dv-section bg-white p-3 rounded-3" aria-labelledby="dv-detail-h">
                    <h2 id="dv-detail-h" class="dv-section__heading">
                        <span class="dv-icon dv-icon--primary"><i class="bi bi-info-circle" aria-hidden="true"></i></span>
                        รายละเอียดการพัฒนา
                    </h2>
                    <dl class="dv-fields">
                        <dt>คณะเดินทาง</dt>
                        <dd><?= $dv($model->data_json['travel_party'] ?? null) ?></dd>

                        <dt>ระยะเวลา</dt>
                        <dd>
                            <i class="bi bi-calendar3 me-1 text-body-secondary" aria-hidden="true"></i><?= $model->showDateRange() ?>
                            <?php if (!empty($model->data_json['time_slot'])): ?>
                                <span class="badge text-bg-light border border-secondary-subtle rounded-pill fw-medium ms-1"><?= Html::encode($model->data_json['time_slot']) ?></span>
                            <?php endif; ?>
                        </dd>

                        <dt>ประเภทการพัฒนา</dt>
                        <dd><?= $dv($model->data_json['development_type_name'] ?? null) ?></dd>

                        <dt>ระดับการพัฒนา</dt>
                        <dd><?= $dv($model->data_json['development_level_name'] ?? null) ?></dd>

                        <dt>ลักษณะ</dt>
                        <dd><?= $dv($model->data_json['development_go_type_name'] ?? null) ?></dd>

                        <dt>การเบิกเงิน</dt>
                        <dd><?= $dv($model->data_json['claim_type_name'] ?? null) ?></dd>
                    </dl>
                </section>

                <!-- Section: สถานที่และหน่วยงาน -->
                <section class="dv-section bg-white p-3 rounded-3" aria-labelledby="dv-loc-h">
                    <h2 id="dv-loc-h" class="dv-section__heading">
                        <span class="dv-icon dv-icon--info"><i class="bi bi-geo-alt" aria-hidden="true"></i></span>
                        สถานที่และหน่วยงาน
                    </h2>
                    <dl class="dv-fields dv-fields--multi">
                        <div class="dv-field">
                            <dt>สถานที่จัด</dt>
                            <dd><?= $dv($model->data_json['location'] ?? null) ?></dd>
                        </div>
                        <div class="dv-field">
                            <dt>จังหวัด</dt>
                            <dd><?= $dv($model->data_json['province_name'] ?? null) ?></dd>
                        </div>
                        <div class="dv-field">
                            <dt>ประเภทสถานที่</dt>
                            <dd>
                                <?php if ($locationType !== ''): ?>
                                    <span class="badge bg-<?= $locationBadge ?> bg-opacity-10 text-<?= $locationBadge ?> border border-<?= $locationBadge ?>-subtle rounded-pill fw-medium px-2 py-1">
                                        <?= Html::encode($locationType) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-body-tertiary">ไม่ระบุ</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div class="dv-field">
                            <dt>หน่วยงานที่จัด</dt>
                            <dd><?= $dv($model->data_json['location_org'] ?? null) ?></dd>
                        </div>
                    </dl>
                </section>

                <!-- Section: บุคลากรที่เกี่ยวข้อง -->
                <section class="dv-section bg-white p-3 rounded-3" aria-labelledby="dv-people-h">
                    <h2 id="dv-people-h" class="dv-section__heading">
                        <span class="dv-icon dv-icon--teal"><i class="bi bi-people" aria-hidden="true"></i></span>
                        บุคลากรที่เกี่ยวข้อง
                    </h2>
                    <dl class="dv-fields dv-fields--multi">
                        <div class="dv-field">
                            <dt>หัวหน้า</dt>
                            <dd>
                                <?php if ($model->leader): ?>
                                    <?= $model->leader->getAvatar(false) ?>
                                <?php else: ?>
                                    <span class="text-body-tertiary">ไม่ระบุ</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div class="dv-field">
                            <dt>ผู้ปฏิบัติหน้าที่แทน</dt>
                            <dd>
                                <?php if ($model->assignedTo): ?>
                                    <?= $model->assignedTo->getAvatar(false) ?>
                                <?php else: ?>
                                    <span class="text-body-tertiary">ไม่ระบุ</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                    </dl>
                </section>

                <!-- Section: ข้อมูลการเดินทาง -->
                <section class="dv-section bg-white p-3 rounded-3" aria-labelledby="dv-travel-h">
                    <h2 id="dv-travel-h" class="dv-section__heading">
                        <span class="dv-icon dv-icon--amber"><i class="bi bi-car-front" aria-hidden="true"></i></span>
                        ข้อมูลการเดินทาง
                    </h2>
                    <dl class="dv-fields dv-fields--multi">
                        <div class="dv-field">
                            <dt>วันที่เดินทาง</dt>
                            <dd>
                                <i class="bi bi-calendar3 me-1 text-body-secondary" aria-hidden="true"></i><?= $model->showVehicleDateRange() ?>
                            </dd>
                        </div>
                        <div class="dv-field">
                            <dt>พาหนะเดินทาง</dt>
                            <dd>
                                <?php if (!empty($model->data_json['vehicle_type_name'])): ?>
                                    <i class="bi bi-car-front-fill me-1 text-body-secondary" aria-hidden="true"></i><?= Html::encode($model->data_json['vehicle_type_name']) ?>
                                <?php else: ?>
                                    <span class="text-body-tertiary">ไม่ระบุ</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div class="dv-field">
                            <dt>ระยะทาง</dt>
                            <dd>
                                <?php if (!empty($model->data_json['distance']) && (string) $model->data_json['distance'] !== '') : ?>
                                    <?= Html::encode($model->data_json['distance']) ?> <span class="text-body-secondary">กม.</span>
                                <?php else: ?>
                                    <span class="text-body-tertiary">ไม่ระบุ</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                    </dl>
                </section>

                <!-- ค่าใช้จ่าย (จาก partial เดิม) -->
                <?= $this->render('expense_type', ['model' => $model]) ?>

            </div>
        </div>

        <!-- คอลัมน์ขวา: timeline สถานะการอนุมัติ -->
        <div class="col-12 col-lg-4">
            <div class="dv-approval">
                <section class="dv-panel" aria-labelledby="dv-timeline-h">
                    <h2 id="dv-timeline-h" class="dv-panel__heading">
                        <span class="dv-icon dv-icon--primary"><i class="bi bi-clipboard-check" aria-hidden="true"></i></span>
                        สถานะการอนุมัติ
                    </h2>
                    <?= $this->render('timeline_approve', ['model' => $model]) ?>
                </section>
            </div>
        </div>

    </div>
</div>

<?php
$css = <<<CSS
.dv-page {
    --dv-divider: var(--bs-border-color);
    --dv-section-gap: 1.75rem;
}

/* === Stack rhythm === */
.dv-stack {
    display: flex;
    flex-direction: column;
    gap: var(--dv-section-gap);
}

/* === Hero === */
.dv-hero {
    padding: 0 0 1.5rem;
    border-bottom: 1px solid var(--dv-divider);
}

.dv-hero__top {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.dv-hero__meta {
    display: inline-flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.dv-hero__doc {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.625rem;
    border-radius: 999px;
    background: var(--bs-tertiary-bg);
    color: var(--bs-body-secondary);
    font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    font-size: 0.8125rem;
    font-weight: 500;
}

.dv-hero__doc i {
    font-size: 0.75rem;
    opacity: 0.65;
}

.dv-hero__status .badge,
.dv-hero__status > span {
    font-size: 0.8125rem;
    padding: 0.3rem 0.625rem;
}

.dv-hero__topic {
    font-size: clamp(1.375rem, 2.2vw, 1.75rem);
    font-weight: 700;
    line-height: 1.3;
    color: var(--bs-body);
    margin: 0 0 1.25rem;
    text-wrap: balance;
}

.dv-hero__requester {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.dv-hero__requester-info {
    min-width: 0;
    flex: 1;
}

.dv-avatar-wrap {
    width: 56px;
    height: 56px;
}

.dv-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid var(--bs-border-color);
    background: var(--bs-body-bg);
}

.dv-avatar--fallback {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--bs-tertiary-bg);
    color: var(--bs-body-secondary);
    font-weight: 600;
    font-size: 1.25rem;
}

.dv-name {
    font-size: 0.9375rem;
    color: var(--bs-body);
    line-height: 1.4;
}

.dv-dot {
    color: var(--bs-secondary-color);
    margin-inline: 0.25rem;
}

/* === Section heading: icon-in-box pattern === */
.dv-section__heading,
.dv-panel__heading {
    font-size: 1.0625rem;
    font-weight: 600;
    color: var(--bs-body);
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
    margin: 0 0 1rem;
    line-height: 1.4;
}

.dv-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 0.625rem;
    font-size: 1rem;
    flex-shrink: 0;
    transition: transform 200ms cubic-bezier(0.22, 1, 0.36, 1);
}

.dv-icon--primary {
    background: rgba(var(--bs-primary-rgb), 0.10);
    color: var(--bs-primary);
}

.dv-icon--info {
    background: rgba(var(--bs-info-rgb), 0.12);
    color: var(--bs-info-text-emphasis, #055160);
}

.dv-icon--teal {
    background: rgba(32, 201, 151, 0.12);
    color: #198754;
}

.dv-icon--amber {
    background: rgba(var(--bs-warning-rgb), 0.14);
    color: #b45309;
}

.dv-section:hover .dv-icon,
.dv-panel:hover .dv-icon {
    transform: scale(1.06);
}

/* === Definition list === */
.dv-fields {
    display: grid;
    grid-template-columns: minmax(7rem, 11rem) 1fr;
    column-gap: 1.5rem;
    row-gap: 0.625rem;
    margin: 0;
}

.dv-fields dt {
    font-weight: 400;
    color: var(--bs-body-secondary);
    padding-top: 0.125rem;
}

.dv-fields dd {
    margin: 0;
    color: var(--bs-body);
    word-wrap: break-word;
    min-width: 0;
}

/* multi-column variant: short fields side-by-side */
.dv-fields--multi {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    column-gap: 1.5rem;
    row-gap: 1.25rem;
}

.dv-fields--multi .dv-field {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 0;
}

.dv-fields--multi dt {
    font-size: 0.78rem;
    color: var(--bs-body-secondary);
    margin: 0;
    text-transform: none;
    letter-spacing: 0;
}

.dv-fields--multi dd {
    margin: 0;
    color: var(--bs-body);
    font-size: 0.9375rem;
}

/* === Right column panel === */
.dv-panel {
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: 0.875rem;
    padding: 1.25rem;
    position: relative;
}

.dv-panel__heading {
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--dv-divider);
    width: 100%;
    display: flex;
}

/* === Page status accent: subtle top edge tied to status color === */
.dv-page[data-status="success"] .dv-panel { border-top-color: rgba(var(--bs-success-rgb), 0.45); border-top-width: 2px; }
.dv-page[data-status="warning"] .dv-panel { border-top-color: rgba(var(--bs-warning-rgb), 0.55); border-top-width: 2px; }
.dv-page[data-status="danger"] .dv-panel  { border-top-color: rgba(var(--bs-danger-rgb), 0.55); border-top-width: 2px; }
.dv-page[data-status="info"] .dv-panel    { border-top-color: rgba(var(--bs-info-rgb), 0.55); border-top-width: 2px; }
.dv-page[data-status="primary"] .dv-panel { border-top-color: rgba(var(--bs-primary-rgb), 0.45); border-top-width: 2px; }

/* === Entrance animation — content always visible (translate only) === */
@keyframes dvSlideUp {
    from { transform: translateY(8px); opacity: 0.85; }
    to   { transform: translateY(0); opacity: 1; }
}

.dv-stack > .dv-hero,
.dv-stack > .dv-section,
.dv-approval > .dv-panel {
    animation: dvSlideUp 380ms cubic-bezier(0.22, 1, 0.36, 1) both;
}

.dv-stack > .dv-hero                       { animation-delay: 0ms; }
.dv-stack > .dv-section:nth-of-type(1)     { animation-delay: 70ms; }
.dv-stack > .dv-section:nth-of-type(2)     { animation-delay: 130ms; }
.dv-stack > .dv-section:nth-of-type(3)     { animation-delay: 190ms; }
.dv-stack > .dv-section:nth-of-type(4)     { animation-delay: 250ms; }
.dv-approval > .dv-panel                   { animation-delay: 110ms; }

/* Topic micro-emphasis on hero mount — underline grows from 0 */
.dv-hero__topic {
    position: relative;
    padding-bottom: 0.5rem;
}

.dv-hero__topic::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: 0;
    height: 3px;
    width: 2.5rem;
    background: var(--bs-primary);
    border-radius: 2px;
    transform-origin: left center;
    animation: dvUnderline 520ms cubic-bezier(0.22, 1, 0.36, 1) 180ms both;
}

@keyframes dvUnderline {
    from { transform: scaleX(0); }
    to   { transform: scaleX(1); }
}

/* === Mobile === */
@media (max-width: 575.98px) {
    .dv-page {
        --dv-section-gap: 1.5rem;
    }

    .dv-hero__topic {
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }

    .dv-fields {
        grid-template-columns: 1fr;
        row-gap: 0.25rem;
        column-gap: 0;
    }

    .dv-fields dt {
        padding-top: 0.5rem;
        font-size: 0.8125rem;
    }

    .dv-fields dt:first-of-type {
        padding-top: 0;
    }

    .dv-fields--multi {
        grid-template-columns: 1fr;
        row-gap: 1rem;
    }

    .dv-hero__action {
        margin-left: auto;
    }
}

/* === Reduced motion: instant, no transform === */
@media (prefers-reduced-motion: reduce) {
    .dv-stack > .dv-hero,
    .dv-stack > .dv-section,
    .dv-approval > .dv-panel,
    .dv-hero__topic::after,
    .dv-icon {
        animation: none;
        transition: none;
        transform: none;
    }
}
CSS;
$this->registerCss($css);
?>
