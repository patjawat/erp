<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\appreciation\models\AppreciationActivity;
use app\modules\appreciation\models\AppreciationParticipation;

$this->title = 'กิจกรรมของคุณ';
$this->params['breadcrumbs'][] = ['label' => 'พลังแห่งคำขอบคุณ', 'url' => ['/appreciation/default/index']];
$this->params['breadcrumbs'][] = $this->title;
$this->registerCssFile('@web/css/appreciation-media.css');

$joinedCount = 0;
foreach ($participations as $participation) {
    if ($participation->status !== AppreciationParticipation::STATUS_REJECTED) $joinedCount++;
}
$typeLabels = AppreciationActivity::typeLabels();
$availableTypes = [];
foreach ($activities as $activity) $availableTypes[$activity->activity_type] = $typeLabels[$activity->activity_type] ?? 'กิจกรรม';
?>

<?php $this->beginBlock('page-title'); ?>กิจกรรมของคุณ<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>เข้าร่วมกิจกรรมและสะสมคะแนนเพิ่มเติม<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับหน้าฟีด', ['/appreciation/default/index'], ['class' => 'btn btn-outline-secondary']) ?>
<?php $this->endBlock(); ?>

<div class="appreciation-home appreciation-activities-page">
    <div class="appreciation-layout appreciation-layout--catalog">
        <aside class="appreciation-rail" aria-label="ข้อมูลกิจกรรมของฉัน">
            <div class="appreciation-rail__inner">
                <section class="appreciation-profile appreciation-activity-summary">
                    <div class="appreciation-profile__person">
                        <?= Html::img($me->showAvatar(), ['class' => 'appreciation-avatar appreciation-avatar--profile', 'width' => '64', 'height' => '64', 'alt' => '']) ?>
                        <div class="min-w-0"><h2 class="appreciation-profile__name"><?= Html::encode($me->fullname()) ?></h2><p class="appreciation-profile__department"><?= $year ? Html::encode($year->name) : 'ยังไม่มีรอบกิจกรรม' ?></p></div>
                    </div>
                    <div class="appreciation-stats appreciation-stats--activity">
                        <div><strong><?= number_format($joinedCount) ?></strong><span>กิจกรรมที่เข้าร่วม</span></div>
                        <div><strong><?= number_format($summary['activityPoints'] ?? 0) ?></strong><span>คะแนนจากกิจกรรม</span></div>
                    </div>
                    <div class="appreciation-balance appreciation-balance--compact">
                        <span class="appreciation-meta">คะแนนที่ใช้ได้ทั้งหมด</span><strong class="appreciation-balance__points"><?= number_format($summary['balance']) ?> คะแนน</strong>
                    </div>
                </section>

                <?php if ($availableTypes): ?>
                    <section class="appreciation-filter-panel" aria-labelledby="activity-filter-title">
                        <h2 id="activity-filter-title">ประเภทกิจกรรม</h2>
                        <div class="appreciation-filter-list" role="radiogroup" aria-label="กรองประเภทกิจกรรม">
                            <button type="button" class="is-active" data-activity-filter="all" aria-checked="true" role="radio">ทั้งหมด <span><?= count($activities) ?></span></button>
                            <?php foreach ($availableTypes as $type => $label): ?>
                                <?php $typeCount = count(array_filter($activities, static fn($item) => $item->activity_type === $type)); ?>
                                <button type="button" data-activity-filter="<?= Html::encode($type) ?>" aria-checked="false" role="radio"><?= Html::encode($label) ?> <span><?= $typeCount ?></span></button>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </aside>

        <main class="appreciation-feed-column">
            <div class="appreciation-feed-head"><div><h2>กิจกรรมที่เปิดรับ</h2><p>กิจกรรมที่ลงทะเบียนแล้วจะแสดงก่อน</p></div></div>

            <?php if (!$activities): ?>
                <div class="appreciation-empty"><h3>ยังไม่มีกิจกรรมที่เปิดรับ</h3><p>กิจกรรมใหม่จะแสดงเมื่อผู้ดูแลประกาศ</p></div>
            <?php else: ?>
                <div class="appreciation-activity-list">
                    <?php
                    $sortedActivities = $activities;
                    usort($sortedActivities, static function ($a, $b) use ($participations) {
                        return (int) isset($participations[$b->id]) <=> (int) isset($participations[$a->id]);
                    });
                    ?>
                    <?php foreach ($sortedActivities as $activity): ?>
                        <?php $participation = $participations[$activity->id] ?? null; ?>
                        <article class="appreciation-activity-item" data-activity-type="<?= Html::encode($activity->activity_type) ?>">
                            <?php if ($activity->image_url): ?>
                                <?= Html::img($activity->image_url, ['class' => 'appreciation-activity-item__image', 'alt' => '', 'loading' => 'lazy']) ?>
                            <?php else: ?>
                                <div class="appreciation-activity-item__image appreciation-activity-item__image--empty"><i class="bi bi-calendar-check" aria-hidden="true"></i></div>
                            <?php endif; ?>
                            <div class="appreciation-activity-item__content">
                                <header>
                                    <div>
                                        <span class="appreciation-activity-item__type"><?= Html::encode($typeLabels[$activity->activity_type] ?? 'กิจกรรม') ?></span>
                                        <h3><?= Html::encode($activity->title) ?></h3>
                                    </div>
                                    <strong class="appreciation-points">+<?= number_format($activity->points) ?> คะแนน</strong>
                                </header>
                                <p class="appreciation-activity-item__description"><?= Html::encode($activity->description ?: 'ดูรายละเอียดและเข้าร่วมกิจกรรม') ?></p>
                                <div class="appreciation-activity-item__meta">
                                    <span>สิ้นสุด <?= Yii::$app->formatter->asDate($activity->end_at) ?></span>
                                    <?php if ($activity->capacity): ?><span>รับ <?= number_format($activity->capacity) ?> คน</span><?php endif; ?>
                                </div>

                                <div class="appreciation-activity-item__footer">
                                    <?php if (!$participation): ?>
                                        <?= Html::a($activity->participation_mode === AppreciationActivity::MODE_EXTERNAL ? 'เริ่มทำกิจกรรม' : 'ลงทะเบียนกิจกรรม', ['join', 'id' => $activity->id], ['class' => 'btn btn-primary', 'data-method' => 'post']) ?>
                                    <?php else: ?>
                                        <span class="appreciation-status appreciation-status--<?= Html::encode($participation->status) ?>"><?= Html::encode(AppreciationParticipation::statusLabels()[$participation->status] ?? $participation->status) ?></span>
                                        <?php if ($participation->status === AppreciationParticipation::STATUS_REGISTERED && $activity->requires_review): ?>
                                            <button type="button" class="btn btn-outline-primary" data-evidence-toggle="evidence-<?= (int) $activity->id ?>" aria-expanded="false">ส่งผลการเข้าร่วม</button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                                <?php if ($participation && $participation->status === AppreciationParticipation::STATUS_REGISTERED && $activity->requires_review): ?>
                                    <form id="evidence-<?= (int) $activity->id ?>" class="appreciation-evidence-form" method="post" action="<?= Html::encode(Url::to(['submit', 'id' => $activity->id])) ?>" hidden>
                                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                                        <label for="evidence-url-<?= (int) $activity->id ?>">ลิงก์หลักฐานหรือผลการทำกิจกรรม</label>
                                        <div><input id="evidence-url-<?= (int) $activity->id ?>" class="form-control" name="evidence_url" placeholder="วางลิงก์หลักฐาน"><button class="btn btn-primary" type="submit">ส่งหลักฐาน</button></div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
$(document).on('click', '[data-activity-filter]', function() {
    var button = $(this);
    var filter = button.data('activity-filter');
    $('[data-activity-filter]').removeClass('is-active').attr('aria-checked', 'false');
    button.addClass('is-active').attr('aria-checked', 'true');
    $('.appreciation-activity-item').each(function() {
        $(this).toggle(filter === 'all' || $(this).data('activity-type') === filter);
    });
});
$(document).on('click', '[data-evidence-toggle]', function() {
    var button = $(this);
    var form = $('#' + button.data('evidence-toggle'));
    var open = form.is(':hidden');
    form.prop('hidden', !open);
    button.attr('aria-expanded', open ? 'true' : 'false');
    if (open) form.find('input:not([type="hidden"])').trigger('focus');
});
JS);
?>
