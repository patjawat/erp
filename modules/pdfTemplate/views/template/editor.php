<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\pdfTemplate\models\PdfTemplate $template */
/** @var array $layout */
/** @var string $serveUrl */
/** @var array $fieldDefinitions */
/** @var array $dataSources */
/** @var string $selectedSourceId */
/** @var string $fieldsForSourceUrl */
/** @var string $developmentPrintDataUrl */
/** @var string $leavePrintDataUrl */
/** @var string $bookingPrintDataUrl */
/** @var array $leaveTypeOptions */

$this->title = 'กำหนดตำแหน่ง — ' . Html::encode($template->name);
$dataSources = $dataSources ?? [];
$selectedSourceId = $selectedSourceId ?? '';
$fieldsForSourceUrl = $fieldsForSourceUrl ?? '';
$developmentPrintDataUrl = $developmentPrintDataUrl ?? '';
$leavePrintDataUrl = $leavePrintDataUrl ?? '';
$bookingPrintDataUrl = $bookingPrintDataUrl ?? '';
$leaveTypeOptions = $leaveTypeOptions ?? [];
$this->params['breadcrumbs'][] = ['label' => 'Template รายงานขอไปราชการ', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'แก้ไข';

$saveUrl = Url::to(['save-layout', 'template_id' => $template->id]);
$previewUrl = Url::to(['preview', 'template_id' => $template->id]);
$csrf = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$layoutJson = json_encode($layout);
$fieldDefsJson = json_encode($fieldDefinitions);
$dataSourcesJson = json_encode($dataSources);
$fieldsForSourceUrlBase = $fieldsForSourceUrl ?: Url::to(['fields-for-source', 'template_id' => $template->id]);
$pageW = (float) $template->page_width;
$pageH = (float) $template->page_height;

$this->registerCss(<<<CSS
.pdf-editor-container { padding: 0; margin: 0; }
.pdf-editor-canvas-wrap { aspect-ratio: 210/297; max-width: 100%; position: relative; background: #eee; }
.pdf-editor-canvas-wrap canvas { display: block; width: 100%; height: 100%; }
.pdf-editor-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; }
.pdf-editor-overlay .field-box { position: absolute; pointer-events: auto; cursor: move; border: 1px dashed #0d6efd; background: rgba(13,110,253,0.08); padding: 2px 6px; box-sizing: border-box; overflow: visible; }
.pdf-editor-overlay .field-box.selected { border-color: #0d6efd; background: rgba(13,110,253,0.15); }
.pdf-editor-overlay { overflow: visible; }
.pdf-editor-canvas-wrap { overflow: visible; }
.field-list-item { cursor: grab; }
#field-list { max-height: min(60vh, 480px); overflow-y: auto; overflow-x: hidden; }
CSS
);
$this->registerJsFile('@web/libs/pdf/pdf.min.js', ['position' => \yii\web\View::POS_HEAD]); // self-hosted (เดิมดึงจาก cdnjs)
?>
<div class="container-fluid pdf-editor-container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="mb-0 fw-semibold"><i class="bi bi-geo-alt me-2"></i><?= Html::encode($this->title) ?></h4>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('<i class="bi bi-printer me-1"></i> พิมพ์ตัวอย่าง (ข้อมูลตัวอย่าง)', ['print-sample', 'template_id' => $template->id], ['class' => 'btn btn-outline-secondary rounded-3', 'target' => '_blank', 'title' => 'เปิด PDF ด้วยข้อมูลตัวอย่าง เพื่อตรวจตำแหน่ง']) ?>
            <?= Html::a('<i class="bi bi-download me-1"></i> ส่งออก config', ['export-config', 'id' => $template->id], ['class' => 'btn btn-outline-info rounded-3', 'title' => 'ดาวน์โหลดไฟล์ JSON ตำแหน่งฟิลด์ไปแชร์หรือเก็บไว้']) ?>
            <?= Html::a('<i class="bi bi-file-earmark-pdf me-1"></i> ดาวน์โหลดไฟล์ PDF เทมเพลต', ['download-template-file', 'id' => $template->id], ['class' => 'btn btn-outline-primary rounded-3', 'title' => 'ดาวน์โหลดไฟล์ PDF ต้นฉบับของเทมเพลตนี้', 'target' => '_blank']) ?>
            <button type="button" id="btn-save-layout" class="btn btn-primary rounded-3"><i class="bi bi-check-lg me-1"></i> บันทึกตำแหน่ง</button>
        </div>
    </div>
    <?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3 small rounded-3">
        <?= Yii::$app->session->getFlash('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3 small rounded-3">
        <?= Yii::$app->session->getFlash('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <div class="card border rounded-3 mb-3">
        <div class="card-header bg-secondary bg-opacity-10 py-2">
            <h6 class="mb-0 small fw-semibold"><i class="bi bi-upload me-1"></i> อัปโหลดเทมเพลต PDF ใหม่</h6>
        </div>
        <div class="card-body p-3">
            <?php \yii\widgets\ActiveForm::begin(['action' => ['upload-template', 'template_id' => $template->id], 'method' => 'post', 'options' => ['enctype' => 'multipart/form-data', 'class' => 'd-flex flex-wrap align-items-end gap-2']]); ?>
            <div class="flex-grow-1" style="min-width: 200px;">
                <input type="file" name="pdf_file" class="form-control" accept=".pdf,application/pdf" required>
            </div>
            <button type="submit" class="btn btn-primary rounded-3 btn-sm"><i class="bi bi-upload me-1"></i> อัปโหลดแทนไฟล์เดิม</button>
            <?php \yii\widgets\ActiveForm::end(); ?>
            <p class="small text-muted mb-0 mt-2">เลือกไฟล์ PDF ใหม่แล้วกดอัปโหลด — หน้าจะโหลดใหม่และแสดง PDF ที่อัปโหลด (ตำแหน่งฟิลด์ที่บันทึกไว้ยังคงอยู่)</p>
        </div>
    </div>
    <div class="alert alert-info py-2 px-3 mb-3 small rounded-3">
        <strong>เหตุที่ได้ข้อมูล sample:</strong> ปุ่ม «พิมพ์ตัวอย่าง» ใช้สำหรับตรวจตำแหน่งฟิลด์ด้วยข้อมูลตัวอย่าง (นายสมชาย ตัวอย่าง, 01/04/2569 ฯลฯ).
        <strong>เมื่อต้องการ PDF ข้อมูลจริง</strong> ให้กด «ไปพิมพ์รายการจริง» แล้วไปกดปุ่ม <strong>พิมพ์ใบขอไปราชการ</strong> ที่รายการที่ต้องการ — ระบบจะใช้ข้อมูลจาก DB.
    </div>
    <?php if ($developmentPrintDataUrl || $leavePrintDataUrl || $bookingPrintDataUrl): ?>
    <div class="card border rounded-3 mb-3" id="card-real-data">
        <div class="card-header bg-success bg-opacity-10 py-2">
            <h6 class="mb-0 small fw-semibold"><i class="bi bi-database me-1"></i> ดึงข้อมูลจริงมาแสดงบน overlay</h6>
        </div>
        <div class="card-body p-3">
            <p class="small text-muted mb-2" id="real-data-hint">ใส่รหัสรายการแล้วกดโหลด — ค่าจริงจะแสดงในกล่องฟิลด์บน PDF</p>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <input type="number" id="development-id-input" class="form-control" placeholder="รหัสรายการ" style="width: 140px;" min="1" aria-describedby="real-data-hint">
                <button type="button" id="btn-load-real-data" class="btn btn-success btn-sm rounded-3"><i class="bi bi-download me-1"></i> โหลดข้อมูลจริง</button>
                <button type="button" id="btn-clear-real-data" class="btn btn-outline-secondary btn-sm rounded-3">ล้าง</button>
            </div>
            <p class="small mb-0 mt-2 text-muted" id="real-data-status"></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-md-3">
            <div class="card border rounded-3 h-100">
                <div class="card-header bg-primary bg-opacity-10 py-2">
                    <h6 class="mb-0 small fw-semibold">ฟิลด์ — ลากไปวางบน PDF</h6>
                </div>
                <div class="card-body p-2">
                    <?php if (count($dataSources) > 1): ?>
                    <div class="mb-2">
                        <label class="form-label small">แหล่งข้อมูล</label>
                        <select id="data-source-select" class="form-select">
                            <?php foreach ($dataSources as $ds): ?>
                            <option value="<?= Html::encode($ds['id']) ?>"<?= ($selectedSourceId !== '' && $ds['id'] === $selectedSourceId) ? ' selected' : '' ?>><?= Html::encode($ds['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="mb-2">
                        <input type="text" id="field-filter" class="form-control" placeholder="ค้นหาฟิลด์..." autocomplete="off">
                    </div>
                <div id="field-list">
                    <?php foreach ($fieldDefinitions as $fd):
                        $src = $fd['source'] ?? $fd['field'] ?? '';
                        $lbl = $fd['label'] ?? $src;
                    ?>
                    <div class="field-list-item border rounded-2 p-2 mb-1 small" data-source="<?= Html::encode($src) ?>" data-field="<?= Html::encode($src) ?>" data-label="<?= Html::encode($lbl) ?>">
                        <?= Html::encode($lbl) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card border rounded-3">
                <div class="card-header bg-primary bg-opacity-10 py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h6 class="mb-0 small fw-semibold">พื้นที่ PDF (อัตราส่วน A4) — ค่าบันทึกเป็น % ไม่ใช้ px</h6>
                    <div id="pdf-page-nav" class="d-none align-items-center gap-2">
                        <label class="small text-muted mb-0">หน้า</label>
                        <select id="pdf-page-select" class="form-select" style="width: auto; min-width: 4rem;">
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-2 d-flex flex-column align-items-center">
                    <div class="pdf-editor-canvas-wrap" id="canvas-wrap" style="width: 100%; max-width: 560px;">
                        <canvas id="pdf-canvas"></canvas>
                        <div class="pdf-editor-overlay" id="field-overlay"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border rounded-3 h-100">
                <div class="card-header bg-primary bg-opacity-10 py-2">
                    <h6 class="mb-0 small fw-semibold">ตั้งค่าฟิลด์ที่เลือก</h6>
                </div>
                <div class="card-body p-3" id="field-settings">
                    <p class="text-muted small mb-0">คลิกที่กล่องฟิลด์บน PDF เพื่อแก้ไข</p>
                    <div id="field-settings-form" class="d-none mt-2">
                        <input type="hidden" id="sel-field-id">
                        <div class="mb-2">
                            <label class="form-label small">ขนาดตัวอักษร</label>
                            <input type="number" id="sel-font-size" class="form-control" min="6" max="24" value="14">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">ความหนาตัวอักษร</label>
                            <select id="sel-font-bold" class="form-select">
                                <option value="">ปกติ</option>
                                <option value="1">ตัวหนา</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">จัดแนว</label>
                            <select id="sel-alignment" class="form-select">
                                <option value="L">ซ้าย</option>
                                <option value="C">กลาง</option>
                                <option value="R">ขวา</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">หน้า</label>
                            <input type="number" id="sel-page" class="form-control" min="1" value="1">
                        </div>
                        <div id="text-width-settings" class="mb-2">
                            <label class="form-label small">ความกว้างกล่องข้อความ (% ของความกว้างหน้า)</label>
                            <input type="number" id="sel-width-percent" class="form-control" min="1" max="100" step="0.5" placeholder="20" title="เปอร์เซ็นต์ของความกว้างหน้า (เช่น 20 = 20%)">
                            <div class="form-text">ถ้ากล่องแคบ ระบบจะตัดข้อความลงบรรทัดใหม่อัตโนมัติ</div>
                        </div>
                        <div id="date-format-settings" class="mb-2 d-none">
                            <label class="form-label small">รูปแบบวันที่</label>
                            <select id="sel-date-format" class="form-select">
                                <option value="">— ไม่จัดรูปแบบ —</option>
                                <option value="raw">ตามข้อมูลต้นทาง (ไม่แปลงรูปแบบ)</option>
                                <option value="day_only">01 (เฉพาะวัน)</option>
                                <option value="month_only">12 (เฉพาะเดือน)</option>
                                <option value="month_name_short">ธ.ค. (เฉพาะชื่อเดือนแบบย่อ)</option>
                                <option value="month_name_full">ธันวาคม (เฉพาะชื่อเดือนเต็ม)</option>
                                <option value="numeric">01/01/2569 (วัน/เดือน/พ.ศ.)</option>
                                <option value="day_month">1 ม.ค. (เฉพาะวัน/เดือน)</option>
                                <option value="year_only">2569 (เฉพาะปี)</option>
                                <option value="short">1 ม.ค. 2569</option>
                                <option value="medium_p">1 มกราคม พ.ศ. 2569</option>
                                <option value="month_year">มกราคม 2569</option>
                                <option value="medium">1 มกราคม 2569</option>
                                <option value="long">วันอาทิตย์ที่ 1 มกราคม พ.ศ. 2569</option>
                            </select>
                        </div>
                        <div id="leave-type-settings" class="mb-2 d-none">
                            <label class="form-label small">ประเภทการลา</label>
                            <select id="sel-leave-type-id" class="form-select">
                                <option value="">ใช้ตามใบลาปัจจุบัน</option>
                                <?php foreach ($leaveTypeOptions as $leaveTypeOption): ?>
                                <option value="<?= Html::encode($leaveTypeOption['code'] ?? '') ?>"><?= Html::encode(($leaveTypeOption['code'] ?? '') . ' - ' . ($leaveTypeOption['title'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">ใช้กับฟิลด์ยอดลา เช่น ลามาแล้ว / รวมวันลาที่ใช้ได้</div>
                        </div>
                        <div id="approval-level-settings" class="d-none">
                            <hr class="my-2">
                            <p class="small fw-medium text-primary mb-2">ผู้อนุมัติ / สถานะผู้อนุมัติ</p>
                            <div class="mb-2">
                                <label class="form-label small">ระดับ (level)</label>
                                <select id="sel-approval-level" class="form-select">
                                    <option value="1">ระดับ 1</option>
                                    <option value="2">ระดับ 2</option>
                                    <option value="3">ระดับ 3</option>
                                    <option value="4">ระดับ 4</option>
                                </select>
                            </div>
                        </div>
                        <div id="approval-status-settings" class="d-none">
                            <hr class="my-2">
                            <p class="small fw-medium text-primary mb-2">สถานะผู้อนุมัติ</p>
                            <div class="mb-2">
                                <label class="form-label small">แสดงเมื่อสถานะเป็น</label>
                                <select id="sel-approval-show-when" class="form-select">
                                    <option value="">แสดงตามสถานะจริง (อนุมัติ/กำหนด/ไม่อนุมัติ)</option>
                                    <option value="approve">อนุมัติ (ช่องนี้ใส่เครื่องหมายเมื่อ Pass หรือ กำหนด)</option>
                                    <option value="reject">ไม่อนุมัติ (ช่องนี้ใส่เครื่องหมายเมื่อ Reject)</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">รูปแบบการแสดง</label>
                                <select id="sel-approval-display-style" class="form-select">
                                    <option value="text">ข้อความ (อนุมัติ / ไม่อนุมัติ)</option>
                                    <option value="checkmark">เครื่องหมายถูก/ผิด (✓ / ✗)</option>
                                    <option value="circle">วงกลมทึบ/โปร่ง (● / ○)</option>
                                </select>
                            </div>
                        </div>
                        <div id="travel-party-list-settings" class="d-none">
                            <hr class="my-2">
                            <p class="small fw-medium text-primary mb-2">รายการคณะเดินทาง (loop)</p>
                            <div class="mb-2">
                                <label class="form-label small">ระยะห่างระหว่างบรรทัด (%)</label>
                                <input type="number" id="sel-line-height-percent" class="form-control" min="1" max="20" step="0.5" placeholder="4" title="เปอร์เซ็นต์ของความสูงหน้า (เช่น 4 = 4%)">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">ตำแหน่งคอลัมน์ «ตำแหน่ง» (x %)</label>
                                <input type="number" id="sel-position-x-percent" class="form-control" min="0" max="100" step="1" placeholder="50" title="จุดเริ่มต้นคอลัมน์ตำแหน่ง (0–100%)">
                            </div>
                        </div>
                        <div id="companion-vertical-settings" class="d-none">
                            <hr class="my-2">
                            <p class="small fw-medium text-primary mb-2">ผู้ร่วมเดินทาง (แนวตั้ง)</p>
                            <div class="mb-2">
                                <label class="form-label small">ระยะห่างระหว่างบรรทัด (%)</label>
                                <input type="number" id="sel-companion-line-height" class="form-control" min="1" max="20" step="0.5" placeholder="4" title="เปอร์เซ็นต์ของความสูงหน้า (เช่น 4 = 4%)">
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sel-companion-show-position">
                                <label class="form-check-label small" for="sel-companion-show-position">แสดงตำแหน่งต่อท้ายชื่อด้วย</label>
                            </div>
                        </div>
                        <div id="signature-size-settings" class="d-none">
                            <hr class="my-2">
                            <p class="small fw-medium text-primary mb-2">ขนาดลายเซ็น</p>
                            <div class="mb-2">
                                <label class="form-label small">ความกว้าง (% ของความกว้างหน้า)</label>
                                <input type="number" id="sel-signature-width-percent" class="form-control" min="1" max="100" step="0.5" placeholder="20" title="เปอร์เซ็นต์ของความกว้างหน้า (เช่น 20 = 20%)">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">ความสูง (% ของความสูงหน้า)</label>
                                <input type="number" id="sel-signature-height-percent" class="form-control" min="1" max="100" step="0.5" placeholder="3" title="เปอร์เซ็นต์ของความสูงหน้า (เช่น 3 = 3%)">
                            </div>
                        </div>
                        <hr class="my-2">
                        <button type="button" id="btn-remove-field" class="btn btn-outline-danger btn-sm w-100">
                            <i class="bi bi-trash me-1"></i> ลบฟิลด์นี้ออก
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    pdfjsLib.GlobalWorkerOptions.workerSrc = '<?= Yii::getAlias('@web') ?>/libs/pdf/pdf.worker.js';
    var serveUrl = <?= json_encode($serveUrl) ?>;
    var dataSources = <?= json_encode($dataSources) ?>;
    var initialSelectedSourceId = <?= json_encode($selectedSourceId) ?>;
    var fieldsForSourceUrlBase = <?= json_encode($fieldsForSourceUrlBase) ?>;
    var developmentPrintDataUrl = <?= json_encode($developmentPrintDataUrl) ?>;
    var leavePrintDataUrl = <?= json_encode($leavePrintDataUrl) ?>;
    var bookingPrintDataUrl = <?= json_encode($bookingPrintDataUrl) ?>;
    var leaveTypeOptions = <?= json_encode($leaveTypeOptions) ?>;
    var saveUrl = <?= json_encode($saveUrl) ?>;
    var previewUrl = <?= json_encode($previewUrl) ?>;
    var csrfParam = <?= json_encode($csrf) ?>;
    var csrfToken = <?= json_encode($csrfToken) ?>;
    var initialLayout = <?= $layoutJson ?>;
    var fieldDefs = <?= $fieldDefsJson ?>;
    // Map field source key -> Thai label (for overlay when no realData)
    var fieldDefMap = {};
    (fieldDefs || []).forEach(function (fd) {
        var src = fd && (fd.source || fd.field || '');
        var lbl = fd && (fd.label || src);
        if (src) fieldDefMap[String(src)] = String(lbl);
    });
    var pageW = <?= json_encode($pageW) ?>;
    var pageH = <?= json_encode($pageH) ?>;

    var wrap = document.getElementById('canvas-wrap');
    var canvas = document.getElementById('pdf-canvas');
    var overlay = document.getElementById('field-overlay');
    var btnSave = document.getElementById('btn-save-layout');

    if (!wrap || !canvas || !overlay) return;

    var state = { layout: [], nextId: 1, selectedId: null, realData: null, pdfDoc: null, numPages: 1, currentPage: 1 };

    function normalizeText(s) {
        return (s || '').toString().toLowerCase().trim();
    }

    function filterFieldList(query) {
        var q = normalizeText(query);
        var items = document.querySelectorAll('#field-list .field-list-item');
        items.forEach(function (el) {
            var label = normalizeText(el.getAttribute('data-label') || el.textContent);
            var src = normalizeText(el.getAttribute('data-source') || el.getAttribute('data-field'));
            el.style.display = (!q || label.includes(q) || src.includes(q)) ? '' : 'none';
        });
    }

    // (removed) data source filter

    function clamp01(v) { return Math.max(0, Math.min(1, v)); }

    function toPercent(px, size) { return size <= 0 ? 0 : clamp01(px / size); }
    function toPx(percent, size) { return percent * size; }

    var A4_PT_W = 595.28;
    var A4_PT_H = 841.89;

    function fontSizeToCanvasPx(fontSizePt, canvasWidth) {
        var pt = parseInt(fontSizePt, 10);
        if (!Number.isFinite(pt)) pt = 14;
        pt = Math.max(6, Math.min(24, pt));
        return Math.max(6, pt * canvasWidth / A4_PT_W);
    }

    function loadPdf() {
        pdfjsLib.getDocument(serveUrl).promise.then(function(pdf) {
            state.pdfDoc = pdf;
            state.numPages = pdf.numPages || 1;
            var nav = document.getElementById('pdf-page-nav');
            var sel = document.getElementById('pdf-page-select');
            if (nav && sel) {
                sel.innerHTML = '';
                for (var p = 1; p <= state.numPages; p++) {
                    var opt = document.createElement('option');
                    opt.value = p;
                    opt.textContent = p;
                    sel.appendChild(opt);
                }
                if (state.numPages > 1) {
                    nav.classList.remove('d-none');
                    sel.addEventListener('change', function() {
                        var p = parseInt(this.value, 10);
                        if (p >= 1 && p <= state.numPages) loadPage(p);
                    });
                }
            }
            loadPage(1);
        }).catch(function(err) {
            overlay.innerHTML = '<div class="p-3 text-danger">โหลด PDF ไม่ได้: ' + (err.message || '') + '</div>';
        });
    }

    function loadPage(pageNum) {
        if (!state.pdfDoc || pageNum < 1 || pageNum > state.numPages) return;
        state.currentPage = pageNum;
        var sel = document.getElementById('pdf-page-select');
        if (sel && sel.value !== String(pageNum)) sel.value = pageNum;
        state.pdfDoc.getPage(pageNum).then(function(page) {
            var s = getWrapSize();
            if (s.w <= 0 || s.h <= 0) { s = { w: 560, h: 560 * A4_PT_H / A4_PT_W }; }
            var viewport = page.getViewport({ scale: 1 });
            var scale = Math.min(s.w / viewport.width, s.h / viewport.height);
            viewport = page.getViewport({ scale: scale });
            var viewW = viewport.width;
            var viewH = viewport.height;
            wrap.style.width = viewW + 'px';
            wrap.style.height = viewH + 'px';
            wrap.style.maxWidth = 'none';
            var ctx = canvas.getContext('2d');
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            canvas.style.width = viewW + 'px';
            canvas.style.height = viewH + 'px';
            page.render({ canvasContext: ctx, viewport: viewport }).promise.then(function() {
                renderOverlay();
            });
        });
    }

    function getWrapSize() {
        var r = wrap.getBoundingClientRect();
        return { w: r.width, h: r.height };
    }

    var LABEL_TO_KEY = {
        'ชื่อหน่วยงาน': 'organization_name',
        'เลขที่หนังสือ': 'document_number',
        'ข้อความกำหนดเอง': 'custom_text',
        'ชื่อผู้รับผิดชอบ': 'officer_name',
        'ชื่อผู้ขอ': 'officer_name',
        'ตำแหน่งผู้ขอ': 'officer_position',
        'ประเภทพนักงาน': 'employee_type',
        'ประเภทพนักงานผู้ขอ': 'officer_employee_type',
        'ประเภทพนักงานผู้รับผิดชอบ': 'officer_employee_type',
        'ลายเซ็นผู้ขอ': 'officer_signature',
        'ชื่อสกุลผู้มอบหมายงาน': 'assigned_to_fullname',
        'ตำแหน่งผู้มอบหมายงาน': 'assigned_to_position',
        'ประเภทพนักงานผู้มอบหมายงาน': 'assigned_to_employee_type',
        'ลายเซ็นผู้มอบหมายงาน': 'assigned_to_signature',
        'วันที่เอกสาร': 'document_date',
        'เรื่อง': 'topic',
        'สถานที่': 'location',
        'สถานที่จัดงาน': 'location',
        'หน่วยงานที่จัด': 'location_org',
        'จังหวัด': 'province_name',
        'พาหนะเดินทาง': 'vehicle_type_title',
        'ทะเบียนพาหนะเดินทาง': 'license_plate',
        'ระยะทาง': 'distance',
        'รวมค่าใช้จ่าย': 'total_expense',
        'ค่าลงทะเบียน': 'registration_amount',
        'ค่าที่พัก': 'accommodation_amount',
        'ค่ายานพาหนะ': 'vehicle_amount',
        'ค่าเบี้ยเลี้ยง': 'allowance_amount',
        'ค่าอื่น ๆ': 'other_amount',
        'วันที่เริ่ม': 'date_start',
        'วันที่สิ้นสุด': 'date_end',
        'คณะเดินทาง': 'travel_party',
        'รายการคณะเดินทาง (loop)': 'travel_party_list',
        'วันออกเดินทาง': 'vehicle_date_start',
        'เวลาออกเดินทาง': 'vehicle_time_start',
        'วันกลับ': 'vehicle_date_end',
        'เวลากลับ': 'vehicle_time_end',
        'นับวัน': 'trip_days',
        'ผู้อนุมัติ (ชื่อ-นามสกุล)': 'approver_fullname',
        'ผู้อนุมัติ (ตำแหน่ง)': 'approver_position',
        'ผู้อนุมัติ (ประเภทพนักงาน)': 'approver_employee_type',
        'ผู้อนุมัติ (วันที่อนุมัติ)': 'approver_approve_date',
        'ผู้อนุมัติ (ลายเซ็น)': 'approver_signature',
        'สถานะผู้อนุมัติ': 'approval_status',
        'ประเภทพนักงานผู้ขอใช้รถ': 'officer_employee_type',
        'ประเภทพนักงานผู้ขอลา': 'emp_employee_type',
        'ประเภทพนักงานผู้ปฏิบัติหน้าที่แทน': 'send_employee_type',
        'ประเภทพนักงานผู้แจ้งซ่อม': 'requester_employee_type',
        'ประเภทพนักงานช่างผู้ดำเนินการ': 'technician_employee_type',
        'ประเภทพนักงานหัวหน้ารับรอง': 'leader_employee_type',
        'ประเภทพนักงานขับ': 'driver_employee_type'
    };

    function isApproverOrStatusField(lookupKey) {
        return lookupKey === 'approval_status' || ['approver_fullname', 'approver_position', 'approver_employee_type', 'approver_approve_date', 'approver_signature'].indexOf(lookupKey) >= 0;
    }

    // ผู้ร่วมเดินทางแบบแนวตั้ง (รองรับตั้งระยะห่างบรรทัด + เลือกแสดงตำแหน่ง)
    function isCompanionVertical(path) {
        var key = LABEL_TO_KEY[path] || path;
        return key === 'companion_names_vertical' || key === 'companion_names_numbered';
    }

    function isSignatureField(lookupKey) {
        return typeof lookupKey === 'string' && lookupKey.length > 0 && lookupKey.lastIndexOf('_signature') === lookupKey.length - 10;
    }

    var DATE_FIELD_KEYS = ['document_date', 'date_start', 'date_end', 'vehicle_date_start', 'vehicle_date_end', 'approver_approve_date'];
    function isDateField(lookupKey) {
        return typeof lookupKey === 'string' && DATE_FIELD_KEYS.indexOf(lookupKey) >= 0;
    }

    var LEAVE_SUMMARY_FIELD_KEYS = ['last_days', 'total_days', 'ld', 'sum', 'leaveType', 'leave_type_id', 'leave_type_title'];
    function isLeaveSummaryField(lookupKey) {
        return typeof lookupKey === 'string' && LEAVE_SUMMARY_FIELD_KEYS.indexOf(lookupKey) >= 0;
    }

    function getLeaveTypeTitle(code) {
        if (!code || !leaveTypeOptions || !leaveTypeOptions.length) return '';
        for (var i = 0; i < leaveTypeOptions.length; i++) {
            if (String(leaveTypeOptions[i].code || '') === String(code)) {
                return String(leaveTypeOptions[i].title || '');
            }
        }
        return '';
    }

    function resolveLeaveSummaryValue(item, lookupKey) {
        var selectedCode = String((item && item.leave_type_id) || '');
        if (!selectedCode && state.realData && state.realData.leave_type_id) {
            selectedCode = String(state.realData.leave_type_id || '');
        }
        var summaryByType = state.realData && state.realData.leave_summary_by_type ? state.realData.leave_summary_by_type : null;
        var row = (summaryByType && selectedCode && summaryByType[selectedCode]) ? summaryByType[selectedCode] : null;
        if (lookupKey === 'leaveType' || lookupKey === 'leave_type_title') {
            return (row && row.title) ? String(row.title) : (getLeaveTypeTitle(selectedCode) || (state.realData && (state.realData.leave_type_title || state.realData.leaveType)) || '');
        }
        if (lookupKey === 'leave_type_id') {
            return selectedCode || (state.realData && String(state.realData.leave_type_id || '')) || '';
        }
        if (lookupKey === 'last_days') {
            return (row && row.last_leave_days != null && row.last_leave_days !== '') ? String(row.last_leave_days) : '';
        }
        if (lookupKey === 'total_days') {
            return (row && row.total_leave_days != null && row.total_leave_days !== '') ? String(row.total_leave_days) : '';
        }
        if (lookupKey === 'ld') {
            return (row && row.entitlement_days != null && row.entitlement_days !== '') ? String(row.entitlement_days) : '';
        }
        if (lookupKey === 'sum') {
            return (row && row.entitlement_total_days != null && row.entitlement_total_days !== '') ? String(row.entitlement_total_days) : '';
        }
        return '';
    }

    function formatApprovalStatus(status, style) {
        if (status === 'Pass') {
            if (style === 'checkmark') return '\u2713';
            if (style === 'circle') return '\u25CF';
            return 'อนุมัติ';
        }
        if (status === 'Reject') {
            if (style === 'checkmark') return '\u2717';
            if (style === 'circle') return '\u25CB';
            return 'ไม่อนุมัติ';
        }
        if (status === 'กำหนด') {
            if (style === 'checkmark') return '\u2713';
            if (style === 'circle') return '\u25CF';
            return 'กำหนด';
        }
        return '';
    }

    function resolveValue(obj, path) {
        if (!obj || !path || typeof path !== 'string') return '';
        var lookupPath = LABEL_TO_KEY[path] || path;
        var segs = lookupPath.split('.').filter(function(s) { return s; });
        var cur = obj;
        for (var i = 0; i < segs.length; i++) {
            if (cur == null) return '';
            cur = cur[segs[i]];
        }
        if (cur == null) return '';
        return typeof cur === 'object' ? JSON.stringify(cur) : String(cur);
    }

    function renderOverlay() {
        var s = getWrapSize();
        overlay.innerHTML = '';
        overlay.style.width = s.w + 'px';
        overlay.style.height = s.h + 'px';
        var currentPage = state.currentPage || 1;
        state.layout.forEach(function(item) {
            if ((parseInt(item.page, 10) || 1) !== currentPage) return;
            var left = toPx(item.x_percent, s.w);
            var top = toPx(item.y_percent, s.h);
            var width = Math.max(16, toPx(item.width_percent || 0.2, s.w));
            var path = (item.source || item.field || '').trim();
            var isList = path === 'travel_party_list' || (LABEL_TO_KEY[path] || path) === 'travel_party_list';
            var isCompVert = isCompanionVertical(path);
            var members = state.realData && state.realData.travel_party_members && Array.isArray(state.realData.travel_party_members) ? state.realData.travel_party_members : [];
            var lineH = (item.line_height_percent != null && item.line_height_percent !== '') ? parseFloat(item.line_height_percent) : 0.04;
            var rowCount = (isList || isCompVert) ? Math.max(1, members.length) : 1;
            var height = (isList || isCompVert) ? toPx(lineH * rowCount, s.h) : Math.max(20, toPx(item.height_percent || 0.03, s.h));
            var div = document.createElement('div');
            div.className = 'field-box' + (state.selectedId === item.id ? ' selected' : '');
            div.dataset.id = item.id;
            div.style.left = left + 'px';
            div.style.top = top + 'px';
            div.style.width = width + 'px';
            div.style.height = height + 'px';
            div.style.fontSize = fontSizeToCanvasPx(item.font_size, s.w) + 'px';
            div.style.lineHeight = '1.2';
            div.style.textAlign = item.alignment === 'C' ? 'center' : (item.alignment === 'R' ? 'right' : 'left');
            var lookupKey = LABEL_TO_KEY[path] || path;
            var level = (item.approval_level != null && item.approval_level >= 1 && item.approval_level <= 4) ? parseInt(item.approval_level, 10) : 1;
            var isApprovalStatus = lookupKey === 'approval_status';
            var isApproverField = isApproverOrStatusField(lookupKey);
            var text;
            if (isList && members.length > 0) {
                text = members.map(function(m) { return (m.fullname || '') + ' | ' + (m.position || ''); }).join('\n');
                div.style.whiteSpace = 'pre-line';
            } else if (isCompVert && members.length > 0) {
                var compNumbered = (LABEL_TO_KEY[path] || path) === 'companion_names_numbered';
                var compShowPos = !!item.companion_show_position;
                text = members.map(function(m, i) {
                    var line = (compNumbered ? (i + 1) + '. ' : '') + (m.fullname || '');
                    if (compShowPos && m.position) line += '  ' + m.position;
                    return line;
                }).join('\n');
                div.style.whiteSpace = 'pre-line';
            } else if (isApprovalStatus && state.realData) {
                var status = state.realData['approver_' + level + '_status'] || '';
                var showWhen = item.approval_show_when || '';
                if (showWhen === 'approve' && status !== 'Pass' && status !== 'กำหนด') status = '';
                if (showWhen === 'reject' && status !== 'Reject') status = '';
                var style = item.approval_display_style || 'text';
                text = formatApprovalStatus(status, style) || item.field_name || item.field;
            } else if (isLeaveSummaryField(lookupKey)) {
                text = resolveLeaveSummaryValue(item, lookupKey) || item.field_name || item.field;
            } else if (isApproverField && state.realData && lookupKey !== 'approval_status') {
                var suffix = lookupKey.replace('approver_', '');
                text = state.realData['approver_' + level + '_' + suffix] || '' || item.field_name || item.field;
            } else if (state.realData && path) {
                text = resolveValue(state.realData, path);
                if (!text) text = item.field_name || item.field;
            } else {
                var fallbackLabel = fieldDefMap[path] || item.field_name || item.field || 'ฟิลด์';
                text = isList ? 'รายการคณะเดินทาง (loop)' : (isApproverField ? fallbackLabel : fallbackLabel);
            }
            div.textContent = text || 'ฟิลด์';
            if (isCompVert) {
                div.style.whiteSpace = 'pre-line';
                div.style.overflow = 'hidden';
                div.style.textOverflow = 'clip';
                div.style.wordBreak = 'break-word';
            } else if (isList) {
                div.style.whiteSpace = 'normal';
                div.style.overflow = 'hidden';
                div.style.textOverflow = 'clip';
                div.style.wordBreak = 'break-word';
            } else {
                div.style.whiteSpace = 'nowrap';
                div.style.overflow = 'hidden';
                div.style.textOverflow = 'ellipsis';
                div.style.wordBreak = 'normal';
            }
            if (item.font_bold) div.style.fontWeight = 'bold';
            overlay.appendChild(div);
            attachDrag(div, item);
            div.addEventListener('click', function() {
                state.selectedId = item.id;
                overlay.querySelectorAll('.field-box').forEach(function(el) { el.classList.remove('selected'); });
                div.classList.add('selected');
                document.getElementById('sel-field-id').value = item.id;
                document.getElementById('sel-font-size').value = item.font_size || 14;
                var selBold = document.getElementById('sel-font-bold');
                if (selBold) selBold.value = item.font_bold ? '1' : '';
                document.getElementById('sel-alignment').value = item.alignment || 'L';
                document.getElementById('sel-page').value = item.page || 1;
                var selDateFormat = document.getElementById('sel-date-format');
                if (selDateFormat) selDateFormat.value = item.date_format || '';
                var lookupKeySel = LABEL_TO_KEY[item.source || item.field || ''] || (item.source || item.field || '');
                var isDateFieldSel = isDateField(lookupKeySel);
                var dateFormatBlock = document.getElementById('date-format-settings');
                if (dateFormatBlock) dateFormatBlock.classList.toggle('d-none', !isDateFieldSel);
                var isListField = (item.source || item.field || '') === 'travel_party_list';
                var listBlock = document.getElementById('travel-party-list-settings');
                if (listBlock) listBlock.classList.toggle('d-none', !isListField);
                var lineHeightInput = document.getElementById('sel-line-height-percent');
                var positionXInput = document.getElementById('sel-position-x-percent');
                if (lineHeightInput) lineHeightInput.value = isListField && (item.line_height_percent != null) ? (parseFloat(item.line_height_percent) * 100) : '4';
                if (positionXInput) positionXInput.value = isListField && (item.position_x_percent != null) ? (parseFloat(item.position_x_percent) * 100) : '50';
                var isCompVertField = isCompanionVertical(item.source || item.field || '');
                var compBlock = document.getElementById('companion-vertical-settings');
                if (compBlock) compBlock.classList.toggle('d-none', !isCompVertField);
                var compLineHeightInput = document.getElementById('sel-companion-line-height');
                if (compLineHeightInput) compLineHeightInput.value = isCompVertField && (item.line_height_percent != null) ? (parseFloat(item.line_height_percent) * 100) : '4';
                var compShowPosInput = document.getElementById('sel-companion-show-position');
                if (compShowPosInput) compShowPosInput.checked = isCompVertField && !!item.companion_show_position;
                var isLeaveField = isLeaveSummaryField(lookupKeySel);
                var leaveTypeBlock = document.getElementById('leave-type-settings');
                if (leaveTypeBlock) leaveTypeBlock.classList.toggle('d-none', !isLeaveField);
                var leaveTypeSel = document.getElementById('sel-leave-type-id');
                if (leaveTypeSel) leaveTypeSel.value = isLeaveField ? (item.leave_type_id || '') : '';
                var showLevel = isApproverOrStatusField(lookupKeySel);
                var approvalLevelBlock = document.getElementById('approval-level-settings');
                if (approvalLevelBlock) approvalLevelBlock.classList.toggle('d-none', !showLevel);
                var levelSel = document.getElementById('sel-approval-level');
                if (levelSel) levelSel.value = showLevel && (item.approval_level >= 1 && item.approval_level <= 4) ? String(item.approval_level) : '1';
                var isApprovalStatusField = lookupKeySel === 'approval_status';
                var approvalBlock = document.getElementById('approval-status-settings');
                if (approvalBlock) approvalBlock.classList.toggle('d-none', !isApprovalStatusField);
                var approvalShowWhenSel = document.getElementById('sel-approval-show-when');
                if (approvalShowWhenSel) approvalShowWhenSel.value = isApprovalStatusField && (item.approval_show_when === 'approve' || item.approval_show_when === 'reject') ? item.approval_show_when : '';
                var approvalStyleSel = document.getElementById('sel-approval-display-style');
                if (approvalStyleSel) approvalStyleSel.value = isApprovalStatusField && item.approval_display_style ? item.approval_display_style : 'text';
                var isSig = isSignatureField(lookupKeySel);
                var sigBlock = document.getElementById('signature-size-settings');
                if (sigBlock) sigBlock.classList.toggle('d-none', !isSig);
                var sigW = document.getElementById('sel-signature-width-percent');
                var sigH = document.getElementById('sel-signature-height-percent');
                var textWidthBlock = document.getElementById('text-width-settings');
                var textWidthInput = document.getElementById('sel-width-percent');
                if (textWidthBlock) textWidthBlock.classList.toggle('d-none', isSig);
                if (textWidthInput) textWidthInput.value = !isSig ? ((item.width_percent != null ? parseFloat(item.width_percent) : 0.2) * 100) : '';
                if (sigW) sigW.value = isSig ? ((item.width_percent != null ? parseFloat(item.width_percent) : 0.2) * 100) : '';
                if (sigH) sigH.value = isSig ? ((item.height_percent != null ? parseFloat(item.height_percent) : 0.03) * 100) : '';
                document.getElementById('field-settings-form').classList.remove('d-none');
            });
        });
    }

    function attachDrag(div, item) {
        var dragging = false, startX, startY, startLeft, startTop;
        div.addEventListener('mousedown', function(e) {
            if (e.button !== 0) return;
            e.preventDefault();
            dragging = true;
            startX = e.clientX;
            startY = e.clientY;
            startLeft = parseFloat(div.style.left) || 0;
            startTop = parseFloat(div.style.top) || 0;
        });
        document.addEventListener('mousemove', function(e) {
            if (!dragging || !div.parentNode) return;
            var s = getWrapSize();
            var newLeft = startLeft + (e.clientX - startX);
            var newTop = startTop + (e.clientY - startY);
            newLeft = Math.max(0, Math.min(s.w - parseFloat(div.style.width), newLeft));
            newTop = Math.max(0, Math.min(s.h - parseFloat(div.style.height), newTop));
            div.style.left = newLeft + 'px';
            div.style.top = newTop + 'px';
            var idx = state.layout.findIndex(function(x) { return x.id === item.id; });
            if (idx >= 0) {
                state.layout[idx].x_percent = toPercent(newLeft, s.w);
                state.layout[idx].y_percent = toPercent(newTop, s.h);
            }
            startX = e.clientX;
            startY = e.clientY;
            startLeft = newLeft;
            startTop = newTop;
        });
        document.addEventListener('mouseup', function() { dragging = false; });
    }

    function buildLayoutFromState() {
        return state.layout.map(function(item) {
            var src = item.source || item.field || item.field_name;
            var out = {
                field: item.field || item.field_name || src,
                page: parseInt(item.page, 10) || 1,
                x_percent: item.x_percent,
                y_percent: item.y_percent,
                width_percent: item.width_percent || 0.2,
                height_percent: item.height_percent || 0.03,
                font_size: parseInt(item.font_size, 10) || 14,
                font_bold: item.font_bold ? 1 : 0,
                alignment: item.alignment || 'L',
            };
            if (src) out.source = src;
            if (item.date_format) out.date_format = item.date_format;
            if (src === 'travel_party_list') {
                out.line_height_percent = item.line_height_percent != null ? item.line_height_percent : 0.04;
                out.position_x_percent = item.position_x_percent != null ? item.position_x_percent : 0.5;
            }
            if (isCompanionVertical(src)) {
                out.line_height_percent = item.line_height_percent != null ? item.line_height_percent : 0.04;
                out.companion_show_position = item.companion_show_position ? 1 : 0;
            }
            if (item.leave_type_id) {
                out.leave_type_id = item.leave_type_id;
            }
            if (src === 'approval_status') {
                out.approval_display_style = (item.approval_display_style && ['checkmark', 'circle', 'text'].indexOf(item.approval_display_style) >= 0) ? item.approval_display_style : 'text';
                out.approval_show_when = (item.approval_show_when === 'approve' || item.approval_show_when === 'reject') ? item.approval_show_when : '';
            }
            if (isApproverOrStatusField(src)) {
                out.approval_level = (item.approval_level >= 1 && item.approval_level <= 4) ? parseInt(item.approval_level, 10) : 1;
            }
            return out;
        });
    }

    function initFromServer() {
        if (initialLayout && initialLayout.length) {
            state.layout = initialLayout.map(function(item, i) {
                var src = item.source || item.field || item.field_name;
                var row = {
                    id: 'f' + (i + 1),
                    field: item.field || item.field_name || src,
                    field_name: item.field_name || item.field || item.label,
                    source: src,
                    page: item.page || 1,
                    x_percent: item.x_percent,
                    y_percent: item.y_percent,
                    width_percent: item.width_percent || 0.2,
                    height_percent: item.height_percent || 0.03,
                    font_size: item.font_size || 14,
                    font_bold: item.font_bold ? 1 : 0,
                    alignment: item.alignment || 'L',
                    date_format: item.date_format || '',
                    leave_type_id: item.leave_type_id || '',
                };
                if (src === 'travel_party_list') {
                    row.line_height_percent = item.line_height_percent != null ? item.line_height_percent : 0.04;
                    row.position_x_percent = item.position_x_percent != null ? item.position_x_percent : 0.5;
                }
                if (isCompanionVertical(src)) {
                    row.line_height_percent = item.line_height_percent != null ? item.line_height_percent : 0.04;
                    row.companion_show_position = item.companion_show_position ? 1 : 0;
                }
                if (src === 'approval_status') {
                    row.approval_display_style = (item.approval_display_style && ['checkmark', 'circle', 'text'].indexOf(item.approval_display_style) >= 0) ? item.approval_display_style : 'text';
                    row.approval_show_when = (item.approval_show_when === 'approve' || item.approval_show_when === 'reject') ? item.approval_show_when : '';
                }
                if (isApproverOrStatusField(src)) {
                    row.approval_level = (item.approval_level >= 1 && item.approval_level <= 4) ? parseInt(item.approval_level, 10) : 1;
                }
                return row;
            });
            state.nextId = state.layout.length + 1;
        }
    }

    document.getElementById('sel-date-format').addEventListener('change', function() {
        var id = document.getElementById('sel-field-id').value;
        var item = state.layout.find(function(x) { return String(x.id) === id; });
        if (item) item.date_format = this.value || '';
    });
    var selLeaveType = document.getElementById('sel-leave-type-id');
    if (selLeaveType) selLeaveType.addEventListener('change', function() {
        var id = document.getElementById('sel-field-id').value;
        var item = state.layout.find(function(x) { return String(x.id) === id; });
        if (item) item.leave_type_id = this.value || '';
        renderOverlay();
    });

    document.getElementById('btn-remove-field').addEventListener('click', function() {
        var id = document.getElementById('sel-field-id').value;
        if (!id) return;
        state.layout = state.layout.filter(function(x) { return String(x.id) !== id; });
        state.selectedId = null;
        document.getElementById('field-settings-form').classList.add('d-none');
        document.getElementById('sel-field-id').value = '';
        renderOverlay();
    });

    function updateSelectedFontSize(input, normalize) {
        var id = document.getElementById('sel-field-id').value;
        var item = state.layout.find(function(x) { return String(x.id) === id; });
        var value = parseInt(input.value, 10);
        if (!item || !Number.isFinite(value)) return;
        value = Math.max(6, Math.min(24, value));
        item.font_size = value;
        if (normalize) input.value = value;
        renderOverlay();
    }
    var selFontSize = document.getElementById('sel-font-size');
    selFontSize.addEventListener('input', function() {
        updateSelectedFontSize(this, false);
    });
    selFontSize.addEventListener('change', function() {
        updateSelectedFontSize(this, true);
    });
    var selFontBold = document.getElementById('sel-font-bold');
    if (selFontBold) selFontBold.addEventListener('change', function() {
        var id = document.getElementById('sel-field-id').value;
        var item = state.layout.find(function(x) { return String(x.id) === id; });
        if (item) item.font_bold = this.value === '1' ? 1 : 0;
        renderOverlay();
    });
    document.getElementById('sel-alignment').addEventListener('change', function() {
        var id = document.getElementById('sel-field-id').value;
        var item = state.layout.find(function(x) { return String(x.id) === id; });
        if (item) item.alignment = this.value;
        renderOverlay();
    });
    document.getElementById('sel-page').addEventListener('change', function() {
        var id = document.getElementById('sel-field-id').value;
        var item = state.layout.find(function(x) { return String(x.id) === id; });
        var p = parseInt(this.value, 10) || 1;
        if (item) item.page = p;
        if (p >= 1 && p <= state.numPages) loadPage(p);
    });
    var lineHeightEl = document.getElementById('sel-line-height-percent');
    if (lineHeightEl) lineHeightEl.addEventListener('change', function() {
        var id = document.getElementById('sel-field-id').value;
        var item = state.layout.find(function(x) { return String(x.id) === id; });
        if (item && (item.source || item.field) === 'travel_party_list') {
            var v = parseFloat(this.value);
            item.line_height_percent = isNaN(v) ? 0.04 : (v / 100);
            renderOverlay();
        }
    });
    var compLineHeightEl = document.getElementById('sel-companion-line-height');
    if (compLineHeightEl) compLineHeightEl.addEventListener('input', function() {
        var id = document.getElementById('sel-field-id').value;
        var item = state.layout.find(function(x) { return String(x.id) === id; });
        if (item && isCompanionVertical(item.source || item.field || '')) {
            var v = parseFloat(this.value);
            item.line_height_percent = isNaN(v) ? 0.04 : (v / 100);
            renderOverlay();
        }
    });
    var compShowPosEl = document.getElementById('sel-companion-show-position');
    if (compShowPosEl) compShowPosEl.addEventListener('change', function() {
        var id = document.getElementById('sel-field-id').value;
        var item = state.layout.find(function(x) { return String(x.id) === id; });
        if (item && isCompanionVertical(item.source || item.field || '')) {
            item.companion_show_position = this.checked ? 1 : 0;
            renderOverlay();
        }
    });
    var positionXEl = document.getElementById('sel-position-x-percent');
    if (positionXEl) positionXEl.addEventListener('change', function() {
        var id = document.getElementById('sel-field-id').value;
        var item = state.layout.find(function(x) { return String(x.id) === id; });
        if (item && (item.source || item.field) === 'travel_party_list') {
            var v = parseFloat(this.value);
            item.position_x_percent = isNaN(v) ? 0.5 : (v / 100);
            renderOverlay();
        }
    });
    var approvalStyleEl = document.getElementById('sel-approval-display-style');
    if (approvalStyleEl) approvalStyleEl.addEventListener('change', function() {
        var id = document.getElementById('sel-field-id').value;
        var item = state.layout.find(function(x) { return String(x.id) === id; });
        if (item && (LABEL_TO_KEY[item.source || item.field || ''] || item.source) === 'approval_status') {
            item.approval_display_style = this.value || 'text';
            renderOverlay();
        }
    });
    var approvalShowWhenEl = document.getElementById('sel-approval-show-when');
    if (approvalShowWhenEl) approvalShowWhenEl.addEventListener('change', function() {
        var id = document.getElementById('sel-field-id').value;
        var item = state.layout.find(function(x) { return String(x.id) === id; });
        if (item && (LABEL_TO_KEY[item.source || item.field || ''] || item.source) === 'approval_status') {
            item.approval_show_when = this.value === 'approve' || this.value === 'reject' ? this.value : '';
            renderOverlay();
        }
    });
    var approvalLevelEl = document.getElementById('sel-approval-level');
    if (approvalLevelEl) approvalLevelEl.addEventListener('change', function() {
        var id = document.getElementById('sel-field-id').value;
        var item = state.layout.find(function(x) { return String(x.id) === id; });
        var lookupKey = item ? (LABEL_TO_KEY[item.source || item.field || ''] || item.source) : '';
        if (item && isApproverOrStatusField(lookupKey)) {
            item.approval_level = parseInt(this.value, 10) || 1;
            renderOverlay();
        }
    });
    var sigWidthEl = document.getElementById('sel-signature-width-percent');
    if (sigWidthEl) sigWidthEl.addEventListener('change', function() {
        var id = document.getElementById('sel-field-id').value;
        var item = state.layout.find(function(x) { return String(x.id) === id; });
        var lookupKey = item ? (LABEL_TO_KEY[item.source || item.field || ''] || item.source) : '';
        if (item && isSignatureField(lookupKey)) {
            var v = parseFloat(this.value);
            item.width_percent = isNaN(v) ? 0.2 : clamp01(v / 100);
            renderOverlay();
        }
    });
    var sigHeightEl = document.getElementById('sel-signature-height-percent');
    if (sigHeightEl) sigHeightEl.addEventListener('change', function() {
        var id = document.getElementById('sel-field-id').value;
        var item = state.layout.find(function(x) { return String(x.id) === id; });
        var lookupKey = item ? (LABEL_TO_KEY[item.source || item.field || ''] || item.source) : '';
        if (item && isSignatureField(lookupKey)) {
            var v = parseFloat(this.value);
            item.height_percent = isNaN(v) ? 0.03 : clamp01(v / 100);
            renderOverlay();
        }
    });
    var textWidthEl = document.getElementById('sel-width-percent');
    if (textWidthEl) textWidthEl.addEventListener('change', function() {
        var id = document.getElementById('sel-field-id').value;
        var item = state.layout.find(function(x) { return String(x.id) === id; });
        var lookupKey = item ? (LABEL_TO_KEY[item.source || item.field || ''] || item.source) : '';
        if (item && !isSignatureField(lookupKey)) {
            var v = parseFloat(this.value);
            item.width_percent = isNaN(v) ? 0.2 : clamp01(v / 100);
            renderOverlay();
        }
    });

    function addFieldItem(el) {
        var source = el.dataset.source || el.dataset.field;
        var label = el.dataset.label || source;
        var s = getWrapSize();
        var newItem = {
            id: 'f' + (state.nextId++),
            field: source,
            field_name: label,
            source: source,
            page: state.currentPage || 1,
            x_percent: 0.1,
            y_percent: 0.1,
            width_percent: 0.2,
            height_percent: 0.03,
            font_size: 14,
            font_bold: 0,
            alignment: 'L',
            date_format: '',
        };
        if (source === 'travel_party_list') {
            newItem.line_height_percent = 0.04;
            newItem.position_x_percent = 0.5;
        }
        if (isCompanionVertical(source)) {
            newItem.line_height_percent = 0.04;
            newItem.companion_show_position = 0;
        }
        if (source === 'approval_status') {
            newItem.approval_display_style = 'text';
            newItem.approval_show_when = '';
        }
        if (isApproverOrStatusField(source)) {
            newItem.approval_level = 1;
        }
        state.layout.push(newItem);
        renderOverlay();
    }
    document.getElementById('field-list').addEventListener('click', function(e) {
        var el = e.target.closest('.field-list-item');
        if (el) addFieldItem(el);
    });
    function getPrintDataUrlForSource(sourceId) {
        if (sourceId === 'leave' && leavePrintDataUrl) return leavePrintDataUrl;
        if (sourceId === 'booking.vehicle.central' && bookingPrintDataUrl) return bookingPrintDataUrl;
        if (developmentPrintDataUrl) return developmentPrintDataUrl;
        return '';
    }
    function updateRealDataCardLabel() {
        var sel = document.getElementById('data-source-select');
        var sourceId = (sel ? sel.value : null) || (typeof initialSelectedSourceId !== 'undefined' ? initialSelectedSourceId : '');
        var hint = document.getElementById('real-data-hint');
        var input = document.getElementById('development-id-input');
        if (sourceId === 'leave') {
            if (hint) hint.textContent = 'ใส่รหัสใบลา (Leave ID) แล้วกดโหลด — ค่าจริงจะแสดงในกล่องฟิลด์บน PDF';
            if (input) input.placeholder = 'รหัสใบลา';
        } else if (sourceId === 'booking.vehicle.central') {
            if (hint) hint.textContent = 'ใส่รหัสคำขอรถส่วนกลาง (Vehicle ID) แล้วกดโหลด — ค่าจริงจะแสดงในกล่องฟิลด์บน PDF';
            if (input) input.placeholder = 'รหัสคำขอรถ (เช่น 125)';
        } else {
            if (hint) hint.textContent = 'ใส่รหัสรายการขอไปราชการ (Development ID) แล้วกดโหลด — ค่าจริงจะแสดงในกล่องฟิลด์บน PDF';
            if (input) input.placeholder = 'รหัสรายการ (เช่น 1138)';
        }
    }
    var btnLoadReal = document.getElementById('btn-load-real-data');
    if ((developmentPrintDataUrl || leavePrintDataUrl || bookingPrintDataUrl) && btnLoadReal) {
        var dsSelect = document.getElementById('data-source-select');
        if (dsSelect) { dsSelect.addEventListener('change', updateRealDataCardLabel); updateRealDataCardLabel(); }
        btnLoadReal.addEventListener('click', function() {
            var id = document.getElementById('development-id-input').value.trim();
            if (!id) { alert('กรุณาใส่รหัสรายการ'); return; }
            var sel = document.getElementById('data-source-select');
            var sourceId = (sel ? sel.value : null) || initialSelectedSourceId || '';
            var url = getPrintDataUrlForSource(sourceId);
            if (!url) { alert('ไม่มีการตั้งค่า URL สำหรับแหล่งข้อมูลนี้'); return; }
            var btn = this;
            btn.disabled = true;
            url = url + (url.indexOf('?') >= 0 ? '&' : '?') + 'id=' + encodeURIComponent(id);
            fetch(url, { credentials: 'same-origin' })
                .then(function(r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.text();
                })
                .then(function(t) {
                    var data;
                    try { data = JSON.parse(t); } catch (e) { throw new Error('ตอบกลับไม่ใช่ JSON'); }
                    if (data && data.error) { alert(data.error); return; }
                    state.realData = data;
                    renderOverlay();
                    if (document.getElementById('real-data-status')) document.getElementById('real-data-status').textContent = 'โหลดแล้ว — แสดงข้อมูลจริงบน overlay';
                })
                .catch(function(e) {
                    alert('โหลดข้อมูลไม่ได้: ' + (e.message || e));
                })
                .finally(function() { btn.disabled = false; });
        });
        document.getElementById('btn-clear-real-data').addEventListener('click', function() {
            state.realData = null;
            document.getElementById('development-id-input').value = '';
            if (document.getElementById('real-data-status')) document.getElementById('real-data-status').textContent = '';
            renderOverlay();
        });
    }
    if (dataSources && dataSources.length > 1 && fieldsForSourceUrlBase) {
        document.getElementById('data-source-select').addEventListener('change', function() {
            var sourceId = this.value;
            fetch(fieldsForSourceUrlBase + (fieldsForSourceUrlBase.indexOf('?') >= 0 ? '&' : '?') + 'source_id=' + encodeURIComponent(sourceId))
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    var list = document.getElementById('field-list');
                    list.innerHTML = '';
                    fieldDefMap = {};
                    (res.fields || []).forEach(function(fd) {
                        var src = fd.source || fd.field || '';
                        var lbl = fd.label || src;
                        if (src) fieldDefMap[String(src)] = String(lbl);
                        var div = document.createElement('div');
                        div.className = 'field-list-item border rounded-2 p-2 mb-1 small';
                        div.setAttribute('data-source', src);
                        div.setAttribute('data-field', src);
                        div.setAttribute('data-label', lbl);
                        div.textContent = lbl;
                        list.appendChild(div);
                    });
                    var q = document.getElementById('field-filter') ? document.getElementById('field-filter').value : '';
                    filterFieldList(q);
                })
                .catch(function() {});
        });
    }

    // Filters UI: field list only
    var fieldFilter = document.getElementById('field-filter');
    if (fieldFilter) {
        fieldFilter.addEventListener('input', function () {
            filterFieldList(this.value);
        });
        filterFieldList(fieldFilter.value || '');
    }

    btnSave.addEventListener('click', function() {
        var payload = { fields: buildLayoutFromState(), [csrfParam]: csrfToken };
        var dsSelect = document.getElementById('data-source-select');
        if (dsSelect) payload.data_source_id = dsSelect.value || '';
        btnSave.disabled = true;
        fetch(saveUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(payload),
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) alert('บันทึกตำแหน่งเรียบร้อย');
            else alert(res.message || 'บันทึกไม่สำเร็จ');
        })
        .catch(function() { alert('เกิดข้อผิดพลาด'); })
        .finally(function() { btnSave.disabled = false; });
    });

    initFromServer();
    loadPdf();
    window.addEventListener('resize', function() { renderOverlay(); });
})();
</script>
