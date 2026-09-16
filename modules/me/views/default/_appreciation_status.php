<?php
use yii\helpers\Html;
// Retained for placement on another dashboard surface.
?>
    <div class="col-12 col-xl-3">
        <div class="card border-0 shadow-sm h-100 appreciation-status-card">
            <div class="card-body p-3 p-md-4 d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div class="min-w-0 flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="appreciation-heart d-inline-flex align-items-center justify-content-center rounded-circle"><i class="bi bi-heart-fill" aria-hidden="true"></i></span>
                            <div><h2 class="h6 fw-bold mb-0">สถานะคำขอบคุณ</h2><div class="small text-muted"><?= !empty($appreciationStatus['year']) ? Html::encode($appreciationStatus['year']->name) : 'ยังไม่เปิดรอบคะแนน' ?></div></div>
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="small text-muted">ระดับ</div>
                        <div class="fw-bold lh-sm" style="font-size:1.35rem;color:<?= Html::encode($appreciationStatus['levelColor'] ?? '#2563eb') ?>"><?= Html::encode($appreciationStatus['levelName'] ?? 'เริ่มต้น') ?></div>
                    </div>
                </div>
                <div class="appreciation-growth mb-2">
                    <span class="appreciation-growth__plant" aria-hidden="true">🌱</span>
                    <div class="small fw-semibold text-dark mb-1"><?= !empty($appreciationStatus['nextLevelName']) ? 'กำลังเติบโตสู่ '.Html::encode($appreciationStatus['nextLevelName']) : 'เติบโตถึงระดับปัจจุบันแล้ว' ?></div>
                    <div class="d-flex justify-content-between gap-2 small mb-1"><span class="text-muted">เส้นทางของคุณ</span><span class="fw-bold text-success"><?= (int)($appreciationStatus['progress'] ?? 0) ?>%</span></div>
                    <div class="progress rounded-pill" style="height:8px"><div class="progress-bar rounded-pill" role="progressbar" style="width:<?= (int)($appreciationStatus['progress'] ?? 0) ?>%" aria-valuenow="<?= (int)($appreciationStatus['progress'] ?? 0) ?>" aria-valuemin="0" aria-valuemax="100"></div></div>
                    <?php if(!empty($appreciationStatus['nextLevelName'])): ?><div class="small text-muted mt-1">อีก <?= number_format($appreciationStatus['pointsToNext']) ?> คะแนน ต้นอ่อนจะเติบโตขึ้น</div><?php endif; ?>
                </div>
                <dl class="row g-0 mb-3 small py-2">
                    <div class="col-4 appreciation-metric px-2"><dt class="text-muted fw-normal"><i class="bi bi-star-fill text-warning me-1" aria-hidden="true"></i>สะสม</dt><dd class="fw-bold fs-6 mb-0 mt-1"><?= number_format($appreciationStatus['earned'] ?? 0) ?></dd></div>
                    <div class="col-4 appreciation-metric px-3"><dt class="text-muted fw-normal"><i class="bi bi-wallet2 text-primary me-1" aria-hidden="true"></i>คงเหลือ</dt><dd class="fw-bold fs-6 text-primary mb-0 mt-1"><?= number_format($appreciationStatus['balance'] ?? 0) ?></dd></div>
                    <div class="col-4 appreciation-metric px-3"><dt class="text-muted fw-normal"><i class="bi bi-gift-fill text-success me-1" aria-hidden="true"></i>รางวัล</dt><dd class="fw-bold fs-6 mb-0 mt-1"><?= number_format($appreciationStatus['rewardsCount'] ?? 0) ?></dd></div>
                </dl>
                <div class="row g-0 text-center border-top pt-3 pb-1">
                    <div class="col-4"><?= Html::a('<span class="appreciation-action__icon"><i class="bi bi-heart-fill"></i></span><span class="d-block small">ส่งคำขอบคุณ</span>', ['/appreciation/default/create'], ['class'=>'appreciation-action appreciation-action--thanks d-inline-flex flex-column align-items-center open-modal','data'=>['size'=>'modal-lg']]) ?></div>
                    <div class="col-4"><?= Html::a('<span class="appreciation-action__icon"><i class="bi bi-gift"></i></span><span class="d-block small">แลกของ</span>', ['/appreciation/reward/index'], ['class'=>'appreciation-action appreciation-action--reward d-inline-flex flex-column align-items-center']) ?></div>
                    <div class="col-4"><?= Html::a('<span class="appreciation-action__icon"><i class="bi bi-calendar-check"></i></span><span class="d-block small">ร่วมกิจกรรม</span>', ['/appreciation/activity/index'], ['class'=>'appreciation-action appreciation-action--activity d-inline-flex flex-column align-items-center']) ?></div>
                </div>
                <div class="text-center mt-3">
                    <?= Html::a('แบบสำรวจความผูกพันของฉัน', ['/hr/engagement/mine'], ['class'=>'btn btn-outline-primary btn-sm mb-2', 'data-pjax'=>'0']) ?>
                    <?= Html::a('<i class="bi bi-heart-fill me-1 text-danger"></i> ดูฟีดคำขอบคุณทั้งหมด <i class="bi bi-arrow-right ms-1"></i>', ['/appreciation/default/index'], ['class'=>'appreciation-feed-link']) ?>
                </div>
            </div>
        </div>
    </div>

