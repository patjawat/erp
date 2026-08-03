<?php
use yii\helpers\Html;

$statusColor = static fn(string $status): string => match ($status) {
    'completed_hire' => 'success',
    'completed_no_hire' => 'danger',
    'waiting_acknowledgement' => 'primary',
    'waiting_decision' => 'warning',
    'cancelled' => 'secondary',
    default => 'warning',
};
$actorEmployee = $actorEmployee ?? $employee;
$isManagedProfile = $isManagedProfile ?? false;
$nextAction = static function ($case) use ($actorEmployee): ?array {
    foreach ($case->rounds as $round) foreach ($round->evaluations as $evaluation) {
        if ((int)$evaluation->evaluator_employee_id === (int)$actorEmployee->id && $evaluation->status === 'open') {
            return ['label' => 'ทำแบบประเมินเดือนที่ '.$round->month_no, 'url' => ['/hr/probation-appraisal/evaluate', 'id' => $evaluation->id]];
        }
    }
    if ((int)$case->final_recommender_employee_id === (int)$actorEmployee->id && $case->status === 'waiting_decision') {
        return ['label' => 'สรุปผลการจ้าง', 'url' => ['/hr/probation-appraisal/decision', 'id' => $case->id]];
    }
    foreach ($case->rounds as $round) {
        if ((int)$case->director_employee_id === (int)$actorEmployee->id && $round->status === 'waiting_acknowledgement') {
            return ['label' => 'รับทราบผลเดือนที่ '.$round->month_no, 'url' => ['/hr/probation-appraisal/view', 'id' => $case->id]];
        }
    }
    return null;
};
?>
<?= $this->render('_styles') ?>
<section class="probation-shell" aria-labelledby="my-probation-title">
    <header class="probation-head">
        <div>
            <h2 id="my-probation-title"><?= $isManagedProfile ? 'การประเมินทดลองงานของบุคลากร' : 'การประเมินทดลองงานของฉัน' ?></h2>
            <p class="text-body-secondary"><?= $isManagedProfile ? 'ติดตามความคืบหน้าและดำเนินการเฉพาะขั้นตอนที่ได้รับมอบหมาย' : 'ดูงานที่ต้องดำเนินการและความคืบหน้าของการประเมินเดือนที่ 1, 2 และ 3' ?></p>
        </div>
        <?php if ($actionCount > 0): ?>
            <span class="badge bg-warning-subtle text-warning-emphasis"><?= number_format($actionCount) ?> งานรอดำเนินการ</span>
        <?php endif ?>
    </header>

    <?php if (!$cases): ?>
        <div class="probation-card probation-empty">
            <h3 class="h5">ยังไม่มีรายการประเมินทดลองงาน</h3>
            <p class="text-body-secondary mb-0">เมื่อ HR มอบหมายงาน รายการและขั้นตอนถัดไปจะแสดงที่นี่</p>
        </div>
    <?php else: ?>
        <div class="d-grid gap-3">
            <?php foreach ($cases as $case): $current = $case->currentRound(); $action = $nextAction($case); $color = $statusColor($case->status); ?>
                <article class="probation-card p-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-2">
                        <div>
                            <h3 class="h6 mb-1"><?= Html::encode($case->employee->fullname) ?></h3>
                            <p class="text-body-secondary small mb-0">
                                <?= Html::encode($case->template->name) ?>
                                · <?= $current ? 'เดือนที่ '.$current->month_no : 'ครบทั้ง 3 เดือน' ?>
                            </p>
                        </div>
                        <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?>-emphasis"><?= Html::encode($case->statusLabel) ?></span>
                    </div>
                    <div class="probation-timeline px-0 pb-0 mt-3" aria-label="ความคืบหน้าการประเมิน">
                        <?php foreach ($case->rounds as $round): $class = $round->status === 'completed' ? 'is-done' : ($current && $current->id === $round->id ? 'is-current' : ''); ?>
                            <div class="probation-month <?= $class ?>">
                                <span class="probation-month__node"><?= $round->month_no ?></span>
                                <small>เดือนที่ <?= $round->month_no ?></small>
                            </div>
                        <?php endforeach ?>
                    </div>
                    <div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-3">
                        <?= Html::a('ดูรายละเอียด', ['/hr/probation-appraisal/view', 'id' => $case->id], ['class' => 'btn btn-outline-secondary']) ?>
                        <?php if ($action): ?><?= Html::a(Html::encode($action['label']), $action['url'], ['class' => 'btn btn-primary']) ?><?php endif ?>
                    </div>
                </article>
            <?php endforeach ?>
        </div>
    <?php endif ?>
</section>
