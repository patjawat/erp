<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var int $year */
/** @var app\modules\hr\models\WorkforceProfile $profile */
/** @var array $rows */
/** @var array $types */
/** @var array $totals */
/** @var array $outOfScope */

$this->title = 'กรอบ Outsource';
$outsourceTypes = array_filter($types, static fn ($t) => !$t['in_frame']);
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/hr/menu', ['active' => 'workforce-frame']) ?>
<?php $this->endBlock(); ?>

<div class="card mb-3">
    <div class="card-body">
        <div class="fw-semibold">รอบปี <?= (int) $year ?> · <?= Html::encode($profile->statusLabel()) ?></div>
        <p class="text-body-secondary small mb-0">
            เกณฑ์ สป.สธ. ให้นับเฉพาะ 5 ประเภทการจ้าง (ข้าราชการ พนักงานราชการ พนักงานกระทรวงฯ ลูกจ้างประจำ ลูกจ้างชั่วคราวรายเดือน)
            รวมเป็นกรอบสายสนับสนุน Back Office ส่วน<strong><?= Html::encode(implode(' · ', array_column($outsourceTypes, 'title'))) ?></strong>
            และงานจ้างเหมา ให้นับในกรอบ Outsource แยกต่างหาก
        </p>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-6 col-lg-4">
        <div class="card h-100"><div class="card-body py-2">
            <div class="small text-body-secondary">อยู่ในกรอบ Outsource</div>
            <div class="fs-4 fw-semibold"><?= (int) $totals['outsource'] ?></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="card h-100"><div class="card-body py-2">
            <div class="small text-body-secondary">นับในกรอบ Back Office</div>
            <div class="fs-4 fw-semibold"><?= (int) $totals['in_frame'] ?></div>
        </div></div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card h-100"><div class="card-body py-2">
            <div class="small text-body-secondary">สายงานที่มีคน Outsource</div>
            <div class="fs-4 fw-semibold"><?= count($rows) ?></div>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-body-tertiary fw-semibold">คนที่อยู่ในกรอบ Outsource แยกตามสายงาน</div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>สายงานตามเกณฑ์</th>
                    <th style="width:8rem" class="text-end">Outsource</th>
                    <th style="width:9rem" class="text-end">นับในกรอบ</th>
                    <th style="width:8rem" class="text-end">กรอบตามเกณฑ์</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                    <tr><td colspan="4" class="text-center text-body-secondary py-4">ไม่มีบุคลากรในกรอบ Outsource</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td>
                            <div><?= Html::encode($row['line']->title) ?></div>
                            <div class="small text-body-secondary"><?= Html::encode($row['line']->categoryLabel()) ?></div>
                        </td>
                        <td class="text-end font-monospace fw-semibold"><?= (int) $row['outsource'] ?></td>
                        <td class="text-end font-monospace text-body-secondary"><?= (int) $row['in_frame'] ?></td>
                        <td class="text-end font-monospace">
                            <?= $row['frame'] === null ? '—' : rtrim(rtrim(number_format((float) $row['frame'], 2), '0'), '.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($outOfScope !== []): ?>
    <div class="card mt-3">
        <div class="card-header bg-body-tertiary fw-semibold">ตำแหน่งที่ไม่มีในเกณฑ์ (<?= count($outOfScope) ?>)</div>
        <div class="card-body">
            <p class="small text-body-secondary">ไม่มีสายงานตรงในเกณฑ์ สป.สธ. จึงไม่มีทั้งกรอบ Back Office และกรอบ Outsource</p>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($outOfScope as $item): ?>
                    <span class="badge bg-body-secondary text-body">
                        <?= Html::encode($item['title']) ?> · <?= (int) $item['count'] ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="d-flex gap-2 mt-3">
    <?= Html::a('กลับไปหน้ากรอบ', ['index', 'thai_year' => $year], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::a('สรุปสำหรับส่ง สสจ.', ['report', 'thai_year' => $year], ['class' => 'btn btn-outline-primary']) ?>
</div>
