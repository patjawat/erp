<?php

use yii\helpers\Html;
use app\components\RichText;

/** @var \app\modules\pm\models\StrategyIndicatorYear $entry @var \app\modules\pm\models\StrategyIndicator $indicator */
/** @var \app\modules\pm\models\StrategyPlan $plan @var \app\modules\pm\models\StrategyIndicatorYear[] $siblings @var bool $canEdit */

$goal = $indicator->goal;
$issue = $goal?->issue;
$mission = $issue?->mission;
$this->title = 'รายละเอียดตัวชี้วัด ' . $indicator->code . ' ปี ' . $entry->fiscal_year;
$this->beginBlock('page-title'); ?>รายละเอียดตัวชี้วัด (KPI Template)<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'indicator']) ?><?php $this->endBlock();

app\assets\RichTextAsset::register($this);
$this->registerCss('@media print{.kpi-noprint{display:none!important}.kpi-sheet{box-shadow:none!important;border:0!important}}.kpi-sheet dt{font-weight:600}.kpi-sheet dd{margin-bottom:0}');

/** แสดงข้อความที่จัดรูปแบบไว้ คงหัวข้อย่อย/ลำดับเลขตามที่ผู้ใช้จัด */
$bullets = static fn(?string $text) => RichText::isEmpty($text)
    ? '<span class="text-muted">—</span>'
    : '<div class="erp-richtext">' . RichText::render($text) . '</div>';
$plain = static fn(?string $v) => $v !== null && trim($v) !== '' ? Html::encode($v) : '<span class="text-muted">—</span>';
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3 kpi-noprint">
    <?= Html::a('<i data-lucide="arrow-left" class="me-1"></i> กลับทะเบียนตัวชี้วัด', ['index', 'type' => 'indicator', 'planId' => $plan->id, 'year' => $entry->fiscal_year], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
    <div class="d-flex flex-wrap gap-2">
        <?= Html::a('<i data-lucide="printer" class="me-1"></i> พิมพ์', '#', ['class' => 'btn btn-sm btn-outline-secondary', 'onclick' => 'window.print();return false;']) ?>
        <?php if (Yii::$app->user->can('pmStrategyManage')): ?>
            <?= Html::a('<i data-lucide="calendar-days" class="me-1"></i> ลงผลรายเดือน', ['monthly', 'id' => $entry->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
        <?php endif; ?>
        <?php if ($canEdit): ?>
            <?= Html::a('<i data-lucide="pencil" class="me-1"></i> แก้ไข', ['update', 'type' => 'indicator', 'id' => $entry->id], ['class' => 'btn btn-sm btn-primary']) ?>
        <?php endif; ?>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-3 kpi-noprint">
    <?php foreach ($siblings as $sibling): ?>
        <?= Html::a('ปี ' . $sibling->fiscal_year, ['template', 'id' => $sibling->id],
            ['class' => 'btn btn-sm rounded-pill ' . ($sibling->id === $entry->id ? 'btn-primary' : 'btn-outline-secondary')]) ?>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm kpi-sheet"><div class="card-body p-4">
    <div class="d-flex justify-content-between align-items-start gap-3 border-bottom pb-3 mb-3">
        <div>
            <h1 class="h5 mb-1">รายละเอียดตัวชี้วัด (KPI Template)</h1>
            <div class="small text-muted">ปีงบประมาณ <?= (int) $entry->fiscal_year ?> · <?= Html::encode($plan->name) ?></div>
        </div>
        <div class="text-end">
            <?php if ($entry->owner_team): ?><span class="badge bg-primary fs-6"><?= Html::encode($entry->owner_team) ?></span><?php endif; ?>
            <?php if ($entry->isCancelled()): ?><div class="mt-1"><span class="badge bg-secondary">ยกเลิกในปีนี้</span></div><?php endif; ?>
        </div>
    </div>

    <dl class="row g-0 mb-3">
        <dt class="col-12 col-md-3">ประเด็นพันธกิจ</dt>
        <dd class="col-12 col-md-9 mb-2"><?= $mission ? Html::encode("$mission->code-$mission->name") : '<span class="text-muted">—</span>' ?></dd>
        <dt class="col-12 col-md-3">ประเด็นยุทธศาสตร์</dt>
        <dd class="col-12 col-md-9 mb-2"><?= $issue ? Html::encode("$issue->code-$issue->name") : '<span class="text-muted">—</span>' ?></dd>
        <dt class="col-12 col-md-3">เป้าประสงค์</dt>
        <dd class="col-12 col-md-9 mb-2"><?= $goal ? Html::encode("$goal->code-$goal->name") : '<span class="text-muted">—</span>' ?></dd>
        <dt class="col-12 col-md-3">ตัวชี้วัด</dt>
        <dd class="col-12 col-md-9"><?= Html::encode($indicator->code . '-' . $entry->displayName()) ?>
            <?php if ($entry->displayUnit()): ?><span class="text-muted">(<?= Html::encode($entry->displayUnit()) ?>)</span><?php endif; ?></dd>
    </dl>

    <div class="table-responsive mb-3"><table class="table table-bordered align-middle mb-0">
        <thead class="table-light"><tr>
            <th><?= $entry->baseline_label ? Html::encode($entry->baseline_label) : 'ค่าเป้าหมาย' ?></th>
            <?php foreach ($siblings as $sibling): ?><th class="text-center">ปีงบ <?= (int) $sibling->fiscal_year ?></th><?php endforeach; ?>
        </tr></thead>
        <tbody><tr>
            <td class="text-muted small">ค่าเป้าหมาย</td>
            <?php foreach ($siblings as $sibling): ?>
                <td class="text-center <?= $sibling->id === $entry->id ? 'fw-semibold table-active' : '' ?>">
                    <?= $sibling->isCancelled() ? '<span class="text-muted small">ยกเลิก</span>' : Html::encode($sibling->target_value ?? '—') ?>
                </td>
            <?php endforeach; ?>
        </tr></tbody>
    </table></div>

    <dl class="mb-3">
        <dt>ประชากรกลุ่มเป้าหมาย</dt><dd class="mb-3"><?= $bullets($entry->target_population) ?></dd>
        <dt>คำจำกัดความ</dt><dd class="mb-3"><?= $bullets($entry->definition) ?></dd>
        <dt>ระยะเวลาการประเมินผล</dt>
        <dd class="mb-3"><?php $selected = array_filter($entry->periodRows(), fn($p) => $p->is_selected); ?>
            <?= $selected ? implode(' · ', array_map(fn($p) => Html::encode($p->label()), $selected)) : '<span class="text-muted">—</span>' ?></dd>
        <dt>สูตรคำนวณตัวชี้วัด</dt><dd class="mb-3"><?= $bullets($entry->formula) ?></dd>
    </dl>

    <div class="fw-semibold mb-2">เกณฑ์การประเมิน</div>
    <div class="table-responsive mb-3"><table class="table table-bordered align-middle mb-0">
        <thead class="table-light"><tr><th>รอบ</th><?php foreach ($entry->periodRows() as $period): ?><th class="text-center"><?= Html::encode($period->label()) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
            <tr><td class="text-muted small">เกณฑ์</td><?php foreach ($entry->periodRows() as $period): ?><td class="text-center"><?= $period->is_selected ? Html::encode($period->target_value ?? '—') : '<span class="text-muted">-</span>' ?></td><?php endforeach; ?></tr>
            <tr><td class="text-muted small">ผลงาน</td><?php foreach ($entry->periodRows() as $period): ?><td class="text-center"><?= $period->is_selected ? Html::encode($period->actual_value ?? '—') : '<span class="text-muted">-</span>' ?></td><?php endforeach; ?></tr>
        </tbody>
    </table></div>

    <ul class="mb-3 ps-3">
        <?php foreach ($entry->scoreRows() as $score): ?>
            <?php if (RichText::isEmpty($score->description) && $score->min_value === null && $score->max_value === null) continue; ?>
            <li>ระดับที่ <?= (int) $score->level ?> <span class="erp-richtext d-inline"><?= RichText::render($score->description) ?></span>
                <?php if ($score->min_value !== null || $score->max_value !== null): ?>
                    <span class="text-muted small">(<?= Html::encode($score->min_value ?? '—') ?> – <?= Html::encode($score->max_value ?? '—') ?>)</span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <dl class="mb-3">
        <dt>วิธีการประเมินผล</dt><dd class="mb-3"><?= $bullets($entry->evaluation_method) ?></dd>
        <dt>แหล่งข้อมูล</dt><dd class="mb-3"><?= $bullets($entry->data_source) ?></dd>
    </dl>

    <div class="fw-semibold mb-2">ผลการดำเนินงานรายเดือน ปีงบประมาณ <?= (int) $entry->fiscal_year ?></div>
    <div class="table-responsive mb-3"><table class="table table-bordered table-sm align-middle mb-0">
        <thead class="table-light"><tr><?php foreach ($entry->monthRows() as $month): ?><th class="text-center small"><?= Html::encode($month->label((int) $entry->fiscal_year)) ?></th><?php endforeach; ?></tr></thead>
        <tbody><tr><?php foreach ($entry->monthRows() as $month): ?><td class="text-center"><?= $month->value !== null ? Html::encode($month->value) : '<span class="text-muted">—</span>' ?></td><?php endforeach; ?></tr></tbody>
    </table></div>
    <div class="small text-muted mb-3">
        บันทึกแล้ว <?= $entry->monthsFilled() ?>/12 เดือน<?php if (($total = $entry->monthlyTotal()) !== null): ?> · รวม <?= Html::encode(rtrim(rtrim(number_format($total, 4, '.', ''), '0'), '.')) ?><?php endif; ?>
        · ผลงานจริงทั้งปีที่บันทึกไว้ <?= Html::encode($entry->actual_value ?? '—') ?>
    </div>

    <div class="fw-semibold mb-2">ข้อมูลพื้นฐาน (Baseline Data)</div>
    <div class="table-responsive mb-3"><table class="table table-bordered align-middle mb-0">
        <thead class="table-light"><tr><?php foreach ($entry->baselines as $baseline): ?><th class="text-center">ปี <?= (int) $baseline->fiscal_year ?></th><?php endforeach; ?><th class="text-center">ค่าเฉลี่ย</th></tr></thead>
        <tbody><tr><?php foreach ($entry->baselines as $baseline): ?><td class="text-center"><?= Html::encode($baseline->value ?? '—') ?></td><?php endforeach; ?><td class="text-center fw-semibold"><?= Html::encode($entry->baseline_average ?? '—') ?></td></tr></tbody>
    </table></div>
    <?php if (!$entry->baselines): ?><div class="small text-muted mb-3">ยังไม่ได้บันทึกข้อมูลพื้นฐาน</div><?php endif; ?>

    <dl class="mb-3"><dt>หมายเหตุ</dt><dd><?= $bullets($entry->note) ?></dd></dl>

    <div class="row g-3 border-top pt-3">
        <div class="col-12 col-md-6">
            <div class="small text-muted">ผู้กำกับดูแลตัวชี้วัด</div>
            <div><?= $plain($entry->supervisor_name) ?></div>
            <div class="small text-muted">เบอร์โทรติดต่อ <?= $plain($entry->supervisor_phone) ?></div>
        </div>
        <div class="col-12 col-md-6">
            <div class="small text-muted">ผู้รับผิดชอบ</div>
            <div><?= $plain($entry->owner_name) ?></div>
            <div class="small text-muted">เบอร์โทรติดต่อ <?= $plain($entry->owner_phone) ?></div>
        </div>
    </div>
</div></div>
