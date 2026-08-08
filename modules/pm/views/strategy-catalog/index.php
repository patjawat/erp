<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\components\RichText;
use app\components\widgets\DataSummaryWidget;
use app\modules\pm\models\StrategyIndicatorYear;

/** @var string $type @var int|null $planId @var \app\modules\pm\models\StrategyPlan|null $plan */
/** @var int|null $year @var string $q @var array $plans @var array $sourceYears @var array $adoptable */

$labels = ['indicator' => 'ตัวชี้วัด', 'factor' => 'ปัจจัยความสำเร็จ/RCA', 'measure' => 'มาตรการ', 'program' => 'แผนงานหลัก'];
$this->title = 'ทะเบียน' . $labels[$type];
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => $type === 'program' ? 'program' : 'indicator']) ?><?php $this->endBlock();

app\assets\RichTextAsset::register($this);
$canEdit = $plan && $plan->isEditable() && Yii::$app->user->can('pmStrategyManage');
$planItems = ArrayHelper::map($plans, 'id', fn($p) => $p->name . ' · รุ่น ' . $p->version);
$isYearly = $type === 'indicator' && $plan;
?>
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-3 mb-3">
    <div>
        <h2 class="h5 mb-1"><?= Html::encode($labels[$type]) ?><?= $isYearly ? ' ปีงบประมาณ ' . $year : '' ?></h2>
        <p class="text-muted mb-0"><?= $isYearly
            ? 'ตัวชี้วัดคงรหัสเดิมตลอดอายุแผน ส่วนนิยามและค่าเป้าหมายปรับได้ในแต่ละปี'
            : 'เลือกชุดแผนเพื่อดูและจัดการข้อมูลที่อ้างอิงร่วมกัน' ?></p>
    </div>
    <?php if ($canEdit && in_array($type, ['indicator', 'program'], true)): ?>
        <?= Html::a('<i data-lucide="plus" class="me-1"></i> เพิ่ม' . $labels[$type], ['create', 'type' => $type, 'planId' => $planId, 'year' => $year], ['class' => 'btn btn-primary']) ?>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm mb-3"><div class="card-body">
    <?= Html::beginForm(['index'], 'get', ['class' => 'row g-3 align-items-end']) ?>
    <?= Html::hiddenInput('type', $type) ?>
    <div class="col-12 col-md-5">
        <label class="form-label fw-semibold">ชุดแผนยุทธศาสตร์</label>
        <?= Html::dropDownList('planId', $planId, $planItems, ['class' => 'form-select', 'prompt' => 'เลือกชุดแผน']) ?>
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label fw-semibold">ค้นหา</label>
        <?= Html::textInput('q', $q, ['class' => 'form-control', 'placeholder' => 'รหัสหรือชื่อรายการ']) ?>
    </div>
    <div class="col-12 col-md-auto"><?= Html::submitButton('แสดงข้อมูล', ['class' => 'btn btn-primary']) ?></div>
    <?= Html::endForm() ?>
</div></div>

<?php if ($isYearly): ?>
    <div class="d-flex flex-wrap gap-2 mb-3">
        <?php foreach ($plan->fiscalYears() as $fy): ?>
            <?= Html::a('ปี ' . $fy, ['index', 'type' => 'indicator', 'planId' => $plan->id, 'year' => $fy, 'q' => $q],
                ['class' => 'btn btn-sm rounded-pill ' . ($fy === $year ? 'btn-primary' : 'btn-outline-secondary')]) ?>
        <?php endforeach; ?>
    </div>

    <?php if ($canEdit && ($sourceYears || $adoptable)): ?>
        <div class="card border-0 shadow-sm mb-3"><div class="card-body d-flex flex-column flex-lg-row gap-3">
            <?php if ($sourceYears): ?>
                <?= Html::beginForm(['copy-year'], 'post', ['class' => 'd-flex flex-wrap align-items-end gap-2 flex-grow-1']) ?>
                <?= Html::hiddenInput('planId', $plan->id) . Html::hiddenInput('toYear', $year) ?>
                <div>
                    <label class="form-label small fw-semibold mb-1">คัดลอกชุดตัวชี้วัดจากปี</label>
                    <?= Html::dropDownList('fromYear', max($sourceYears), array_combine($sourceYears, $sourceYears), ['class' => 'form-select form-select-sm']) ?>
                </div>
                <?= Html::submitButton('<i data-lucide="copy" class="me-1"></i> คัดลอกมาปี ' . $year, [
                    'class' => 'btn btn-sm btn-outline-primary',
                    'data-confirm' => "คัดลอกตัวชี้วัดที่ใช้งานอยู่ทั้งหมดมายังปี {$year}? รายการที่มีอยู่แล้วจะถูกข้าม",
                ]) ?>
                <?= Html::endForm() ?>
            <?php endif; ?>
            <?php if ($adoptable): ?>
                <?= Html::beginForm(['adopt'], 'post', ['class' => 'd-flex flex-wrap align-items-end gap-2 flex-grow-1']) ?>
                <?= Html::hiddenInput('planId', $plan->id) . Html::hiddenInput('year', $year) ?>
                <div class="flex-grow-1" style="min-width:16rem">
                    <label class="form-label small fw-semibold mb-1">นำตัวชี้วัดที่มีอยู่เข้าปีนี้</label>
                    <?= Html::dropDownList('indicatorId', null, $adoptable, ['class' => 'form-select form-select-sm']) ?>
                </div>
                <?= Html::submitButton('เพิ่มเข้าปี ' . $year, ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                <?= Html::endForm() ?>
            <?php endif; ?>
        </div></div>
    <?php endif; ?>
<?php endif; ?>

<div class="card border-0 shadow-sm overflow-hidden"><div class="card-body p-0">
    <div class="table-responsive d-none d-md-block"><table class="table align-middle mb-0">
        <thead class="table-light"><tr>
            <th class="ps-4">รายการ</th>
            <th>รายละเอียด</th>
            <?php if ($isYearly): ?><th class="text-nowrap">ค่าเป้าหมาย</th><th>สถานะ</th><?php endif; ?>
            <th class="text-end pe-4">จัดการ</th>
        </tr></thead>
        <tbody>
        <?php foreach ($dataProvider->models as $item): ?>
            <?php $row = $isYearly ? $this->render('_indicator_row', ['entry' => $item, 'canEdit' => $canEdit]) : null; ?>
            <?php if ($isYearly): ?><?= $row ?><?php else: ?>
                <tr>
                    <td class="ps-4"><div class="fw-semibold"><?= Html::encode($item->code ?: '-') ?></div></td>
                    <td class="erp-richtext"><?= RichText::render($item->name) ?: '-' ?></td>
                    <td class="text-end pe-4"><?php if ($canEdit): ?>
                        <?= Html::a('แก้ไข', ['update', 'type' => $type, 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= Html::a('ลบ', ['delete', 'type' => $type, 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-method' => 'post', 'data-confirm' => 'ยืนยันการลบ?']) ?>
                    <?php endif; ?></td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if (!$dataProvider->count): ?>
            <tr><td colspan="<?= $isYearly ? 5 : 3 ?>" class="text-center text-muted py-5">
                <?= $planId ? ($isYearly ? "ยังไม่มีตัวชี้วัดในปี {$year} — คัดลอกจากปีก่อนหน้าหรือเพิ่มรายการใหม่ได้" : 'ยังไม่มีข้อมูลในชุดแผนนี้') : 'กรุณาเลือกชุดแผนยุทธศาสตร์' ?>
            </td></tr>
        <?php endif; ?>
        </tbody>
    </table></div>

    <div class="d-md-none p-3 d-grid gap-3">
        <?php foreach ($dataProvider->models as $item): ?>
            <article class="border rounded-3 p-3">
                <?php if ($isYearly): ?>
                    <div class="d-flex justify-content-between gap-2">
                        <div class="fw-semibold"><?= Html::encode($item->indicator->code ?: '-') ?></div>
                        <span class="badge <?= $item->isCancelled() ? 'bg-secondary-subtle text-secondary' : 'bg-success-subtle text-success' ?>"><?= Html::encode(StrategyIndicatorYear::statusList()[$item->status] ?? $item->status) ?></span>
                    </div>
                    <div class="small text-muted mt-2"><?= Html::encode($item->displayName()) ?></div>
                    <div class="small mt-2">เป้าหมาย <?= Html::encode($item->target_value ?? '-') ?> <?= Html::encode($item->displayUnit() ?? '') ?></div>
                <?php else: ?>
                    <div class="fw-semibold"><?= Html::encode($item->code ?: '-') ?></div>
                    <div class="small text-muted mt-2"><?= Html::encode(RichText::plain($item->name, 160) ?: '-') ?></div>
                <?php endif; ?>
                <?php if ($canEdit): ?><div class="d-flex flex-wrap gap-2 mt-3">
                    <?= Html::a('แก้ไข', ['update', 'type' => $isYearly ? 'indicator' : $type, 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                    <?php if ($isYearly): ?>
                        <?= $item->isCancelled()
                            ? Html::a('กลับมาใช้', ['restore-year', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-success', 'data-method' => 'post'])
                            : Html::a('ยกเลิกในปีนี้', ['cancel-year', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-warning', 'data-method' => 'post', 'data-confirm' => 'ยกเลิกการใช้ตัวชี้วัดนี้ในปีดังกล่าว? ข้อมูลจะยังคงอยู่ในทะเบียน']) ?>
                    <?php else: ?>
                        <?= Html::a('ลบ', ['delete', 'type' => $type, 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-method' => 'post', 'data-confirm' => 'ยืนยันการลบ?']) ?>
                    <?php endif; ?>
                </div><?php endif; ?>
            </article>
        <?php endforeach; ?>
        <?php if (!$dataProvider->count): ?>
            <div class="text-center text-muted py-4"><?= $planId ? 'ยังไม่มีข้อมูล' : 'กรุณาเลือกชุดแผนยุทธศาสตร์' ?></div>
        <?php endif; ?>
    </div>
</div>
<div class="card-footer bg-body px-4 py-3"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div>
</div>
