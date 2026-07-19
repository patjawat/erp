<?php

use yii\helpers\Html;
use app\components\ThaiDateHelper;
use app\modules\appreciation\models\AppreciationChallenge;

$this->title = 'กิจกรรมท้าทาย';
$this->params['breadcrumbs'][] = ['label' => 'พลังแห่งคำขอบคุณ', 'url' => ['/appreciation/default/index']];
$this->params['breadcrumbs'][] = $this->title;
$this->registerCssFile('@web/css/appreciation-media.css');
?>

<?php $this->beginBlock('page-title'); ?>กิจกรรมท้าทาย<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ทำเป้าหมายคำขอบคุณให้ครบและรับรางวัล<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับหน้าฟีด', ['/appreciation/default/index'], ['class' => 'btn btn-outline-secondary']) ?>
<?php $this->endBlock(); ?>

<div class="appreciation-home appreciation-challenges-page">
    <div class="appreciation-layout appreciation-layout--catalog">
        <aside class="appreciation-rail" aria-label="ข้อมูลกิจกรรมท้าทาย">
            <div class="appreciation-rail__inner">
                <section class="appreciation-profile appreciation-challenge-summary">
                    <div class="appreciation-profile__person">
                        <?= Html::img($me->showAvatar(), ['class' => 'appreciation-avatar appreciation-avatar--profile', 'width' => '64', 'height' => '64', 'alt' => '']) ?>
                        <div class="min-w-0"><h2 class="appreciation-profile__name"><?= Html::encode($me->fullname()) ?></h2><p class="appreciation-profile__department">ภารกิจคำขอบคุณของคุณ</p></div>
                    </div>
                    <div class="appreciation-stats appreciation-stats--activity">
                        <div><strong><?= count($active) ?></strong><span>กำลังดำเนินการ</span></div>
                        <div><strong><?= count(array_filter($myProgress, static fn($progress) => !empty($progress->completed_at))) ?></strong><span>ทำสำเร็จแล้ว</span></div>
                    </div>
                </section>

                <div class="appreciation-rail-note">
                    <i class="bi bi-trophy" aria-hidden="true"></i>
                    <p>ระบบจะนับความคืบหน้าอัตโนมัติเมื่อคุณส่งหรือได้รับคำขอบคุณตามเงื่อนไขของภารกิจ</p>
                </div>
            </div>
        </aside>

        <main class="appreciation-feed-column">
            <?php if ($active): ?>
                <section aria-labelledby="active-challenges-title">
                    <div class="appreciation-feed-head"><div><h2 id="active-challenges-title">กำลังดำเนินการ</h2><p>ภารกิจที่นับความคืบหน้าอยู่ในขณะนี้</p></div></div>
                    <div class="appreciation-challenge-list">
                        <?php foreach ($active as $challenge): ?>
                            <?php
                            $progress = $myProgress[$challenge->id] ?? null;
                            $current = $progress ? (int) $progress->current_value : 0;
                            $goal = (int) $challenge->goal_value;
                            $percent = $goal > 0 ? min(100, (int) round($current / $goal * 100)) : 0;
                            $completed = $progress && $progress->completed_at;
                            ?>
                            <article class="appreciation-challenge-item">
                                <header>
                                    <span class="appreciation-challenge__icon"><i class="bi bi-trophy" aria-hidden="true"></i></span>
                                    <div><h3><?= Html::encode($challenge->name) ?></h3><p><?= Html::encode(AppreciationChallenge::goalTypeLabels()[$challenge->goal_type] ?? $challenge->goal_type) ?> <?= number_format($goal) ?> ครั้ง</p></div>
                                    <span class="appreciation-status<?= $completed ? ' is-complete' : '' ?>"><?= $completed ? 'สำเร็จแล้ว' : 'กำลังดำเนินการ' ?></span>
                                </header>
                                <?php if ($challenge->description): ?><p class="appreciation-challenge-item__description"><?= Html::encode($challenge->description) ?></p><?php endif; ?>
                                <div class="appreciation-progress" role="progressbar" aria-label="ความคืบหน้าของ <?= Html::encode($challenge->name) ?>" aria-valuenow="<?= $current ?>" aria-valuemin="0" aria-valuemax="<?= $goal ?>"><span style="width: <?= $percent ?>%"></span></div>
                                <div class="appreciation-challenge__meta"><strong><?= number_format($current) ?> จาก <?= number_format($goal) ?> ครั้ง</strong><span>ถึง <?= ThaiDateHelper::formatThaiDate($challenge->end_at) ?></span></div>
                                <footer>
                                    <?php if ($challenge->reward_name): ?><span class="appreciation-challenge-item__reward"><i class="bi bi-gift" aria-hidden="true"></i> <?= Html::encode($challenge->reward_name) ?></span><?php endif; ?>
                                    <?= Html::a('ดูรายละเอียด', ['view', 'id' => $challenge->id], ['class' => 'btn btn-outline-primary']) ?>
                                </footer>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($upcoming): ?>
                <section class="appreciation-secondary-section" aria-labelledby="upcoming-title">
                    <div class="appreciation-feed-head"><div><h2 id="upcoming-title">เร็ว ๆ นี้</h2><p>ภารกิจที่กำลังจะเปิด</p></div></div>
                    <ul class="appreciation-compact-list" role="list">
                        <?php foreach ($upcoming as $challenge): ?>
                            <li><div><strong><?= Html::encode($challenge->name) ?></strong><span>เริ่ม <?= ThaiDateHelper::formatThaiDate($challenge->start_at) ?></span></div><?= Html::a('ดูรายละเอียด', ['view', 'id' => $challenge->id]) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php if ($ended): ?>
                <section class="appreciation-secondary-section" aria-labelledby="ended-title">
                    <div class="appreciation-feed-head"><div><h2 id="ended-title">สิ้นสุดแล้ว</h2><p>ภารกิจล่าสุดที่ผ่านมา</p></div></div>
                    <ul class="appreciation-compact-list" role="list">
                        <?php foreach ($ended as $challenge): ?>
                            <li><div><strong><?= Html::encode($challenge->name) ?></strong><span>สิ้นสุด <?= ThaiDateHelper::formatThaiDate($challenge->end_at) ?></span></div><?= Html::a('ดูผล', ['view', 'id' => $challenge->id]) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php if (!$active && !$upcoming && !$ended): ?>
                <div class="appreciation-empty"><h3>ยังไม่มีกิจกรรมท้าทาย</h3><p>ภารกิจใหม่จะแสดงเมื่อผู้ดูแลเปิดกิจกรรม</p></div>
            <?php endif; ?>
        </main>
    </div>
</div>
