<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var app\modules\leave\models\Leave $model */
/** @var string|null $pdfUrl URL ของ PDF ที่สร้างจากเทมเพลต (ถ้ามี) */

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

    #iframe-leave-pdf {
        width: 100%;
        min-height: 80vh;
        border: 0;
        border-radius: 0.5rem;
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
                    <?php if ($pdfUrl): ?>
                        <?= Html::a('<i class="bi bi-file-earmark-pdf me-1"></i> เปิดเฉพาะ PDF', ['/leave/leave/pdf', 'id' => $model->id], ['class' => 'btn btn-outline-primary rounded-3 px-3', 'target' => '_blank', 'rel' => 'noopener']) ?>
                        <button type="button" class="btn btn-primary rounded-3 px-3" id="btn-print-pdf">
                            <i class="bi bi-printer me-1"></i> พิมพ์ใบลา
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-primary rounded-3 px-3" onclick="window.print();">
                            <i class="bi bi-printer me-1"></i> พิมพ์ใบลา
                        </button>
                    <?php endif; ?>
                    <a href="<?= Url::to(['/leave/leave/view', 'id' => $model->id]) ?>" class="btn btn-outline-secondary rounded-3">
                        <i class="bi bi-x-lg me-1"></i> ปิด
                    </a>
         
        </div>
    </div>
       </div>
            </div>

    <p class="small text-muted mb-0">
        <?php if ($pdfUrl): ?>
            ใบลาด้านล่างสร้างจากเทมเพลตที่อัปโหลด และใช้<strong>ตำแหน่งข้อมูลที่กำหนดใน การตั้งค่า → แบบฟอร์มใบลา → กำหนดตำแหน่งข้อมูลบน PDF</strong> — กดปุ่ม «พิมพ์ใบลา» หรือ Ctrl+P (Cmd+P) เพื่อพิมพ์
        <?php else: ?>
            กดปุ่มด้านบนหรือใช้ Ctrl+P (Cmd+P) เพื่อพิมพ์ — ต้องการใช้เทมเพลต PDF ให้ไปที่ <strong>การตั้งค่า → แบบฟอร์มใบลา</strong> อัปโหลดเทมเพลตและกำหนดตำแหน่ง
        <?php endif; ?>
    </p>
</div>

<?php if ($pdfUrl): ?>
    <div class="container-fluid py-3">
        <iframe id="iframe-leave-pdf" src="<?= Html::encode($pdfUrl) ?>#toolbar=0" title="ใบลา (PDF จากเทมเพลต)"></iframe>
    </div>
    <?php
    $this->registerJs(
        <<<JS
(function(){
    var btn = document.getElementById('btn-print-pdf');
    var iframe = document.getElementById('iframe-leave-pdf');
    if (btn && iframe) {
        btn.addEventListener('click', function() {
            try {
                if (iframe.contentWindow) {
                    iframe.contentWindow.print();
                } else {
                    window.print();
                }
            } catch (e) {
                window.print();
            }
        });
    }
})();
JS
    );
    ?>
<?php else: ?>
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
<?php endif; ?>