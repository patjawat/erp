<?php

use yii\helpers\Html;
use app\components\RichText;
use app\modules\pm\components\KpiStatus;

/** @var app\modules\pm\models\KpiIndicator[] $indicators */
/** @var int $year @var string $q @var int|null $group @var int|null $unit */
/** @var array $summary @var array $groups @var array $units @var array $unitNames @var bool $canManage */

$this->title = 'KPI โรงพยาบาล';
$this->beginBlock('page-title'); ?>KPI โรงพยาบาล<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'kpi']) ?><?php $this->endBlock();
app\assets\RichTextAsset::register($this);

$defYear = \app\modules\pm\services\KpiRegistry::defaultFiscalYear();
$yearOpts = range($defYear + 1, $defYear - 4);

$cards = [
    ['label' => 'ตัวชี้วัดทั้งหมด', 'value' => $summary['total'], 'cls' => 'text-body', 'status' => null, 'border' => 'primary'],
    ['label' => 'ผ่าน (PASS)', 'value' => $summary['pass'], 'cls' => 'text-success', 'status' => 'pass', 'border' => 'success'],
    ['label' => 'ต้องพัฒนา (GAP)', 'value' => $summary['gap'], 'cls' => 'text-danger', 'status' => 'gap', 'border' => 'danger'],
    ['label' => 'ยังไม่มีข้อมูล', 'value' => $summary['nodata'], 'cls' => 'text-secondary', 'status' => 'nodata', 'border' => 'secondary'],
];
// รักษาตัวกรองเดิมไว้เมื่อคลิกการ์ด
$cardBase = ['index', 'year' => $year, 'group' => $group, 'unit' => $unit, 'q' => $q];
?>

<?php foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $cls): ?>
    <?php if (Yii::$app->session->hasFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode(Yii::$app->session->getFlash($key)) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
    <div>
        <h2 class="h5 mb-1">ตัวชี้วัดของโรงพยาบาล</h2>
        <p class="text-muted mb-0">ตัวชี้วัดของ รพ. / ทีมประสาน / งานพยาบาล / หน่วยงาน — ปีงบประมาณ <?= $year ?></p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?= Html::a('<i class="bi bi-printer me-1"></i> พิมพ์รายงาน', ['/pm/default/report', 'year' => $year], ['class' => 'btn btn-outline-secondary', 'target' => '_blank']) ?>
        <?php if ($canManage): ?>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#copyYearBox"><i class="bi bi-files me-1"></i> คัดลอกค่าข้ามปี</button>
            <?= Html::a('<i data-lucide="plus" class="me-1"></i> เพิ่มตัวชี้วัด', ['create'], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
    </div>
</div>

<?php if ($canManage): ?>
    <div class="collapse mb-3" id="copyYearBox"><div class="card border-0 shadow-sm"><div class="card-body">
        <?= Html::beginForm(['copy-year'], 'post', ['class' => 'row g-2 align-items-end']) ?>
        <div class="col-auto"><label class="form-label small fw-semibold mb-1">จากปี</label>
            <?= Html::dropDownList('from_year', $defYear - 1, array_combine($yearOpts, $yearOpts), ['class' => 'form-select form-select-sm']) ?></div>
        <div class="col-auto d-flex align-items-end pb-2"><i class="bi bi-arrow-right"></i></div>
        <div class="col-auto"><label class="form-label small fw-semibold mb-1">ไปปี</label>
            <?= Html::dropDownList('to_year', $defYear, array_combine($yearOpts, $yearOpts), ['class' => 'form-select form-select-sm']) ?></div>
        <div class="col-auto"><?= Html::submitButton('คัดลอกเป้าหมาย', ['class' => 'btn btn-sm btn-primary', 'data-confirm' => 'คัดลอกค่าเป้าหมายไปปีปลายทาง? (ผลจริงเว้นว่าง, ตัวที่มีข้อมูลปีปลายทางแล้วจะข้าม)']) ?></div>
        <div class="col-12"><div class="form-text">คัดลอกเฉพาะ "ค่าเป้าหมาย" ผลจริงเว้นว่างให้กรอกใหม่ · ข้ามตัวที่มีข้อมูลปีปลายทางอยู่แล้ว</div></div>
        <?= Html::endForm() ?>
    </div></div></div>
<?php endif; ?>

<div class="row g-2 g-md-3 mb-3">
    <?php foreach ($cards as $c): ?>
        <?php $isActive = ($status === $c['status']); ?>
        <div class="col-6 col-lg-3">
            <?= Html::a(
                '<div class="card-body py-3"><div class="small text-muted d-flex justify-content-between align-items-center">'
                    . Html::encode($c['label'])
                    . ($isActive ? '<i class="bi bi-funnel-fill text-' . $c['border'] . '"></i>' : '')
                    . '</div><div class="fs-3 fw-bold ' . $c['cls'] . '" style="font-variant-numeric:tabular-nums">' . (int) $c['value'] . '</div></div>',
                array_merge($cardBase, ['status' => $c['status']]),
                ['class' => 'card border-0 shadow-sm h-100 text-decoration-none' . ($isActive ? ' border-2 border-' . $c['border'] : ''), 'style' => $isActive ? 'outline:2px solid var(--bs-' . $c['border'] . ')' : '']
            ) ?>
        </div>
    <?php endforeach; ?>
</div>
<?php if ($status): ?>
    <div class="mb-3"><span class="badge bg-light text-dark border">กรอง: <?= Html::encode(\app\modules\pm\components\KpiStatus::label($status)) ?></span>
        <?= Html::a('<i class="bi bi-x"></i> ล้างตัวกรองสถานะ', $cardBase, ['class' => 'btn btn-sm btn-link text-decoration-none py-0']) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-3"><div class="card-body">
    <?= Html::beginForm(['index'], 'get', ['class' => 'row g-3 align-items-end']) ?>
    <div class="col-12 col-md-3">
        <label class="form-label fw-semibold">ปีงบประมาณ</label>
        <?= Html::input('number', 'year', $year, ['class' => 'form-control']) ?>
    </div>
    <div class="col-12 col-md-3">
        <label class="form-label fw-semibold">กลุ่ม</label>
        <?= Html::dropDownList('group', $group, $groups, ['class' => 'form-select', 'prompt' => 'ทุกกลุ่ม']) ?>
    </div>
    <div class="col-12 col-md-3">
        <label class="form-label fw-semibold">หน่วยงาน</label>
        <?= Html::dropDownList('unit', $unit, $units, ['class' => 'form-select', 'prompt' => 'ทุกหน่วยงาน']) ?>
    </div>
    <div class="col-12 col-md-2">
        <label class="form-label fw-semibold">ค้นหา</label>
        <?= Html::textInput('q', $q, ['class' => 'form-control', 'placeholder' => 'ชื่อตัวชี้วัด']) ?>
    </div>
    <div class="col-12 col-md-auto d-flex gap-2">
        <?= Html::submitButton('<i data-lucide="search"></i>', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ล้าง', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <?= Html::endForm() ?>
</div></div>

<div class="card border-0 shadow-sm overflow-hidden"><div class="card-body p-0">
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead class="table-light"><tr>
            <th class="ps-4">ตัวชี้วัด</th><th>กลุ่ม</th><th>หน่วยงาน</th><th class="text-center">หน่วย</th>
            <th class="text-end">เป้า</th><th class="text-end">ผลจริง</th><th class="text-center">สถานะ</th>
            <?php if ($canManage): ?><th class="text-end pe-4">จัดการ</th><?php endif; ?>
        </tr></thead>
        <tbody>
        <?php foreach ($indicators as $ind): ?>
            <?php $entry = $ind->yearEntry($year); $rowStatus = $statusMap[$ind->id] ?? $ind->statusFor($year); ?>
            <tr>
                <td class="ps-4"><?= Html::a(Html::encode(RichText::plain($ind->name, 160)), ['view', 'id' => $ind->id], ['class' => 'fw-semibold text-decoration-none']) ?></td>
                <td><span class="badge rounded-pill" style="background:<?= Html::encode($ind->group->color ?? '#6c757d') ?>1a;color:<?= Html::encode($ind->group->color ?? '#6c757d') ?>"><?= Html::encode($ind->group->name ?? '-') ?></span></td>
                <td class="small"><?= Html::encode($ind->org_unit_id ? ($unitNames[$ind->org_unit_id] ?? '-') : '-') ?></td>
                <td class="text-center small"><?= Html::encode($ind->unit ?: '-') ?></td>
                <td class="text-end" style="font-variant-numeric:tabular-nums"><?= $entry && $entry->target_value !== null ? Html::encode(rtrim(rtrim((string) $entry->target_value, '0'), '.')) : '-' ?></td>
                <td class="text-end" style="font-variant-numeric:tabular-nums"><?= $entry && $entry->actual_value !== null ? Html::encode(rtrim(rtrim((string) $entry->actual_value, '0'), '.')) : '-' ?></td>
                <td class="text-center"><span class="badge <?= KpiStatus::badgeClass($rowStatus) ?>"><?= Html::encode(KpiStatus::label($rowStatus)) ?></span></td>
                <?php if ($canManage): ?>
                    <td class="text-end pe-4">
                        <?= Html::a('แก้ไข', ['update', 'id' => $ind->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= Html::a('ลบ', ['delete', 'id' => $ind->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-method' => 'post', 'data-confirm' => 'ยืนยันการลบตัวชี้วัดนี้? (ค่ารายปีจะถูกลบด้วย)']) ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        <?php if (!$indicators): ?>
            <tr><td colspan="<?= $canManage ? 8 : 7 ?>" class="text-center text-muted py-5">ยังไม่มีตัวชี้วัดในเงื่อนไขนี้</td></tr>
        <?php endif; ?>
        </tbody>
    </table></div>
</div></div>
