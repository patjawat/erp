<?php

use yii\helpers\Html;
use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\modules\hr\models\IdpPlan;
use app\modules\hr\models\IdpGoal;
use app\modules\hr\models\IdpActivity;

/**
 * สมุดพกการพัฒนารายบุคคล (development passport) — เปิดเป็น modal
 *
 * @var yii\web\View $this
 * @var array $data ผลจาก DevelopmentReport::personPassport()
 * @var array|null $idp แผนพัฒนารายบุคคล (DevelopmentReport::personIdp) หรือ null
 * @var int $year ปีงบประมาณ
 */
$emp = $data['emp'];
$stats = $data['stats'];
$life = $data['lifetime'];
$activities = $data['activities'];

$roleBadge = static function (string $role): string {
    return $role === 'requester'
        ? '<span class="badge rounded-pill text-bg-primary">ผู้ขอ</span>'
        : '<span class="badge rounded-pill text-bg-info">คณะเดินทาง</span>';
};
?>

<div class="dev-passport">
    <!-- หัว: ชื่อ + หน่วยงาน -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <div class="rounded-circle bg-primary-subtle text-primary-emphasis d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
            <i class="bi bi-person fs-4"></i>
        </div>
        <div>
            <div class="fw-semibold"><?= Html::encode($emp['name']) ?></div>
            <?php if (!empty($emp['dept'])): ?>
                <div class="small text-body-secondary"><i class="bi bi-diagram-3 me-1"></i><?= Html::encode($emp['dept']) ?></div>
            <?php endif; ?>
        </div>
        <span class="badge text-bg-light ms-auto">ปีงบ <?= $year ?></span>
    </div>

    <!-- สถิติปีนี้ -->
    <div class="row g-2 mb-2">
        <div class="col-3"><div class="border rounded p-2 text-center"><div class="fs-4 fw-bold text-primary"><?= number_format($stats['times']) ?></div><div class="small text-body-secondary">ครั้ง</div></div></div>
        <div class="col-3"><div class="border rounded p-2 text-center"><div class="fs-4 fw-bold text-info"><?= number_format($stats['days']) ?></div><div class="small text-body-secondary">วันพัฒนา</div></div></div>
        <div class="col-3"><div class="border rounded p-2 text-center"><div class="fs-4 fw-bold text-success"><?= number_format($stats['type_count']) ?></div><div class="small text-body-secondary">ประเภท</div></div></div>
        <div class="col-3"><div class="border rounded p-2 text-center"><div class="fs-4 fw-bold text-warning"><?= number_format($stats['as_speaker']) ?></div><div class="small text-body-secondary">เป็นวิทยากร</div></div></div>
    </div>
    <p class="small text-body-secondary mb-2">
        <i class="bi bi-clock-history me-1"></i>สะสมทุกปี: <b><?= number_format($life['times']) ?></b> ครั้ง · <b><?= number_format($life['days']) ?></b> วัน · <?= number_format($life['years']) ?> ปีงบ
        &nbsp;|&nbsp; ปีนี้เป็นผู้ขอ <?= $stats['as_requester'] ?> · คณะเดินทาง <?= $stats['as_member'] ?>
    </p>

    <?php if (!empty($stats['types'])): ?>
        <div class="mb-3 d-flex flex-wrap gap-1">
            <?php foreach ($stats['types'] as $t): ?>
                <span class="badge text-bg-light border"><?= Html::encode($t['label']) ?> · <?= $t['n'] ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- รายการกิจกรรม -->
    <?php if (empty($activities)): ?>
        <div class="alert alert-warning small mb-0">
            <i class="bi bi-exclamation-triangle me-1"></i>ยังไม่ได้รับการพัฒนาในปีงบ <?= $year ?> — ควรพิจารณาบรรจุในแผนพัฒนา/IDP
        </div>
    <?php else: ?>
        <div class="table-responsive" style="max-height:55vh;overflow:auto;">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light sticky-top">
                    <tr><th>วันที่</th><th>หัวข้อ</th><th>ประเภท</th><th class="text-center">บทบาท</th><th class="text-center">วัน</th><th class="text-center">สถานะ</th></tr>
                </thead>
                <tbody>
                <?php foreach ($activities as $a): $st = AppHelper::viewStatus($a['status']); ?>
                    <tr>
                        <td class="small text-nowrap"><?= ThaiDateHelper::formatThaiDateRange($a['date_start'], $a['date_end'], 'long', 'short') ?></td>
                        <td class="small"><?= Html::encode($a['topic']) ?></td>
                        <td class="small text-body-secondary"><?= Html::encode($a['type_label'] ?: '-') ?></td>
                        <td class="text-center"><?= $roleBadge($a['role']) ?></td>
                        <td class="text-center"><?= (int) $a['days'] ?></td>
                        <td class="text-center"><span class="badge rounded-pill text-bg-<?= $st['color'] ?? 'secondary' ?>"><?= Html::encode($st['title'] ?? $a['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- แผนพัฒนารายบุคคล (IDP) — เฟส 5 เชื่อม competency/IDP -->
    <hr class="my-3">
    <h6 class="fw-semibold mb-2"><i class="bi bi-signpost-2 me-1 text-primary"></i>แผนพัฒนารายบุคคล (IDP)</h6>
    <?php if (!$idp): ?>
        <div class="alert alert-light border small mb-0">
            <i class="bi bi-dash-circle me-1"></i>ยังไม่มีแผนพัฒนารายบุคคล (IDP) ในปีงบ <?= $year ?> — ตาม HA การพัฒนาควรตอบช่องว่างสมรรถนะ (competency gap) ผ่าน IDP
        </div>
    <?php else: ?>
        <div class="small text-body-secondary mb-2">
            รอบ: <?= Html::encode($idp['cycle']) ?>
            · สถานะ: <span class="badge text-bg-secondary"><?= Html::encode(IdpPlan::statusOptions()[$idp['status']] ?? $idp['status']) ?></span>
            · ความคืบหน้า <?= (float) $idp['progress'] ?>%
        </div>
        <?php foreach ($idp['goals'] as $g): ?>
            <div class="border rounded p-2 mb-2">
                <div class="fw-medium small">
                    <i class="bi bi-bullseye me-1 text-danger"></i><?= Html::encode($g['title']) ?>
                    <span class="badge text-bg-light border ms-1"><?= Html::encode(IdpGoal::sourceOptions()[$g['source_type']] ?? $g['source_type']) ?></span>
                </div>
                <?php if (!empty($g['gap_reason'])): ?>
                    <div class="small text-body-secondary">ช่องว่างสมรรถนะ: <?= Html::encode($g['gap_reason']) ?></div>
                <?php endif; ?>
                <?php if (!empty($g['activities'])): ?>
                    <ul class="small mb-0 mt-1">
                        <?php foreach ($g['activities'] as $a): ?>
                            <li>
                                <?= Html::encode($a['title']) ?>
                                <span class="text-body-secondary">(<?= Html::encode(IdpActivity::methodOptions()[$a['method_type']] ?? $a['method_type']) ?> · <?= (float) $a['progress_percent'] ?>%)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="small text-body-secondary fst-italic">ยังไม่มีกิจกรรมในเป้าหมายนี้</div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
