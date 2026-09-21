<?php
/**
 * ปุ่ม/สคริปต์ย่อ-ขยายกลุ่มหน่วยงาน สำหรับหน้าทะเบียนแผน (expenses/parcel/personnel)
 * หัวกลุ่ม: <tr class="plan-grp" data-grp="gN">  แถวย่อย: <tr class="plan-grp-row" data-grp="gN">
 * @var yii\web\View $this
 */

use yii\web\View;

$css = <<<CSS
.plan-grp { cursor: pointer; }
.plan-grp td { background-color: var(--bs-primary-bg-subtle, #cfe2ff); }
.plan-grp:hover td { filter: brightness(0.97); }
.plan-grp-caret { transition: transform .15s ease; }
.plan-grp.collapsed .plan-grp-caret { transform: rotate(-90deg); }
CSS;
$this->registerCss($css);

$js = <<<JS
(function () {
    var wrap = document.getElementById('plan-group-table');
    if (!wrap) return;

    function toggle(header) {
        var collapsed = header.classList.toggle('collapsed');
        wrap.querySelectorAll('.plan-grp-row[data-grp="' + header.dataset.grp + '"]').forEach(function (r) {
            r.style.display = collapsed ? 'none' : '';
        });
    }

    wrap.addEventListener('click', function (e) {
        if (e.target.closest('a, button, .action, form')) return; // ไม่ชนปุ่มจัดการ
        var header = e.target.closest('.plan-grp');
        if (header && wrap.contains(header)) toggle(header);
    });

    document.getElementById('planExpandAll')?.addEventListener('click', function () {
        wrap.querySelectorAll('.plan-grp.collapsed').forEach(toggle);
    });
    document.getElementById('planCollapseAll')?.addEventListener('click', function () {
        wrap.querySelectorAll('.plan-grp:not(.collapsed)').forEach(toggle);
    });
})();
JS;
$this->registerJs($js, View::POS_END);
