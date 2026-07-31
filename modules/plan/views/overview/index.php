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
            <!-- หัวข้อประเภท -->
            <tr>
                <td colspan="15" class="fw-semibold bg-warning text-dark bg-opacity-25">
                    <i class="fa-solid fa-chevron-right me-1"></i><?= Html::encode($type['title']) ?>
                </td>
            </tr>

            <?php foreach ($type['categories'] as $cat): ?>
                <tr>
                    <td style="width:16px"></td>
                    <td><?= Html::encode($cat['title']) ?></td>
                    <td class="text-end"><?= $fmt($cat['total']) ?></td>
                    <?php foreach ($monthCols as $m): ?>
                        <?= $cell($cat[$m['k']], $m['q']) ?>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>

            <!-- รวมประเภท -->
            <tr class="fw-semibold table-light">
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
