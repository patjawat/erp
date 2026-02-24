<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;
use app\components\ThaiDateHelper;
use app\modules\appreciation\models\AppreciationChallenge;

$me = UserHelper::GetEmployee();
$this->title = 'Challenge รับรางวัล';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/me']];
$this->params['breadcrumbs'][] = ['label' => 'พลังแห่งคำขอบคุณ', 'url' => ['/appreciation/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-warning text-dark py-3">
        <h5 class="mb-0 fw-bold">กิจกรรมเป้าหมาย แข่งกันทำครบรับของรางวัล</h5>
        <p class="mb-0 small opacity-75">ส่งคำขอบคุณหรือรับคำชมให้ครบตามเป้า ภายในช่วงเวลาที่กำหนด เพื่อรับรางวัล</p>
    </div>
</div>

<?php if (!empty($active)): ?>
    <h6 class="text-uppercase fw-bold text-muted mb-3">กำลังจัดกิจกรรม</h6>
    <div class="row g-3 mb-4">
        <?php foreach ($active as $ch): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <span class="badge bg-success rounded-pill mb-2">กำลังดำเนินการ</span>
                        <h6 class="card-title fw-bold"><?= Html::encode($ch->name) ?></h6>
                        <p class="card-text small text-muted"><?= nl2br(Html::encode(mb_substr($ch->description ?? '', 0, 120))) ?><?= mb_strlen($ch->description ?? '') > 120 ? '...' : '' ?></p>
                        <p class="small mb-1">
                            <span class="text-muted">เป้า:</span>
                            <?= Html::encode(AppreciationChallenge::goalTypeLabels()[$ch->goal_type] ?? $ch->goal_type) ?>
                            <strong><?= (int) $ch->goal_value ?> ครั้ง</strong>
                        </p>
                        <p class="small text-muted mb-2">
                            <?= ThaiDateHelper::formatThaiDate($ch->start_at) ?> - <?= ThaiDateHelper::formatThaiDate($ch->end_at) ?>
                        </p>
                        <?php if ($ch->reward_name): ?>
                            <p class="small mb-2"><span class="badge bg-warning text-dark">รางวัล: <?= Html::encode($ch->reward_name) ?></span></p>
                        <?php endif; ?>
                        <?= Html::a('ดูรายละเอียด', ['view', 'id' => $ch->id], ['class' => 'btn btn-primary btn-sm rounded-3']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($upcoming)): ?>
    <h6 class="text-uppercase fw-bold text-muted mb-3">เร็วๆ นี้</h6>
    <div class="row g-3 mb-4">
        <?php foreach ($upcoming as $ch): ?>
            <div class="col-12 col-md-6">
                <div class="card border border-light shadow-sm">
                    <div class="card-body py-3">
                        <span class="badge bg-secondary rounded-pill mb-2">เร็วๆ นี้</span>
                        <h6 class="card-title fw-bold mb-1"><?= Html::encode($ch->name) ?></h6>
                        <p class="small text-muted mb-0">เริ่ม <?= ThaiDateHelper::formatThaiDate($ch->start_at) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($ended)): ?>
    <h6 class="text-uppercase fw-bold text-muted mb-3">กิจกรรมที่สิ้นสุดแล้ว</h6>
    <ul class="list-group list-group-flush">
        <?php foreach ($ended as $ch): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><?= Html::encode($ch->name) ?></span>
                <?= Html::a('ดูผล', ['view', 'id' => $ch->id], ['class' => 'btn btn-outline-secondary btn-sm rounded-3']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (empty($active) && empty($upcoming) && empty($ended)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <p class="mb-2">ยังไม่มีกิจกรรม Challenge</p>
            <p class="small">รอการเปิดกิจกรรมจากผู้ดูแลระบบ</p>
        </div>
    </div>
<?php endif; ?>

<?= Html::a('กลับฟีดคำขอบคุณ', ['/appreciation/default/index'], ['class' => 'btn btn-outline-secondary rounded-3 mt-3']) ?>
