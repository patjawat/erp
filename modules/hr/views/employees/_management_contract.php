<?php
use app\components\AppHelper;
use yii\helpers\Html;

$contract = $records[0] ?? null;
$data = $contract && is_array($contract->data_json) ? $contract->data_json : [];
$formatDate = static fn($date) => $date ? AppHelper::DateFormDb($date) : 'ไม่ระบุ';
?>
<section class="card border-0 shadow-sm" aria-labelledby="employment-contract-title">
    <div class="card-body p-4">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
            <div><h2 id="employment-contract-title" class="h5 mb-1">สัญญาจ้าง</h2><p class="text-body-secondary mb-0">ข้อมูลการจ้างที่เกี่ยวข้องกับการกำกับงาน</p></div>
            <i data-lucide="file-signature" class="text-primary" aria-hidden="true"></i>
        </div>
        <?php if (!$contract): ?>
            <div class="alert alert-secondary mb-0">ยังไม่พบข้อมูลสัญญาจ้างของบุคลากรรายนี้</div>
        <?php else: ?>
            <dl class="row mb-0">
                <dt class="col-sm-4 text-body-secondary fw-normal py-2">ประเภทการจ้าง</dt><dd class="col-sm-8 py-2 fw-semibold"><?= Html::encode($contract->positionTypeName() ?: $model->positionTypeName() ?: 'ไม่ระบุ') ?></dd>
                <dt class="col-sm-4 text-body-secondary fw-normal py-2">วันที่เริ่มสัญญา</dt><dd class="col-sm-8 py-2"><?= Html::encode($formatDate($data['date_start'] ?? null)) ?></dd>
                <dt class="col-sm-4 text-body-secondary fw-normal py-2">วันที่สิ้นสุดสัญญา</dt><dd class="col-sm-8 py-2"><?= Html::encode($formatDate($data['date_end'] ?? null)) ?></dd>
                <dt class="col-sm-4 text-body-secondary fw-normal py-2">สถานะ/รายการเปลี่ยนแปลง</dt><dd class="col-sm-8 py-2"><?= Html::encode($data['statuslist'] ?? 'ไม่ระบุ') ?></dd>
                <dt class="col-sm-4 text-body-secondary fw-normal py-2">เอกสารอ้างอิง</dt><dd class="col-sm-8 py-2"><?= Html::encode($data['doc_ref'] ?? 'ไม่ระบุ') ?></dd>
            </dl>
            <div class="alert alert-secondary d-flex gap-2 mt-4 mb-0" role="note"><i data-lucide="lock-keyhole" aria-hidden="true"></i><span>มุมมองนี้ไม่แสดงเงินเดือนและข้อมูลค่าตอบแทน</span></div>
        <?php endif ?>
    </div>
</section>
