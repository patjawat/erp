<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\modules\pm\models\StrategyIndicatorYear;

/** @var app\modules\pm\models\StrategyPlan|null $plan @var app\modules\pm\models\StrategyPlan[] $plans */
/** @var int|null $year @var string $q @var array $sourceYears */
/** @var app\modules\pm\models\StrategyIndicator[] $primaries ตัวชี้วัดหลักพร้อมตัวชี้วัดรอง */
/** @var StrategyIndicatorYear[] $entries ข้อมูลรายปี คีย์ด้วย indicator_id */

$this->title = 'ตัวชี้วัดยุทธศาสตร์';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'indicator']) ?><?php $this->endBlock();

$canEdit = $plan && $plan->isEditable() && Yii::$app->user->can('pmStrategyManage');
$planItems = ArrayHelper::map($plans, 'id', fn($p) => $p->name . ' · รุ่น ' . $p->version);
?>
<?php if (!$plan): ?>
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5">
        <div class="text-muted mb-3">ยังไม่มีแผนยุทธศาสตร์ในระบบ</div>
        <?= Html::a('ไปที่แผนยุทธศาสตร์', ['/pm/strategy-plan/index'], ['class' => 'btn btn-outline-primary']) ?>
    </div></div>
    <?php return; ?>
<?php endif; ?>

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-3 mb-3">
    <div>
        <h2 class="h5 mb-1"><?= Html::encode($plan->name) ?></h2>
        <p class="text-muted mb-0">
            พ.ศ. <?= (int) $plan->start_year ?>–<?= (int) $plan->end_year ?> · รุ่น <?= (int) $plan->version ?>
            <span class="badge <?= $plan->status === 'published' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?> ms-1"><?= Html::encode($plan::statusList()[$plan->status] ?? $plan->status) ?></span>
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?= Html::a('<i data-lucide="git-branch" class="me-1"></i> โครงสร้างยุทธศาสตร์', ['/pm/strategy-plan/view', 'id' => $plan->id], ['class' => 'btn btn-outline-secondary']) ?>
        <?php if ($canEdit): ?>
            <?= Html::a('<i data-lucide="plus" class="me-1"></i> เพิ่มตัวชี้วัด', ['create', 'type' => 'indicator', 'planId' => $plan->id, 'year' => $year], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3"><div class="card-body">
    <?= Html::beginForm(['index'], 'get', ['class' => 'row g-3 align-items-end']) ?>
    <?= Html::hiddenInput('type', 'indicator') ?>
    <div class="col-12 col-md-5">
        <label class="form-label fw-semibold">ชุดแผนยุทธศาสตร์</label>
        <?= Html::dropDownList('planId', $plan->id, $planItems, ['class' => 'form-select']) ?>
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label fw-semibold">ค้นหา</label>
        <?= Html::textInput('q', $q, ['class' => 'form-control', 'placeholder' => 'รหัสหรือชื่อตัวชี้วัด']) ?>
    </div>
    <div class="col-12 col-md-auto"><?= Html::submitButton('แสดงข้อมูล', ['class' => 'btn btn-primary']) ?></div>
    <?= Html::endForm() ?>
</div></div>

<div class="d-flex flex-wrap gap-2 mb-3">
    <?php foreach ($plan->fiscalYears() as $fy): ?>
        <?= Html::a('ปี ' . $fy, ['index', 'type' => 'indicator', 'planId' => $plan->id, 'year' => $fy, 'q' => $q],
            ['class' => 'btn btn-sm rounded-pill ' . ($fy === $year ? 'btn-primary' : 'btn-outline-secondary')]) ?>
    <?php endforeach; ?>
</div>

<?php if ($canEdit && $sourceYears): ?>
    <div class="card border-0 shadow-sm mb-3"><div class="card-body">
        <?= Html::beginForm(['copy-year'], 'post', ['class' => 'd-flex flex-wrap align-items-end gap-2']) ?>
        <?= Html::hiddenInput('planId', $plan->id) . Html::hiddenInput('toYear', $year) ?>
        <div>
            <label class="form-label small fw-semibold mb-1">คัดลอกรายละเอียดตัวชี้วัดจากปี</label>
            <?= Html::dropDownList('fromYear', max($sourceYears), array_combine($sourceYears, $sourceYears), ['class' => 'form-select form-select-sm']) ?>
        </div>
        <?= Html::submitButton('<i data-lucide="copy" class="me-1"></i> คัดลอกมาปี ' . $year, [
            'class' => 'btn btn-sm btn-outline-primary',
            'data-confirm' => "คัดลอกรายละเอียดตัวชี้วัดที่ใช้งานอยู่ทั้งหมดมายังปี {$year}? รายการที่มีอยู่แล้วจะถูกข้าม",
        ]) ?>
        <?= Html::endForm() ?>
    </div></div>
<?php endif; ?>

<div class="card border-0 shadow-sm overflow-hidden"><div class="card-body p-0">
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead class="table-light"><tr>
            <th class="ps-4">ตัวชี้วัด</th>
            <th class="text-nowrap">ค่าเป้าหมาย ปี <?= $year ?></th>
            <th>รายละเอียดปีนี้</th>
            <th class="text-end pe-4">จัดการ</th>
        </tr></thead>
        <tbody>
        <?php foreach ($primaries as $indicator): ?>
            <?= $this->render('_indicator_row', ['indicator' => $indicator, 'entry' => $entries[$indicator->id] ?? null, 'year' => $year, 'canEdit' => $canEdit, 'isChild' => false]) ?>
            <?php foreach ($indicator->children as $child): ?>
                <?= $this->render('_indicator_row', ['indicator' => $child, 'entry' => $entries[$child->id] ?? null, 'year' => $year, 'canEdit' => $canEdit, 'isChild' => true]) ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <?php if (!$primaries): ?>
            <tr><td colspan="4" class="text-center text-muted py-5">
                <?= $q !== '' ? 'ไม่พบตัวชี้วัดที่ค้นหา' : 'ยังไม่มีตัวชี้วัดในชุดแผนนี้ — เพิ่มได้จากหน้าโครงสร้างยุทธศาสตร์ใต้เป้าประสงค์' ?>
            </td></tr>
        <?php endif; ?>
        </tbody>
    </table></div>
</div></div>
