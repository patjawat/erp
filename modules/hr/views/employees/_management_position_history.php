<?php
use app\components\AppHelper;
use yii\helpers\Html;

$formatDate = static fn($date) => $date ? AppHelper::DateFormDb($date) : 'ไม่ระบุ';
?>
<section class="card border-0 shadow-sm" aria-labelledby="position-history-title">
    <div class="card-body p-4">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-4"><div><h2 id="position-history-title" class="h5 mb-1">ประวัติดำรงตำแหน่ง</h2><p class="text-body-secondary mb-0">ลำดับตำแหน่งและรายการเปลี่ยนแปลงในการทำงาน</p></div><i data-lucide="history" class="text-primary" aria-hidden="true"></i></div>
        <?php if (!$records): ?>
            <div class="alert alert-secondary mb-0">ยังไม่พบประวัติการดำรงตำแหน่ง</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0"><thead><tr><th>ช่วงเวลา</th><th>ตำแหน่ง</th><th>รายการเปลี่ยนแปลง</th><th>เอกสารอ้างอิง</th></tr></thead><tbody>
                <?php foreach ($records as $record): $data = is_array($record->data_json) ? $record->data_json : []; ?>
                    <tr><td class="text-nowrap"><?= Html::encode($formatDate($data['date_start'] ?? null)) ?><span class="d-block small text-body-secondary">ถึง <?= Html::encode($formatDate($data['date_end'] ?? null)) ?></span></td><td class="fw-semibold"><?= Html::encode($record->positionName() ?: '-') ?></td><td><?= Html::encode($data['statuslist'] ?? '-') ?></td><td><?= Html::encode($data['doc_ref'] ?? '-') ?></td></tr>
                <?php endforeach ?>
                </tbody></table>
            </div>
        <?php endif ?>
    </div>
</section>
