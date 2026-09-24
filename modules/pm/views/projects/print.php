<?php

use yii\helpers\Html;
use yii\helpers\Json;

/**
 * หน้าพิมพ์แบบเสนอโครงการ — แสดงเป็นกระดาษ A4 ที่แก้ไขข้อความบนจอได้ก่อนพิมพ์
 *
 * ใช้ contenteditable ตรง ๆ (แนวเดียวกับ /purchase/doc) เพราะต้องการให้หน้าตาบนจอ
 * ตรงกับกระดาษที่พิมพ์ออกมา · การแก้บนจอไม่เขียนกลับฐานข้อมูล
 * แต่เก็บร่างไว้ในเบราว์เซอร์ผูกกับเวลาแก้ไขล่าสุดของโครงการ — ถ้าข้อมูลโครงการ
 * ถูกแก้ภายหลัง ร่างเก่าจะไม่ถูกนำมาใช้ ป้องกันพิมพ์ข้อมูลที่ล้าสมัยออกไป
 *
 * @var yii\web\View $this
 * @var app\modules\pm\models\Projects $model
 */

$this->title = $model->documentTitle();
$draftKey = 'pm-project-print:' . $model->id . ':' . ($model->updated_at ?: '0');
$fileName = preg_replace('/[\\\\\/:*?"<>|]+/u', '-', $model->code ?: ('project-' . $model->id));
?>
<div class="pp-toolbar no-print">
    <div class="pp-toolbar-inner">
        <div class="btn-group btn-group-sm" role="group" aria-label="จัดรูปแบบ">
            <button type="button" class="btn btn-outline-secondary" data-cmd="bold" title="ตัวหนา"><i class="bi bi-type-bold"></i></button>
            <button type="button" class="btn btn-outline-secondary" data-cmd="italic" title="ตัวเอียง"><i class="bi bi-type-italic"></i></button>
            <button type="button" class="btn btn-outline-secondary" data-cmd="underline" title="ขีดเส้นใต้"><i class="bi bi-type-underline"></i></button>
        </div>
        <div class="btn-group btn-group-sm" role="group" aria-label="จัดแนว">
            <button type="button" class="btn btn-outline-secondary" data-cmd="justifyLeft" title="ชิดซ้าย"><i class="bi bi-text-left"></i></button>
            <button type="button" class="btn btn-outline-secondary" data-cmd="justifyCenter" title="กึ่งกลาง"><i class="bi bi-text-center"></i></button>
            <button type="button" class="btn btn-outline-secondary" data-cmd="justifyFull" title="เต็มแนว"><i class="bi bi-justify"></i></button>
        </div>
        <div class="btn-group btn-group-sm" role="group" aria-label="รายการ">
            <button type="button" class="btn btn-outline-secondary" data-cmd="insertOrderedList" title="รายการลำดับเลข"><i class="bi bi-list-ol"></i></button>
            <button type="button" class="btn btn-outline-secondary" data-cmd="insertUnorderedList" title="รายการสัญลักษณ์"><i class="bi bi-list-ul"></i></button>
        </div>
        <select id="pp-font-size" class="form-select form-select-sm" style="width:auto" title="ขนาดตัวอักษรทั้งเอกสาร">
            <?php foreach ([14, 15, 16, 18] as $size): ?>
                <option value="<?= $size ?>" <?= $size === 16 ? 'selected' : '' ?>><?= $size ?> pt</option>
            <?php endforeach; ?>
        </select>

        <div class="ms-auto d-flex gap-2 flex-wrap">
            <button type="button" id="pp-reset" class="btn btn-sm btn-outline-danger" title="ทิ้งการแก้ไขบนจอ แล้วดึงข้อมูลจากระบบใหม่">
                <i class="bi bi-arrow-counterclockwise me-1"></i>คืนค่าจากระบบ
            </button>
            <button type="button" id="pp-word" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-file-earmark-word me-1"></i>ดาวน์โหลด Word
            </button>
            <button type="button" id="pp-print" class="btn btn-sm btn-primary">
                <i class="bi bi-printer me-1"></i>พิมพ์
            </button>
        </div>
    </div>
    <div class="pp-hint">
        <i class="bi bi-pencil-square me-1"></i>คลิกที่ข้อความบนกระดาษเพื่อแก้ไขได้ทันที ·
        การแก้ไขในหน้านี้ใช้สำหรับพิมพ์เท่านั้น ไม่บันทึกกลับเข้าระบบ (ร่างจะจำไว้ในเครื่องนี้จนกว่าข้อมูลโครงการจะถูกแก้ไข)
        <span id="pp-draft-state" class="ms-1"></span>
    </div>
</div>

<div class="pp-desk">
    <div class="pp-sheet" id="pp-sheet" contenteditable="true" spellcheck="false">
        <?= $this->render('_document', ['model' => $model]) ?>
    </div>
</div>

<?php
$docCss = <<<CSS
.pp-sheet, .pp-sheet * { font-family: 'THSarabunNew', 'TH Sarabun New', sans-serif; }
.pp-sheet { font-size: 16pt; line-height: 1.25; color: #000; }
.pp-sheet p { margin: 0 0 2pt; }
.pp-sheet .pdoc-title { font-weight: bold; font-size: 1.1em; margin-bottom: 12pt; }
.pp-sheet .pdoc-section { margin-bottom: 8pt; }
.pp-sheet .pdoc-head { font-weight: bold; }
.pp-sheet .pdoc-body { padding-left: 36pt; }
.pp-sheet .pdoc-list, .pp-sheet ol, .pp-sheet ul { margin: 0; padding-left: 20pt; }
.pp-sheet .pdoc-blank { border-bottom: 1px dotted #999; min-height: 1.2em; }
.pp-sheet .pdoc-note { margin: -4pt 0 8pt; }
.pp-sheet .pdoc-sign { text-align: center; margin: 18pt 0 6pt; page-break-inside: avoid; }
CSS;

$this->registerCss($docCss . <<<CSS
@font-face { font-family: 'THSarabunNew'; font-weight: normal; src: url('/fonts/THSarabunNew/THSarabunNew.ttf') format('truetype'); }
@font-face { font-family: 'THSarabunNew'; font-weight: bold; src: url('/fonts/THSarabunNew/THSarabunNew-Bold.ttf') format('truetype'); }
body { background: #e9ecef !important; padding: 0 !important; }
.pp-toolbar { position: sticky; top: 0; z-index: 10; background: #fff; border-bottom: 1px solid #dee2e6; padding: .5rem 1rem; }
.pp-toolbar-inner { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; max-width: 210mm; margin: 0 auto; }
.pp-hint { max-width: 210mm; margin: .35rem auto 0; font-size: .8rem; color: #6c757d; }
.pp-desk { padding: 1.5rem 1rem 3rem; }
.pp-sheet { background: #fff; width: 210mm; max-width: 100%; min-height: 297mm; margin: 0 auto; padding: 2cm 2cm 2cm 2.5cm;
    box-shadow: 0 2px 12px rgba(0,0,0,.15); outline: none; box-sizing: border-box; }
.pp-sheet:focus { box-shadow: 0 0 0 2px rgba(13,110,253,.35), 0 2px 12px rgba(0,0,0,.15); }
@page { size: A4; margin: 2cm 2cm 2cm 2.5cm; }
@media print {
    .no-print { display: none !important; }
    body { background: #fff !important; }
    .pp-desk { padding: 0; }
    .pp-sheet { width: auto; min-height: 0; padding: 0; box-shadow: none !important; }
    .pp-sheet .pdoc-blank { border-bottom-color: #bbb; }
}
CSS);

$cfg = Json::htmlEncode([
    'draftKey' => $draftKey,
    'fileName' => $fileName,
    'title' => $this->title,
    'docCss' => $docCss,
]);

$js = <<<JS
(function () {
    var cfg = $cfg;
    var sheet = document.getElementById('pp-sheet');
    var state = document.getElementById('pp-draft-state');
    var original = sheet.innerHTML;
    var saveTimer = null;

    // localStorage อาจใช้ไม่ได้ (โหมดส่วนตัว/ถูกบล็อก) — ห่อ try ทุกครั้ง หน้าต้องใช้งานได้แม้ไม่มี
    function store(fn) { try { return fn(window.localStorage); } catch (e) { return null; } }

    // ล้างร่างของโครงการเดียวกันที่ผูกกับเวลาแก้ไขเก่า — ไม่ให้ค้างสะสมในเครื่อง
    var prefix = cfg.draftKey.split(':').slice(0, 2).join(':') + ':';
    store(function (ls) {
        for (var i = ls.length - 1; i >= 0; i--) {
            var k = ls.key(i);
            if (k && k.indexOf(prefix) === 0 && k !== cfg.draftKey) ls.removeItem(k);
        }
    });

    var draft = store(function (ls) { return ls.getItem(cfg.draftKey); });
    if (draft) {
        sheet.innerHTML = draft;
        state.textContent = '· กำลังแสดงร่างที่แก้ไว้';
    }

    sheet.addEventListener('input', function () {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(function () {
            store(function (ls) { ls.setItem(cfg.draftKey, sheet.innerHTML); });
            state.textContent = '· บันทึกร่างในเครื่องแล้ว';
        }, 600);
    });

    document.querySelectorAll('[data-cmd]').forEach(function (btn) {
        // mousedown + preventDefault เพื่อไม่ให้ปุ่มแย่ง focus จนตำแหน่งที่เลือกไว้บนกระดาษหาย
        btn.addEventListener('mousedown', function (e) { e.preventDefault(); });
        btn.addEventListener('click', function () {
            document.execCommand(btn.getAttribute('data-cmd'), false, null);
            sheet.dispatchEvent(new Event('input'));
        });
    });

    document.getElementById('pp-font-size').addEventListener('change', function () {
        sheet.style.fontSize = this.value + 'pt';
    });

    document.getElementById('pp-reset').addEventListener('click', function () {
        if (!confirm('ทิ้งการแก้ไขทั้งหมดบนหน้านี้ แล้วแสดงข้อมูลจากระบบใหม่?')) return;
        store(function (ls) { ls.removeItem(cfg.draftKey); });
        sheet.innerHTML = original;
        state.textContent = '';
    });

    document.getElementById('pp-print').addEventListener('click', function () { window.print(); });

    // ส่งออกเป็น .doc (HTML ที่ Word เปิดได้) จากเนื้อหาบนจอ ณ ขณะนั้น — รวมสิ่งที่แก้ไขแล้ว
    document.getElementById('pp-word').addEventListener('click', function () {
        var size = document.getElementById('pp-font-size').value;
        var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">'
            + '<head><meta charset="utf-8"><title>' + cfg.title.replace(/</g, '&lt;') + '</title>'
            + '<style>@page WordSection1 { size: 21cm 29.7cm; margin: 2cm 2cm 2cm 2.5cm; } div.WordSection1 { page: WordSection1; }'
            + cfg.docCss.replace(/\.pp-sheet/g, '.WordSection1') + ' .WordSection1 { font-size: ' + size + 'pt; }</style></head>'
            + '<body><div class="WordSection1">' + sheet.innerHTML + '</div></body></html>';
        var blob = new Blob(['\\ufeff', html], { type: 'application/msword' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = cfg.fileName + '.doc';
        document.body.appendChild(a);
        a.click();
        setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 1000);
    });
})();
JS;
$this->registerJs($js);
