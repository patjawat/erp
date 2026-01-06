<?php
use yii\helpers\Html;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

// --- Logic สำหรับ QR Code (ปรับปรุงให้รองรับทุกเวอร์ชัน) ---
$url = Yii::$app->urlManager->createAbsoluteUrl(['/asset/view', 'id' => $model->asset_id]);

$options = new QROptions([
    'version'      => 5,
    // ใช้ string 'svg' แทนการเรียก Constant เพื่อเลี่ยงปัญหา Undefined Constant
    'outputType'   => 'svg', 
    'eccLevel'     => 1, // ระดับ 1 คือ ECC_L
    'addQuietzone' => true,
    'outputBase64' => true, // บังคับให้เป็น Data URI เพื่อใช้ใน <img> ได้ทันที
]);

$qrcode = (new QRCode($options))->render($url);
$this->title = 'ใบรับคืนเครื่องมือแพทย์ - ' . $model->ref;
?>

<div class="print-receipt-view bg-white p-4">

    <div class="row mb-4 border-bottom pb-3 align-items-center">
        <div class="col-8">
            <h3 class="fw-bold text-primary mb-1">ใบยืนยันการรับคืนเครื่องมือแพทย์</h3>
            <p class="text-muted mb-0">
                <i class="bi bi-hospital me-1"></i> ระบบบริหารจัดการครุภัณฑ์การแพทย์ (ERP System)
            </p>
        </div>
        <div class="col-4 text-end border-start">
            <div class="fw-bold">เลขที่อ้างอิง: <span class="text-danger"><?= Html::encode($model->ref) ?></span></div>
            <div class="small">วันที่คืนจริง: <?= Yii::$app->formatter->asDatetime($model->actual_date) ?></div>
            <div class="small text-muted">วันที่พิมพ์: <?= date('d/m/Y H:i') ?> น.</div>
        </div>
    </div>

    <div class="card border-0 mb-4 shadow-sm no-shadow-print">
        <div class="card-header bg-light fw-bold">
            <i class="bi bi-info-circle-fill me-1"></i> ข้อมูลครุภัณฑ์
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <tr>
                    <th width="20%" class="bg-light-subtle text-muted">ชื่อรายการ</th>
                    <td width="30%" class="fw-bold"><?= Html::encode($model->asset->asset_name) ?></td>
                    <th width="20%" class="bg-light-subtle text-muted">รหัสครุภัณฑ์</th>
                    <td width="30%"><?= Html::encode($model->asset->code) ?></td>
                </tr>
                <tr>
                    <th class="bg-light-subtle text-muted">Serial Number</th>
                    <td><?= Html::encode($model->asset->serial_number ?? '-') ?></td>
                    <th class="bg-light-subtle text-muted">หน่วยงานที่ใช้งาน</th>
                    <td><?= Html::encode($model->employee->departmentName()) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card border-0 mb-4 shadow-sm no-shadow-print">
        <div class="card-header bg-light fw-bold">
            <i class="bi bi-clipboard-check-fill me-1 text-success"></i> บันทึกการส่งคืน
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <tr>
                    <th width="20%" class="bg-light-subtle text-muted">ผู้ส่งคืน</th>
                    <td><?= Html::encode($model->employee->fullname) ?></td>
                    <th width="20%" class="bg-light-subtle text-muted">สภาพหลังใช้งาน</th>
                    <td>
                        <?php if ($model->data_json['return_result'] === 'normal'): ?>
                            <span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> ปกติ</span>
                        <?php else: ?>
                            <span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> ชำรุด/แจ้งซ่อม</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th class="bg-light-subtle text-muted">หมายเหตุ</th>
                    <td colspan="3"><?= nl2br(Html::encode($model->data_json['note'])) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row align-items-center mb-5 mt-4 border rounded p-3 bg-light-subtle mx-0">
        <div class="col-3 text-center border-end">
            <img src="<?= $qrcode ?>" alt="QR Code" style="width: 110px; height: 110px;">
        </div>
        <div class="col-9 ps-4">
            <h6 class="fw-bold mb-1">ตรวจสอบข้อมูลผ่านระบบดิจิทัล</h6>
            <p class="small text-muted mb-0">เจ้าหน้าที่สามารถสแกน QR Code เพื่อตรวจสอบประวัติการยืม-คืน และรายละเอียดทางเทคนิคของเครื่องมือชิ้นนี้ได้ทันทีผ่านระบบ ERP ของโรงพยาบาล</p>
        </div>
    </div>

    <div class="row mt-5 pt-4 text-center">
        <div class="col-6">
            <div class="mb-5">...........................................................</div>
            <div class="fw-bold">( <?= Html::encode($model->employee->fullname) ?> )</div>
            <div class="small text-muted">ผู้ส่งคืน</div>
        </div>
        <div class="col-6">
            <div class="mb-5">...........................................................</div>
            <div class="fw-bold">( <?= Html::encode(Yii::$app->user->identity->fullname ?? '..................................') ?> )</div>
            <div class="small text-muted">เจ้าหน้าที่ผู้รับตรวจสอบ</div>
        </div>
    </div>

    <div class="text-center mt-5 no-print">
        <hr>
        <button onclick="window.print();" class="btn btn-primary btn-lg px-5 rounded-pill shadow">
            <i class="bi bi-printer me-2"></i> พิมพ์ใบรับคืนเครื่อง
        </button>
        <?php //  Html::a('กลับหน้าครุภัณฑ์', ['asset/view', 'id' => $model->asset_id], ['class' => 'btn btn-link link-secondary']) ?>
    </div>

</div>

<style>
/* ตกแต่งสไตล์ตารางให้ดูเบาบางลง */
.bg-light-subtle { background-color: #f8f9fa; }
.table th { font-weight: 500; font-size: 0.9rem; }

@media print {
    /* ซ่อนปุ่มและเมนูที่ไม่เกี่ยวข้อง */
    .no-print { display: none !important; }
    .main-sidebar, .main-header, .main-footer { display: none !important; }
    
    /* ปรับขนาดให้เต็มหน้ากระดาษ */
    .content-wrapper { margin-left: 0 !important; }
    body { background-color: #fff !important; font-size: 12pt; }
    .print-receipt-view { padding: 0 !important; }
    .card { border: 1px solid #dee2e6 !important; }
    .no-shadow-print { shadow: none !important; box-shadow: none !important; }
    
    /* บังคับให้พิมพ์พื้นหลัง (ในบาง Browser) */
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>