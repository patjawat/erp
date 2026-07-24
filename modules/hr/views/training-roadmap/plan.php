<?php
use yii\helpers\Html;
use app\modules\hr\models\EmployeeTrainingPlan;
use app\modules\hr\models\TrainingRoadmapActivity;

$this->title = $model->roadmap->title . ' · ' . $model->employee->fullname;
echo $this->render('_styles');
$this->beginBlock('page-title'); echo Html::encode($this->title); $this->endBlock();
$resultsByActivity = [];
foreach ($model->results as $result) $resultsByActivity[$result->activity_id] = $result;
?>
<div class="trm-shell" id="employee-roadmap-plan">
    <div class="trm-page-head">
        <div><span class="trm-status trm-status--<?= Html::encode($model->status) ?>"><?= Html::encode(EmployeeTrainingPlan::statusOptions()[$model->status] ?? $model->status) ?></span><h1 class="mt-2"><?= Html::encode($model->roadmap->title) ?></h1><p><?= Html::encode($model->employee->fullname) ?> · <?= Html::encode($model->start_date) ?> ถึง <?= Html::encode($model->target_end_date ?: 'ยังไม่กำหนด') ?></p></div>
        <div class="text-end"><div class="fw-semibold"><?= number_format((float) $model->progress_percent, 0) ?>%</div><div class="trm-meta">ความก้าวหน้า</div></div>
    </div>
    <div class="trm-builder">
        <main class="trm-builder__main trm-card">
        <?php foreach ($model->roadmap->phases as $index => $phase): ?>
            <section class="trm-phase">
                <div class="trm-phase__rail"><div class="trm-phase__num"><?= $index + 1 ?></div><div class="trm-phase__line"></div></div>
                <div class="trm-phase__head"><div><div class="trm-period"><?= Html::encode($phase->period_label ?: 'ระยะที่ ' . ($index + 1)) ?></div><h2 class="trm-phase__title"><?= Html::encode($phase->title) ?></h2></div></div>
                <?php foreach ($phase->activities as $activity): $result = $resultsByActivity[$activity->id] ?? null; ?>
                <div class="trm-activity">
                    <div><div class="trm-activity__title"><?= Html::encode($activity->title) ?></div><div class="trm-tags"><span class="trm-tag"><?= Html::encode(TrainingRoadmapActivity::requirementOptions()[$activity->requirement_type] ?? $activity->requirement_type) ?></span><?php if ($result): ?><span class="trm-status trm-status--<?= Html::encode($result->status) ?>"><?= Html::encode(['pending' => 'ยังไม่เริ่ม', 'in_progress' => 'กำลังดำเนินการ', 'passed' => 'ผ่าน', 'completed' => 'ทำครบแล้ว', 'failed' => 'ยังไม่ผ่าน'][$result->status] ?? $result->status) ?></span><?php endif ?></div></div>
                    <?php if ($result): ?><?= Html::a('บันทึกผล', ['result', 'id' => $result->id, 'title' => 'บันทึกผลการพัฒนา'], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data-size' => 'modal-lg']) ?><?php endif ?>
                </div>
                <?php endforeach ?>
            </section>
        <?php endforeach ?>
        </main>
        <aside class="trm-builder__side">
            <div class="trm-card"><div class="trm-section-head"><h2>สรุปแผน</h2></div><div class="trm-summary"><div class="d-flex justify-content-between trm-meta mb-2"><span>ความก้าวหน้า</span><span><?= number_format((float) $model->progress_percent, 0) ?>%</span></div><div class="trm-progress"><span style="width:<?= min(100, (float) $model->progress_percent) ?>%"></span></div><dl class="mt-3"><dt>เริ่มแผน</dt><dd><?= Html::encode($model->start_date) ?></dd><dt>ครบกำหนด</dt><dd><?= Html::encode($model->target_end_date ?: '—') ?></dd><dt>สถานะ</dt><dd><?= Html::encode(EmployeeTrainingPlan::statusOptions()[$model->status] ?? $model->status) ?></dd></dl></div></div>
        </aside>
    </div>
</div>
