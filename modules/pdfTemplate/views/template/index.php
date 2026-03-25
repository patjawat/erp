<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\pdfTemplate\models\PdfTemplate[] $templates */
/** @var int|null $developmentTemplateId */
/** @var int|null $leaveTemplateId */
/** @var int|null $leaveRestTemplateId */
/** @var int|null $repairNoticeTemplateId */
/** @var int|null $bookingVehicleCentralTemplateId */

$this->title = 'Template รายงานขอไปราชการ';
$developmentTemplateId = $developmentTemplateId ?? null;
$leaveTemplateId = $leaveTemplateId ?? null;
$leaveRestTemplateId = $leaveRestTemplateId ?? null;
$repairNoticeTemplateId = $repairNoticeTemplateId ?? null;
$bookingVehicleCentralTemplateId = $bookingVehicleCentralTemplateId ?? null;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-primary bg-opacity-10 text-primary border-0 py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-file-earmark-pdf me-2"></i><?= Html::encode($this->title) ?></h5>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('<i class="bi bi-download me-1"></i> นำเข้า config', ['import-config'], ['class' => 'btn btn-outline-secondary btn-sm rounded-3']) ?>
            <?= Html::a('<i class="bi bi-plus-lg me-1"></i> เพิ่มเทมเพลต', ['create'], ['class' => 'btn btn-primary btn-sm rounded-3']) ?>
        </div>
    </div>
    <div class="card-body p-4">
        <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= Yii::$app->session->getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card border rounded-3 mb-4">
            <div class="card-header bg-primary bg-opacity-10 py-2">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-gear me-1"></i> ตั้งค่าเทมเพลตสำหรับใบขอไปราชการ</h6>
            </div>
            <div class="card-body p-3">
                <p class="small text-muted mb-2">เลือกเทมเพลตที่ใช้เมื่อพิมพ์จาก <code>/hr/development/print?id=...</code></p>
                <?php $form = \yii\widgets\ActiveForm::begin(['action' => ['set-template-for-context'], 'method' => 'post', 'options' => ['class' => 'd-flex flex-wrap align-items-end gap-2']]); ?>
                <input type="hidden" name="context" value="development">
                <div class="flex-grow-1" style="min-width: 200px;">
                    <select name="template_id" class="form-select">
                        <option value="">— ใช้เทมเพลตแรก (ตาม id) —</option>
                        <?php foreach ($templates as $t): ?>
                        <option value="<?= (int) $t->id ?>"<?= $developmentTemplateId === (int) $t->id ? ' selected' : '' ?>><?= Html::encode($t->name) ?> (id=<?= (int) $t->id ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary rounded-3"><i class="bi bi-check-lg me-1"></i> บันทึก</button>
                <?php \yii\widgets\ActiveForm::end(); ?>
            </div>
        </div>

        <div class="card border rounded-3 mb-4">
            <div class="card-header bg-success bg-opacity-10 py-2">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-file-earmark-text me-1"></i> ตั้งค่าเทมเพลตสำหรับใบลา (ลาป่วย / ลาคลอดบุตร / ลากิจส่วนตัว)</h6>
            </div>
            <div class="card-body p-3">
                <p class="small text-muted mb-2">ใช้เมื่อพิมพ์ใบลาป่วย (LT1), ลาคลอดบุตร (LT2), ลากิจส่วนตัว (LT3) จาก <code>/hr/leave</code></p>
                <?php $formLeave = \yii\widgets\ActiveForm::begin(['action' => ['set-template-for-context'], 'method' => 'post', 'options' => ['class' => 'd-flex flex-wrap align-items-end gap-2']]); ?>
                <input type="hidden" name="context" value="leave">
                <div class="flex-grow-1" style="min-width: 200px;">
                    <select name="template_id" class="form-select">
                        <option value="">— ยังไม่เลือก —</option>
                        <?php foreach ($templates as $t): ?>
                        <option value="<?= (int) $t->id ?>"<?= $leaveTemplateId === (int) $t->id ? ' selected' : '' ?>><?= Html::encode($t->name) ?> (id=<?= (int) $t->id ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success rounded-3"><i class="bi bi-check-lg me-1"></i> บันทึก</button>
                <?php \yii\widgets\ActiveForm::end(); ?>
            </div>
        </div>

        <div class="card border rounded-3 mb-4">
            <div class="card-header bg-info bg-opacity-10 py-2">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-file-earmark-text me-1"></i> ตั้งค่าเทมเพลตสำหรับใบลาพักผ่อน</h6>
            </div>
            <div class="card-body p-3">
                <p class="small text-muted mb-2">ใช้เมื่อพิมพ์ใบลาพักผ่อน (LT4) จาก <code>/hr/leave</code> — แยกจากใบลาป่วย/คลอด/กิจ</p>
                <?php $formLeaveRest = \yii\widgets\ActiveForm::begin(['action' => ['set-template-for-context'], 'method' => 'post', 'options' => ['class' => 'd-flex flex-wrap align-items-end gap-2']]); ?>
                <input type="hidden" name="context" value="leave.rest">
                <div class="flex-grow-1" style="min-width: 200px;">
                    <select name="template_id" class="form-select">
                        <option value="">— ยังไม่เลือก —</option>
                        <?php foreach ($templates as $t): ?>
                        <option value="<?= (int) $t->id ?>"<?= $leaveRestTemplateId === (int) $t->id ? ' selected' : '' ?>><?= Html::encode($t->name) ?> (id=<?= (int) $t->id ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-info rounded-3"><i class="bi bi-check-lg me-1"></i> บันทึก</button>
                <?php \yii\widgets\ActiveForm::end(); ?>
            </div>
        </div>

        <div class="card border rounded-3 mb-4">
            <div class="card-header bg-warning bg-opacity-10 py-2">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-wrench-adjustable-circle me-1"></i> ตั้งค่าเทมเพลตสำหรับใบส่งซ่อม</h6>
            </div>
            <div class="card-body p-3">
                <p class="small text-muted mb-2">ใช้เมื่อพิมพ์เอกสารจากระบบงานซ่อม (Helpdesk2) — ใบส่งซ่อม</p>
                <?php $formRepairNotice = \yii\widgets\ActiveForm::begin(['action' => ['set-template-for-context'], 'method' => 'post', 'options' => ['class' => 'd-flex flex-wrap align-items-end gap-2']]); ?>
                <input type="hidden" name="context" value="helpdesk2.repair.notice">
                <div class="flex-grow-1" style="min-width: 200px;">
                    <select name="template_id" class="form-select">
                        <option value="">— ยังไม่เลือก —</option>
                        <?php foreach ($templates as $t): ?>
                        <option value="<?= (int) $t->id ?>"<?= $repairNoticeTemplateId === (int) $t->id ? ' selected' : '' ?>><?= Html::encode($t->name) ?> (id=<?= (int) $t->id ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-warning rounded-3"><i class="bi bi-check-lg me-1"></i> บันทึก</button>
                <?php \yii\widgets\ActiveForm::end(); ?>
            </div>
        </div>

        <div class="card border rounded-3 mb-4">
            <div class="card-header bg-primary bg-opacity-10 py-2">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-car-front me-1"></i> ตั้งค่าเทมเพลตสำหรับ ขอใช้รถยนต์ส่วนกลาง</h6>
            </div>
            <div class="card-body p-3">
                <p class="small text-muted mb-2">ใช้เมื่อพิมพ์เอกสารขอใช้รถยนต์ส่วนกลาง (Booking / Vehicle)</p>
                <?php $formBooking = \yii\widgets\ActiveForm::begin(['action' => ['set-template-for-context'], 'method' => 'post', 'options' => ['class' => 'd-flex flex-wrap align-items-end gap-2']]); ?>
                <input type="hidden" name="context" value="booking.vehicle.central">
                <div class="flex-grow-1" style="min-width: 200px;">
                    <select name="template_id" class="form-select">
                        <option value="">— ยังไม่เลือก —</option>
                        <?php foreach ($templates as $t): ?>
                        <option value="<?= (int) $t->id ?>"<?= $bookingVehicleCentralTemplateId === (int) $t->id ? ' selected' : '' ?>><?= Html::encode($t->name) ?> (id=<?= (int) $t->id ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary rounded-3"><i class="bi bi-check-lg me-1"></i> บันทึก</button>
                <?php \yii\widgets\ActiveForm::end(); ?>
            </div>
        </div>

        <div class="alert alert-info border-0 rounded-3 mb-4 d-flex align-items-start gap-2">
            <i class="bi bi-info-circle fs-5 flex-shrink-0"></i>
            <div class="small">
                <strong>ทำไงต่อ</strong><br>
                1) ถ้ายังไม่มีเทมเพลต — กด <strong>«เพิ่มเทมเพลต»</strong> แล้วกรอกชื่อ + อัปโหลดไฟล์ PDF ต้นแบบ<br>
                2) หลังมีเทมเพลตแล้ว — กดปุ่ม <strong>«กำหนดตำแหน่ง»</strong> เพื่อลากวางฟิลด์บน PDF<br>
                3) ในหน้าแก้ไข: คลิกฟิลด์ทางซ้ายเพื่อเพิ่มบน PDF ลากปรับตำแหน่ง แล้วกด <strong>«บันทึกตำแหน่ง»</strong><br>
                4) <strong>พิมพ์ตัวอย่าง</strong> — กดปุ่ม «พิมพ์ตัวอย่าง» เพื่อดู PDF ที่เติมข้อมูลตัวอย่างตามตำแหน่งที่กำหนด<br>
                5) <strong>นำไปใช้งานจริง</strong> — ดูตัวอย่างโค้ดด้านล่างเพื่อเรียกใช้จากโมดูลอื่น (เช่น ระบบขอไปราชการ)
            </div>
        </div>
        <p class="text-muted small mb-3">เลือกรายการเพื่อกำหนดตำแหน่งฟิลด์บน PDF (ระบบใช้ค่าสัมพัทธ์ 0–1 ไม่ขึ้นกับความละเอียดจอ)</p>
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>ชื่อ</th>
                    <th>ไฟล์</th>
                    <th class="text-end">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider align-middle">
                <?php foreach ($templates as $t): ?>
                <tr>
                    <td><?= (int) $t->id ?></td>
                    <td><?= Html::encode($t->name) ?></td>
                    <td class="small text-muted"><?= $t->file_path ? Html::encode(basename($t->file_path)) : ($t->upload_id ? 'PDF' : '—') ?></td>
                    <td class="text-end">
                        <?= Html::a('<i class="bi bi-printer me-1"></i> พิมพ์ตัวอย่าง', ['print-sample', 'template_id' => $t->id], ['class' => 'btn btn-outline-secondary btn-sm rounded-3', 'target' => '_blank']) ?>
                        <?= Html::a('<i class="bi bi-download me-1"></i> ส่งออก config', ['export-config', 'id' => $t->id], ['class' => 'btn btn-outline-info btn-sm rounded-3', 'title' => 'ดาวน์โหลดไฟล์ JSON ตำแหน่งฟิลด์']) ?>
                        <?= Html::a('<i class="bi bi-geo-alt me-1"></i> กำหนดตำแหน่ง', ['editor', 'template_id' => $t->id], ['class' => 'btn btn-primary btn-sm rounded-3']) ?>
                        <?= Html::a('<i class="bi bi-pencil me-1"></i> แก้ไข', ['update', 'id' => $t->id], ['class' => 'btn btn-outline-primary btn-sm rounded-3']) ?>
                        <?php $formDelete = \yii\widgets\ActiveForm::begin(['action' => ['delete', 'id' => $t->id], 'method' => 'post', 'options' => ['class' => 'd-inline']]); ?>
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-3" onclick="return confirm(<?= json_encode('ต้องการลบเทมเพลต «' . $t->name . '» และตำแหน่งฟิลด์ทั้งหมดหรือไม่') ?>);">
                            <i class="bi bi-trash me-1"></i> ลบ
                        </button>
                        <?php \yii\widgets\ActiveForm::end(); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($templates)): ?>
        <p class="text-muted mb-0">ยังไม่มีเทมเพลต — กดปุ่ม <strong>«เพิ่มเทมเพลต»</strong> ด้านบนเพื่ออัปโหลดไฟล์ PDF ต้นแบบ</p>
        <?php endif; ?>

        <hr class="my-4">
        <h6 class="fw-semibold mb-2"><i class="bi bi-code-slash me-1"></i> นำไปใช้งานจริง (จากโมดูลอื่น)</h6>
        <p class="small text-muted mb-2">เมื่อต้องการพิมพ์เอกสารจริง (เช่น จากรายการขอไปราชการ) ให้เรียกใช้ Service แล้วส่งข้อมูลจริงเข้าไป:</p>
        <pre class="small bg-light rounded-3 p-3 border mb-0" style="font-size: 0.8rem;"><code><?= Html::encode("use app\\modules\\pdfTemplate\\services\\PdfTemplateService;

\$service = new PdfTemplateService();
\$templateId = 1; // id ของเทมเพลต
\$data = [
    'officer_name' => \$model->createdByEmp->fullname ?? '-',
    'document_date' => ThaiDateHelper::formatThaiDate(date('Y-m-d')),
    'topic' => \$model->topic,
    'location' => \$model->data_json['location'] ?? '-',
    'date_start' => ThaiDateHelper::formatThaiDate(\$model->date_start),
    'date_end' => ThaiDateHelper::formatThaiDate(\$model->date_end),
];
\$pdfBinary = \$service->generatePdfWithData(\$templateId, \$data);
// ส่งเป็น response หรือบันทึกไฟล์") ?></code></pre>
    </div>
</div>
