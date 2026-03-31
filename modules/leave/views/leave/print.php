<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var app\modules\leave\models\Leave $model */
/** @var string|null $pdfUrl ไม่ใช้แล้ว — มีเทมเพลต PDF จะ redirect จาก actionPrint ให้แสดง PDF โดยตรง */

$this->title = 'พิมพ์ใบลา';
$this->params['breadcrumbs'][] = ['label' => 'การลางาน', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$author = $model->getAvatar($model->emp_id, '');
$fullname = $author['fullname'] ?? ($model->employee->fullname ?? 'ไม่ระบุ');
$department = $author['department'] ?? ($model->employee ? $model->employee->departmentName() : 'ไม่ระบุ');
?>
<style>
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<div class="container-fluid py-4 no-print">
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <h4 class="fw-bold text-body mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-printer text-primary"></i>
                    <?= Html::encode($this->title) ?>
                </h4>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-primary rounded-3 px-3" onclick="window.print();">
                        <i class="bi bi-printer me-1"></i> พิมพ์ใบลา
                    </button>
                    <a href="<?= Url::to(['/leave/leave/view', 'id' => $model->id]) ?>" class="btn btn-outline-secondary rounded-3">
                        <i class="bi bi-x-lg me-1"></i> ปิด
                    </a>
                </div>
            </div>
        </div>
    </div>
    <p class="small text-muted mb-0">
        กดปุ่มด้านบนหรือใช้ Ctrl+P (Cmd+P) เพื่อพิมพ์หน้านี้ — แนะนำตั้งค่าใบลาแบบหลักที่ <?= Html::a('/pdf-template/template', ['/pdf-template/template'], ['target' => '_blank', 'rel' => 'noopener noreferrer']) ?> (ลิงก์ «พิมพ์ใบลา (PDF)» ในระบบจะเปิดเส้นทางนั้นก่อน) ถ้ายังไม่ตั้งจะใช้แบบฟอร์มไฟล์ที่ <strong>การตั้งค่า → แบบฟอร์มใบลา</strong> หรือหน้านี้
    </p>
</div>

<div class="container-fluid py-3" id="leave-print-content">
        <div class="card border rounded-3 shadow-sm">
            <div class="card-body">
                <h5 class="border-bottom pb-2 mb-4 fw-bold text-body">ใบขอลา</h5>
                <div class="row g-3 small">
                    <div class="col-md-6">
                        <span class="text-muted">ชื่อ-นามสกุลผู้ขอลา</span>
                        <div class="fw-semibold text-body"><?= Html::encode($fullname) ?></div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted">หน่วยงาน/แผนก</span>
                        <div class="fw-semibold text-body"><?= Html::encode($department) ?></div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted">ประเภทการลา</span>
                        <div class="fw-semibold text-body"><?= $model->leaveType ? Html::encode($model->leaveType->title) : '-' ?></div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted">จำนวนวัน</span>
                        <div class="fw-semibold text-body"><?= (float) $model->total_days ?> วัน</div>
                    </div>
                    <div class="col-12">
                        <span class="text-muted">ช่วงเวลาที่ลา</span>
                        <div class="fw-semibold text-body"><?= $model->showLeaveDate() ?></div>
                    </div>
                    <div class="col-12">
                        <span class="text-muted">เหตุผลการลา</span>
                        <div class="fw-semibold text-body"><?= Html::encode($model->data_json['reason'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted">ที่อยู่ที่ติดต่อได้</span>
                        <div class="fw-semibold text-body"><?= Html::encode($model->data_json['address'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted">เบอร์โทรติดต่อ</span>
                        <div class="fw-semibold text-body"><?= Html::encode($model->data_json['phone'] ?? $model->data_json['leave_contact_phone'] ?? '-') ?></div>
                    </div>
                    <div class="col-12">
                        <span class="text-muted">วันที่ยื่นคำขอ</span>
                        <div class="fw-semibold text-body"><?= $model->created_at ? ThaiDateHelper::formatThaiDate($model->created_at, 'long') : '-' ?></div>
                    </div>
                </div>
            </div>
        </div>
</div>