<?php
use yii\helpers\Html;

$this->title = 'แฟ้มประเมินทดลองงาน';
echo $this->render('_styles');
$rounds = $model->rounds;
$current = $model->currentRound();
$statusIcon = static fn($evaluation) => $evaluation->status === 'submitted' ? 'check' : ($evaluation->status === 'open' ? 'pencil' : 'lock');
$roundTone = static fn($round) => $round->status === 'completed' ? 'success' : ($round->status === 'scheduled' ? 'secondary' : 'primary');
?>
<div class="probation-shell probation-detail-view">
    <header class="probation-head">
        <div>
            <h1><?= Html::encode($model->employee->fullname) ?></h1>
            <p class="text-body-secondary"><?= Html::encode($model->employee->employeePosition->title ?? $model->employee->position_name) ?> · เริ่มงาน <?= Yii::$app->formatter->asDate($model->start_date, 'php:d/m/Y') ?></p>
        </div>
        <?= Html::a('กลับรายการ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </header>

    <div class="probation-detail-grid">
        <main>
            <section class="probation-card probation-detail-timeline" aria-labelledby="probation-timeline-title">
                <div class="probation-detail-timeline__head">
                    <div><h2 id="probation-timeline-title" class="h5 mb-1">เส้นทางการประเมินทดลองงาน</h2><p class="text-body-secondary mb-0">ติดตามผลเดือนที่ 1, 2 และ 3 ตามลำดับ</p></div>
                    <span class="badge bg-primary-subtle text-primary-emphasis"><?= Html::encode($model->statusLabel) ?></span>
                </div>
                <div class="probation-timeline" aria-label="รอบการประเมิน">
                    <?php foreach ($rounds as $round):
                        $class = $round->status === 'completed' ? 'is-done' : ($current && $current->id === $round->id ? 'is-current' : '');
                    ?>
                        <div class="probation-month <?= $class ?>">
                            <span class="probation-month__node"><?= $round->month_no ?></span>
                            <strong>เดือนที่ <?= $round->month_no ?></strong>
                            <small class="d-block text-body-secondary">ครบกำหนด <?= Yii::$app->formatter->asDate($round->due_date, 'php:d/m/Y') ?></small>
                        </div>
                    <?php endforeach ?>
                </div>
            </section>

            <div class="probation-rounds">
                <?php foreach ($rounds as $round): $tone = $roundTone($round); ?>
                    <section class="probation-round <?= $current && $current->id === $round->id ? 'is-current' : '' ?>" aria-labelledby="round-<?= $round->month_no ?>-title">
                        <header class="probation-round__header">
                            <div class="d-flex align-items-center gap-3">
                                <span class="probation-round__number"><?= $round->month_no ?></span>
                                <div><h2 id="round-<?= $round->month_no ?>-title" class="h5 mb-1">การประเมินเดือนที่ <?= $round->month_no ?></h2><span class="text-body-secondary small">ครบกำหนด <?= Yii::$app->formatter->asDate($round->due_date, 'php:d/m/Y') ?></span></div>
                            </div>
                            <span class="badge bg-<?= $tone ?>-subtle text-<?= $tone ?>-emphasis"><?= Html::encode($round->statusLabel) ?></span>
                        </header>
                        <div class="probation-workflow" aria-label="ขั้นตอนการประเมินเดือนที่ <?= $round->month_no ?>">
                            <?php foreach ($round->evaluations as $index => $evaluation):
                                $class = $evaluation->status === 'submitted' ? 'is-done' : ($evaluation->status === 'open' ? 'is-open' : '');
                            ?>
                                <div class="probation-step <?= $class ?>">
                                    <div class="probation-step__top"><span class="probation-step__sequence">ขั้นตอน <?= $index + 1 ?></span><span class="probation-step__icon"><i data-lucide="<?= $statusIcon($evaluation) ?>"></i></span></div>
                                    <strong><?= Html::encode($evaluation->roleLabel) ?></strong>
                                    <small class="d-block text-body-secondary mt-1"><?= Html::encode($evaluation->evaluator->fullname) ?></small>
                                    <?php if ($evaluation->status === 'submitted'): ?>
                                        <span class="d-block probation-numeric fw-semibold fs-5 mt-3"><?= number_format($evaluation->percent_score, 2) ?>%</span>
                                    <?php elseif ($evaluation->status === 'open' && $currentEmployee && (int)$currentEmployee->id === (int)$evaluation->evaluator_employee_id): ?>
                                        <?= Html::a('ทำแบบประเมิน', ['evaluate', 'id' => $evaluation->id], ['class' => 'btn btn-primary btn-sm mt-3']) ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis mt-3"><?= $evaluation->status === 'open' ? 'รอดำเนินการ' : 'ยังไม่เปิด' ?></span>
                                    <?php endif ?>
                                </div>
                            <?php endforeach ?>
                            <?php $directorStep = count($round->evaluations) + 1; ?>
                            <div class="probation-step <?= $round->acknowledgement ? 'is-done' : ($round->status === 'waiting_acknowledgement' ? 'is-open' : '') ?>">
                                <div class="probation-step__top"><span class="probation-step__sequence">ขั้นตอน <?= $directorStep ?></span><span class="probation-step__icon"><i data-lucide="<?= $round->acknowledgement ? 'check' : ($round->status === 'waiting_acknowledgement' ? 'eye' : 'lock') ?>"></i></span></div>
                                <strong>ผอ.รับทราบ</strong>
                                <small class="d-block text-body-secondary mt-1"><?= Html::encode($model->director->fullname) ?></small>
                                <?php if ($round->acknowledgement): ?>
                                    <span class="badge bg-success-subtle text-success-emphasis mt-3">รับทราบแล้ว</span>
                                <?php elseif ($currentEmployee && (int)$currentEmployee->id === (int)$model->director_employee_id && $round->status === 'waiting_acknowledgement'): ?>
                                    <?= Html::beginForm(['acknowledge', 'id' => $model->id], 'post', ['class' => 'mt-3']) ?>
                                    <?= Html::hiddenInput('round_id', $round->id) ?>
                                    <?= Html::submitButton('รับทราบเดือนที่ '.$round->month_no, ['class' => 'btn btn-primary btn-sm']) ?>
                                    <?= Html::endForm() ?>
                                <?php endif ?>
                            </div>
                        </div>
                    </section>
                <?php endforeach ?>
            </div>
        </main>

        <aside>
            <div class="probation-card p-3 probation-sticky">
                <h2 class="h6">สรุปแฟ้ม</h2>
                <dl class="mb-0"><dt class="text-body-secondary fw-normal mt-3">Template</dt><dd><?= Html::encode($model->template->name) ?> rev. <?= $model->template->revision_no ?></dd><dt class="text-body-secondary fw-normal">ผู้สรุปผล</dt><dd><?= Html::encode($model->finalRecommender->fullname) ?></dd><dt class="text-body-secondary fw-normal">สถานะ</dt><dd><?= Html::encode($model->statusLabel) ?></dd></dl>
                <?php if ($model->decision): ?><hr><div class="probation-summary"><div><small class="text-body-secondary">คะแนนเฉลี่ย</small><strong class="d-block fs-4 probation-numeric"><?= number_format($model->decision->average_percent, 2) ?>%</strong></div></div><p class="mt-3 mb-0"><?= nl2br(Html::encode($model->decision->summary_comment)) ?></p><?php endif ?>
                <hr>
                <h2 class="h6 mb-2">พิมพ์แบบประเมิน</h2>
                <p class="small text-body-secondary mb-2">เลือกเดือนที่ประเมินเสร็จแล้ว</p>
                <div class="btn-group w-100" role="group" aria-label="เลือกเดือนสำหรับพิมพ์แบบประเมิน">
                    <?php foreach ($rounds as $round): ?>
                        <?php $canPrint = $round->status === 'completed' && ((int) $round->month_no !== 3 || $model->decision); ?>
                        <?php if ($canPrint): ?>
                            <?= Html::a('เดือน '.$round->month_no, ['pdf', 'id' => $model->id, 'month' => $round->month_no], ['class' => 'btn btn-outline-primary', 'target' => '_blank', 'rel' => 'noopener', 'aria-label' => 'พิมพ์แบบประเมินเดือนที่ '.$round->month_no]) ?>
                        <?php else: ?>
                            <?= Html::button('เดือน '.$round->month_no, ['class' => 'btn btn-outline-secondary', 'disabled' => true, 'title' => (int) $round->month_no === 3 && $round->status === 'completed' ? 'ยังไม่ได้สรุปผลการจ้าง' : 'ยังประเมินไม่เสร็จ']) ?>
                        <?php endif ?>
                    <?php endforeach ?>
                </div>
                <?php if ($currentEmployee && (int)$currentEmployee->id === (int)$model->final_recommender_employee_id && $model->status === 'waiting_decision'): ?><div class="d-grid mt-3"><?= Html::a('สรุปผลการจ้าง', ['decision', 'id' => $model->id], ['class' => 'btn btn-primary']) ?></div><?php endif ?>
            </div>
        </aside>
    </div>
</div>
