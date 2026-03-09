<?php
use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Categorise $model */
/** @var array $config */
/** @var array $items */
/** @var array $fieldLabels */
/** @var string $templateUrl */
/** @var array $signatureKeys */

$signatureKeys = $signatureKeys ?? [];
$memberStartX   = (float)($config['member_fullname_start_x'] ?? 25);
$memberStartY   = (float)($config['member_fullname_start_y'] ?? 85);
$positionStartX = (float)($config['member_position_start_x'] ?? 100);
$positionStartY = (float)($config['member_position_start_y'] ?? 85);
$lineSpacing    = (float)($config['line_spacing'] ?? 5.5);
$memberFontSize = (int)($config['member_font_size'] ?? 14);
$memberBold     = !empty($config['member_bold']);

$this->title = 'กำหนดตำแหน่งข้อมูลบน PDF — Template รายงานขอไปราชการ';
$this->params['breadcrumbs'][] = ['label' => 'อบรม/ประชุม/ดูงาน', 'url' => ['/hr/development/index']];
$this->params['breadcrumbs'][] = ['label' => 'Template รายงานขอไปราชการ', 'url' => ['/hr/development/pdf-editor']];
$this->params['breadcrumbs'][] = 'กำหนดตำแหน่ง';

$this->registerCssFile(Url::to('@web/css/thsarabunnew.css'), ['depends' => [\yii\web\YiiAsset::class]]);
$this->registerCss('.leave-field-chip { font-family: "THSarabunNew", sans-serif; background: transparent !important; border: none !important; box-shadow: none !important; padding: 0 !important; color: #0d6efd !important; }');

$scale = 3;
$pageW = 210;
$pageH = 297;
$canvasW = $pageW * $scale;
$canvasH = $pageH * $scale;
$pageWpt = 595.276;
$fontDisplayScale = ($canvasW / $pageWpt) * 0.55;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-geo-alt"></i> <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('menu', ['active' => 'setting-template']) ?>
<?php $this->endBlock(); ?>

<?php if (Yii::$app->session->hasFlash('success')): ?>
<div class="alert alert-success alert-dismissible fade show mb-3">
    <?= Yii::$app->session->getFlash('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
<div class="alert alert-danger alert-dismissible fade show mb-3">
    <?= Yii::$app->session->getFlash('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="alert alert-info border-0 rounded-3 mb-3 d-flex align-items-start gap-2" role="status">
    <i class="bi bi-info-circle fs-5 text-info flex-shrink-0 mt-1"></i>
    <div class="small">
        <strong>การพิมพ์ใบขอไปราชการ</strong> — หลังบันทึกตำแหน่งแล้ว ไปที่ <strong>รายการขอไปราชการ</strong> แล้วกดปุ่ม «พิมพ์» ที่รายการที่ต้องการ
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-primary bg-opacity-10 text-primary border-0 py-3 px-4 rounded-top-3">
        <h6 class="mb-0 small fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-hand-index-thumb"></i> ลากฟิลด์ไปวางบนพื้นที่เทมเพลต (A4) ให้ตรงตำแหน่งที่ต้องการ
        </h6>
    </div>
    <div class="card-body p-4">
        <div id="positions-alert" class="alert d-none mb-3"></div>
        <?php $currentDateFormat = $config['date_format'] ?? 'medium'; ?>
        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-body">รูปแบบวันที่บน PDF</label>
                    <select name="date_format" id="date-format-select" class="form-select mb-3" aria-label="รูปแบบวันที่">
                        <option value="medium" <?= $currentDateFormat === 'medium' ? 'selected' : '' ?>>12 มกราคม 2569</option>
                        <option value="short" <?= $currentDateFormat === 'short' ? 'selected' : '' ?>>12 ม.ค. 2569</option>
                        <option value="numeric" <?= $currentDateFormat === 'numeric' ? 'selected' : '' ?>>12/01/2569</option>
                        <option value="long" <?= $currentDateFormat === 'long' ? 'selected' : '' ?>>วันอาทิตย์ที่ 12 มกราคม พ.ศ. 2569</option>
                    </select>
                </div>
                <div class="card border border-secondary border-opacity-25 rounded-3 mb-3">
                    <div class="card-header bg-primary bg-opacity-10 py-2 px-3">
                        <h6 class="mb-0 small fw-semibold text-primary"><i class="bi bi-people me-1"></i> ส่วนคณะเดินทาง (รายชื่อ)</h6>
                    </div>
                    <div class="card-body p-3">
                        <p class="small text-muted mb-2">กำหนดจุดเริ่มต้นคอลัมน์ «ชื่อ» และ «ตำแหน่ง» ของรายชื่อคณะเดินทาง (ด้วยข้าพเจ้า) — ลากชิป «ชื่อ(คณะเดินทาง)» และ «ตำแหน่ง(คณะเดินทาง)» บนเทมเพลตด้านขวาได้ หรือกรอกตัวเลขด้านล่าง</p>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small mb-0">ชื่อ (X)</label>
                                <input type="number" step="0.1" id="member_fullname_start_x" class="form-control" value="<?= Html::encode($memberStartX) ?>" aria-label="จุดเริ่มต้นชื่อ X">
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-0">ชื่อ (Y)</label>
                                <input type="number" step="0.1" id="member_fullname_start_y" class="form-control" value="<?= Html::encode($memberStartY) ?>" aria-label="จุดเริ่มต้นชื่อ Y">
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small mb-0">ตำแหน่ง (X)</label>
                                <input type="number" step="0.1" id="member_position_start_x" class="form-control" value="<?= Html::encode($positionStartX) ?>" aria-label="จุดเริ่มต้นตำแหน่ง X">
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-0">ตำแหน่ง (Y)</label>
                                <input type="number" step="0.1" id="member_position_start_y" class="form-control" value="<?= Html::encode($positionStartY) ?>" aria-label="จุดเริ่มต้นตำแหน่ง Y">
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small mb-0">ขนาดตัวอักษร</label>
                                <input type="number" min="6" max="24" id="member_font_size" class="form-control" value="<?= (int)$memberFontSize ?>" aria-label="ขนาดตัวอักษรคณะเดินทาง">
                            </div>
                            <div class="col-6 d-flex align-items-end pb-2">
                                <div class="form-check">
                                    <input type="checkbox" id="member_bold" class="form-check-input" value="1" <?= $memberBold ? 'checked' : '' ?> aria-label="ตัวหนา">
                                    <label class="form-check-label small mb-0" for="member_bold">หนา</label>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="form-label small mb-0">ระยะห่างระหว่างบรรทัด (mm)</label>
                            <input type="number" step="0.1" min="3" max="15" id="line_spacing" class="form-control" value="<?= Html::encode($lineSpacing) ?>" aria-label="ระยะห่างบรรทัด">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-body">ตำแหน่งข้อมูลบนเทมเพลต</label>
                    <p class="small text-muted mb-2">เพิ่มได้หลายจุดต่อฟิลด์ เลือกประเภทฟิลด์ ปรับขนาด/ความหนา checkbox แสดง แล้วลากชิปไปวางบนเทมเพลต</p>
                </div>
                <form id="positions-form">
                    <div id="positions-rows" class="d-flex flex-column gap-2 mb-3">
                        <?php foreach ($items as $item): ?>
                        <?php
                            $itemId = $item['id'];
                            $key = $item['key'];
                            $x = (float) ($item['x'] ?? 0);
                            $y = (float) ($item['y'] ?? 0);
                            $fontSize = (int) ($item['fontSize'] ?? 15);
                            $bold = !empty($item['bold']);
                            $enabled = isset($item['enabled']) ? (int) $item['enabled'] : 1;
                            $label = $item['label'] ?? $key;
                            $isSignature = in_array($key, $signatureKeys, true);
                            $width  = (float) ($item['width'] ?? 35);
                            $height = (float) ($item['height'] ?? 15);
                        ?>
                        <div class="position-row d-flex align-items-center gap-2 p-2 rounded-3 border border-1 border-secondary border-opacity-25 flex-wrap <?= !$enabled ? 'opacity-75' : '' ?>" data-item-id="<?= Html::encode($itemId) ?>">
                            <div class="d-flex align-items-center gap-1 flex-grow-1" style="min-width: 0;">
                                <select name="positions[<?= Html::encode($itemId) ?>][key]" class="form-select position-key-select" style="width: auto; min-width: 10rem;" aria-label="ประเภทฟิลด์" data-item-id="<?= Html::encode($itemId) ?>">
                                    <?php foreach ($fieldLabels as $fk => $fl): ?>
                                    <option value="<?= Html::encode($fk) ?>" <?= $fk === $key ? 'selected' : '' ?>><?= Html::encode($fl['label'] ?? $fk) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="positions[<?= Html::encode($itemId) ?>][enabled]" value="0">
                                <div class="form-check mb-0 flex-shrink-0">
                                    <input type="checkbox" name="positions[<?= Html::encode($itemId) ?>][enabled]" value="1" class="form-check-input field-enabled-cb" data-item-id="<?= Html::encode($itemId) ?>" <?= $enabled ? 'checked' : '' ?> aria-label="แสดงบนเทมเพลต">
                                    <label class="form-check-label small text-muted mb-0">แสดง</label>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-1 text-size-wrap <?= $isSignature ? 'd-none' : '' ?>">
                                <label class="small text-muted mb-0 me-1">ขนาด</label>
                                <input type="number" min="6" max="24" name="positions[<?= Html::encode($itemId) ?>][fontSize]" value="<?= (int) $fontSize ?>" class="form-control position-font-size" style="width: 3.5rem;" data-item-id="<?= Html::encode($itemId) ?>" aria-label="ขนาดตัวอักษร">
                            </div>
                            <div class="d-flex align-items-center gap-1 signature-size-wrap <?= !$isSignature ? 'd-none' : '' ?>">
                                <label class="small text-muted mb-0 me-1">กว้าง (mm)</label>
                                <input type="number" min="5" max="100" step="0.5" name="positions[<?= Html::encode($itemId) ?>][width]" value="<?= Html::encode($width) ?>" class="form-control position-width" style="width: 3.5rem;" data-item-id="<?= Html::encode($itemId) ?>" aria-label="ความกว้างลายเซ็น">
                                <label class="small text-muted mb-0 me-1">สูง (mm)</label>
                                <input type="number" min="5" max="80" step="0.5" name="positions[<?= Html::encode($itemId) ?>][height]" value="<?= Html::encode($height) ?>" class="form-control position-height" style="width: 3.5rem;" data-item-id="<?= Html::encode($itemId) ?>" aria-label="ความสูงลายเซ็น">
                            </div>
                            <div class="d-flex align-items-center gap-1 text-bold-wrap <?= $isSignature ? 'd-none' : '' ?>">
                                <input type="hidden" name="positions[<?= Html::encode($itemId) ?>][bold]" value="0">
                                <div class="form-check mb-0">
                                    <input type="checkbox" name="positions[<?= Html::encode($itemId) ?>][bold]" value="1" class="form-check-input position-bold" data-item-id="<?= Html::encode($itemId) ?>" <?= $bold ? 'checked' : '' ?> aria-label="ตัวหนา">
                                    <label class="form-check-label small text-muted mb-0">หนา</label>
                                </div>
                            </div>
                            <input type="hidden" name="positions[<?= Html::encode($itemId) ?>][x]" value="<?= Html::encode($x) ?>" data-pos-x data-item-id="<?= Html::encode($itemId) ?>">
                            <input type="hidden" name="positions[<?= Html::encode($itemId) ?>][y]" value="<?= Html::encode($y) ?>" data-pos-y data-item-id="<?= Html::encode($itemId) ?>">
                            <button type="button" class="btn btn-outline-danger btn-sm position-remove" data-item-id="<?= Html::encode($itemId) ?>" aria-label="ลบตำแหน่ง"><i class="bi bi-trash"></i></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary rounded-3" id="btn-add-position"><i class="bi bi-plus-lg me-1"></i> เพิ่มตำแหน่ง</button>
                    </div>
                    <template id="position-row-tpl">
                        <div class="position-row d-flex align-items-center gap-2 p-2 rounded-3 border border-1 border-secondary border-opacity-25 flex-wrap" data-item-id="__ITEM_ID__">
                            <div class="d-flex align-items-center gap-1 flex-grow-1" style="min-width: 0;">
                                <select name="positions[__ITEM_ID__][key]" class="form-select position-key-select" style="width: auto; min-width: 10rem;" aria-label="ประเภทฟิลด์" data-item-id="__ITEM_ID__"></select>
                                <input type="hidden" name="positions[__ITEM_ID__][enabled]" value="0">
                                <div class="form-check mb-0 flex-shrink-0">
                                    <input type="checkbox" name="positions[__ITEM_ID__][enabled]" value="1" class="form-check-input field-enabled-cb" data-item-id="__ITEM_ID__" aria-label="แสดงบนเทมเพลต">
                                    <label class="form-check-label small text-muted mb-0">แสดง</label>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-1 text-size-wrap">
                                <label class="small text-muted mb-0 me-1">ขนาด</label>
                                <input type="number" min="6" max="24" name="positions[__ITEM_ID__][fontSize]" value="15" class="form-control position-font-size" style="width: 3.5rem;" data-item-id="__ITEM_ID__" aria-label="ขนาดตัวอักษร">
                            </div>
                            <div class="d-flex align-items-center gap-1 signature-size-wrap d-none">
                                <label class="small text-muted mb-0 me-1">กว้าง (mm)</label>
                                <input type="number" min="5" max="100" step="0.5" name="positions[__ITEM_ID__][width]" value="35" class="form-control position-width" style="width: 3.5rem;" data-item-id="__ITEM_ID__" aria-label="ความกว้างลายเซ็น">
                                <label class="small text-muted mb-0 me-1">สูง (mm)</label>
                                <input type="number" min="5" max="80" step="0.5" name="positions[__ITEM_ID__][height]" value="15" class="form-control position-height" style="width: 3.5rem;" data-item-id="__ITEM_ID__" aria-label="ความสูงลายเซ็น">
                            </div>
                            <div class="d-flex align-items-center gap-1 text-bold-wrap">
                                <input type="hidden" name="positions[__ITEM_ID__][bold]" value="0">
                                <div class="form-check mb-0">
                                    <input type="checkbox" name="positions[__ITEM_ID__][bold]" value="1" class="form-check-input position-bold" data-item-id="__ITEM_ID__" aria-label="ตัวหนา">
                                    <label class="form-check-label small text-muted mb-0">หนา</label>
                                </div>
                            </div>
                            <input type="hidden" name="positions[__ITEM_ID__][x]" value="0" data-pos-x data-item-id="__ITEM_ID__">
                            <input type="hidden" name="positions[__ITEM_ID__][y]" value="0" data-pos-y data-item-id="__ITEM_ID__">
                            <button type="button" class="btn btn-outline-danger btn-sm position-remove" data-item-id="__ITEM_ID__" aria-label="ลบตำแหน่ง"><i class="bi bi-trash"></i></button>
                        </div>
                    </template>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <button type="submit" class="btn btn-primary rounded-3 px-3" id="btn-save-positions">
                            <i class="bi bi-check-lg me-1"></i> บันทึกตำแหน่ง
                        </button>
                        <?= Html::a('<i class="bi bi-download me-1"></i> ส่งออกการตั้งค่า', ['/hr/development/export-pdf-settings'], ['class' => 'btn btn-outline-success rounded-3']) ?>
                        <button type="button" class="btn btn-outline-info rounded-3" data-bs-toggle="modal" data-bs-target="#import-settings-modal" aria-label="นำเข้าการตั้งค่า">
                            <i class="bi bi-upload me-1"></i> นำเข้าการตั้งค่า
                        </button>
                        <?= Html::a('ย้อนกลับ', ['/hr/development/pdf-editor'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
                    </div>
                </form>

                <!-- Modal นำเข้าการตั้งค่า -->
                <div class="modal fade" id="import-settings-modal" tabindex="-1" aria-labelledby="import-settings-modal-label" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-3">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-semibold" id="import-settings-modal-label"><i class="bi bi-upload me-2"></i>นำเข้าการตั้งค่า</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                            </div>
                            <div class="modal-body pt-2">
                                <p class="small text-muted mb-3">เลือกไฟล์ JSON ที่ส่งออกจากการตั้งค่า (หรือจากเครื่องอื่น) การนำเข้าจะแทนที่การตั้งค่าปัจจุบัน</p>
                                <?php $importUrl = \yii\helpers\Url::to(['/hr/development/import-pdf-settings']); ?>
                                <form action="<?= Html::encode($importUrl) ?>" method="post" enctype="multipart/form-data" id="import-settings-form">
                                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Html::encode(Yii::$app->request->csrfToken) ?>">
                                    <div class="mb-3">
                                        <label for="settings_file" class="form-label small fw-semibold">ไฟล์การตั้งค่า (.json)</label>
                                        <input type="file" name="settings_file" id="settings_file" class="form-control" accept=".json,application/json" required aria-required="true">
                                    </div>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">ยกเลิก</button>
                                        <button type="submit" class="btn btn-primary rounded-3"><i class="bi bi-upload me-1"></i> นำเข้า</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-8">
                <p class="small text-muted mb-2">พื้นที่เทมเพลต (A4) — พื้นหลังเป็น PDF ลากชิปไปวางให้ตรงตำแหน่ง</p>
                <div class="border rounded-3 overflow-auto bg-secondary bg-opacity-10 d-flex justify-content-center p-3" style="max-height: min(920px, 85vh);">
                    <div id="pdf-canvas-wrapper" class="position-relative shadow-sm" style="width: <?= (int) $canvasW ?>px; height: <?= (int) $canvasH ?>px;">
                        <iframe src="<?= Html::encode($templateUrl) ?>#toolbar=0" class="position-absolute top-0 start-0 w-100 h-100 border-0" style="pointer-events: none; z-index: 0;" title="เทมเพลต PDF (พื้นหลัง)"></iframe>
                        <div id="pdf-canvas" class="position-absolute top-0 start-0 w-100 h-100" style="z-index: 1; background: transparent;" data-font-display-scale="<?= Html::encode($fontDisplayScale) ?>">
                        <?php foreach ($items as $item): ?>
                        <?php
                            $itemId = $item['id'];
                            $key = $item['key'];
                            $enabled = isset($item['enabled']) ? (int) $item['enabled'] : 1;
                            $x = (float) ($item['x'] ?? 0);
                            $y = (float) ($item['y'] ?? 0);
                            $fontSize = (int) ($item['fontSize'] ?? 15);
                            $bold = !empty($item['bold']);
                            $label = $item['label'] ?? $key;
                            $left = round($x * $scale);
                            $top = round($y * $scale);
                            $chipFontSizePt = round($fontSize * $fontDisplayScale, 1);
                            $chipFontWeight = $bold ? 'bold' : 'normal';
                        ?>
                        <div class="position-absolute leave-field-chip text-primary user-select-none <?= !$enabled ? 'd-none' : '' ?>"
                             data-item-id="<?= Html::encode($itemId) ?>"
                             data-field-key="<?= Html::encode($key) ?>"
                             style="left: <?= (int) $left ?>px; top: <?= (int) $top ?>px; cursor: grab; font-family: 'THSarabunNew', sans-serif; font-size: <?= Html::encode($chipFontSizePt) ?>pt; font-weight: <?= Html::encode($chipFontWeight) ?>;"
                             title="<?= Html::encode($label) ?>"
                             draggable="false">
                            <span class="chip-label text-truncate d-inline-block" style="max-width: 140px;"><?= Html::encode($label) ?></span>
                        </div>
                        <?php endforeach; ?>
                        <?php
                        $memberNameLeft = round((float)$memberStartX * $scale);
                        $memberNameTop  = round((float)$memberStartY * $scale);
                        $memberPosLeft  = round((float)$positionStartX * $scale);
                        $memberPosTop   = round((float)$positionStartY * $scale);
                        ?>
                        <div class="position-absolute leave-field-chip member-position-chip text-info user-select-none border border-info border-opacity-50 rounded-1 px-1"
                             data-member-field="fullname"
                             style="left: <?= (int) $memberNameLeft ?>px; top: <?= (int) $memberNameTop ?>px; cursor: grab; font-family: 'THSarabunNew', sans-serif; font-size: 11px;"
                             title="จุดเริ่มต้นคอลัมน์ชื่อคณะเดินทาง — ลากวางได้">
                            <span class="chip-label">ชื่อ(คณะเดินทาง)</span>
                        </div>
                        <div class="position-absolute leave-field-chip member-position-chip text-info user-select-none border border-info border-opacity-50 rounded-1 px-1"
                             data-member-field="position"
                             style="left: <?= (int) $memberPosLeft ?>px; top: <?= (int) $memberPosTop ?>px; cursor: grab; font-family: 'THSarabunNew', sans-serif; font-size: 11px;"
                             title="จุดเริ่มต้นคอลัมน์ตำแหน่งคณะเดินทาง — ลากวางได้">
                            <span class="chip-label">ตำแหน่ง(คณะเดินทาง)</span>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$saveUrl = Url::to(['/hr/development/save-positions']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$scaleJs = $scale;
$pageWJs = $pageW;
$pageHJs = $pageH;
$fieldLabelsJson = json_encode(array_map(function ($v) { return $v['label'] ?? ''; }, $fieldLabels));
$fieldKeysJson = json_encode(array_keys($fieldLabels));
$signatureKeysJson = json_encode($signatureKeys);
$this->registerJs(<<<JS
(function() {
    var scale = {$scaleJs};
    var pageW = {$pageWJs};
    var pageH = {$pageHJs};
    var fieldLabels = {$fieldLabelsJson};
    var fieldKeys = {$fieldKeysJson};
    var signatureKeys = {$signatureKeysJson};
    var canvas = document.getElementById('pdf-canvas');
    var fontDisplayScale = parseFloat(canvas.getAttribute('data-font-display-scale')) || (420 / 595.276);
    var form = document.getElementById('positions-form');
    var btn = document.getElementById('btn-save-positions');
    var alertEl = document.getElementById('positions-alert');
    var rowsContainer = document.getElementById('positions-rows');
    var rowTpl = document.getElementById('position-row-tpl');
    var addBtn = document.getElementById('btn-add-position');
    if (!canvas || !form || !btn || !rowsContainer || !rowTpl) return;

    function nextItemId() {
        return 'item_' + Date.now() + '_' + Math.random().toString(36).slice(2, 9);
    }

    function pxToMm(px, isY) {
        var v = px / scale;
        var max = isY ? pageH : pageW;
        return Math.round(Math.max(0, Math.min(max, v)) * 10) / 10;
    }

    function updateHiddenInput(itemId, x, y) {
        var xInput = form.querySelector('input[name="positions[' + itemId + '][x]"]');
        var yInput = form.querySelector('input[name="positions[' + itemId + '][y]"]');
        if (xInput) xInput.value = x;
        if (yInput) yInput.value = y;
    }

    function updateMemberInputs(field, xMm, yMm) {
        var xId = field === 'fullname' ? 'member_fullname_start_x' : 'member_position_start_x';
        var yId = field === 'fullname' ? 'member_fullname_start_y' : 'member_position_start_y';
        var xEl = document.getElementById(xId);
        var yEl = document.getElementById(yId);
        if (xEl) xEl.value = Math.round(xMm * 10) / 10;
        if (yEl) yEl.value = Math.round(yMm * 10) / 10;
    }

    function getLabelForKey(key) {
        return fieldLabels[key] !== undefined ? fieldLabels[key] : key;
    }

    function isSignatureKey(key) {
        return signatureKeys && signatureKeys.indexOf(key) >= 0;
    }

    function toggleSignatureRow(row, key) {
        var isSig = isSignatureKey(key);
        var textSize = row.querySelector('.text-size-wrap');
        var sigSize = row.querySelector('.signature-size-wrap');
        var textBold = row.querySelector('.text-bold-wrap');
        if (textSize) textSize.classList.toggle('d-none', isSig);
        if (sigSize) sigSize.classList.toggle('d-none', !isSig);
        if (textBold) textBold.classList.toggle('d-none', isSig);
    }

    function updateChipLabel(chip, key) {
        var span = chip.querySelector('.chip-label');
        if (span) span.textContent = getLabelForKey(key);
        chip.setAttribute('data-field-key', key);
        chip.title = getLabelForKey(key);
    }

    function updateChipFont(itemId, fontSize, bold) {
        var chip = canvas.querySelector('.leave-field-chip[data-item-id="' + itemId + '"]');
        if (!chip) return;
        var sizePt = (fontSize || 15) * fontDisplayScale;
        chip.style.fontSize = sizePt.toFixed(1) + 'pt';
        chip.style.fontWeight = bold ? 'bold' : 'normal';
    }

    function attachChipDrag(chip) {
        var itemId = chip.getAttribute('data-item-id');
        var memberField = chip.getAttribute('data-member-field');
        var dragging = false;
        var startX, startY, startLeft, startTop;

        chip.addEventListener('mousedown', function(e) {
            if (e.button !== 0) return;
            e.preventDefault();
            dragging = true;
            startX = e.clientX;
            startY = e.clientY;
            startLeft = parseInt(chip.style.left, 10) || 0;
            startTop = parseInt(chip.style.top, 10) || 0;
            chip.style.cursor = 'grabbing';
        });

        document.addEventListener('mousemove', function(e) {
            if (!dragging || !chip.parentNode) return;
            var dx = e.clientX - startX;
            var dy = e.clientY - startY;
            var newLeft = Math.max(0, Math.min(canvas.offsetWidth - chip.offsetWidth, startLeft + dx));
            var newTop = Math.max(0, Math.min(canvas.offsetHeight - chip.offsetHeight, startTop + dy));
            chip.style.left = newLeft + 'px';
            chip.style.top = newTop + 'px';
            if (memberField) {
                updateMemberInputs(memberField, pxToMm(newLeft, false), pxToMm(newTop, true));
            } else if (itemId) {
                updateHiddenInput(itemId, pxToMm(newLeft, false), pxToMm(newTop, true));
            }
            startX = e.clientX;
            startY = e.clientY;
            startLeft = newLeft;
            startTop = newTop;
        });

        document.addEventListener('mouseup', function() {
            if (dragging) {
                dragging = false;
                chip.style.cursor = 'grab';
            }
        });
    }

    canvas.querySelectorAll('.leave-field-chip').forEach(attachChipDrag);

    function syncMemberChipsFromInputs() {
        var mX = document.getElementById('member_fullname_start_x');
        var mY = document.getElementById('member_fullname_start_y');
        var pX = document.getElementById('member_position_start_x');
        var pY = document.getElementById('member_position_start_y');
        var chipName = canvas.querySelector('.member-position-chip[data-member-field="fullname"]');
        var chipPos = canvas.querySelector('.member-position-chip[data-member-field="position"]');
        if (chipName && mX && mY) {
            chipName.style.left = (parseFloat(mX.value) || 0) * scale + 'px';
            chipName.style.top = (parseFloat(mY.value) || 0) * scale + 'px';
        }
        if (chipPos && pX && pY) {
            chipPos.style.left = (parseFloat(pX.value) || 0) * scale + 'px';
            chipPos.style.top = (parseFloat(pY.value) || 0) * scale + 'px';
        }
    }
    ['member_fullname_start_x', 'member_fullname_start_y', 'member_position_start_x', 'member_position_start_y'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', syncMemberChipsFromInputs);
    });

    function doSave() {
        var positions = {};
        rowsContainer.querySelectorAll('.position-row').forEach(function(row) {
            var itemId = row.getAttribute('data-item-id');
            if (!itemId) return;
            var keyInput = form.querySelector('select[name="positions[' + itemId + '][key]"]');
            var xInput = form.querySelector('input[name="positions[' + itemId + '][x]"]');
            var yInput = form.querySelector('input[name="positions[' + itemId + '][y]"]');
            var fsInput = form.querySelector('input[name="positions[' + itemId + '][fontSize]"]');
            var boldCb = form.querySelector('input[name="positions[' + itemId + '][bold]"][type="checkbox"]');
            var enabledCb = form.querySelector('input[name="positions[' + itemId + '][enabled]"][type="checkbox"]');
            var widthInput = form.querySelector('input[name="positions[' + itemId + '][width]"]');
            var heightInput = form.querySelector('input[name="positions[' + itemId + '][height]"]');
            if (!keyInput || !xInput || !yInput) return;
            var key = keyInput.value;
            if (!key) return;
            var obj = {
                key: key,
                x: parseFloat(xInput.value) || 0,
                y: parseFloat(yInput.value) || 0,
                fontSize: parseInt(fsInput ? fsInput.value : 15, 10) || 15,
                bold: (boldCb && boldCb.checked) ? 1 : 0,
                enabled: (enabledCb && enabledCb.checked) ? 1 : 0
            };
            if (isSignatureKey(key) && widthInput && heightInput) {
                obj.width = parseFloat(widthInput.value) || 35;
                obj.height = parseFloat(heightInput.value) || 15;
            }
            positions[itemId] = obj;
        });
        var data = {};
        data.positions = positions;
        var dateFormatEl = document.getElementById('date-format-select');
        if (dateFormatEl) data.date_format = dateFormatEl.value;
        var mX = document.getElementById('member_fullname_start_x');
        var mY = document.getElementById('member_fullname_start_y');
        var pX = document.getElementById('member_position_start_x');
        var pY = document.getElementById('member_position_start_y');
        var spacing = document.getElementById('line_spacing');
        if (mX) data.member_fullname_start_x = parseFloat(mX.value) || 25;
        if (mY) data.member_fullname_start_y = parseFloat(mY.value) || 85;
        if (pX) data.member_position_start_x = parseFloat(pX.value) || 100;
        if (pY) data.member_position_start_y = parseFloat(pY.value) || 85;
        if (spacing) data.line_spacing = parseFloat(spacing.value) || 5.5;
        var memberFontSizeEl = document.getElementById('member_font_size');
        var memberBoldEl = document.getElementById('member_bold');
        if (memberFontSizeEl) data.member_font_size = parseInt(memberFontSizeEl.value, 10) || 14;
        if (memberBoldEl) data.member_bold = memberBoldEl.checked ? 1 : 0;
        data['{$csrfParam}'] = '{$csrfToken}';

        btn.disabled = true;
        alertEl.classList.add('d-none');
        fetch('{$saveUrl}', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'บันทึกตำแหน่งเรียบร้อย', timer: 2000, showConfirmButton: false });
                } else {
                    alertEl.className = 'alert alert-success alert-dismissible fade show mb-3';
                    alertEl.innerHTML = 'บันทึกตำแหน่งเรียบร้อย <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>';
                    alertEl.classList.remove('d-none');
                    setTimeout(function() { alertEl.classList.add('d-none'); }, 2000);
                }
            } else {
                alertEl.className = 'alert alert-danger alert-dismissible fade show mb-3';
                alertEl.innerHTML = (res.message || 'บันทึกไม่สำเร็จ') + ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>';
                alertEl.classList.remove('d-none');
            }
        })
        .catch(function() {
            alertEl.className = 'alert alert-danger alert-dismissible fade show mb-3';
            alertEl.innerHTML = 'เกิดข้อผิดพลาด <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>';
            alertEl.classList.remove('d-none');
        })
        .finally(function() { btn.disabled = false; });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (typeof Swal === 'undefined') {
            doSave();
            return;
        }
        Swal.fire({
            title: 'ยืนยันการบันทึก',
            text: 'ต้องการบันทึกตำแหน่งข้อมูลบนเทมเพลต PDF หรือไม่',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'บันทึก',
            cancelButtonText: 'ยกเลิก'
        }).then(function(result) {
            if (result.isConfirmed) doSave();
        });
    });

    rowsContainer.addEventListener('input', function(e) {
        var itemId = e.target.getAttribute('data-item-id');
        if (!itemId) return;
        if (e.target.classList.contains('position-font-size') || e.target.classList.contains('position-bold')) {
            var fs = form.querySelector('input[name="positions[' + itemId + '][fontSize]"]');
            var boldCb = form.querySelector('input[name="positions[' + itemId + '][bold]"][type="checkbox"]');
            updateChipFont(itemId, fs ? parseInt(fs.value, 10) : 15, boldCb ? boldCb.checked : false);
        }
    });
    rowsContainer.addEventListener('change', function(e) {
        var itemId = e.target.getAttribute('data-item-id');
        if (!itemId) return;
        if (e.target.classList.contains('position-key-select')) {
            var chip = canvas.querySelector('.leave-field-chip[data-item-id="' + itemId + '"]');
            if (chip) updateChipLabel(chip, e.target.value);
            var row = e.target.closest('.position-row');
            if (row) toggleSignatureRow(row, e.target.value);
        } else if (e.target.classList.contains('position-font-size') || e.target.classList.contains('position-bold')) {
            var fs = form.querySelector('input[name="positions[' + itemId + '][fontSize]"]');
            var boldCb = form.querySelector('input[name="positions[' + itemId + '][bold]"][type="checkbox"]');
            updateChipFont(itemId, fs ? parseInt(fs.value, 10) : 15, boldCb ? boldCb.checked : false);
        } else if (e.target.classList.contains('field-enabled-cb')) {
            var chip = canvas.querySelector('.leave-field-chip[data-item-id="' + itemId + '"]');
            var row = e.target.closest('.position-row');
            if (chip) chip.classList.toggle('d-none', !e.target.checked);
            if (row) row.classList.toggle('opacity-75', !e.target.checked);
        }
    });

    addBtn.addEventListener('click', function() {
        var itemId = nextItemId();
        var html = rowTpl.innerHTML.replace(/__ITEM_ID__/g, itemId);
        var wrap = document.createElement('div');
        wrap.innerHTML = html;
        var row = wrap.firstElementChild;
        var keySelect = row.querySelector('.position-key-select');
        fieldKeys.forEach(function(k) {
            var opt = document.createElement('option');
            opt.value = k;
            opt.textContent = getLabelForKey(k);
            keySelect.appendChild(opt);
        });
        rowsContainer.appendChild(row);

        var chip = document.createElement('div');
        chip.className = 'position-absolute leave-field-chip text-primary user-select-none';
        chip.setAttribute('data-item-id', itemId);
        chip.setAttribute('data-field-key', fieldKeys[0] || '');
        chip.style.left = '0px';
        chip.style.top = '0px';
        chip.style.cursor = 'grab';
        chip.style.fontSize = (15 * fontDisplayScale).toFixed(1) + 'pt';
        chip.style.fontWeight = 'normal';
        chip.style.fontFamily = "'THSarabunNew', sans-serif";
        chip.title = getLabelForKey(fieldKeys[0] || '');
        chip.draggable = false;
        var span = document.createElement('span');
        span.className = 'chip-label text-truncate d-inline-block';
        span.style.maxWidth = '140px';
        span.textContent = getLabelForKey(fieldKeys[0] || '');
        chip.appendChild(span);
        canvas.appendChild(chip);
        attachChipDrag(chip);
        toggleSignatureRow(row, fieldKeys[0] || '');

        row.querySelector('.position-remove').addEventListener('click', function() {
            row.remove();
            chip.remove();
        });
    });

    rowsContainer.querySelectorAll('.position-remove').forEach(function(btnEl) {
        btnEl.addEventListener('click', function() {
            var itemId = btnEl.getAttribute('data-item-id');
            var row = btnEl.closest('.position-row');
            var chip = canvas.querySelector('.leave-field-chip[data-item-id="' + itemId + '"]');
            if (row) row.remove();
            if (chip) chip.remove();
        });
    });
})();
JS
);
?>
