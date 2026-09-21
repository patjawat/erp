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

$cards = [
    ['label' => 'ตัวชี้วัดทั้งหมด', 'value' => $summary['total'], 'cls' => 'text-body'],
    ['label' => 'ผ่าน (PASS)', 'value' => $summary['pass'], 'cls' => 'text-success'],
    ['label' => 'ต้องพัฒนา (GAP)', 'value' => $summary['gap'], 'cls' => 'text-danger'],
    ['label' => 'ยังไม่มีข้อมูล', 'value' => $summary['nodata'], 'cls' => 'text-secondary'],
];
?>

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
    <div>
        <h2 class="h5 mb-1">ตัวชี้วัดของโรงพยาบาล</h2>
        <p class="text-muted mb-0">ตัวชี้วัดของ รพ. / ทีมประสาน / งานพยาบาล / หน่วยงาน — ปีงบประมาณ <?= $year ?></p>
    </div>
    <?php if ($canManage): ?>
        <?= Html::a('<i data-lucide="plus" class="me-1"></i> เพิ่มตัวชี้วัด', ['create'], ['class' => 'btn btn-primary']) ?>
    <?php endif; ?>
</div>

<div class="row g-2 g-md-3 mb-3">
    <?php foreach ($cards as $c): ?>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
                <div class="small text-muted"><?= Html::encode($c['label']) ?></div>
                <div class="fs-3 fw-bold <?= $c['cls'] ?>" style="font-variant-numeric:tabular-nums"><?= (int) $c['value'] ?></div>
            </div></div>
        </div>
    <?php endforeach; ?>
</div>

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
            <?php $entry = $ind->yearEntry($year); $status = $ind->statusFor($year); ?>
            <tr>
                <td class="ps-4"><div class="fw-semibold"><?= Html::encode(RichText::plain($ind->name, 160)) ?></div></td>
                <td><span class="badge rounded-pill" style="background:<?= Html::encode($ind->group->color ?? '#6c757d') ?>1a;color:<?= Html::encode($ind->group->color ?? '#6c757d') ?>"><?= Html::encode($ind->group->name ?? '-') ?></span></td>
                <td class="small"><?= Html::encode($ind->org_unit_id ? ($unitNames[$ind->org_unit_id] ?? '-') : '-') ?></td>
                <td class="text-center small"><?= Html::encode($ind->unit ?: '-') ?></td>
                <td class="text-end" style="font-variant-numeric:tabular-nums"><?= $entry && $entry->target_value !== null ? Html::encode(rtrim(rtrim((string) $entry->target_value, '0'), '.')) : '-' ?></td>
                <td class="text-end" style="font-variant-numeric:tabular-nums"><?= $entry && $entry->actual_value !== null ? Html::encode(rtrim(rtrim((string) $entry->actual_value, '0'), '.')) : '-' ?></td>
                <td class="text-center"><span class="badge <?= KpiStatus::badgeClass($status) ?>"><?= Html::encode(KpiStatus::label($status)) ?></span></td>
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
