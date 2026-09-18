<?php

use app\components\AppHelper;
use app\modules\housing\models\Resident;
use yii\helpers\Html;

/** @var \app\modules\housing\models\Occupancy $occupancy */
/** @var \app\modules\housing\models\Unit $unit */
/** @var \app\modules\housing\models\Room|null $room */
/** @var Resident[] $residents */
/** @var array{total:int,over15:int} $counts */

$locationName = trim(($unit->name ?? '') . ($room ? ' / ' . $room->name : ''));
$backUrl = ['view', 'id' => $unit->id];
if ($room) {
    $backUrl['room_id'] = $room->id;
}
$relationshipLabels = Resident::relationshipOptions();
$ageOf = static function (?string $birth): ?int {
    if (!$birth) {
        return null;
    }
    try {
        return (new DateTime($birth))->diff(new DateTime('now'))->y;
    } catch (\Throwable $e) {
        return null;
    }
};

$this->title = 'ทะเบียนผู้พักอาศัย';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'unit']) ?><?php $this->endBlock();
?>
<style>
.resident-page{--r-border:var(--bs-border-color);--r-bg:var(--bs-tertiary-bg)}
.resident-page .soft-panel{background:var(--bs-body-bg);border:1px solid var(--r-border);border-radius:.8rem}
.resident-page .count-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem;max-width:420px}
.resident-page .count-item{padding:.85rem 1rem;background:var(--r-bg);border:1px solid var(--r-border);border-radius:.7rem}
.resident-page .count-value{font-weight:700;font-size:1.3rem}
</style>
<div class="container-fluid py-3 resident-page">
    <?php foreach (['success', 'error'] as $flash): ?>
        <?php if (Yii::$app->session->hasFlash($flash)): ?>
            <div class="alert alert-<?= $flash === 'error' ? 'danger' : 'success' ?>"><?= Html::encode(Yii::$app->session->getFlash($flash)) ?></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="soft-panel p-3 mb-3 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <?= Html::a('<i class="bi bi-arrow-left"></i> กลับหน้าห้องพัก', $backUrl, ['class' => 'btn btn-sm btn-outline-secondary mb-2']) ?>
            <h1 class="h5 mb-1"><?= Html::encode($occupancy->employee?->fullname() ?: ('รหัสบุคลากร ' . $occupancy->emp_id)) ?></h1>
            <div class="small text-body-secondary"><?= Html::encode($locationName ?: 'บ้านพัก') ?> · <?= Html::encode($occupancy->employee?->positionName() ?: 'ไม่ระบุตำแหน่ง') ?></div>
        </div>
        <?= Html::a('<i class="bi bi-person-plus"></i> เพิ่มสมาชิก', ['create-resident', 'occupancy_id' => $occupancy->id], ['class' => 'btn btn-primary open-modal', 'data-size' => 'modal-lg']) ?>
    </div>

    <div class="count-grid mb-3">
        <div class="count-item"><div class="small text-body-secondary">ผู้พักอาศัยรวม</div><div class="count-value"><?= (int) $counts['total'] ?> คน</div></div>
        <div class="count-item"><div class="small text-body-secondary">คิดค่าใช้จ่ายรายหัว (เกิน 15 ปี)</div><div class="count-value"><?= (int) $counts['over15'] ?> คน</div></div>
    </div>

    <div class="soft-panel overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ชื่อ-สกุล</th>
                        <th>ความสัมพันธ์</th>
                        <th class="text-center">อายุ</th>
                        <th>เลขบัตร ปชช.</th>
                        <th>เบอร์โทร</th>
                        <th class="text-center">คิดรายหัว</th>
                        <th>สถานะ</th>
                        <th class="text-end">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($residents === []): ?>
                        <tr><td colspan="8" class="text-center py-5 text-body-secondary">ยังไม่มีข้อมูลผู้พักอาศัย — กด “เพิ่มสมาชิก” เพื่อบันทึก</td></tr>
                    <?php else: ?>
                        <?php foreach ($residents as $resident):
                            $age = $ageOf($resident->birth_date);
                            $isEmployee = $resident->isEmployee();
                        ?>
                            <tr class="<?= $resident->status === 'ended' ? 'opacity-50' : '' ?>">
                                <td>
                                    <span class="fw-semibold"><?= Html::encode($resident->fullName()) ?></span>
                                    <?php if ($isEmployee): ?><span class="badge text-bg-primary ms-1">หัวหน้าครัวเรือน</span><?php endif; ?>
                                </td>
                                <td><?= Html::encode($isEmployee ? 'เจ้าหน้าที่ผู้ครอบครอง' : ($relationshipLabels[$resident->relationship] ?? ($resident->relationship ?: '—'))) ?></td>
                                <td class="text-center">
                                    <?php if ($age !== null): ?>
                                        <?= $age ?> ปี<?php if ($age > 15): ?> <span class="badge text-bg-secondary">เกิน 15</span><?php endif; ?>
                                    <?php else: ?><span class="text-body-secondary">—</span><?php endif; ?>
                                </td>
                                <td><?= Html::encode($resident->citizen_id ?: '—') ?></td>
                                <td><?= Html::encode($resident->phone ?: '—') ?></td>
                                <td class="text-center"><?= $resident->count_for_charge ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-dash-circle text-body-secondary"></i>' ?></td>
                                <td><?= $resident->status === 'active' ? '<span class="badge text-bg-success-subtle text-success-emphasis">พักอาศัยอยู่</span>' : '<span class="badge text-bg-secondary">ย้ายออกแล้ว</span>' ?></td>
                                <td class="text-end text-nowrap">
                                    <?= Html::a('<i class="bi bi-pencil"></i>', ['update-resident', 'id' => $resident->id], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data-size' => 'modal-lg', 'aria-label' => 'แก้ไข']) ?>
                                    <?php if (!$isEmployee): ?>
                                        <?= Html::a('<i class="bi bi-trash"></i>', ['delete-resident', 'id' => $resident->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-method' => 'post', 'data-confirm' => 'ลบ ' . $resident->fullName() . ' หรือไม่?', 'aria-label' => 'ลบ']) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
