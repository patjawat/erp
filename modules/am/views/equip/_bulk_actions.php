<?php

use yii\helpers\Url;

/** @var yii\web\View $this */

// ปุ่มในแถบ bulk — ชี้ไปที่ actionBulkEdit ตาม section (เปิดในโมดัลแบบเดียวกับ quick-edit)
$bulkButtons = [
    ['section' => 'category', 'icon' => 'fa-tags', 'label' => 'หมวดหมู่'],
    ['section' => 'assignment', 'icon' => 'fa-location-dot', 'label' => 'สถานที่ / ผู้รับผิดชอบ'],
    ['section' => 'receive_date', 'icon' => 'fa-regular fa-calendar', 'label' => 'วันที่รับ'],
    ['section' => 'price', 'icon' => 'fa-tag', 'label' => 'ราคาแรกรับ'],
    ['section' => 'asset_condition', 'icon' => 'fa-heart-pulse', 'label' => 'สภาพ'],
    ['section' => 'risk_level', 'icon' => 'fa-triangle-exclamation', 'label' => 'ความเสี่ยง'],
    ['section' => 'asset_status', 'icon' => 'fa-circle-info', 'label' => 'สถานะ'],
];
?>

<style>
    /* ===== แถบแก้ไขหลายรายการ (bulk) ===== */
    .equip-bulk-bar {
        position: fixed;
        left: 50%;
        bottom: 1.5rem;
        transform: translateX(-50%) translateY(12px);
        z-index: 1055;
        display: flex;
        align-items: center;
        gap: 1rem;
        max-width: calc(100vw - 2rem);
        background: #1e293b;
        color: #fff;
        border-radius: 14px;
        padding: 0.6rem 0.75rem 0.6rem 1.1rem;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.28);
        opacity: 0;
        pointer-events: none;
        transition: opacity 160ms cubic-bezier(0.16, 1, 0.3, 1),
            transform 160ms cubic-bezier(0.16, 1, 0.3, 1);
    }

    .equip-bulk-bar.is-open {
        opacity: 1;
        pointer-events: auto;
        transform: translateX(-50%) translateY(0);
    }

    .equip-bulk-bar__count {
        font-size: 0.86rem;
        font-weight: 600;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .equip-bulk-bar__count .n {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        padding: 0 6px;
        margin-right: 4px;
        background: #3b82f6;
        border-radius: 999px;
        font-size: 0.78rem;
    }

    .equip-bulk-bar__actions {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: wrap;
    }

    .equip-bulk-bar__btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border: 1px solid rgba(255, 255, 255, 0.14);
        background: rgba(255, 255, 255, 0.06);
        color: #fff;
        border-radius: 9px;
        padding: 0.34rem 0.7rem;
        font-size: 0.82rem;
        line-height: 1.2;
        text-decoration: none;
        cursor: pointer;
        transition: background-color 120ms ease, border-color 120ms ease;
    }

    .equip-bulk-bar__btn:hover {
        background: rgba(255, 255, 255, 0.16);
        color: #fff;
    }

    .equip-bulk-bar__btn i {
        font-size: 0.78rem;
        opacity: 0.85;
    }

    .equip-bulk-bar__clear {
        flex-shrink: 0;
        border: none;
        background: transparent;
        color: rgba(255, 255, 255, 0.7);
        font-size: 1.1rem;
        line-height: 1;
        padding: 0.2rem 0.4rem;
        cursor: pointer;
        border-radius: 8px;
    }

    .equip-bulk-bar__clear:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.12);
    }

    @media (max-width: 640px) {
        .equip-bulk-bar {
            left: 0.75rem;
            right: 0.75rem;
            transform: translateY(12px);
            flex-direction: column;
            align-items: stretch;
        }

        .equip-bulk-bar.is-open {
            transform: translateY(0);
        }

        .equip-bulk-bar__actions {
            justify-content: center;
        }
    }
</style>

<div id="equip-bulk-bar" class="equip-bulk-bar" hidden>
    <div class="equip-bulk-bar__count"><span class="n">0</span> รายการที่เลือก</div>
    <div class="equip-bulk-bar__actions">
        <?php foreach ($bulkButtons as $b): ?>
            <?php $iconClass = strpos($b['icon'], 'fa-regular') !== false ? $b['icon'] : 'fa-solid ' . $b['icon']; ?>
            <a class="equip-bulk-bar__btn open-modal" data-size="modal-md"
               href="<?= Url::to(['/am/equip/bulk-edit', 'section' => $b['section']]) ?>">
                <i class="<?= $iconClass ?>"></i><?= $b['label'] ?>
            </a>
        <?php endforeach; ?>
    </div>
    <button type="button" class="equip-bulk-bar__clear" title="ล้างการเลือก" aria-label="ล้างการเลือก">&times;</button>
</div>

<?php
$js = <<<JS
(function () {
    var bar = document.getElementById('equip-bulk-bar');
    if (!bar) { return; }
    var countEl = bar.querySelector('.equip-bulk-bar__count .n');

    function checks() {
        return Array.prototype.slice.call(document.querySelectorAll('.equip-bulk-check'));
    }
    function checkedCount() {
        return document.querySelectorAll('.equip-bulk-check:checked').length;
    }

    function refresh() {
        var n = checkedCount();
        countEl.textContent = n;

        if (n > 0) {
            bar.hidden = false;
            requestAnimationFrame(function () { bar.classList.add('is-open'); });
        } else {
            bar.classList.remove('is-open');
            window.setTimeout(function () { if (checkedCount() === 0) { bar.hidden = true; } }, 180);
        }

        // sync ปุ่มเลือกทั้งหมด (checked / indeterminate)
        var all = checks();
        var allBox = document.querySelector('.equip-bulk-all');
        if (allBox) {
            allBox.checked = all.length > 0 && n === all.length;
            allBox.indeterminate = n > 0 && n < all.length;
        }
    }

    function clearAll() {
        checks().forEach(function (c) { c.checked = false; });
        refresh();
    }

    jQuery(document)
        .off('change.equipbulk', '.equip-bulk-check')
        .on('change.equipbulk', '.equip-bulk-check', refresh)
        .off('change.equipbulkall', '.equip-bulk-all')
        .on('change.equipbulkall', '.equip-bulk-all', function () {
            var on = this.checked;
            checks().forEach(function (c) { c.checked = on; });
            refresh();
        });

    jQuery(bar.querySelector('.equip-bulk-bar__clear')).off('click.equipbulk').on('click.equipbulk', clearAll);

    // ตั้งค่าเริ่มต้นเมื่อ list ถูก render/reload
    refresh();
})();
JS;
$this->registerJs($js);
?>
