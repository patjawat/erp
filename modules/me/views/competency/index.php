<?php

use yii\helpers\Html;
use app\modules\hr\models\AppraisalRound;
use app\modules\hr\models\CompetencyEvaluation;

/** @var yii\web\View $this */
/** @var AppraisalRound|null $round */
/** @var AppraisalRound[] $rounds */
/** @var array $groups */
/** @var array $summary */

$this->title = 'ประเมินสมรรถนะ';
echo $this->render('_styles');
$this->beginBlock('page-title'); echo Html::encode($this->title); $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/me/menu', ['active' => 'competency']); $this->endBlock();

$readyToSubmit = $summary['total'] > 0
    && $summary['completed'] > 0
    && $summary['in_progress'] === 0
    && $summary['not_started'] === 0;
$allSubmitted = $summary['total'] > 0 && $summary['submitted'] === $summary['total'];
$done = $summary['submitted'] + $summary['completed'];
$percent = $summary['total'] > 0 ? (int) round($done / $summary['total'] * 100) : 0;
?>
<div class="ev-shell">
    <header class="ev-head">
        <div>
            <h1>ประเมินสมรรถนะ</h1>
            <p>
                <?= $round ? Html::encode($round->getTitle()) : 'ยังไม่มีรอบที่ต้องประเมิน' ?>
                <?php if ($round && $round->due_date): ?>
                    · กำหนดส่ง <?= Html::encode(Yii::$app->formatter->asDate($round->due_date, 'long')) ?>
                <?php endif ?>
            </p>
        </div>
        <?php if (count($rounds) > 1): ?>
            <div class="d-flex gap-2">
                <?php foreach ($rounds as $item): ?>
                    <?= Html::a('รอบที่ ' . (int) $item->round_no,
                        ['/me/competency/index', 'rd' => $item->round_no],
                        ['class' => 'btn btn-sm ' . ($round && $item->id === $round->id ? 'btn-primary' : 'btn-outline-primary')]) ?>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </header>

    <?php if (!$round || $summary['total'] === 0): ?>
        <div class="ev-empty">
            <i class="bi bi-clipboard-check"></i>
            <h2>ยังไม่มีรายชื่อที่ต้องประเมิน</h2>
            <p>เมื่อ HR เปิดรอบและมอบหมายให้คุณเป็นผู้ประเมิน รายชื่อจะแสดงที่นี่</p>
        </div>
    <?php else: ?>
        <section class="ev-progress">
            <div class="ev-progress__bar" role="img"
                 aria-label="ความคืบหน้า <?= $percent ?> เปอร์เซ็นต์">
                <span style="width:<?= $percent ?>%"></span>
            </div>
            <div class="ev-progress__meta">
                <strong><?= $done ?>/<?= $summary['total'] ?></strong> คน
                <?php if ($summary['submitted'] > 0): ?>
                    · ส่งผลแล้ว <?= $summary['submitted'] ?>
                <?php endif ?>
                <?php if ($summary['in_progress'] > 0): ?>
                    · ทำค้าง <?= $summary['in_progress'] ?>
                <?php endif ?>
                <?php if ($summary['not_started'] > 0): ?>
                    · ยังไม่เริ่ม <?= $summary['not_started'] ?>
                <?php endif ?>
            </div>
            <?php if ($allSubmitted): ?>
                <span class="ev-chip ev-chip--done"><i class="bi bi-check-circle-fill"></i> ส่งผลครบแล้ว</span>
            <?php elseif ($round->status === AppraisalRound::STATUS_OPEN): ?>
                <?= Html::beginForm(['/me/competency/submit', 'rd' => $round->round_no], 'post') ?>
                <?= Html::submitButton('<i class="bi bi-send-fill"></i> ส่งผลประเมิน', [
                    'class' => 'btn btn-primary' . ($readyToSubmit ? '' : ' disabled'),
                    'disabled' => !$readyToSubmit,
                    'title' => $readyToSubmit ? 'ส่งผลทั้งชุด' : 'ประเมินให้ครบทุกคนก่อนจึงจะส่งได้',
                    'data-confirm' => 'ส่งผลแล้วจะแก้ไขคะแนนไม่ได้ ยืนยันหรือไม่',
                ]) ?>
                <?= Html::endForm() ?>
            <?php endif ?>
        </section>

        <?php foreach ($groups as $group): ?>
            <section class="ev-group">
                <h2><i class="bi bi-diagram-3"></i> <?= Html::encode($group['name']) ?>
                    <span><?= count($group['rows']) ?> คน</span></h2>
                <ul class="ev-list" role="list">
                    <?php foreach ($group['rows'] as $row): ?>
                        <?php
                        /** @var CompetencyEvaluation|null $evaluation */
                        $evaluation = $row['evaluation'];
                        $progress = $row['progress'];
                        $employee = $row['employee'];
                        $submitted = $evaluation && $evaluation->status === CompetencyEvaluation::STATUS_SUBMITTED;
                        $complete = $progress['expected'] > 0 && $progress['rated'] >= $progress['expected'];

                        if ($submitted) {
                            [$state, $label, $action] = ['done', 'ส่งผลแล้ว', 'ดูผล'];
                        } elseif ($complete) {
                            [$state, $label, $action] = ['ready', 'ประเมินครบแล้ว', 'แก้ไข'];
                        } elseif ($progress['rated'] > 0) {
                            [$state, $label, $action] = ['doing', 'ทำค้าง ' . $progress['percent'] . '%', 'ทำต่อ'];
                        } else {
                            [$state, $label, $action] = ['todo', 'ยังไม่เริ่ม', 'เริ่มประเมิน'];
                        }
                        ?>
                        <li class="ev-row ev-row--<?= $state ?>">
                            <div class="ev-row__who">
                                <strong><?= Html::encode($employee->fullname()) ?></strong>
                                <small><?= Html::encode(strip_tags((string) $employee->positionName())) ?></small>
                            </div>
                            <div class="ev-row__state">
                                <span class="ev-chip ev-chip--<?= $state ?>"><?= Html::encode($label) ?></span>
                                <?php if ($progress['expected'] > 0): ?>
                                    <small><?= $progress['rated'] ?>/<?= $progress['expected'] ?> ข้อ</small>
                                <?php else: ?>
                                    <small class="text-danger">HR ยังไม่กำหนดระดับที่คาดหวัง</small>
                                <?php endif ?>
                            </div>
                            <div class="ev-row__score">
                                <?php if ($evaluation && $evaluation->score_percent !== null): ?>
                                    <strong><?= Yii::$app->formatter->asDecimal($evaluation->score_percent, 2) ?></strong>
                                    <small>คะแนน</small>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif ?>
                            </div>
                            <div class="ev-row__go">
                                <?= Html::a($action, ['/me/competency/evaluate', 'id' => $row['assignment']->id], [
                                    'class' => 'btn btn-sm ' . ($state === 'todo' ? 'btn-primary' : 'btn-outline-primary'),
                                ]) ?>
                            </div>
                        </li>
                    <?php endforeach ?>
                </ul>
            </section>
        <?php endforeach ?>

        <p class="ev-hint">
            <i class="bi bi-info-circle"></i>
            ถ้ามีผู้ใต้บังคับบัญชาที่ตกหล่นจากรายการนี้ แจ้ง HR เพื่อเพิ่มรายชื่อและกำหนดระดับที่คาดหวังให้ก่อน
        </p>
    <?php endif ?>
</div>
