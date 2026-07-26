<?php

use yii\helpers\Url;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use app\modules\am\models\AssetStatus;
use app\modules\am\models\AssetCondition;

/** @var yii\web\View $this */

// ตัวเลือกสำหรับ popover แบบ enum ดึงจาก model เดียวกับที่ฟอร์มใช้เพื่อไม่ให้ค่าเพี้ยนกัน
$conditionOptions = [];
foreach (
    AssetCondition::find()->where(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all() as $c
) {
    $conditionOptions[] = ['value' => (string) $c->id, 'label' => (string) $c->name];
}

$statusOptions = [];
foreach (AssetStatus::find()->all() as $s) {
    $statusOptions[] = ['value' => (string) $s->id, 'label' => (string) $s->name];
}

$riskOptions = [
    ['value' => 'L', 'label' => 'ต่ำ'],
    ['value' => 'M', 'label' => 'กลาง'],
    ['value' => 'H', 'label' => 'สูง'],
    ['value' => '', 'label' => 'ยังไม่ประเมิน'],
];

$config = Json::encode([
    'url' => Url::to(['/am/equip/quick-update']),
    'options' => [
        'asset_condition' => $conditionOptions,
        'asset_status' => $statusOptions,
        'risk_level' => $riskOptions,
    ],
]);
?>

<style>
    /* ===== Quick inline edit: โทน token ตาม PRODUCT.md ===== */
    .qedit,
    .qedit-modal {
        cursor: pointer;
        border-radius: 6px;
        transition: background-color 120ms cubic-bezier(0.16, 1, 0.3, 1),
            box-shadow 120ms cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* พื้นที่คลิกของ popover cell เผยร่องรอยว่าคลิกแก้ได้เมื่อ hover */
    .qedit {
        display: inline-block;
        padding: 2px 6px;
        margin: -2px -6px;
        position: relative;
    }

    .qedit:hover,
    .qedit:focus-visible {
        background-color: var(--bs-tertiary-bg);
        outline: none;
    }

    .qedit:focus-visible {
        box-shadow: 0 0 0 var(--bs-focus-ring-width) var(--bs-focus-ring-color);
    }

    /* ปากกาเล็ก ๆ บอกใบ้ตอน hover เท่านั้น (ไม่รกตอนปกติ) */
    .qedit::after {
        content: "\f304";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        font-size: 9px;
        color: var(--bs-tertiary-color);
        margin-left: 5px;
        opacity: 0;
        transition: opacity 120ms ease;
    }

    .qedit:hover::after,
    .qedit:focus-visible::after {
        opacity: 1;
    }

    .qedit-modal:hover {
        background-color: var(--bs-tertiary-bg);
        box-shadow: 0 0 0 4px var(--bs-tertiary-bg);
    }

    .qedit.is-saving {
        opacity: 0.55;
        pointer-events: none;
    }

    /* ===== Popover ===== */
    .qedit-pop {
        position: fixed;
        z-index: 1060;
        min-width: 208px;
        max-width: 288px;
        background: var(--bs-body-bg);
        color: var(--bs-body-color);
        border: 1px solid var(--bs-border-color);
        border-radius: 10px;
        box-shadow: var(--bs-box-shadow);
        padding: 0.7rem 0.75rem;
        opacity: 0;
        transform: translateY(-4px);
        transition: opacity 140ms cubic-bezier(0.16, 1, 0.3, 1),
            transform 140ms cubic-bezier(0.16, 1, 0.3, 1);
    }

    .qedit-pop.is-open {
        opacity: 1;
        transform: translateY(0);
    }

    .qedit-pop__title {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--bs-secondary-color);
        margin-bottom: 0.5rem;
    }

    .qedit-pop__chips {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }

    .qedit-chip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        width: 100%;
        text-align: left;
        border: 1px solid var(--bs-border-color);
        background: var(--bs-body-bg);
        color: var(--bs-body-color);
        border-radius: 8px;
        padding: 0.42rem 0.6rem;
        font-size: 0.86rem;
        line-height: 1.2;
        cursor: pointer;
        transition: background-color 120ms ease, border-color 120ms ease;
    }

    .qedit-chip:hover {
        background: var(--bs-secondary-bg);
    }

    .qedit-chip.is-active {
        border-color: var(--bs-primary-border-subtle);
        color: var(--bs-primary-text-emphasis);
        background: var(--bs-primary-bg-subtle);
        font-weight: 600;
    }

    .qedit-chip__check {
        opacity: 0;
        color: var(--bs-primary-text-emphasis);
        font-size: 0.8rem;
    }

    .qedit-chip.is-active .qedit-chip__check {
        opacity: 1;
    }

    .qedit-pop__input {
        display: flex;
        gap: 0.4rem;
    }

    .qedit-pop__input .form-control {
        min-height: 40px;
        border-radius: 8px;
        border: 1px solid var(--bs-border-color);
        font-size: 0.9rem;
    }

    .qedit-pop__input .form-control:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 var(--bs-focus-ring-width) var(--bs-focus-ring-color);
    }

    .qedit-pop__save {
        flex-shrink: 0;
        min-width: 40px;
        border-radius: 8px;
        font-weight: 600;
    }

    /* ===== Undo toast ===== */
    .qedit-toast {
        position: fixed;
        z-index: 1080;
        left: 50%;
        bottom: 1.25rem;
        transform: translateX(-50%) translateY(8px);
        display: flex;
        align-items: center;
        gap: 0.85rem;
        background: var(--bs-secondary-bg);
        color: var(--bs-body-color);
        border: 1px solid var(--bs-border-color);
        border-radius: 999px;
        padding: 0.55rem 0.7rem 0.55rem 1.1rem;
        font-size: 0.86rem;
        box-shadow: var(--bs-box-shadow);
        opacity: 0;
        pointer-events: none;
        transition: opacity 180ms cubic-bezier(0.16, 1, 0.3, 1),
            transform 180ms cubic-bezier(0.16, 1, 0.3, 1);
    }

    .qedit-toast.is-open {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
        pointer-events: auto;
    }

    .qedit-toast__btn {
        font-weight: 600;
        border-radius: 999px;
        padding: 0.28rem 0.85rem;
        cursor: pointer;
    }

    @media (min-width: 992px) {
        .qedit-toast {
            left: auto;
            right: 1.5rem;
            transform: translateY(8px);
        }

        .qedit-toast.is-open {
            transform: translateY(0);
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .qedit,
        .qedit-modal,
        .qedit-pop,
        .qedit-toast {
            transition: opacity 80ms linear;
        }

        .qedit-pop {
            transform: none;
        }
    }
</style>

<?php
$js = <<<JS
(function () {
    var CFG = {$config};

    // ---- DOM (สร้างครั้งเดียว) ----
    var pop = document.getElementById('qedit-pop');
    if (!pop) {
        pop = document.createElement('div');
        pop.id = 'qedit-pop';
        pop.className = 'qedit-pop';
        pop.setAttribute('role', 'dialog');
        pop.hidden = true;
        pop.innerHTML = '<div class="qedit-pop__title"></div><div class="qedit-pop__body"></div>';
        document.body.appendChild(pop);
    }
    var toast = document.getElementById('qedit-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'qedit-toast';
        toast.className = 'qedit-toast';
        toast.hidden = true;
        toast.innerHTML = '<span class="qedit-toast__msg"></span><button type="button" class="qedit-toast__btn btn btn-sm btn-outline-secondary rounded-pill">ยกเลิก</button>';
        document.body.appendChild(toast);
    }

    var csrfParam = document.querySelector('meta[name=csrf-param]');
    var csrfToken = document.querySelector('meta[name=csrf-token]');
    csrfParam = csrfParam ? csrfParam.getAttribute('content') : '_csrf';
    csrfToken = csrfToken ? csrfToken.getAttribute('content') : '';

    var current = null;   // เซลล์ที่กำลังแก้
    var toastTimer = null;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function closePop() {
        // ทำลาย instance ปฏิทินเดิม ไม่ให้ค้างเมื่อสร้าง popover ใหม่
        try {
            var df = pop.querySelector('#qedit-date-field');
            if (df && window.jQuery && jQuery(df).datetimepicker) { jQuery(df).datetimepicker('destroy'); }
        } catch (e) {}
        pop.classList.remove('is-open');
        window.setTimeout(function () { pop.hidden = true; }, 140);
        current = null;
    }

    function placePop(anchor) {
        var r = anchor.getBoundingClientRect();
        pop.hidden = false;
        var pw = pop.offsetWidth, ph = pop.offsetHeight;
        var left = Math.min(Math.max(8, r.left), window.innerWidth - pw - 8);
        var top = r.bottom + 6;
        if (top + ph > window.innerHeight - 8) {
            top = Math.max(8, r.top - ph - 6);
        }
        pop.style.left = left + 'px';
        pop.style.top = top + 'px';
        requestAnimationFrame(function () { pop.classList.add('is-open'); });
    }

    function buildBody(type, field, value) {
        var body = pop.querySelector('.qedit-pop__body');
        if (type === 'enum') {
            var opts = (CFG.options[field] || []);
            var html = '<div class="qedit-pop__chips">';
            opts.forEach(function (o) {
                var active = String(o.value) === String(value) ? ' is-active' : '';
                html += '<button type="button" class="qedit-chip' + active + '" data-val="' + esc(o.value) + '">'
                    + '<span>' + esc(o.label) + '</span>'
                    + '<i class="fa-solid fa-check qedit-chip__check"></i></button>';
            });
            html += '</div>';
            body.innerHTML = html;
        } else if (type === 'number') {
            body.innerHTML = '<div class="qedit-pop__input">'
                + '<input type="number" step="0.01" min="0" class="form-control qedit-pop__field" value="' + esc(value) + '">'
                + '<button type="button" class="qedit-pop__save btn btn-primary"><i class="fa-solid fa-check"></i></button></div>';
        } else if (type === 'date') {
            body.innerHTML = '<div class="qedit-pop__input">'
                + '<input type="text" id="qedit-date-field" autocomplete="off" class="form-control qedit-pop__field" value="' + esc(value) + '" placeholder="วว/ดด/ปปปป (พ.ศ.)">'
                + '<button type="button" class="qedit-pop__save btn btn-primary"><i class="fa-solid fa-check"></i></button></div>';
        }
    }

    function openEditor(anchor) {
        var tr = anchor.closest('tr');
        current = {
            el: anchor,
            id: tr ? tr.getAttribute('data-qedit-id') : null,
            field: anchor.getAttribute('data-qedit-field'),
            type: anchor.getAttribute('data-qedit-type'),
            value: anchor.getAttribute('data-qedit-value'),
            oldHtml: anchor.innerHTML
        };
        pop.querySelector('.qedit-pop__title').textContent = anchor.getAttribute('data-qedit-title') || 'แก้ไข';
        buildBody(current.type, current.field, current.value);
        placePop(anchor);

        // วันที่ใช้ DatepickerThai (พ.ศ.); bind หลัง input อยู่ใน DOM แล้ว
        if (current.type === 'date' && typeof thaiDatepicker === 'function') {
            thaiDatepicker('#qedit-date-field');
        }

        var fld = pop.querySelector('.qedit-pop__field');
        if (fld) { fld.focus(); if (current.type !== 'date' && fld.select) { fld.select(); } }
    }

    // ส่งค่าไปบันทึกแบบ optimistic พร้อม undo
    function save(id, field, value, cell, oldValue, oldHtml, isUndo) {
        cell.classList.add('is-saving');
        var data = { field: field, value: value };
        data[csrfParam] = csrfToken;

        jQuery.ajax({
            url: CFG.url + '?id=' + encodeURIComponent(id),
            type: 'POST',
            dataType: 'json',
            data: data
        }).done(function (res) {
            cell.classList.remove('is-saving');
            if (res && res.status === 'success') {
                cell.innerHTML = res.html;
                cell.setAttribute('data-qedit-value', res.value);
                if (!isUndo) {
                    showToast(id, field, oldValue, cell, oldHtml, res.value);
                }
            } else {
                cell.innerHTML = oldHtml;
                cell.setAttribute('data-qedit-value', oldValue);
                errorAlert(res && res.message);
            }
        }).fail(function (xhr) {
            cell.classList.remove('is-saving');
            cell.innerHTML = oldHtml;
            cell.setAttribute('data-qedit-value', oldValue);
            errorAlert('ไม่สามารถติดต่อ Server ได้ (Error ' + xhr.status + ')');
        });
    }

    function errorAlert(msg) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'บันทึกไม่สำเร็จ', text: msg || 'กรุณาลองใหม่', timer: 2200, showConfirmButton: false });
        }
    }

    function showToast(id, field, oldValue, cell, oldHtml, newValue) {
        window.clearTimeout(toastTimer);
        toast.hidden = false;
        toast.querySelector('.qedit-toast__msg').textContent = 'อัปเดตรายการแล้ว';
        requestAnimationFrame(function () { toast.classList.add('is-open'); });

        var btn = toast.querySelector('.qedit-toast__btn');
        btn.onclick = function () {
            hideToast();
            // ย้อนกลับไปค่าเดิม
            save(id, field, oldValue, cell, newValue, cell.innerHTML, true);
        };
        toastTimer = window.setTimeout(hideToast, 5000);
    }

    function hideToast() {
        toast.classList.remove('is-open');
        window.setTimeout(function () { toast.hidden = true; }, 200);
    }

    // เปิด toast helper สำหรับ modal quick-edit ให้ใช้ร่วมกันได้
    window.erpQuickEditToast = function (msg) {
        window.clearTimeout(toastTimer);
        toast.hidden = false;
        toast.querySelector('.qedit-toast__msg').textContent = msg || 'อัปเดตรายการแล้ว';
        toast.querySelector('.qedit-toast__btn').style.display = 'none';
        requestAnimationFrame(function () { toast.classList.add('is-open'); });
        toastTimer = window.setTimeout(function () {
            hideToast();
            toast.querySelector('.qedit-toast__btn').style.display = '';
        }, 3000);
    };

    function commitInput() {
        if (!current) { return; }
        var fld = pop.querySelector('.qedit-pop__field');
        if (!fld) { return; }
        var val = fld.value;
        var c = current;
        closePop();
        if (String(val) !== String(c.value)) {
            save(c.id, c.field, val, c.el, c.value, c.oldHtml, false);
        }
    }

    // ---- Delegated handlers (rebind ปลอดภัยเมื่อ pjax reload) ----
    jQuery(document)
        .off('click.qedit keydown.qedit')
        .on('click.qedit', '.qedit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (current && current.el === this) { closePop(); return; }
            openEditor(this);
        })
        .on('keydown.qedit', '.qedit', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openEditor(this); }
        });

    // เลือก chip (enum) → บันทึกทันที
    jQuery(pop)
        .off('click.qedit keydown.qedit')
        .on('click.qedit', '.qedit-chip', function () {
            if (!current) { return; }
            var val = this.getAttribute('data-val');
            var c = current;
            closePop();
            if (String(val) !== String(c.value)) {
                save(c.id, c.field, val, c.el, c.value, c.oldHtml, false);
            }
        })
        .on('click.qedit', '.qedit-pop__save', commitInput)
        .on('keydown.qedit', '.qedit-pop__field', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); commitInput(); }
            if (e.key === 'Escape') { closePop(); }
        });

    // ปิด popover เมื่อคลิกนอก / Esc / scroll
    jQuery(document)
        .off('mousedown.qeditout keydown.qeditesc')
        .on('mousedown.qeditout', function (e) {
            // คลิกในปฏิทิน xdsoft (append ที่ body) ไม่ถือว่าคลิกนอก popover
            if (e.target.closest && e.target.closest('.xdsoft_datetimepicker')) { return; }
            if (current && !pop.contains(e.target) && !current.el.contains(e.target)) { closePop(); }
        })
        .on('keydown.qeditesc', function (e) {
            if (e.key === 'Escape' && current) { closePop(); }
        });
    jQuery(window)
        .off('scroll.qedit resize.qedit')
        .on('scroll.qedit resize.qedit', function () { if (current) { closePop(); } });
})();
JS;
$this->registerJs($js);
?>
