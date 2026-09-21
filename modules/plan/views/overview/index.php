<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $thaiYear */
/** @var array $years */
/** @var string $status */
/** @var array $summary */

$statusOptions = [
    'all'     => 'ทุกสถานะ',
    'approve' => 'อนุมัติแล้ว',
    'submit'  => 'รออนุมัติ',
    'draft'   => 'ร่าง',
    'reject'  => 'ไม่อนุมัติ',
];
$status = $status ?? 'all';

$this->title = 'แผนรายจ่าย';
$this->params['breadcrumbs'][] = ['label' => 'แผนงาน', 'url' => ['/plan/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

// ลำดับเดือนตามปีงบประมาณ (เริ่ม ต.ค.) + สีประจำไตรมาส
$monthCols = [
    ['k' => 'm10', 'l' => 'ต.ค.', 'q' => 1],
    ['k' => 'm11', 'l' => 'พ.ย.', 'q' => 1],
    ['k' => 'm12', 'l' => 'ธ.ค.', 'q' => 1],
    ['k' => 'm1',  'l' => 'ม.ค.', 'q' => 2],
    ['k' => 'm2',  'l' => 'ก.พ.', 'q' => 2],
    ['k' => 'm3',  'l' => 'มี.ค.', 'q' => 2],
    ['k' => 'm4',  'l' => 'เม.ย.', 'q' => 3],
    ['k' => 'm5',  'l' => 'พ.ค.', 'q' => 3],
    ['k' => 'm6',  'l' => 'มิ.ย.', 'q' => 3],
    ['k' => 'm7',  'l' => 'ก.ค.', 'q' => 4],
    ['k' => 'm8',  'l' => 'ส.ค.', 'q' => 4],
    ['k' => 'm9',  'l' => 'ก.ย.', 'q' => 4],
];
$qClass = [1 => 'bg-primary', 2 => 'bg-secondary', 3 => 'bg-success', 4 => 'bg-danger'];

$shortPrev = substr((string) ($thaiYear - 1), -2); // ปีของ ต.ค.-ธ.ค.
$shortCur  = substr((string) $thaiYear, -2);        // ปีของ ม.ค.-ก.ย.

$fmt = fn($v) => number_format((float) $v, 2);
$cell = fn($v, $q) => '<td class="text-end ' . $qClass[$q] . ' text-dark bg-opacity-25">' . number_format((float) $v, 2) . '</td>';
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-clock">
            <path d="M16 14v2.2l1.6 1" />
            <path d="M16 4h2a2 2 0 0 1 2 2v.832" />
            <path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h2" />
            <circle cx="16" cy="16" r="6" />
            <rect x="8" y="2" width="8" height="4" rx="1" />
        </svg>
        <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'overview']) ?>
<?php $this->endBlock(); ?>

<div class="d-flex align-items-center gap-2 mb-3">
    <?php $form = \yii\widgets\ActiveForm::begin(['method' => 'get', 'action' => ['index'], 'options' => ['class' => 'd-flex align-items-center gap-2']]); ?>
    <label class="fw-medium mb-0">ปีงบประมาณ</label>
    <select name="thai_year" class="form-select w-auto" onchange="this.form.submit()">
        <?php foreach ($years as $y): ?>
            <option value="<?= $y ?>" <?= (int) $y === $thaiYear ? 'selected' : '' ?>><?= $y ?></option>
        <?php endforeach; ?>
    </select>
    <label class="fw-medium mb-0 ms-2">สถานะ</label>
    <select name="status" class="form-select w-auto" onchange="this.form.submit()">
        <?php foreach ($statusOptions as $val => $label): ?>
            <option value="<?= $val ?>" <?= $status === $val ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
    </select>
    <?php \yii\widgets\ActiveForm::end(); ?>

    <div class="ms-auto d-flex align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="ovExpandAll">
            <i class="bi bi-arrows-expand me-1"></i>ขยายทุกรายการ
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="ovCollapseAll">
            <i class="bi bi-arrows-collapse me-1"></i>ย่อทั้งหมด
        </button>
    </div>
</div>

<div class="table-responsive">
<table class="table table-bordered table-hover table-overview align-middle">
    <thead>
        <tr>
            <td rowspan="2" colspan="2" class="fw-semibold text-center align-middle" style="min-width:260px">รายการ</td>
            <td rowspan="2" class="fw-semibold text-center align-middle">แผนปี <?= $thaiYear ?></td>
            <td colspan="3" class="fw-semibold text-center bg-primary text-dark bg-opacity-25">ไตรมาส 1</td>
            <td colspan="3" class="fw-semibold text-center bg-secondary text-dark bg-opacity-25">ไตรมาส 2</td>
            <td colspan="3" class="fw-semibold text-center bg-success text-dark bg-opacity-25">ไตรมาส 3</td>
            <td colspan="3" class="fw-semibold text-center bg-danger text-dark bg-opacity-25">ไตรมาส 4</td>
        </tr>
        <tr>
            <?php foreach ($monthCols as $m): ?>
                <?php $yy = $m['q'] === 1 ? $shortPrev : $shortCur; ?>
                <td class="fw-semibold text-center <?= $qClass[$m['q']] ?> text-dark bg-opacity-25"><?= $m['l'] . ' ' . $yy ?></td>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody class="table-group-divider">
        <?php if (empty($summary['types'])): ?>
            <tr><td colspan="15" class="text-center text-muted py-3">ยังไม่มีข้อมูลแผนสำหรับปีงบประมาณ <?= $thaiYear ?></td></tr>
        <?php endif; ?>

        <?php foreach ($summary['types'] as $typeCode => $type): ?>
            <!-- หัวข้อประเภท (คลิกเพื่อย่อ/ขยายทั้งกลุ่ม) -->
            <tr class="ov-type" data-type="<?= Html::encode($typeCode) ?>" role="button">
                <td colspan="15" class="fw-semibold bg-warning text-dark bg-opacity-25">
                    <i class="fa-solid fa-chevron-down ov-caret me-1"></i><?= Html::encode($type['title']) ?>
                </td>
            </tr>

            <?php foreach ($type['categories'] as $cat): ?>
                <?php
                    $items = $cat['items'] ?? [];
                    $hasItems = count($items) > 0;
                ?>
                <tr class="ov-child ov-cat<?= $hasItems ? '' : ' ov-noexpand' ?>"
                    data-type="<?= Html::encode($typeCode) ?>"
                    data-cat="<?= Html::encode($cat['code']) ?>"
                    <?= $hasItems ? 'role="button"' : '' ?>>
                    <td class="text-center" style="width:24px">
                        <?php if ($hasItems): ?>
                            <i class="fa-solid fa-chevron-right ov-caret text-muted"></i>
                        <?php endif; ?>
                    </td>
                    <td><?= Html::encode($cat['title']) ?></td>
                    <td class="text-end fw-semibold"><?= $fmt($cat['total']) ?></td>
                    <?php foreach ($monthCols as $m): ?>
                        <?= $cell($cat[$m['k']], $m['q']) ?>
                    <?php endforeach; ?>
                </tr>

                <?php foreach ($items as $it): ?>
                    <?php $empty = (float) $it['total'] <= 0; ?>
                    <tr class="ov-child ov-item<?= $empty ? ' ov-empty' : '' ?>"
                        data-type="<?= Html::encode($typeCode) ?>"
                        data-cat="<?= Html::encode($cat['code']) ?>"
                        style="display:none">
                        <td style="width:24px"></td>
                        <td class="ps-4 small">
                            <i class="fa-solid fa-turn-up fa-rotate-90 text-muted me-1 small"></i>
                            <?= Html::encode($it['title']) ?>
                        </td>
                        <td class="text-end small"><?= $fmt($it['total']) ?></td>
                        <?php foreach ($monthCols as $m): ?>
                            <td class="text-end small <?= $qClass[$m['q']] ?> text-dark bg-opacity-10"><?= $fmt($it[$m['k']]) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <!-- รวมประเภท -->
            <tr class="ov-child fw-semibold table-light" data-type="<?= Html::encode($typeCode) ?>">
                <td colspan="2" class="text-end">รวม<?= Html::encode($type['title']) ?></td>
                <td class="text-end"><?= $fmt($type['sub']['total']) ?></td>
                <?php foreach ($monthCols as $m): ?>
                    <td class="text-end <?= $qClass[$m['q']] ?> text-dark bg-opacity-25"><?= $fmt($type['sub'][$m['k']]) ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>

        <!-- รวมทั้งสิ้น -->
        <tr class="fw-bold table-active">
            <td colspan="2" class="text-end">รวมรายจ่ายทั้งสิ้น</td>
            <td class="text-end"><?= $fmt($summary['grand']['total']) ?></td>
            <?php foreach ($monthCols as $m): ?>
                <td class="text-end"><?= $fmt($summary['grand'][$m['k']]) ?></td>
            <?php endforeach; ?>
        </tr>
    </tbody>
</table>
</div>

<p class="text-muted small mt-2">
    <i class="fa-solid fa-circle-info me-1"></i>
    รวมยอดจากคำขอที่ผูกรายการ (plan item) ในปีงบประมาณ <?= $thaiYear ?> — หมวดรายรับ/งบกลางยังไม่รวมในตารางนี้ (ยังไม่มีชุดข้อมูล)
</p>

<?php
$css = <<<CSS
.ov-type { cursor: pointer; }
.ov-cat[role="button"] { cursor: pointer; }
.ov-caret { transition: transform .15s ease; }
.ov-cat.ov-open .ov-caret { transform: rotate(90deg); }
.ov-type.ov-collapsed .ov-caret { transform: rotate(-90deg); }
.ov-item.ov-empty td { color: #b02a37; }
.table-overview .ov-item td { background-clip: padding-box; }
CSS;
$this->registerCss($css);

$js = <<<JS
(function () {
    var table = document.querySelector('.table-overview');
    if (!table) return;

    function catItems(catRow) {
        return table.querySelectorAll(
            '.ov-item[data-type="' + catRow.dataset.type + '"][data-cat="' + catRow.dataset.cat + '"]'
        );
    }

    function openCat(catRow, open) {
        if (catRow.classList.contains('ov-noexpand')) return;
        catRow.classList.toggle('ov-open', open);
        catItems(catRow).forEach(function (r) {
            // เปิดรายการย่อยเฉพาะเมื่อกลุ่มประเภทไม่ได้ถูกย่ออยู่
            var typeRow = table.querySelector('.ov-type[data-type="' + r.dataset.type + '"]');
            var typeCollapsed = typeRow && typeRow.classList.contains('ov-collapsed');
            r.style.display = (open && !typeCollapsed) ? '' : 'none';
        });
    }

    function toggleType(typeRow) {
        var collapsed = typeRow.classList.toggle('ov-collapsed');
        var rows = table.querySelectorAll('.ov-child[data-type="' + typeRow.dataset.type + '"]');
        rows.forEach(function (r) {
            if (collapsed) {
                r.style.display = 'none';
            } else if (r.classList.contains('ov-item')) {
                // แสดงรายการย่อยเฉพาะหมวดที่กำลังเปิดอยู่
                var catRow = table.querySelector(
                    '.ov-cat[data-type="' + r.dataset.type + '"][data-cat="' + r.dataset.cat + '"]'
                );
                r.style.display = (catRow && catRow.classList.contains('ov-open')) ? '' : 'none';
            } else {
                r.style.display = '';
            }
        });
    }

    table.addEventListener('click', function (e) {
        var catRow = e.target.closest('.ov-cat');
        if (catRow && table.contains(catRow)) {
            openCat(catRow, !catRow.classList.contains('ov-open'));
            return;
        }
        var typeRow = e.target.closest('.ov-type');
        if (typeRow && table.contains(typeRow)) {
            toggleType(typeRow);
        }
    });

    document.getElementById('ovExpandAll')?.addEventListener('click', function () {
        table.querySelectorAll('.ov-type.ov-collapsed').forEach(toggleType);
        table.querySelectorAll('.ov-cat').forEach(function (c) { openCat(c, true); });
    });

    document.getElementById('ovCollapseAll')?.addEventListener('click', function () {
        table.querySelectorAll('.ov-cat').forEach(function (c) { openCat(c, false); });
    });
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
