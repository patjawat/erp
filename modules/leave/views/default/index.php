<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ThaiDateHelper;
use app\components\ApproveHelper;
use app\modules\leave\models\Leave;

$this->title = 'การลางาน';
$this->params['breadcrumbs'][] = $this->title;

$name = $employee ? trim(($employee->fname ?? '') . ' ' . ($employee->lname ?? '')) : 'ผู้ใช้';
?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('../menu_user', ['active' => 'index']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2">
    <span class="erp-icon-box bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center">
        <i data-lucide="calendar-check" style="width:1.25rem;height:1.25rem"></i>
    </span>
    <h4 class="fw-bold text-body mb-0">การลางาน</h4>
</div>
<?php $this->endBlock(); ?>

<?php
$lucideIconMap = [
    'calendar-check' => 'calendar-check',
    'heart' => 'heart',
    'droplet' => 'droplet',
    'baby' => 'baby',
    'sun' => 'sun',
    'stethoscope' => 'stethoscope',
    'palm' => 'palmtree',
    'palmtree' => 'palmtree',
    'calendar' => 'calendar',
    'briefcase' => 'briefcase',
    'coffee' => 'coffee',
    'umbrella' => 'umbrella',
    'heart-pulse' => 'heart-pulse',
    'syringe' => 'syringe',
    'graduation-cap' => 'graduation-cap',
    'book-open' => 'book-open',
    'church' => 'church',
    'scale' => 'scale',
];
$typeTheme = [
    'LT1' => ['lucide' => 'palmtree', 'color' => '#0d9488'],
    'LT2' => ['lucide' => 'baby', 'color' => '#db2777'],
    'LT3' => ['lucide' => 'stethoscope', 'color' => '#dc2626'],
    'LT4' => ['lucide' => 'briefcase', 'color' => '#2563eb'],
    'LT5' => ['lucide' => 'heart-pulse', 'color' => '#ea580c'],
    'LT6' => ['lucide' => 'graduation-cap', 'color' => '#7c3aed'],
    'LT7' => ['lucide' => 'baby', 'color' => '#059669'],
    'LT8' => ['lucide' => 'church', 'color' => '#4b5563'],
    'LT9' => ['lucide' => 'scale', 'color' => '#0d9488'],
];
?>
<div class="container-fluid py-3">

    <div class="row g-4">
        <!-- คอลัมน์ซ้าย: ข้อมูลปีงบประมาณ + การ์ดประเภทลา + เกณฑ์การลา -->
        <div class="col-12 col-lg-4">
        <div class="card">
        <div class="card-body p-3">
            <div class="d-flex align-items-start gap-2">
                <span class="erp-icon-box bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
                    <i data-lucide="calendar-days" style="width:1.125rem;height:1.125rem"></i>
                </span>
                <p class="text-muted small mb-0">ข้อมูลปีงบประมาณ <?= Html::encode($fiscalLabel) ?> — ข้อมูลด้านล่างและประวัติการลาถูกกรองตามปีที่เลือก</p>
            </div>
            </div>
            </div>
            <?php foreach ($typeSummaries as $s):
                $code = $s['code'] ?? '';
                $theme = $typeTheme[$code] ?? null;
                if ($theme) {
                    $typeIcon = $theme['lucide'];
                    $typeColor = $theme['color'];
                } else {
                    $icon = $s['icon'] ?? 'calendar-check';
                    $typeIcon = $lucideIconMap[$icon] ?? 'calendar-check';
                    $typeColor = $s['color'] ?? '#0d6efd';
                }
                $typeColorSafe = Html::encode($typeColor);
            ?>
                <div class="card border-0 shadow-sm rounded-3 mb-3">
                    <div class="card-body p-3">
                        <div class="row align-items-center g-2">
                            <div class="col-auto">
                                <span class="erp-icon-box rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: <?= $typeColorSafe ?>20; color: <?= $typeColorSafe ?>">
                                    <i data-lucide="<?= Html::encode($typeIcon) ?>" style="width:1.25rem;height:1.25rem"></i>
                                </span>
                            </div>
                            <div class="col">
                                <div class="small fw-medium text-body"><?= Html::encode($s['title']) ?></div>
                                <?php
                                $ent = (int) ($s['entitlement_days'] ?? 0);
                                $used = (int) ($s['days_used'] ?? 0);
                                $remain = max(0, $ent - $used);
                                ?>
                                <div class="small text-secondary">สิทธิวันลา <?= $ent ?> วัน | <?= (int) ($s['times_used'] ?? 0) ?> ครั้ง</div>
                            </div>
                            <div class="col-auto text-end">
                                <span class="small fw-semibold text-primary">คงเหลือ <?= $remain ?> วัน</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (!empty($criteriaRules)): ?>
                <div class="card border-0 shadow-sm rounded-3 mt-3">
                    <div class="card-header bg-transparent border-0 py-2 px-3">
                        <h6 class="mb-0 fw-semibold text-body d-flex align-items-center gap-2">
                            <i data-lucide="file-text" style="width:1rem;height:1rem"></i>
                            เกณฑ์การลา
                        </h6>
                    </div>
                    <div class="card-body pt-0 px-3 pb-3">
                        <ul class="small text-secondary mb-0 ps-3 list-unstyled">
                            <?php foreach ($criteriaRules as $rule): ?>
                                <li class="mb-2 position-relative" style="padding-left: 0.5rem;">
                                    <span class="position-absolute start-0 top-0 text-primary">•</span>
                                    <?= Html::encode($rule) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- คอลัมน์ขวา: ประวัติการลาล่าสุด -->
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body p-2">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <h5 class="d-flex align-items-center gap-2 fw-bold text-body mb-0">
                            <span class="erp-icon-box bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center">
                                <i data-lucide="list" style="width:1.125rem;height:1.125rem"></i>
                            </span>
                            ประวัติการลาล่าสุด
                        </h5>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="dropdown">
                                <button class="btn btn-light border rounded-3 dropdown-toggle" type="button" id="dropdownYear" data-bs-toggle="dropdown" aria-expanded="false">
                                    ปีงบประมาณ <?= (int) $thaiYear ?>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownYear">
                                    <?php foreach ((array) $listThaiYear as $y => $label): ?>
                                        <li><a class="dropdown-item" href="<?= Url::to(array_merge(['/leave/default/index'], $_GET, ['thai_year' => $y])) ?>">ปี <?= Html::encode($label) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?= Html::a('<i data-lucide="plus" style="width:1rem;height:1rem" class="me-1 align-middle"></i> สร้างใบลา', ['/leave/leave/create'], ['class' => 'btn btn-primary rounded-3']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="table-responsive" style="min-height: 625px;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small fw-semibold text-center" style="width: 3rem;">ลำดับ</th>
                                <th class="small fw-semibold">วันที่ส่ง</th>
                                <th class="small fw-semibold">ประเภท</th>
                                <th class="small fw-semibold">ช่วงเวลา</th>
                                <th class="small fw-semibold text-center">จำนวน</th>
                                <th class="small fw-semibold">ผู้อนุมัติ</th>
                                <th class="small fw-semibold">สถานะ/ความคืบหน้า</th>
                                <th class="small fw-semibold text-end">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider align-middle">
                            <?php
                            $offset = (int) ($dataProvider->pagination->offset ?? 0);
                            foreach ($dataProvider->getModels() as $index => $item):
                                $no = $offset + $index + 1;
                            ?>
                                <tr>
                                    <td class="small text-center text-muted"><?= $no ?></td>
                                    <td class="small">
                                        <div><?= ThaiDateHelper::formatThaiDate($item->created_at, 'short') ?></div>
                                        <div class="text-muted"><?= date('H:i', strtotime($item->created_at)) ?> น.</div>
                                    </td>
                                    <td class="small">
                                        <div><?= Html::encode($item->leaveType ? $item->leaveType->title : '-') ?></div>
                                        <div class="text-muted"><?= Html::encode($item->data_json['reason'] ?? '-') ?></div>
                                    </td>
                                    <td class="small"><?= $item->showLeaveDate() ?></td>
                                    <td class="small text-center"><?= (int) $item->total_days ?></td>
                                    <td class="small py-3 px-3">
                                        <?= $item->stackChecker() ?: '<span class="text-muted">—</span>' ?>
                                    </td>
                                    <td class="small py-3 px-3">
                                        <?= $item->viewStatus() ?? '' ?>
                                        <?= ApproveHelper::viewStepFromSteps($item->approves ?? []) ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i> ดำเนินการ
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <?= Html::a(
                                                        '<i class="bi bi-eye me-2"></i> แสดง',
                                                        ['/leave/leave/view', 'id' => $item->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> รายละเอียดใบลา'],
                                                        ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]
                                                    ) ?>
                                                </li>
                                                <?php if (!$item->hasApprovalDecision()): ?>
                                                    <li>
                                                        <?= Html::a(
                                                            '<i class="bi bi-pencil me-2"></i> แก้ไข',
                                                            ['/leave/leave/update', 'id' => $item->id],
                                                            ['class' => 'dropdown-item']
                                                        ) ?>
                                                    </li>
                                                <?php endif; ?>
                                                <li>
                                                    <?= Html::a(
                                                        '<i class="bi bi-printer me-2"></i> พิมพ์ใบลา (PDF)',
                                                        ['/leave/leave/pdf', 'id' => $item->id],
                                                        [
                                                            'class' => 'dropdown-item',
                                                            'target' => '_blank',
                                                            'rel' => 'noopener noreferrer',
                                                            'data-pjax' => '0',
                                                            'title' => 'ใช้เทมเพลตจาก /pdf-template ก่อน; ถ้ายังไม่ตั้งจะใช้แบบฟอร์มใบลาเดิม — พิมพ์ได้ทุกสถานะ',
                                                        ]
                                                    ) ?>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ($dataProvider->getCount() === 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4 small">ยังไม่มีประวัติการลา</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-transparent border-0 py-2">
                    <?= \yii\bootstrap5\LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$this->registerJs("if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();", \yii\web\View::POS_END);
?>