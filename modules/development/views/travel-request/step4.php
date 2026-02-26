<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ThaiDateHelper;

/** @var array $draft */

$this->title = 'บันทึกข้อความขอไปราชการ';
$this->params['breadcrumbs'] = [];
$members = $draft['members'] ?? [];
$totalDays = 0;
if (!empty($draft['date_start']) && !empty($draft['date_end'])) {
    $start = \DateTime::createFromFormat('d/m/Y', $draft['date_start']);
    $end = \DateTime::createFromFormat('d/m/Y', $draft['date_end']);
    if ($start && $end) {
        $totalDays = $start->diff($end)->days + 1;
    }
}
?>
<div class="travel-request-wizard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="<?= Url::to(['/development/travel-request/step2']) ?>" class="btn btn-link text-decoration-none text-body p-0">
            <i class="bi bi-arrow-left me-1"></i><?= Html::encode($this->title) ?>
        </a>
        <span class="text-muted small">Step 4 of 4</span>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-body mb-3">4. ยืนยันและลงนาม</h5>
                    <p class="text-muted small mb-3">
                        <i class="bi bi-file-earmark-text me-2"></i>บันทึกข้อความขอไปราชการ
                    </p>
                    <div class="mb-2 small">
                        <span class="text-muted">วันที่เดินทาง:</span>
                        <strong><?= Html::encode($draft['date_start'] ?? '-') ?></strong>
                        <span class="text-muted ms-2">รวม <?= (int) $totalDays ?> วัน</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small">ผู้ขอ / ผู้ร่วมเดินทาง:</span>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            <?php foreach ($members as $m): ?>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1"><?= Html::encode($m['label'] ?? $m['emp_id'] ?? '') ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-2 small">
                        <span class="text-muted">สถานที่ / เหตุผล:</span>
                        <strong><?= Html::encode($draft['topic'] ?? '') ?></strong>
                        <span class="text-muted"><?= Html::encode($draft['province_name'] ?? '') ?></span>
                    </div>
                    <div class="mb-2 small">
                        <span class="text-muted">สิ่งที่ส่งมาด้วย:</span>
                        <ul class="mb-0 ps-3">
                            <?php if (!empty($draft['attach_invitation'])): ?><li>หนังสือเชิญ</li><?php endif; ?>
                            <?php if (!empty($draft['attach_class_change'])): ?><li>ใบขอเปลี่ยนคาบสอน</li><?php endif; ?>
                            <?php if (!empty($draft['attach_vehicle'])): ?><li>ขออนุญาตใช้รถ รร.</li><?php endif; ?>
                        </ul>
                    </div>
                    <?php if (!empty($draft['registration_amount'])): ?>
                    <div class="small">
                        <span class="text-muted">ค่าใช้จ่ายที่ลงทะเบียน:</span>
                        <strong><?= Html::encode($draft['registration_amount']) ?> บาท</strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-body mb-3"><i class="bi bi-pen me-2"></i>ลงลายมือชื่อผู้ขอ</h6>
                    <div class="btn-group btn-group-sm mb-3" role="group">
                        <button type="button" class="btn btn-outline-secondary active">เซ็นสด</button>
                        <button type="button" class="btn btn-outline-secondary">เซ็นด่วน</button>
                        <button type="button" class="btn btn-outline-secondary">ไม่เซ็น</button>
                    </div>
                    <div class="border border-2 border-dashed rounded-3 bg-white mb-2" style="min-height: 120px;"></div>
                    <a href="#" class="small text-primary">ใช้ลายเซ็นจากระบบ</a>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between pt-4 mt-3">
        <?= Html::a('ย้อนกลับ', ['step2'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
        <?php $f = \yii\bootstrap5\ActiveForm::begin(['action' => ['submit'], 'method' => 'post', 'options' => ['class' => 'd-inline']]); ?>
        <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i> ยืนยันสร้างบันทึก', ['class' => 'btn btn-success rounded-3 px-4']) ?>
        <?php \yii\bootstrap5\ActiveForm::end(); ?>
    </div>
</div>
