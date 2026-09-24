<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinanceChequeTemplate $tpl */
/** @var array $fields */

$this->title = 'ปรับตำแหน่งพิมพ์เช็ค — ' . $tpl->name;
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'พิมพ์เช็ค', 'url' => ['/finance/cheque']];
$this->params['breadcrumbs'][] = 'ปรับตำแหน่ง';
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo 'จัดพิกัดช่องพิมพ์เป็น % ของแผ่นเช็ค — บันทึกแล้วดูพรีวิว/พิมพ์ทดสอบวางทาบเช็คจริง';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'cheque']);
$this->endBlock();

// พารามิเตอร์ตัวอย่างสำหรับพรีวิว
$sample = ['d' => date('Y-m-d'), 'p' => 'ตัวอย่าง ผู้รับเงิน จำกัด', 'a' => 53460, 'ac' => 1];
$previewUrl = Url::to(array_merge(['preview', 'id' => $tpl->id], $sample));
$testUrl = Url::to(array_merge(['test-print', 'id' => $tpl->id], $sample));
$aligns = ['L' => 'ซ้าย', 'C' => 'กลาง', 'R' => 'ขวา'];
?>

<div class="row g-3">
    <div class="col-lg-6">
        <?php $form = Html::beginForm(['calibrate', 'id' => $tpl->id], 'post'); ?>
        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-aspect-ratio me-1"></i>ขนาดแผ่นเช็ค & ชดเชยเครื่องพิมพ์</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <label class="form-label small">กว้าง (มม.)</label>
                        <input type="number" step="0.1" name="page_width_mm" class="form-control form-control-sm" value="<?= Html::encode($tpl->page_width_mm) ?>">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small">สูง (มม.)</label>
                        <input type="number" step="0.1" name="page_height_mm" class="form-control form-control-sm" value="<?= Html::encode($tpl->page_height_mm) ?>">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small">ชดเชย X (มม.)</label>
                        <input type="number" step="0.1" name="calibrate_offset_x" class="form-control form-control-sm" value="<?= Html::encode($tpl->calibrate_offset_x) ?>">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small">ชดเชย Y (มม.)</label>
                        <input type="number" step="0.1" name="calibrate_offset_y" class="form-control form-control-sm" value="<?= Html::encode($tpl->calibrate_offset_y) ?>">
                    </div>
                </div>
                <div class="form-text mt-2">ถ้าพิมพ์ทั้งใบเลื่อนไปทางเดียวกัน ให้ปรับค่าชดเชย X/Y แทนการแก้ทีละช่อง</div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-grid-3x3-gap me-1"></i>ตำแหน่งช่องพิมพ์ (x, y = % ของแผ่น)</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>พิมพ์</th><th>ช่อง</th><th style="width:74px">X %</th><th style="width:74px">Y %</th>
                            <th style="width:70px">ขนาด</th><th style="width:84px">จัดชิด</th><th>หนา</th>
                            <th style="width:78px" title="ระยะห่างต่อตัวอักษร (% ของแผ่น) สำหรับช่องตัวเลข เช่น วันที่">ช่อง %</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($fields as $f): $k = $f['key']; ?>
                        <tr>
                            <td><input type="checkbox" class="form-check-input" name="field[<?= $k ?>][enabled]" value="1" <?= $f['enabled'] ? 'checked' : '' ?>></td>
                            <td class="small"><?= Html::encode($f['label']) ?></td>
                            <td><input type="number" step="0.1" class="form-control form-control-sm" name="field[<?= $k ?>][x]" value="<?= Html::encode($f['x']) ?>"></td>
                            <td><input type="number" step="0.1" class="form-control form-control-sm" name="field[<?= $k ?>][y]" value="<?= Html::encode($f['y']) ?>"></td>
                            <td><input type="number" step="0.5" class="form-control form-control-sm" name="field[<?= $k ?>][font_size]" value="<?= Html::encode($f['font_size']) ?>"></td>
                            <td>
                                <select name="field[<?= $k ?>][align]" class="form-select form-select-sm">
                                    <?php foreach ($aligns as $av => $al): ?>
                                        <option value="<?= $av ?>" <?= $f['align'] === $av ? 'selected' : '' ?>><?= $al ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="checkbox" class="form-check-input" name="field[<?= $k ?>][bold]" value="1" <?= $f['bold'] ? 'checked' : '' ?>></td>
                            <td><input type="number" step="0.05" class="form-control form-control-sm" name="field[<?= $k ?>][pitch]" value="<?= Html::encode($f['pitch']) ?>" placeholder="0"></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-body d-flex gap-2 flex-wrap">
                <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึก & ดูตัวอย่าง', ['class' => 'btn btn-primary']) ?>
                <a href="<?= $testUrl ?>" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>พิมพ์ทดสอบ (ลงเช็คจริง)</a>
            </div>
        </div>
        <?php Html::endForm(); ?>

        <div class="card shadow-sm">
            <div class="card-header fw-semibold"><i class="bi bi-image me-1"></i>รูปสแกนเช็คเปล่า (ใช้ดูตำแหน่งบนจอ)</div>
            <div class="card-body">
                <?php $up = Html::beginForm(['upload-background', 'id' => $tpl->id], 'post', ['enctype' => 'multipart/form-data', 'class' => 'd-flex gap-2 flex-wrap align-items-center']); ?>
                    <input type="file" name="background" accept="image/*" class="form-control form-control-sm" style="max-width:280px">
                    <?= Html::submitButton('อัปโหลด', ['class' => 'btn btn-outline-primary btn-sm']) ?>
                    <?php if ($tpl->background_path): ?><span class="text-success small"><i class="bi bi-check-circle me-1"></i>มีรูปพื้นหลังแล้ว</span><?php endif; ?>
                <?php Html::endForm(); ?>
                <div class="form-text">รูปนี้ใช้เฉพาะดูตำแหน่งในพรีวิว ไม่ถูกพิมพ์ลงเช็คจริง</div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm sticky-lg-top" style="top:1rem">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-eye me-1"></i>พรีวิว (ข้อมูลตัวอย่าง)</span>
                <a href="<?= $previewUrl ?>" target="_blank" class="btn btn-sm btn-outline-secondary">เปิดแท็บใหม่</a>
            </div>
            <div class="card-body p-2">
                <iframe src="<?= $previewUrl ?>" style="width:100%;height:420px;border:1px solid #dee2e6;border-radius:.375rem"></iframe>
                <div class="form-text mt-2">ตัวอย่าง: วันที่วันนี้ · ผู้รับ "ตัวอย่าง ผู้รับเงิน จำกัด" · 53,460.00 บาท</div>
            </div>
        </div>
    </div>
</div>
