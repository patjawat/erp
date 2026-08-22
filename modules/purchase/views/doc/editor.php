<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\purchase\models\Doc;
use app\modules\purchase\models\DocTemplate;
use app\modules\purchase\components\DocRenderer;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Doc $model */
/** @var array|null $routes ปลายทาง save/reset ของโมดูลที่นำ editor ไปใช้ */

/**
 * หน้าแก้ไขเอกสารบนกระดาษ A4 (เนื้อหาใน modal)
 *
 * ทำไมใช้ contenteditable ตรง ๆ ไม่ใช่ Summernote ที่โปรเจกต์มีอยู่แล้ว
 *
 * Summernote ใช้อยู่ที่ฟอร์ม TOR และเหมาะกับที่นั่น เพราะที่นั่นเป็น "กล่องข้อความ
 * ในฟอร์ม" แต่ที่นี่คือ "กระดาษ A4 ทั้งแผ่น" ซึ่งต้องคุมความกว้าง ขอบกระดาษ
 * และขนาดฟอนต์ให้ตรงกับที่ mPDF จะพิมพ์ออกมาเป๊ะ ๆ Summernote ครอบ CSS ของ
 * ตัวเองลงบนพื้นที่แก้ไข (padding, line-height, font ของ .note-editable) ทำให้
 * ระยะบนจอกับบนกระดาษเลื่อนจากกัน และแถบเครื่องมือของมันก็ไม่มีปุ่มที่เอกสารนี้
 * ต้องใช้อยู่ดี (เพิ่ม/ลบแถวตาราง สลับขนาดตราครุฑ รีเซ็ตกลับเป็นแม่แบบ)
 *
 * document.execCommand ถูกประกาศเลิกใช้ในสเปกแล้ว แต่ยังทำงานในทุกเบราว์เซอร์
 * ที่ใช้จริงและเป็นทางเดียวที่ทำตัวหนา/เอียง/จัดชิดใน contenteditable ได้โดยไม่
 * ต้องลากไลบรารีใหม่เข้าโปรเจกต์ ถ้าวันหนึ่งมันถูกถอดออกจริง จุดที่ต้องแก้อยู่ใน
 * ฟังก์ชัน exec() ที่เดียว
 */

$fontSizes = [12, 13, 14, 15, 16, 18, 20];
$locked = $model->status === Doc::STATUS_FINAL;
$routes = array_merge([
    'save' => ['/purchase/doc/save', 'id' => $model->id],
    'reset' => ['/purchase/doc/reset', 'id' => $model->id],
], $routes ?? []);

$cfg = [
    'saveUrl' => Url::to($routes['save']),
    'resetUrl' => Url::to($routes['reset']),
    'emblemUrl' => Yii::getAlias('@web') . '/' . DocRenderer::EMBLEM_FILE,
    'emblem' => (string) $model->emblem,
    'fontSize' => (int) $model->font_size,
    'locked' => $locked,
    'csrfParam' => Yii::$app->request->csrfParam,
    'csrfToken' => Yii::$app->request->csrfToken,
];
?>

<style>
<?= DocRenderer::sheetCss($model) ?>

<?= DocRenderer::screenCss($model) ?>

.d-toolbar {
    position: sticky;
    top: 0;
    z-index: 3;
    background: var(--bs-body-bg, #fff);
    border-bottom: 1px solid var(--bs-border-color, #dee2e6);
}
.d-toolbar .btn { --bs-btn-padding-y: .2rem; --bs-btn-padding-x: .5rem; }
.d-stage { max-height: calc(100vh - 260px); }
</style>

<div class="d-toolbar d-flex flex-wrap align-items-center gap-2 px-2 py-2">
    <span class="small text-muted">จัดรูปแบบ:</span>

    <div class="btn-group" role="group" aria-label="รูปแบบตัวอักษร">
        <button type="button" class="btn btn-outline-secondary" data-doc-cmd="bold" title="ตัวหนา">
            <i class="bi bi-type-bold"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary" data-doc-cmd="italic" title="ตัวเอียง">
            <i class="bi bi-type-italic"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary" data-doc-cmd="underline" title="ขีดเส้นใต้">
            <i class="bi bi-type-underline"></i>
        </button>
    </div>

    <div class="btn-group" role="group" aria-label="การจัดชิด">
        <button type="button" class="btn btn-outline-secondary" data-doc-cmd="justifyLeft" title="ชิดซ้าย">
            <i class="bi bi-text-left"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary" data-doc-cmd="justifyCenter" title="กลาง">
            <i class="bi bi-text-center"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary" data-doc-cmd="justifyRight" title="ชิดขวา">
            <i class="bi bi-text-right"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary" data-doc-cmd="justifyFull" title="ชิดขอบสองข้าง">
            <i class="bi bi-justify"></i>
        </button>
    </div>

    <select id="doc-font-size" class="form-select form-select-sm" style="width:auto"
        aria-label="ขนาดฟอนต์ทั้งเอกสาร">
        <?php foreach ($fontSizes as $size): ?>
            <option value="<?= $size ?>" <?= (int) $model->font_size === $size ? 'selected' : '' ?>>
                <?= $size ?>pt
            </option>
        <?php endforeach; ?>
    </select>

    <div class="btn-group" role="group" aria-label="แถวตาราง">
        <button type="button" class="btn btn-outline-secondary" data-doc-row="add">
            <i class="bi bi-plus-lg me-1"></i>แถว
        </button>
        <button type="button" class="btn btn-outline-secondary" data-doc-row="remove">
            <i class="bi bi-dash-lg me-1"></i>แถว
        </button>
    </div>

    <div class="dropdown">
        <button type="button" class="btn btn-outline-warning dropdown-toggle" data-bs-toggle="dropdown"
            aria-expanded="false">
            <i class="bi bi-award me-1"></i><span id="doc-emblem-label"><?=
                Html::encode(DocTemplate::emblemList()[$model->emblem] ?? 'ตราครุฑ')
            ?></span>
        </button>
        <ul class="dropdown-menu">
            <?php foreach (DocTemplate::emblemList() as $value => $label): ?>
                <li>
                    <a class="dropdown-item<?= (string) $model->emblem === (string) $value ? ' active' : '' ?>"
                        href="#" data-doc-emblem="<?= Html::encode($value) ?>">
                        <?= Html::encode($label) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <button type="button" class="btn btn-outline-primary" id="doc-reset">
        <i class="bi bi-arrow-counterclockwise me-1"></i>รีเซ็ต
    </button>

    <div class="ms-auto small text-muted d-flex align-items-center gap-2">
        <span class="js-doc-dirty d-none text-warning-emphasis">
            <i class="bi bi-pencil-fill me-1"></i>ยังไม่บันทึก
        </span>
        <span class="js-doc-saved d-none text-success">
            <i class="bi bi-check-circle-fill me-1"></i>บันทึกแล้ว
        </span>
        <span><i class="bi bi-lightbulb me-1"></i>คลิกในเอกสารเพื่อแก้ไขได้</span>
    </div>
</div>

<?php if ($locked): ?>
    <div class="alert alert-warning rounded-0 mb-0 small">
        <i class="bi bi-lock me-1"></i>
        เอกสารฉบับนี้บันทึกไว้ว่า "ออกเลขแล้ว" จึงล็อกไม่ให้แก้เนื้อความ
        หากต้องแก้ ให้เปลี่ยนสถานะกลับเป็นร่างที่หน้าทะเบียนก่อน
    </div>
<?php endif; ?>

<div class="d-stage">
    <div class="d-sheet" id="doc-sheet" <?= $locked ? '' : 'contenteditable="true"' ?>
        spellcheck="false" role="textbox" aria-multiline="true" aria-label="เนื้อเอกสาร">
        <?= DocRenderer::body($model) ?>
    </div>
</div>

<script>
window.erpDocEditorCfg = <?= json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

window.erpDocEditorInit = function () {
    var cfg = window.erpDocEditorCfg || {};
    var $modal = jQuery('#main-modal');
    var sheet = document.getElementById('doc-sheet');
    if (!sheet) { return; }

    var dirty = false;
    var saving = null;
    var timer = null;

    /** แสดงเอกสารที่มีตัวแบ่งหน้าเป็นกระดาษคนละแผ่นบนจอ */
    function layoutPages() {
        if (sheet.querySelector('.d-doc-page')) { return; }
        var pageBreak = sheet.querySelector('.d-page-break');
        if (!pageBreak || !pageBreak.parentNode) { return; }

        var container = pageBreak.parentNode;
        var firstPage = document.createElement('section');
        var secondPage = document.createElement('section');
        firstPage.className = 'd-doc-page';
        secondPage.className = 'd-doc-page';

        while (container.firstChild && container.firstChild !== pageBreak) {
            firstPage.appendChild(container.firstChild);
        }
        pageBreak.remove();
        while (container.firstChild) {
            secondPage.appendChild(container.firstChild);
        }
        container.appendChild(firstPage);
        container.appendChild(secondPage);
    }

    layoutPages();

    function note(state) {
        $modal.find('.js-doc-dirty').toggleClass('d-none', state !== 'dirty');
        $modal.find('.js-doc-saved').toggleClass('d-none', state !== 'saved');
    }

    function touch() {
        if (cfg.locked) { return; }
        dirty = true;
        note('dirty');
        // บันทึกอัตโนมัติ เพราะปุ่มพริ้นท์กับส่งออก Word ต้องอ่านจากฐาน ไม่ได้อ่านจากจอ
        // ถ้าไม่บันทึกให้ ผู้ใช้ที่แก้แล้วกดพริ้นท์ทันทีจะได้กระดาษที่ไม่ตรงกับที่เห็น
        window.clearTimeout(timer);
        timer = window.setTimeout(function () { save(); }, 900);
    }

    function payload() {
        var data = {
            body_html: sheet.innerHTML,
            emblem: cfg.emblem,
            font_size: parseInt(jQuery('#doc-font-size').val(), 10) || cfg.fontSize
        };
        data[cfg.csrfParam] = cfg.csrfToken;
        return data;
    }

    function save() {
        if (cfg.locked || !dirty) { return jQuery.Deferred().resolve().promise(); }
        window.clearTimeout(timer);
        var snapshot = payload();
        saving = jQuery.post(cfg.saveUrl, snapshot).done(function (res) {
            if (res && res.status === 'success') {
                dirty = false;
                note('saved');
            } else if (typeof warning === 'function') {
                warning((res && res.message) || 'บันทึกไม่สำเร็จ');
            }
        }).fail(function () {
            if (typeof warning === 'function') {
                warning('บันทึกไม่สำเร็จ กรุณาตรวจการเชื่อมต่อแล้วลองอีกครั้ง');
            }
        });
        return saving;
    }

    /** เปิดลิงก์หลังบันทึกเสร็จ — มีทางถอยเมื่อเบราว์เซอร์บล็อกแท็บใหม่ */
    function openAfterSave(url, newTab) {
        jQuery.when(save()).always(function () {
            if (!newTab) {
                // ดาวน์โหลดไฟล์ไม่ได้พาออกจากหน้า จึงตั้ง location ได้ตรง ๆ
                window.location.assign(url);
                return;
            }
            var win = window.open(url, '_blank');
            if (!win) {
                // ตัวบล็อกป๊อปอัปทำงานเพราะ window.open ถูกเรียกหลัง ajax ไม่ใช่จากคลิกตรง ๆ
                // จึงต้องยื่นลิงก์ให้ผู้ใช้กดเอง ไม่ปล่อยให้เงียบแล้วคิดว่าปุ่มเสีย
                if (typeof warning === 'function') {
                    warning('เบราว์เซอร์บล็อกการเปิดแท็บใหม่ — กรุณากดปุ่มพริ้นท์อีกครั้ง หรืออนุญาตป๊อปอัปของเว็บนี้');
                }
            }
        });
    }

    function exec(cmd) {
        document.execCommand(cmd, false, null);
        sheet.focus();
        touch();
    }

    /** แถว <tr> ที่เคอร์เซอร์อยู่ — null เมื่อเคอร์เซอร์ไม่ได้อยู่ในตาราง */
    function currentRow() {
        var sel = window.getSelection();
        if (!sel || !sel.rangeCount) { return null; }
        var node = sel.getRangeAt(0).startContainer;
        if (node.nodeType === 3) { node = node.parentNode; }
        while (node && node !== sheet && node.tagName !== 'TR') { node = node.parentNode; }
        return (node && node.tagName === 'TR') ? node : null;
    }

    function rowHint() {
        if (typeof warning === 'function') {
            warning('กรุณาคลิกในช่องของตารางที่ต้องการก่อน แล้วจึงกดเพิ่มหรือลบแถว');
        }
    }

    function addRow() {
        var tr = currentRow();
        if (!tr) { return rowHint(); }
        var clone = tr.cloneNode(true);
        // ล้างข้อความในแถวที่คัดลอกมา ไม่งั้นผู้ใช้ได้แถวซ้ำที่ต้องลบทิ้งทีละช่อง
        Array.prototype.forEach.call(clone.querySelectorAll('td,th'), function (cell) {
            cell.innerHTML = '&nbsp;';
        });
        // แถวหัวตารางถูกคัดลอกได้ แต่แถวใหม่ต้องไม่หนาและไม่มีพื้นสีเหมือนหัวตาราง
        clone.classList.remove('d-items-head');
        tr.parentNode.insertBefore(clone, tr.nextSibling);
        touch();
    }

    function removeRow() {
        var tr = currentRow();
        if (!tr) { return rowHint(); }
        var parent = tr.parentNode;
        if (parent.querySelectorAll('tr').length <= 1) {
            if (typeof warning === 'function') {
                warning('ตารางนี้เหลือแถวเดียว ลบแล้วตารางจะหายไปทั้งตาราง');
            }
            return;
        }
        parent.removeChild(tr);
        touch();
    }

    function setEmblem(value, label) {
        var slot = sheet.querySelector('.d-emblem');
        if (!slot) {
            if (typeof warning === 'function') {
                warning('แม่แบบของเอกสารนี้ไม่ได้วางตำแหน่งตราครุฑ ({{emblem}}) ไว้ จึงเปลี่ยนขนาดไม่ได้');
            }
            return;
        }
        var mm = (value === '1.5') ? 15 : (value === '3.0' ? 30 : 0);
        slot.innerHTML = mm
            ? '<img src="' + cfg.emblemUrl + '" alt="ตราครุฑ" style="height:' + mm + 'mm">'
            : '';
        cfg.emblem = value;
        jQuery('#doc-emblem-label').text(label);
        $modal.find('[data-doc-emblem]').removeClass('active')
            .filter('[data-doc-emblem="' + value + '"]').addClass('active');
        touch();
    }

    // ผูก event ใหม่ทุกครั้งที่เปิด modal — ใช้ namespace เดียวแล้ว off ทีเดียว
    // เพราะ modal ตัวเดียวถูกใช้ซ้ำกับเอกสารฉบับอื่น ถ้าไม่ล้าง handler เก่า
    // การกดปุ่มครั้งเดียวจะยิงคำสั่งไปที่เอกสารฉบับก่อนหน้าด้วย
    $modal.off('.docEditor');

    if (!cfg.locked) {
        sheet.addEventListener('input', touch);

        $modal.on('click.docEditor', '[data-doc-cmd]', function (e) {
            e.preventDefault();
            exec(jQuery(this).data('doc-cmd'));
        });

        $modal.on('click.docEditor', '[data-doc-row]', function (e) {
            e.preventDefault();
            if (jQuery(this).data('doc-row') === 'add') { addRow(); } else { removeRow(); }
        });

        $modal.on('change.docEditor', '#doc-font-size', function () {
            var value = parseInt(this.value, 10) || cfg.fontSize;
            sheet.style.fontSize = value + 'pt';
            touch();
        });

        $modal.on('click.docEditor', '[data-doc-emblem]', function (e) {
            e.preventDefault();
            setEmblem(String(jQuery(this).data('doc-emblem')), jQuery(this).text().trim());
        });

        $modal.on('click.docEditor', '#doc-reset', function (e) {
            e.preventDefault();
            var ask = window.Swal
                ? Swal.fire({
                    title: 'ดึงข้อความจากแม่แบบกลับมา?',
                    text: 'การแก้ไขทั้งหมดที่ทำไว้ในเอกสารฉบับนี้จะหายไป',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'ใช่, รีเซ็ต',
                    cancelButtonText: 'ยกเลิก'
                }).then(function (r) { return r.value === true; })
                : jQuery.Deferred().resolve(
                    window.confirm('การแก้ไขทั้งหมดจะหายไป ยืนยันรีเซ็ต?')
                ).promise();

            jQuery.when(ask).done(function (ok) {
                if (!ok) { return; }
                var data = {};
                data[cfg.csrfParam] = cfg.csrfToken;
                jQuery.post(cfg.resetUrl, data).done(function (res) {
                    if (res && res.status === 'success') {
                        sheet.innerHTML = res.body_html;
                        layoutPages();
                        dirty = false;
                        note('saved');
                    } else if (typeof warning === 'function') {
                        warning((res && res.message) || 'รีเซ็ตไม่สำเร็จ');
                    }
                });
            });
        });

        $modal.on('click.docEditor', '#doc-save', function (e) {
            e.preventDefault();
            if (!dirty) {
                note('saved');
                return;
            }
            save();
        });
    }

    $modal.on('click.docEditor', '#doc-print', function (e) {
        e.preventDefault();
        openAfterSave(jQuery(this).data('url'), true);
    });

    $modal.on('click.docEditor', '#doc-word', function (e) {
        e.preventDefault();
        openAfterSave(jQuery(this).data('url'), false);
    });

    // เตือนเมื่อปิด modal ทั้งที่ยังมีการแก้ที่ยังไม่ได้บันทึก — autosave หน่วง 900ms
    // จึงมีช่วงที่ผู้ใช้กดปิดทันหลังพิมพ์ตัวสุดท้าย
    $modal.on('hide.bs.modal.docEditor', function () {
        if (dirty) { save(); }
    });
};
</script>
