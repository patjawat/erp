<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ListView;
use yii\widgets\Pjax;
use app\components\UserHelper;
use app\modules\appreciation\models\Appreciation;

$me = UserHelper::GetEmployee();
$this->title = 'พลังแห่งคำขอบคุณ';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;

$showCelebrate = (bool) (Yii::$app->request->get('celebrate') ?? false);
?>

<div class="row g-4">
    <!-- คอลัมน์ฟีด (Bento: กล่องหลัก) -->
    <div class="col-12 col-lg-8">
        <!-- Compose bar (Glass-style) -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden bg-white bg-opacity-75">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center gap-3">
                    <?php if ($me): ?>
                        <?= Html::img($me->showAvatar(), [
                            'class' => 'rounded-circle border border-2 border-white shadow-sm flex-shrink-0',
                            'width' => '48',
                            'height' => '48',
                            'alt' => '',
                        ]) ?>
                        <?= Html::a(
                            '<span class="text-muted">ส่งคำขอบคุณให้เพื่อน...</span>',
                            ['create'],
                            ['class' => 'flex-grow-1 text-start text-decoration-none rounded-3 border p-3 d-block bg-light bg-opacity-50 open-modal', 'data' => ['size' => 'modal-lg']]
                        ) ?>
                    <?php else: ?>
                        <?= Html::a('ส่งคำขอบคุณ', ['create'], ['class' => 'btn btn-primary rounded-3 px-4']) ?>
                    <?php endif; ?>
                </div>
                <div class="mt-2 d-flex flex-wrap gap-2">
                    <?= Html::a('<i class="bi bi-heart text-danger me-1"></i> ส่งคำขอบคุณ', ['create'], ['class' => 'btn btn-outline-danger btn-sm rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                    <?= Html::a('Challenge', ['challenge/index'], ['class' => 'btn btn-outline-secondary btn-sm rounded-pill']) ?>
                </div>
            </div>
        </div>

        <h6 class="mb-3 fw-bold text-muted small text-uppercase">คำขอบคุณล่าสุด (ที่เกี่ยวข้องกับคุณ)</h6>

        <?php Pjax::begin(['id' => 'appreciation-feed', 'timeout' => 5000]); ?>
        <?= ListView::widget([
            'dataProvider' => $dataProvider,
            'itemView' => '_item',
            'layout' => "{items}\n<div class='d-flex justify-content-center py-4'>{pager}</div>",
            'emptyText' => '<div class="card border-0 shadow-sm rounded-3"><div class="card-body text-center py-5"><p class="text-muted mb-2 fs-5">ยังไม่มีคำขอบคุณที่เกี่ยวข้องกับคุณ</p><p class="small text-muted mb-4">ลองเริ่มส่งคำขอบคุณให้เพื่อนร่วมงาน แล้วรายการของคุณจะปรากฏที่นี่</p>' . Html::a('<i class="bi bi-heart me-1"></i> ส่งคำขอบคุณ', ['create'], ['class' => 'btn btn-primary rounded-3 open-modal', 'data' => ['size' => 'modal-lg']]) . '</div></div>',
            'viewParams' => ['me' => $me],
        ]) ?>
        <?php Pjax::end(); ?>
    </div>

    <!-- Sidebar Bento: โปรไฟล์ + อันดับ + Challenge -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden bg-white bg-opacity-75">
            <div class="card-body p-0">
                <?php if ($me): ?>
                    <div class="p-4 bg-primary bg-opacity-10 text-center border-bottom">
                        <?= Html::img($me->showAvatar(), [
                            'class' => 'rounded-circle border border-3 border-white shadow-sm mb-2',
                            'width' => '72',
                            'height' => '72',
                            'alt' => '',
                        ]) ?>
                        <h6 class="mb-0 fw-bold"><?= Html::encode($me->fullname()) ?></h6>
                        <p class="mb-0 small text-muted"><?= Html::encode($me->departmentName() ?: '-') ?></p>
                    </div>
                <?php endif; ?>
                <div class="p-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="p-3 rounded-3 border text-center">
                                <p class="text-muted small mb-0">ได้รับคำขอบคุณ</p>
                                <p class="mb-0 fw-bold fs-4 text-primary"><?= (int) $receivedCount ?></p>
                                <p class="mb-0 small">ครั้ง</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3 border text-center">
                                <p class="text-muted small mb-0">คะแนนสะสม</p>
                                <p class="mb-0 fw-bold fs-4 text-warning"><?= number_format($totalPoints) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?= $this->render('_leaderboard', ['leaderboard' => $leaderboard, 'me' => $me]) ?>
        <?= $this->render('_challenge_widget', ['activeChallenges' => $activeChallenges, 'myChallengeProgress' => $myChallengeProgress]) ?>

        <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับหน้า Me', ['/me'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
    </div>
</div>

<?php
$likeUrl = Url::to(['default/like']);
$js = <<<JS
$(document).on('click', '.btn-appreciation-like', function(e) {
    e.preventDefault();
    var btn = $(this);
    var id = btn.data('id');
    if (!id) return;
    btn.prop('disabled', true);
    var offset = btn.offset();
    $.post('{$likeUrl}', { id: id }).then(function(res) {
        if (res.success) {
            btn.toggleClass('active text-danger', res.liked);
            btn.find('.like-count').text(res.count);
            if (res.liked) {
                for (var i = 0; i < 8; i++) {
                    (function(j) {
                        setTimeout(function() {
                            var h = $('<span class="appreciation-heart-burst">❤</span>');
                            h.css({
                                position: 'fixed',
                                left: (offset.left + 20) + 'px',
                                top: (offset.top + 10) + 'px',
                                zIndex: 9999,
                                fontSize: '18px',
                                pointerEvents: 'none',
                                opacity: 1
                            });
                            $('body').append(h);
                            h.animate({
                                top: (offset.top - 40 - j * 8) + 'px',
                                left: (offset.left + (j % 2 === 0 ? 1 : -1) * (20 + j * 6)) + 'px',
                                opacity: 0
                            }, 600, function() { h.remove(); });
                        }, j * 40);
                    })(i);
                }
            }
        }
    }).always(function() { btn.prop('disabled', false); });
});
JS;
if ($showCelebrate) {
    $js .= "\n(function(){ var c=0; var t=setInterval(function(){ if(c++>=12) { clearInterval(t); return; } var h=$('<span class=\"appreciation-heart-burst\">❤</span>'); h.css({ position:'fixed', left:(Math.random()*60+20)+'%', top:(Math.random()*40+25)+'%', zIndex:9999, fontSize:'24px', opacity:1, pointerEvents:'none' }); $('body').append(h); h.animate({ top:'0%', opacity:0 }, 1200, function(){ h.remove(); }); }, 120); })();";
}
$this->registerJs($js);
?>
