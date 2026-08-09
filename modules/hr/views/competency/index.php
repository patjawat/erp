<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\LinkPager;
use kartik\widgets\Select2;
use app\modules\hr\models\AppraisalRound;
use app\modules\hr\models\CompetencyAssignment;
use app\modules\hr\models\CompetencyEvaluation;
use app\modules\hr\models\CompetencyYear;
use app\modules\hr\models\Employees;

/** @var yii\web\View $this */
/** @var int $fiscalYear */
/** @var int[] $years */
/** @var AppraisalRound[] $rounds */
/** @var AppraisalRound|null $round */
/** @var AppraisalRound[] $copySourceRounds */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var Employees[] $employees */
/** @var CompetencyYear[] $competencies */
/** @var array<int, array<int, int>> $expectations */
/** @var array<int, CompetencyAssignment> $assignments */
/** @var array<int, CompetencyEvaluation> $evaluations */
/** @var array<int, int|null> $suggestedEvaluators */
/** @var array<int, string> $evaluatorItems */
/** @var app\modules\hr\models\Organization[] $departments */
/** @var string $keyword */
/** @var int $depId */
/** @var string $statusFilter */
/** @var bool $showAll */
/** @var int $maxLevelOverall */
/** @var array $metrics */
/** @var array $overview */

$this->title = 'สมรรถนะหลัก (Core Competency)';
echo $this->render('@app/modules/hr/views/workforce/_styles');
echo $this->render('_styles');
$this->beginBlock('page-title'); echo Html::encode($this->title); $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/hr/menu', ['active' => 'core']); $this->endBlock();

$competencyCount = count($competencies);
// กำหนดค่าได้เฉพาะเมื่อมีรอบและรอบยังไม่ปิด
$canAssign = $round !== null && $round->isEditable();

$depOptions = [];
foreach ($departments as $dep) {
    $depOptions[$dep->id] = str_repeat('— ', max(0, (int) $dep->lvl - 1)) . $dep->name;
}

$levelOptions = [];
for ($level = 1; $level <= $maxLevelOverall; $level++) {
    $levelOptions[$level] = 'ระดับที่ ' . $level;
}
?>
<div class="workforce-shell">
    <?= $this->render('@app/modules/hr/views/workforce/_menu', ['active' => 'core']) ?>

    <header class="workforce-head">
        <div>
            <h1>สมรรถนะหลัก (Core Competency)</h1>
            <p>กำหนดระดับที่คาดหวังและผู้ประเมินของบุคลากรแต่ละคน — รายชื่อจะไปแสดงให้ผู้ประเมินเมื่อเปิดรอบแล้ว</p>
        </div>
        <div>
            <?= Html::a('<i class="bi bi-sliders"></i> ตั้งค่า Core', ['/hr/competency/setting', 'fy' => $fiscalYear], [
                'class' => 'btn btn-outline-primary',
                'title' => 'ทะเบียนสมรรถนะหลักประจำปี ระดับ และข้อพฤติกรรมบ่งชี้',
            ]) ?>
        </div>
    </header>

    <?php if ($rounds === []): ?>
        <div class="cp-alert">
            <i class="bi bi-exclamation-triangle"></i>
            <span>
                ปีงบประมาณ <?= $fiscalYear ?> ยังไม่มีรอบประเมิน —
                <?= Html::a('สร้างรอบที่ 1', ['/hr/competency/round', 'fy' => $fiscalYear, 'no' => 1], [
                    'class' => 'alert-link open-modal', 'data-size' => 'modal-lg',
                ]) ?>
                ก่อนจึงจะกำหนดผู้ประเมินได้
            </span>
        </div>
    <?php else: ?>
        <?= $this->render('_round_bar', compact('fiscalYear', 'rounds', 'round', 'copySourceRounds')) ?>
    <?php endif ?>

    <?= $this->render('@app/modules/hr/views/_kpi_cards', ['cards' => [
        ['label' => 'บุคลากรที่ปฏิบัติงาน', 'value' => $metrics['employees'], 'icon' => 'bi-people-fill', 'color' => 'primary',
            'hint' => 'สมรรถนะที่ประกาศใช้ ' . number_format($metrics['competencies']) . ' ตัว'],
        ['label' => 'กำหนดผู้ประเมินแล้ว', 'value' => $metrics['assigned'], 'icon' => 'bi-person-check-fill', 'color' => 'success',
            'hint' => 'พร้อมส่งให้ผู้ประเมินดำเนินการ'],
        ['label' => 'ยังไม่มีผู้ประเมิน', 'value' => $metrics['unassigned'], 'icon' => 'bi-person-dash', 'color' => 'danger',
            'hint' => 'ยังไม่ไปแสดงบนหน้าของผู้ประเมิน'],
        ['label' => 'กำหนดระดับครบแล้ว', 'value' => $metrics['complete'], 'icon' => 'bi-check2-circle', 'color' => 'info',
            'hint' => 'ครบทุกสมรรถนะของปี ' . $fiscalYear],
    ]]) ?>

    <?php if ($competencyCount === 0): ?>
        <div class="cp-alert mt-3">
            <i class="bi bi-exclamation-triangle"></i>
            <span>
                ปีงบประมาณ <?= $fiscalYear ?> ยังไม่มีสมรรถนะหลักที่ประกาศใช้ —
                <?= Html::a('ไปตั้งค่า Core', ['/hr/competency/setting', 'fy' => $fiscalYear], ['class' => 'alert-link']) ?>
                ก่อนจึงจะกำหนดระดับที่คาดหวังได้
            </span>
        </div>
    <?php endif ?>

    <?php if ($overview !== []): ?>
        <section class="cp-panel mt-3">
            <div class="cp-overview__head">
                <h2>ภาพรวมความคืบหน้ารายหน่วยงาน</h2>
                <span>ติดตามว่าหน่วยไหนยังไม่ขยับ · <?= Html::encode($round?->getTitle() ?? '') ?></span>
            </div>
            <div class="table-responsive">
                <table class="table cp-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>หน่วยงาน</th>
                            <th>ผู้ประเมิน</th>
                            <th style="width:230px">ความคืบหน้า</th>
                            <th style="width:280px" class="text-end">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($overview as $row): ?>
                            <?php
                            $done = $row['submitted'] + $row['completed'];
                            $percent = $row['total'] > 0 ? (int) round($done / $row['total'] * 100) : 0;
                            ?>
                            <tr>
                                <td><div class="cp-name"><?= Html::encode($row['name']) ?></div></td>
                                <td class="cp-def"><?= Html::encode($row['evaluator']) ?></td>
                                <td>
                                    <div class="cp-bar"><span style="width:<?= $percent ?>%"></span></div>
                                    <div class="cp-meta"><?= $done ?>/<?= $row['total'] ?> คน (<?= $percent ?>%)</div>
                                </td>
                                <td class="text-end">
                                    <?php if ($row['submitted'] > 0): ?>
                                        <span class="cp-badge cp-badge--active">ส่งผลแล้ว <?= $row['submitted'] ?></span>
                                    <?php endif ?>
                                    <?php if ($row['completed'] > 0): ?>
                                        <span class="cp-badge cp-badge--ready">ประเมินครบ <?= $row['completed'] ?></span>
                                    <?php endif ?>
                                    <?php if ($row['doing'] > 0): ?>
                                        <span class="cp-badge cp-badge--draft">ทำค้าง <?= $row['doing'] ?></span>
                                    <?php endif ?>
                                    <?php if ($row['todo'] > 0): ?>
                                        <span class="cp-badge cp-badge--todo">ยังไม่เริ่ม <?= $row['todo'] ?></span>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif ?>

    <section class="jd-registry mt-3" aria-labelledby="cp-registry-title">
        <div class="jd-registry__head">
            <div>
                <h2 id="cp-registry-title">
                    ระดับที่คาดหวังและผู้ประเมิน · <?= $round ? Html::encode($round->getTitle()) : 'ปีงบประมาณ ' . $fiscalYear ?>
                </h2>
                <p>กรองรายชื่อ แล้วติ๊กเลือกเพื่อกำหนดพร้อมกันหลายคน หรือคลิกที่รายชื่อเพื่อปรับรายบุคคล</p>
            </div>
        </div>

        <?= Html::beginForm(['/hr/competency/index'], 'get', ['class' => 'jd-registry__search']) ?>
        <div class="jd-registry__search-control">
            <i data-lucide="search" aria-hidden="true"></i>
            <?= Html::textInput('q', $keyword, [
                'class' => 'form-control',
                'placeholder' => 'ค้นหาชื่อบุคลากรหรือตำแหน่ง',
            ]) ?>
        </div>
        <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), [
            'class' => 'form-select', 'style' => 'max-width:120px',
        ]) ?>
        <?= Html::hiddenInput('rd', $round?->round_no ?? '') ?>
        <?= Html::dropDownList('dep', $depId ?: null, $depOptions, [
            'class' => 'form-select', 'prompt' => 'ทุกหน่วยงาน', 'style' => 'max-width:250px',
        ]) ?>
        <?= Html::dropDownList('st', $statusFilter, [
            'assigned' => 'กำหนดผู้ประเมินแล้ว',
            'unassigned' => 'ยังไม่มีผู้ประเมิน',
        ], ['class' => 'form-select', 'prompt' => 'ทุกสถานะ', 'style' => 'max-width:190px']) ?>
        <label class="d-inline-flex align-items-center gap-1 small text-nowrap mb-0">
            <?= Html::checkbox('show_all', $showAll, ['value' => 1, 'class' => 'form-check-input mt-0']) ?>
            แสดงทั้งหมด
        </label>
        <?= Html::submitButton('ค้นหา', ['class' => 'btn btn-primary']) ?>
        <?php if ($keyword !== '' || $depId > 0 || $statusFilter !== '' || $showAll): ?>
            <?= Html::a('ล้าง', ['/hr/competency/index', 'fy' => $fiscalYear], ['class' => 'btn btn-outline-secondary']) ?>
        <?php endif ?>
        <?= Html::endForm() ?>

        <?php if ($employees === []): ?>
            <div class="jd-registry__empty">
                <strong>ไม่พบบุคลากรที่ตรงกับเงื่อนไข</strong>
                <span>ลองปรับคำค้นหา เลือกหน่วยงานอื่น หรือเปลี่ยนสถานะ</span>
            </div>
        <?php else: ?>
            <?= Html::beginForm(['/hr/competency/assign'], 'post', ['id' => 'cp-assign-form']) ?>
            <?= Html::hiddenInput('round_id', $round?->id) ?>
            <?= Html::hiddenInput('q', $keyword) ?>
            <?= Html::hiddenInput('dep', $depId ?: '') ?>
            <?= Html::hiddenInput('st', $statusFilter) ?>
            <?= Html::hiddenInput('show_all', $showAll ? 1 : '') ?>
            <?= Html::hiddenInput('page', (int) $dataProvider->getPagination()->getPage() + 1) ?>

            <?php if (!$canAssign && $round): ?>
                <div class="cp-alert mb-3">
                    <i class="bi bi-lock-fill"></i>
                    <span><?= Html::encode($round->getTitle()) ?> ปิดแล้ว — ดูได้อย่างเดียว แก้ไขการกำหนดไม่ได้</span>
                </div>
            <?php endif ?>

            <div class="cp-bulkbar<?= $canAssign ? '' : ' d-none' ?>" id="cp-bulkbar">
                <span class="cp-bulkbar__count"><strong id="cp-selected">0</strong> คนที่เลือก</span>
                <div>
                    <label for="cp-bulk-level">ระดับที่คาดหวัง</label>
                    <?= Html::dropDownList('overall_level', null, $levelOptions, [
                        'class' => 'form-select form-select-sm', 'id' => 'cp-bulk-level',
                        'prompt' => '— ไม่เปลี่ยน —',
                    ]) ?>
                </div>
                <div class="cp-bulkbar__grow">
                    <label for="cp-bulk-evaluator">ผู้ประเมิน</label>
                    <?= Select2::widget([
                        'name' => 'evaluator_id',
                        'data' => $evaluatorItems,
                        'options' => ['placeholder' => '— ไม่เปลี่ยน —', 'id' => 'cp-bulk-evaluator'],
                        'pluginOptions' => ['allowClear' => true],
                        'size' => Select2::SMALL,
                    ]) ?>
                </div>
                <div class="cp-bulkbar__actions">
                    <?= Html::submitButton('<i class="bi bi-check-lg"></i> กำหนดให้ผู้ที่เลือก', [
                        'class' => 'btn btn-primary btn-sm text-nowrap', 'id' => 'cp-assign-submit',
                    ]) ?>
                    <?= Html::submitButton('<i class="bi bi-diagram-3"></i> ใช้ผู้ประเมินตามผังองค์กร', [
                        'class' => 'btn btn-outline-primary btn-sm text-nowrap',
                        'name' => 'use_suggested', 'value' => '1',
                        'title' => 'ใช้หัวหน้าหน่วยงานตามผัง — ถ้าผังชี้กลับมาที่ตัวเองจะข้ามไว้ให้ระบุเอง',
                    ]) ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="jd-registry__table cp-assign-table">
                    <thead>
                    <tr>
                        <th style="width:38px">
                            <?php if ($canAssign): ?>
                                <?= Html::checkbox('', false, ['class' => 'form-check-input mt-0', 'id' => 'cp-check-all',
                                    'title' => 'เลือกทั้งหน้า']) ?>
                            <?php endif ?>
                        </th>
                        <th>บุคลากร</th>
                        <th>ตำแหน่ง</th>
                        <th>หน่วยงาน</th>
                        <th>ระดับที่คาดหวัง</th>
                        <th style="width:200px">ผู้ประเมิน</th>
                        <th style="width:130px">คะแนน</th>
                        <th><span class="visually-hidden">ดำเนินการ</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($employees as $employee): ?>
                        <?php
                        $empId = (int) $employee->id;
                        $rows = $expectations[$empId] ?? [];
                        $setCount = count($rows);
                        $assignment = $assignments[$empId] ?? null;
                        $evaluator = $assignment?->evaluator;
                        $suggestedId = $suggestedEvaluators[$empId] ?? null;
                        ?>
                        <tr>
                            <td>
                                <?php if ($canAssign): ?>
                                    <?= Html::checkbox('emp_ids[]', false, [
                                        'value' => $empId, 'class' => 'form-check-input mt-0 cp-check',
                                    ]) ?>
                                <?php endif ?>
                            </td>
                            <td>
                                <strong><?= Html::encode($employee->fullname()) ?></strong>
                                <small>รหัสบุคลากร <?= $empId ?></small>
                            </td>
                            <td><?= Html::encode(strip_tags((string) $employee->positionName())) ?></td>
                            <td><?= Html::encode($employee->departmentName()) ?></td>
                            <td>
                                <?php if ($competencyCount === 0): ?>
                                    <span class="jd-status is-neutral">ยังไม่ประกาศสมรรถนะ</span>
                                <?php elseif ($setCount === 0): ?>
                                    <span class="jd-status is-danger">ยังไม่ได้กำหนด</span>
                                <?php else: ?>
                                    <span class="jd-status <?= $setCount >= $competencyCount ? 'is-success' : 'is-warning' ?>">
                                        <?= $setCount ?>/<?= $competencyCount ?> สมรรถนะ
                                    </span>
                                    <div class="cp-levels">
                                        <?php foreach ($competencies as $competency): ?>
                                            <?php $level = $rows[(int) $competency->id] ?? null ?>
                                            <span class="cp-chip<?= $level === null ? ' cp-chip--empty' : '' ?>"
                                                  title="<?= Html::encode($competency->name) ?>">
                                                <?= $level === null ? '–' : 'ระดับ ' . $level ?>
                                            </span>
                                        <?php endforeach ?>
                                    </div>
                                <?php endif ?>
                            </td>
                            <td>
                                <?php if ($evaluator): ?>
                                    <div class="cp-name"><?= Html::encode($evaluator->fullname()) ?></div>
                                    <span class="cp-badge <?= $assignment->status === CompetencyAssignment::STATUS_READY ? 'cp-badge--active' : 'cp-badge--draft' ?>">
                                        <?= Html::encode($assignment->getStatusLabel()) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="jd-status is-danger">ยังไม่มีผู้ประเมิน</span>
                                    <?php if ($suggestedId && isset($evaluatorItems[$suggestedId])): ?>
                                        <div class="cp-meta">ตามผัง: <?= Html::encode(strtok($evaluatorItems[$suggestedId], '—')) ?></div>
                                    <?php else: ?>
                                        <div class="cp-meta">ผังองค์กรแนะนำให้ไม่ได้ ต้องระบุเอง</div>
                                    <?php endif ?>
                                <?php endif ?>
                            </td>
                            <td>
                                <?php $evaluation = $evaluations[$empId] ?? null ?>
                                <?php if ($evaluation && $evaluation->score_percent !== null): ?>
                                    <div class="cp-score"><?= Yii::$app->formatter->asDecimal($evaluation->score_percent, 2) ?></div>
                                    <span class="jd-status <?= $evaluation->status === CompetencyEvaluation::STATUS_SUBMITTED ? 'is-success' : 'is-warning' ?>">
                                        <?= Html::encode($evaluation->getStatusLabel()) ?>
                                    </span>
                                <?php elseif ($evaluation): ?>
                                    <span class="jd-status is-warning"><?= Html::encode($evaluation->getStatusLabel()) ?></span>
                                <?php else: ?>
                                    <span class="jd-status is-neutral">ยังไม่ประเมิน</span>
                                <?php endif ?>
                            </td>
                            <td class="text-end">
                                <?= Html::a($setCount > 0 ? 'เปิดข้อมูล' : 'กำหนดระดับ',
                                    ['/hr/competency/employee', 'emp_id' => $empId, 'fy' => $fiscalYear, 'rd' => $round?->round_no],
                                    ['class' => 'btn btn-sm btn-outline-primary text-nowrap']) ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <?= Html::endForm() ?>

            <div class="d-flex justify-content-center mt-3">
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->getPagination(),
                    'options' => ['class' => 'pagination mb-0'],
                    'linkOptions' => ['class' => 'page-link'],
                    'pageCssClass' => 'page-item',
                    'activePageCssClass' => 'page-item active',
                    'disabledPageCssClass' => 'page-item disabled',
                ]) ?>
            </div>
        <?php endif ?>
    </section>

    <p class="cp-hint">
        <i class="bi bi-info-circle"></i>
        การเลือกครอบคลุมเฉพาะรายชื่อในหน้านี้ — ถ้าต้องการทั้งหน่วยงาน ให้กรองหน่วยงานก่อนแล้วค่อยเลือกทั้งหน้า ·
        คอลัมน์คะแนนจะแสดงผลเมื่อเปิดรอบประเมินและผู้ประเมินให้คะแนนแล้ว
    </p>
</div>
<?php
$this->registerJs(<<<JS
(function () {
    var form = document.getElementById('cp-assign-form');
    if (!form) { return; }

    function checks() { return \$(form).find('.cp-check'); }

    function refresh() {
        var selected = checks().filter(':checked').length;
        \$('#cp-selected').text(selected);
        \$('#cp-bulkbar').toggleClass('is-active', selected > 0);
        \$('#cp-check-all').prop('checked', selected > 0 && selected === checks().length);
    }

    \$(document).off('change.cpAll').on('change.cpAll', '#cp-check-all', function () {
        checks().prop('checked', \$(this).is(':checked'));
        refresh();
    });
    \$(document).off('change.cpOne').on('change.cpOne', '.cp-check', refresh);

    // กันการส่งฟอร์มโดยไม่ได้เลือกใคร หรือไม่ได้เลือกอะไรจะเปลี่ยนเลย
    \$(form).off('submit.cpAssign').on('submit.cpAssign', function (event) {
        if (checks().filter(':checked').length === 0) {
            event.preventDefault();
            warning('กรุณาติ๊กเลือกบุคลากรก่อน');
            return;
        }
        var useSuggested = \$(document.activeElement).attr('name') === 'use_suggested';
        if (!useSuggested && !\$('#cp-bulk-level').val() && !\$('#cp-bulk-evaluator').val()) {
            event.preventDefault();
            warning('เลือกระดับที่คาดหวังหรือผู้ประเมินอย่างน้อยหนึ่งอย่าง');
        }
    });

    refresh();
})();
JS);
