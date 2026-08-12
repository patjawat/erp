<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\hr\models\CompetencyYear;

/** @var yii\web\View $this */
/** @var int $fiscalYear */
/** @var int[] $years */
/** @var CompetencyYear[] $items */
/** @var array<int, array{levels:int, indicators:int}> $counts */
/** @var int[] $copySourceYears */

$this->title = 'ตั้งค่าสมรรถนะหลัก (Core Competency)';
echo $this->render('@app/modules/hr/views/workforce/_styles');
echo $this->render('_styles');
$this->beginBlock('page-title'); echo Html::encode($this->title); $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/hr/menu', ['active' => 'core']); $this->endBlock();

$totalIndicators = 0;
$totalLevels = 0;
foreach ($counts as $count) {
    $totalLevels += $count['levels'];
    $totalIndicators += $count['indicators'];
}
$activeCount = count(array_filter($items, static fn ($item): bool => $item->status === CompetencyYear::STATUS_ACTIVE));

// น้ำหนักต่อสมรรถนะในแบบประเมินเดิมคิดเท่ากันทุกตัว = 100 / จำนวนตัวที่ประกาศใช้
$weightPerItem = $activeCount > 0 ? round(100 / $activeCount, 2) : 0;

$statusClass = [
    CompetencyYear::STATUS_ACTIVE => 'cp-badge cp-badge--active',
    CompetencyYear::STATUS_DRAFT => 'cp-badge cp-badge--draft',
    CompetencyYear::STATUS_RETIRED => 'cp-badge cp-badge--retired',
];
?>
<div class="workforce-shell">
    <?= $this->render('@app/modules/hr/views/workforce/_menu', ['active' => 'core']) ?>

    <header class="workforce-head">
        <div>
            <h1>ตั้งค่าสมรรถนะหลัก (Core Competency)</h1>
            <p>ชุดสมรรถนะหลักที่ประกาศใช้ประเมินพฤติกรรมการปฏิบัติราชการของบุคลากรทุกคนในแต่ละปีงบประมาณ</p>
        </div>
        <div>
            <?= Html::a('<i class="bi bi-arrow-left"></i> กลับไปรายชื่อบุคลากร', ['/hr/competency/index', 'fy' => $fiscalYear], [
                'class' => 'btn btn-outline-secondary',
            ]) ?>
        </div>
    </header>

    <form class="cp-toolbar" method="get" action="<?= Url::to(['/hr/competency/setting']) ?>">
        <div>
            <label for="cp-fy">ปีงบประมาณ</label>
            <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), [
                'class' => 'form-select', 'id' => 'cp-fy', 'onchange' => 'this.form.submit()',
            ]) ?>
        </div>
        <div class="ms-auto d-flex align-items-center gap-2">
            <?php if ($copySourceYears !== []): ?>
                <?= Html::button('<i class="bi bi-files"></i> คัดลอกจากปีอื่น', [
                    'class' => 'btn btn-outline-secondary', 'id' => 'cp-copy-open',
                ]) ?>
            <?php endif ?>
            <?= Html::a('<i class="bi bi-plus-lg"></i> เพิ่มสมรรถนะหลัก', ['/hr/competency/form', 'fy' => $fiscalYear], [
                'class' => 'btn btn-primary open-modal', 'data-size' => 'modal-lg',
            ]) ?>
        </div>
    </form>

    <div class="cp-cards">
        <div class="cp-card" style="--cp-accent:#2457a7">
            <span class="cp-card__label"><i class="bi bi-diagram-3"></i> สมรรถนะที่ประกาศใช้</span>
            <span class="cp-card__value"><?= number_format($activeCount) ?></span>
            <span class="cp-card__hint">จากทั้งหมด <?= number_format(count($items)) ?> รายการในปี <?= $fiscalYear ?></span>
        </div>
        <div class="cp-card" style="--cp-accent:#0f766e">
            <span class="cp-card__label"><i class="bi bi-bar-chart-steps"></i> ระดับสมรรถนะรวม</span>
            <span class="cp-card__value"><?= number_format($totalLevels) ?></span>
            <span class="cp-card__hint">ผู้ประเมินให้คะแนนถึงระดับที่คาดหวังของแต่ละคน</span>
        </div>
        <div class="cp-card" style="--cp-accent:#9a6700">
            <span class="cp-card__label"><i class="bi bi-list-check"></i> ข้อพฤติกรรมบ่งชี้</span>
            <span class="cp-card__value"><?= number_format($totalIndicators) ?></span>
            <span class="cp-card__hint">หน่วยย่อยที่สุดที่ให้คะแนน 1–5</span>
        </div>
        <div class="cp-card" style="--cp-accent:#7e22ce">
            <span class="cp-card__label"><i class="bi bi-percent"></i> น้ำหนักต่อสมรรถนะ</span>
            <span class="cp-card__value"><?= $weightPerItem > 0 ? number_format($weightPerItem, 2) : '—' ?></span>
            <span class="cp-card__hint">คิดเท่ากันทุกตัว = 100 ÷ จำนวนที่ประกาศใช้</span>
        </div>
    </div>

    <section class="cp-panel">
        <?php if ($items === []): ?>
            <div class="cp-empty">
                <i class="bi bi-inbox"></i>
                <p>ยังไม่ได้กำหนดสมรรถนะหลักของปีงบประมาณ <?= $fiscalYear ?></p>
                <?php if ($copySourceYears !== []): ?>
                    <p class="text-muted small mb-0">คัดลอกทั้งชุดจากปีอื่นได้ หรือเพิ่มทีละรายการ</p>
                <?php endif ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table cp-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:70px">ลำดับ</th>
                            <th>สมรรถนะ</th>
                            <th style="width:110px" class="text-center">ระดับ</th>
                            <th style="width:130px" class="text-center">ข้อพฤติกรรม</th>
                            <th style="width:120px">สถานะ</th>
                            <th style="width:150px" class="text-end">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $index => $item): ?>
                            <tr>
                                <td class="cp-order">Core <?= $index + 1 ?></td>
                                <td>
                                    <div class="cp-name"><?= Html::encode($item->name) ?></div>
                                    <?php if ($item->definition): ?>
                                        <div class="cp-def"><?= Html::encode($item->definition) ?></div>
                                    <?php endif ?>
                                    <?php if ($item->note): ?>
                                        <div class="cp-note"><i class="bi bi-exclamation-triangle"></i> <?= Html::encode($item->note) ?></div>
                                    <?php endif ?>
                                </td>
                                <td class="text-center">
                                    <?php $levelCount = $counts[$item->id]['levels'] ?? 0 ?>
                                    <?php if ($levelCount > 0): ?>
                                        <span class="cp-count">1–<?= $levelCount ?></span>
                                    <?php else: ?>
                                        <span class="cp-count cp-count--empty">ยังไม่มี</span>
                                    <?php endif ?>
                                </td>
                                <td class="text-center">
                                    <span class="cp-count"><?= number_format($counts[$item->id]['indicators'] ?? 0) ?> ข้อ</span>
                                </td>
                                <td>
                                    <span class="<?= $statusClass[$item->status] ?? 'cp-badge' ?>"><?= Html::encode($item->getStatusLabel()) ?></span>
                                </td>
                                <td class="text-end">
                                    <?= Html::a('<i class="bi bi-list-ul"></i>', ['/hr/competency/view', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-secondary open-modal',
                                        'data-size' => 'modal-xl',
                                        'title' => 'ดูระดับและข้อพฤติกรรมบ่งชี้',
                                    ]) ?>
                                    <?= Html::a('<i class="bi bi-pencil"></i>', ['/hr/competency/form', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-primary open-modal',
                                        'data-size' => 'modal-lg',
                                        'title' => 'แก้ไข',
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endif ?>
    </section>

    <p class="cp-hint">
        <i class="bi bi-info-circle"></i>
        ระดับสมรรถนะและข้อพฤติกรรมบ่งชี้ยังแก้ไขจากหน้านี้ไม่ได้ในขั้นนี้ — กดปุ่มรายการเพื่อดูรายละเอียดที่นำเข้ามาจากแบบฟอร์มเดิม
        ส่วนการกำหนด<strong>ระดับที่คาดหวังรายบุคคล</strong>ทำที่หน้ารายชื่อบุคลากร
    </p>
</div>

<?php if ($copySourceYears !== []): ?>
<div class="modal fade" id="cp-copy-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <?= Html::beginForm(['/hr/competency/copy'], 'post', ['class' => 'modal-content']) ?>
            <div class="modal-header">
                <h5 class="modal-title">คัดลอกชุดสมรรถนะจากปีอื่น</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <label class="form-label" for="cp-copy-from">คัดลอกจากปีงบประมาณ</label>
                <?= Html::dropDownList('from', reset($copySourceYears), array_combine($copySourceYears, $copySourceYears), [
                    'class' => 'form-select', 'id' => 'cp-copy-from',
                ]) ?>
                <?= Html::hiddenInput('to', $fiscalYear) ?>
                <p class="text-muted small mt-3 mb-0">
                    คัดลอกทั้งสมรรถนะ ระดับ และข้อพฤติกรรมบ่งชี้มายังปี <strong><?= $fiscalYear ?></strong>
                    โดยตั้งสถานะเป็นฉบับร่างไว้ให้ตรวจทานก่อนประกาศใช้
                    รายการที่มีอยู่แล้วในปี <?= $fiscalYear ?> จะถูกข้าม ไม่เขียนทับ
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">คัดลอกมาปี <?= $fiscalYear ?></button>
            </div>
        <?= Html::endForm() ?>
    </div>
</div>
<?php
$this->registerJs(<<<JS
\$(document).off('click.cpCopy').on('click.cpCopy', '#cp-copy-open', function () {
    new bootstrap.Modal(document.getElementById('cp-copy-modal')).show();
});
JS);
?>
<?php endif ?>
