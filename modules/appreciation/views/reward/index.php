<?php

use yii\helpers\Html;
use app\modules\appreciation\models\AppreciationRedemption;

$this->title = 'แลกรางวัล';
$this->params['breadcrumbs'][] = ['label' => 'พลังแห่งคำขอบคุณ', 'url' => ['/appreciation/default/index']];
$this->params['breadcrumbs'][] = $this->title;
$this->registerCssFile('@web/css/appreciation-media.css');
?>

<?php $this->beginBlock('page-title'); ?>แลกรางวัล<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>เลือกของรางวัลด้วยคะแนนคำขอบคุณ<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับหน้าฟีด', ['/appreciation/default/index'], ['class' => 'btn btn-outline-secondary']) ?>
<?php $this->endBlock(); ?>

<div class="appreciation-home appreciation-catalog">
    <div class="appreciation-layout appreciation-layout--catalog">
        <aside class="appreciation-rail" aria-label="คะแนนของฉัน">
            <div class="appreciation-rail__inner">
                <section class="appreciation-profile appreciation-wallet">
                    <div class="appreciation-profile__person">
                        <?= Html::img($me->showAvatar(), ['class' => 'appreciation-avatar appreciation-avatar--profile', 'width' => '64', 'height' => '64', 'alt' => '']) ?>
                        <div class="min-w-0">
                            <h2 class="appreciation-profile__name"><?= Html::encode($me->fullname()) ?></h2>
                            <p class="appreciation-profile__department"><?= $year ? Html::encode($year->name) : 'ยังไม่มีรอบกิจกรรมที่เปิดใช้งาน' ?></p>
                        </div>
                    </div>

                    <div class="appreciation-wallet__balance">
                        <span>คะแนนที่ใช้ได้</span>
                        <strong><?= number_format($summary['balance']) ?></strong>
                        <small>คะแนน</small>
                    </div>

                    <dl class="appreciation-detail-list">
                        <div><dt>ระดับปัจจุบัน</dt><dd><?= $summary['level'] ? Html::encode($summary['level']->name) : 'ระดับเริ่มต้น' ?></dd></div>
                        <div><dt>คะแนนที่ได้รับ</dt><dd><?= number_format($summary['earned']) ?></dd></div>
                        <div><dt>คะแนนที่ใช้แล้ว</dt><dd><?= number_format($summary['used']) ?></dd></div>
                    </dl>
                </section>

                <div class="appreciation-rail-note">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    <p>คะแนนจะถูกหักเมื่อส่งคำขอแลกรางวัล และผู้ดูแลจะแจ้งสถานที่รับของภายหลัง</p>
                </div>
            </div>
        </aside>

        <main class="appreciation-feed-column">
            <div class="appreciation-view-tabs" role="tablist" aria-label="มุมมองรางวัล">
                <button type="button" class="is-active" role="tab" aria-selected="true" aria-controls="reward-catalog" data-appreciation-tab="reward-catalog">ของรางวัล</button>
                <button type="button" role="tab" aria-selected="false" aria-controls="reward-history" data-appreciation-tab="reward-history">ประวัติการแลก <span><?= count($history) ?></span></button>
            </div>

            <section id="reward-catalog" class="appreciation-tab-panel is-active" role="tabpanel">
                <div class="appreciation-feed-head">
                    <div><h2>ของรางวัลที่แลกได้</h2><p>เรียงจากคะแนนน้อยไปมาก</p></div>
                </div>

                <?php if (!$year): ?>
                    <div class="appreciation-empty"><h3>ยังไม่เปิดรอบสะสมคะแนน</h3><p>ของรางวัลจะแสดงเมื่อผู้ดูแลเปิดรอบกิจกรรม</p></div>
                <?php elseif (!$rewards): ?>
                    <div class="appreciation-empty"><h3>ยังไม่มีของรางวัลให้แลก</h3><p>กลับมาตรวจสอบอีกครั้งเมื่อมีการเพิ่มของรางวัล</p></div>
                <?php else: ?>
                    <div class="appreciation-catalog-list">
                        <?php foreach ($rewards as $reward): ?>
                            <?php
                            $inStock = (int) $reward->stock_qty > 0;
                            $canRedeem = $inStock && $summary['balance'] >= $reward->points_cost;
                            $missingPoints = max(0, (int) $reward->points_cost - (int) $summary['balance']);
                            ?>
                            <article class="appreciation-catalog-item<?= !$inStock ? ' is-unavailable' : '' ?>">
                                <?php if ($reward->image_url): ?>
                                    <?= Html::img($reward->image_url, ['class' => 'appreciation-catalog-item__image', 'alt' => Html::encode($reward->name), 'loading' => 'lazy']) ?>
                                <?php else: ?>
                                    <div class="appreciation-catalog-item__image appreciation-catalog-item__image--empty"><i class="bi bi-gift" aria-hidden="true"></i></div>
                                <?php endif; ?>
                                <div class="appreciation-catalog-item__content">
                                    <header>
                                        <div><h3><?= Html::encode($reward->name) ?></h3><p><?= Html::encode($reward->description ?: 'ของรางวัลสำหรับบุคลากร') ?></p></div>
                                        <strong class="appreciation-cost"><?= number_format($reward->points_cost) ?> คะแนน</strong>
                                    </header>
                                    <div class="appreciation-catalog-item__meta">
                                        <span>คงเหลือ <?= number_format($reward->stock_qty) ?> ชิ้น</span>
                                        <?php if (!$inStock): ?><span class="is-danger">ของรางวัลหมด</span>
                                        <?php elseif ($canRedeem): ?><span class="is-success">คะแนนเพียงพอสำหรับแลก</span>
                                        <?php else: ?><span>ขาดอีก <?= number_format($missingPoints) ?> คะแนน</span><?php endif; ?>
                                    </div>
                                    <div class="appreciation-catalog-item__action">
                                        <?= Html::a($inStock ? 'แลกรางวัล' : 'ของรางวัลหมด', ['redeem', 'id' => $reward->id], [
                                            'class' => 'btn ' . ($canRedeem ? 'btn-primary' : 'btn-outline-secondary disabled'),
                                            'data-method' => 'post',
                                            'aria-disabled' => $canRedeem ? 'false' : 'true',
                                        ]) ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section id="reward-history" class="appreciation-tab-panel" role="tabpanel" hidden>
                <div class="appreciation-feed-head"><div><h2>ประวัติการแลกรางวัล</h2><p>รายการคำขอในรอบกิจกรรมปัจจุบัน</p></div></div>
                <?php if (!$history): ?>
                    <div class="appreciation-empty"><h3>ยังไม่มีรายการแลกรางวัล</h3><p>รายการจะแสดงที่นี่หลังจากส่งคำขอแลก</p></div>
                <?php else: ?>
                    <ul class="appreciation-history" role="list">
                        <?php foreach ($history as $item): ?>
                            <li>
                                <div><strong><?= Html::encode($item->reward ? $item->reward->name : 'ของรางวัล') ?></strong><span><?= Yii::$app->formatter->asDatetime($item->requested_at) ?> · <?= number_format($item->points_used) ?> คะแนน</span></div>
                                <span class="appreciation-status"><?= Html::encode(AppreciationRedemption::statusLabels()[$item->status] ?? $item->status) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </main>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
$(document).on('click', '[data-appreciation-tab]', function() {
    var button = $(this);
    var target = button.data('appreciation-tab');
    button.closest('.appreciation-view-tabs').find('[data-appreciation-tab]').removeClass('is-active').attr('aria-selected', 'false');
    button.addClass('is-active').attr('aria-selected', 'true');
    $('.appreciation-tab-panel').removeClass('is-active').attr('hidden', true);
    $('#' + target).addClass('is-active').removeAttr('hidden');
});
JS);
?>
