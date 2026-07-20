<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ListView;
use yii\widgets\Pjax;

$this->title = 'พลังแห่งคำขอบคุณ';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;
$showCelebrate = (bool) (Yii::$app->request->get('celebrate') ?? false);
$this->registerCssFile('@web/css/appreciation-media.css');
?>

<?php $this->beginBlock('page-title'); ?>พลังแห่งคำขอบคุณ<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>แบ่งปันเรื่องดี ๆ และส่งกำลังใจให้เพื่อนร่วมงาน<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<div class="d-flex flex-wrap align-items-center gap-2">
    <?= Html::a('<i class="bi bi-send-heart me-1"></i> ส่งคำขอบคุณ', ['create'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
    <?php if (Yii::$app->user->can('admin') || Yii::$app->user->can('hr')): ?>
        <?= Html::a('<i class="bi bi-gear me-1"></i> จัดการระบบ', ['/appreciation/admin/index'], ['class' => 'btn btn-outline-secondary']) ?>
    <?php endif; ?>
</div>
<?php $this->endBlock(); ?>

<div class="appreciation-home">
    <div class="appreciation-layout">
        <aside class="appreciation-rail" aria-label="ข้อมูลและกิจกรรมของฉัน">
            <div class="appreciation-rail__inner">
                <section class="appreciation-profile" aria-labelledby="appreciation-profile-title">
                    <?php if ($me): ?>
                        <div class="appreciation-profile__person">
                            <?= Html::img($me->showAvatar(), [
                                'class' => 'appreciation-avatar appreciation-avatar--profile',
                                'width' => '64',
                                'height' => '64',
                                'alt' => '',
                            ]) ?>
                            <div class="min-w-0">
                                <h2 id="appreciation-profile-title" class="appreciation-profile__name"><?= Html::encode($me->fullname()) ?></h2>
                                <p class="appreciation-profile__department"><?= Html::encode($me->departmentName() ?: 'ไม่ระบุหน่วยงาน') ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="appreciation-balance">
                        <div>
                            <span class="appreciation-meta"><?= $programYear ? Html::encode($programYear->name) : 'รอบกิจกรรมปัจจุบัน' ?></span>
                            <strong><?= !empty($pointSummary['level']) ? Html::encode($pointSummary['level']->name) : 'ระดับเริ่มต้น' ?></strong>
                        </div>
                        <div class="text-end">
                            <span class="appreciation-meta">คะแนนที่ใช้ได้</span>
                            <strong class="appreciation-balance__points"><?= number_format($pointSummary['balance']) ?> คะแนน</strong>
                        </div>
                    </div>

                    <div class="appreciation-stats" aria-label="สถิติคำขอบคุณ">
                        <div><strong><?= number_format($receivedCount) ?></strong><span>คำขอบคุณที่ได้รับ</span></div>
                        <div><strong><?= number_format($totalPoints) ?></strong><span>คะแนนสะสมทั้งหมด</span></div>
                    </div>
                </section>

                <?= $this->render('_challenge_widget', ['activeChallenges' => $activeChallenges, 'myChallengeProgress' => $myChallengeProgress]) ?>

                <section class="appreciation-promo" aria-labelledby="featured-rewards-title">
                    <div class="appreciation-section-head">
                        <div>
                            <h2 id="featured-rewards-title">ของรางวัลแนะนำ</h2>
                            <p>ใช้คะแนนจากคำขอบคุณแลกรางวัล</p>
                        </div>
                        <?= Html::a('ดูทั้งหมด', ['/appreciation/reward/index'], ['class' => 'appreciation-text-link']) ?>
                    </div>

                    <?php if (empty($featuredRewards)): ?>
                        <div class="appreciation-quiet-state">
                            <i class="bi bi-gift" aria-hidden="true"></i>
                            <span>ของรางวัลใหม่จะแสดงที่นี่เมื่อเปิดให้แลก</span>
                        </div>
                    <?php else: ?>
                        <div class="appreciation-reward-list">
                            <?php foreach ($featuredRewards as $reward): ?>
                                <a class="appreciation-reward" href="<?= Html::encode(Url::to(['/appreciation/reward/index'])) ?>">
                                    <?php if ($reward->image_url): ?>
                                        <?= Html::img($reward->image_url, ['class' => 'appreciation-reward__image', 'alt' => '', 'loading' => 'lazy']) ?>
                                    <?php else: ?>
                                        <span class="appreciation-reward__image appreciation-reward__image--empty"><i class="bi bi-gift" aria-hidden="true"></i></span>
                                    <?php endif; ?>
                                    <span class="appreciation-reward__content">
                                        <strong><?= Html::encode($reward->name) ?></strong>
                                        <span><?= number_format($reward->points_cost) ?> คะแนน</span>
                                        <?php if ($pointSummary['balance'] < $reward->points_cost): ?>
                                            <small>ขาดอีก <?= number_format($reward->points_cost - $pointSummary['balance']) ?> คะแนน</small>
                                        <?php else: ?>
                                            <small class="is-ready">คะแนนเพียงพอสำหรับแลก</small>
                                        <?php endif; ?>
                                    </span>
                                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <nav class="appreciation-shortcuts" aria-label="ทางลัด">
                    <?= Html::a('<i class="bi bi-calendar-check"></i><span>กิจกรรมของฉัน</span>', ['/appreciation/activity/index']) ?>
                    <?= Html::a('<i class="bi bi-gift"></i><span>แลกรางวัล</span>', ['/appreciation/reward/index']) ?>
                </nav>
            </div>
        </aside>

        <main class="appreciation-feed-column">
            <section class="appreciation-composer" aria-label="ส่งคำขอบคุณ">
                <?php if ($me): ?>
                    <?= Html::img($me->showAvatar(), ['class' => 'appreciation-avatar', 'width' => '44', 'height' => '44', 'alt' => '']) ?>
                <?php endif; ?>
                <?= Html::a('<span>อยากขอบคุณใครในวันนี้</span><i class="bi bi-send-heart" aria-hidden="true"></i>', ['create'], [
                    'class' => 'appreciation-composer__button open-modal',
                    'data' => ['size' => 'modal-lg'],
                ]) ?>
            </section>

            <div class="appreciation-feed-head">
                <div>
                    <h2>คำขอบคุณของคุณ</h2>
                    <p>เรื่องราวที่คุณส่งหรือได้รับ</p>
                </div>
            </div>

            <?php Pjax::begin(['id' => 'appreciation-feed', 'timeout' => 5000]); ?>
            <?= ListView::widget([
                'dataProvider' => $dataProvider,
                'itemView' => '_item',
                'layout' => "<div class='appreciation-post-list'>{items}</div><div class='d-flex justify-content-center py-4'>{pager}</div>",
                'emptyText' => '<div class="appreciation-empty"><h3>ยังไม่มีคำขอบคุณที่เกี่ยวข้องกับคุณ</h3><p>เริ่มส่งข้อความดี ๆ ให้เพื่อนร่วมงาน แล้วเรื่องราวจะแสดงที่นี่</p>' . Html::a('<i class="bi bi-send-heart me-1"></i> ส่งคำขอบคุณ', ['create'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) . '</div>',
                'viewParams' => ['me' => $me],
            ]) ?>
            <?php Pjax::end(); ?>
        </main>
    </div>
</div>

<?php
$likeUrl = Url::to(['default/like']);
$js = <<<JS
$(document).on('click', '.btn-appreciation-like', function(e) {
    e.preventDefault();
    var btn = $(this);
    var id = btn.data('id');
    if (!id || btn.prop('disabled')) return;
    btn.prop('disabled', true).attr('aria-busy', 'true');
    $.post('{$likeUrl}', { id: id }).then(function(res) {
        if (res.success) {
            btn.toggleClass('is-liked', res.liked).attr('aria-pressed', res.liked ? 'true' : 'false');
            btn.find('.like-count').text(res.count);
            btn.find('svg').attr('fill', res.liked ? 'currentColor' : 'none');
        }
    }).always(function() {
        btn.prop('disabled', false).removeAttr('aria-busy');
    });
});
JS;
if ($showCelebrate) {
    $js .= "\n$('.appreciation-composer').addClass('is-highlighted'); setTimeout(function(){ $('.appreciation-composer').removeClass('is-highlighted'); }, 900);";
}
$this->registerJs($js);
?>
