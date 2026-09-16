<?php
use yii\helpers\Html;
$this->params['current_page'] = $current_page ?? 'attendance';
$this->params['mobileTitle'] = 'ลงเวลา';
$this->params['mobileSubtitle'] = 'บันทึกเวลาเข้า-ออกตามตารางเวร';
?>
<?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', ['icon' => 'clock', 'title' => 'ลงเวลา', 'subtitle' => 'บันทึกเวลาเข้า-ออกตามตารางเวร']) ?>
<div class="app-scroll">
    <div class="card border-0 shadow-sm mb-3"><div class="card-body">
        <?= $this->render('@app/modules/attendance/views/default/_clock_form') ?>
    </div></div>
    <h2 class="fs-6 fw-semibold">ประวัติย้อนหลัง 14 วัน</h2>
    <div class="list-group mb-3">
        <?php foreach ($history ?? [] as $record): ?>
        <?= Html::a(Html::encode($record->checkin_at . ' · ' . $record->getCheckTypeLabel() . ' · ' . $record->getStatusLabel()), ['/attendance/checkin/view', 'id' => $record->id], ['class' => 'list-group-item list-group-item-action']) ?>
        <?php endforeach; ?>
        <?php if (empty($history)): ?><p class="text-body-secondary">ยังไม่มีรายการลงเวลา</p><?php endif; ?>
    </div>
</div>
