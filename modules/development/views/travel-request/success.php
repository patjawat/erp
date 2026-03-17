<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ThaiDateHelper;

/** @var app\modules\hr\models\Development $model */

$this->title = 'บันทึกข้อมูลสำเร็จ';
$this->params['breadcrumbs'] = [];
$dataJson = $model->data_json ?? [];
$members = $model->listMemberPrint();
$emp = $model->createdByEmp ?? $model->emp;
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="text-center mb-3">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
            <h4 class="fw-bold text-body mt-2">บันทึกข้อมูลสำเร็จ</h4>
            <p class="text-muted">สร้างบันทึกข้อความขอไปราชการเรียบร้อยแล้ว</p>
        </div>
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <h6 class="fw-semibold text-body mb-3"><i class="bi bi-file-earmark-text text-primary me-2"></i>รายละเอียดที่บันทึก</h6>
                <p class="small mb-2"><span class="text-muted">วันที่เดินทาง:</span> <strong><?= $model->date_start ? ThaiDateHelper::formatThaiDate($model->date_start, 'short') : '-' ?></strong></p>
                <p class="small mb-2"><span class="text-muted">ผู้ขอ / ผู้ร่วมเดินทาง:</span></p>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <?php if ($emp): ?>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1"><?= Html::encode($emp->fullname()) ?></span>
                    <?php endif; ?>
                    <?php foreach ($members as $m): ?>
                    <?php $e = $m->emp; ?>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1"><?= $e ? Html::encode($e->fullname()) : Html::encode($m->data_json['label'] ?? $m->emp_id) ?></span>
                    <?php endforeach; ?>
                </div>
                <p class="small mb-2"><span class="text-muted">สถานที่ / เหตุผล:</span> <?= Html::encode($model->topic) ?> <?= Html::encode($dataJson['province_name'] ?? '') ?></p>
                <p class="small mb-2"><span class="text-muted">สิ่งที่ส่งมาด้วย:</span></p>
                <ul class="small ps-3 mb-2">
                    <?php if (!empty($dataJson['attach_invitation'])): ?><li>หนังสือเชิญ</li><?php endif; ?>
                    <?php if (!empty($dataJson['attach_class_change'])): ?><li>ใบขอเปลี่ยนคาบสอน</li><?php endif; ?>
                    <?php if (!empty($dataJson['attach_vehicle'])): ?><li>ขออนุญาตใช้รถ รร.</li><?php endif; ?>
                </ul>
                <?php if (!empty($dataJson['registration_amount'])): ?>
                <p class="small mb-0"><span class="text-muted">ค่าใช้จ่ายที่ลงทะเบียน:</span> <strong><?= Html::encode($dataJson['registration_amount']) ?> บาท</strong> <i class="bi bi-check-circle-fill text-success"></i></p>
                <?php endif; ?>
                <div class="d-flex gap-2 mt-4 flex-wrap">
                    <?= Html::a('<i class="bi bi-download me-1"></i> ดาวน์โหลด PDF', ['/hr/development/print', 'id' => $model->id], ['class' => 'btn btn-outline-primary rounded-3', 'target' => '_blank']) ?>
                    <?= Html::a('กลับหน้าหลัก', ['/development/default/dashboard'], ['class' => 'btn btn-primary rounded-3']) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-transparent border-0">
                <h6 class="fw-semibold text-body mb-0">ตัวอย่างเอกสาร</h6>
            </div>
            <div class="card-body p-0">
                <iframe src="<?= Url::to(['/hr/development/print', 'id' => $model->id]) ?>" class="w-100 border-0" style="min-height: 70vh;"></iframe>
            </div>
        </div>
    </div>
</div>
