<?php
use app\components\AppHelper;
use yii\helpers\Html;

$formatDate = static fn($date) => $date ? AppHelper::DateFormDb($date) : 'ไม่ระบุ';
?>
<section class="card border-0 shadow-sm" aria-labelledby="license-title">
    <div class="card-body p-4">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-4"><div><h2 id="license-title" class="h5 mb-1">ข้อมูลใบประกอบวิชาชีพ</h2><p class="text-body-secondary mb-0">ใบอนุญาต หน่วยงานผู้ออก และระยะเวลาที่มีผล</p></div><i data-lucide="badge-check" class="text-primary" aria-hidden="true"></i></div>
        <?php if (!$records): ?>
            <div class="alert alert-secondary mb-0">ยังไม่พบข้อมูลใบประกอบวิชาชีพ</div>
        <?php else: ?>
            <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>ใบอนุญาต</th><th>หน่วยงานผู้ออก</th><th>วันที่มีผล</th><th>วันหมดอายุ</th><th>เอกสารอ้างอิง</th></tr></thead><tbody>
            <?php foreach ($records as $record): $data = is_array($record->data_json) ? $record->data_json : []; ?>
                <tr><td class="fw-semibold"><?= Html::encode($data['license_name'] ?? '-') ?></td><td><?= Html::encode($data['license_company'] ?? '-') ?></td><td class="text-nowrap"><?= Html::encode($formatDate($data['date_start'] ?? null)) ?></td><td class="text-nowrap"><?= Html::encode($formatDate($data['date_end'] ?? null)) ?></td><td><?= Html::encode($data['doc_ref'] ?? '-') ?></td></tr>
            <?php endforeach ?>
            </tbody></table></div>
        <?php endif ?>
    </div>
</section>
