<?php

use yii\helpers\Html;
use app\modules\hr\models\AppraisalRound;
use app\modules\hr\models\CompetencyAssignment;
use app\modules\hr\models\CompetencyExpectation;
use app\modules\hr\models\CompetencyYear;
use app\modules\hr\models\Employees;

/** @var yii\web\View $this */
/** @var Employees $employee */
/** @var int $fiscalYear */
/** @var int[] $years */
/** @var AppraisalRound[] $rounds */
/** @var AppraisalRound|null $round */
/** @var CompetencyAssignment|null $assignment */
/** @var CompetencyYear[] $competencies */
/** @var array<int, int> $levelCounts */
/** @var array<int, CompetencyExpectation> $current */
/** @var array<int, array{level: int|null, years: float|null, reason: string}> $suggestions */

$this->title = 'ระดับที่คาดหวัง · ' . $employee->fullname();
echo $this->render('@app/modules/hr/views/workforce/_styles');
echo $this->render('_styles');
$this->beginBlock('page-title'); echo Html::encode($this->title); $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/hr/menu', ['active' => 'core']); $this->endBlock();

$suggestionReason = '';
$suggestedLevel = null;
foreach ($suggestions as $suggestion) {
    if ($suggestion['level'] !== null) {
        $suggestedLevel = (int) $suggestion['level'];
        $suggestionReason = $suggestion['reason']
            . ($suggestion['years'] !== null ? ' (' . $suggestion['years'] . ' ปี)' : '');
        break;
    }
}
$hasSuggestion = $suggestionReason !== '';

$totalCore = count($competencies);
$maxLevelOverall = $levelCounts ? max($levelCounts) : 0;

// อ่านค่าที่ตั้งไว้กลับมาเป็น "ประเมินกี่ Core / ถึงระดับไหน"
// จำนวน Core = ลำดับสุดท้ายที่ยังกำหนดไว้ · ระดับ = ค่าที่ใช้บ่อยที่สุดในกลุ่มที่กำหนด
$currentCoreCount = 0;
$levelTally = [];
foreach (array_values($competencies) as $position => $competency) {
    $expectation = $current[(int) $competency->id] ?? null;
    if ($expectation) {
        $currentCoreCount = $position + 1;
        $levelTally[(int) $expectation->expected_level] = ($levelTally[(int) $expectation->expected_level] ?? 0) + 1;
    }
}
$currentLevel = null;
if ($levelTally !== []) {
    arsort($levelTally);
    $currentLevel = (int) array_key_first($levelTally);
}
// ยังไม่เคยตั้งค่า → ตั้งต้นที่ประเมินครบทุกตัว และระดับที่ระบบแนะนำ
$defaultCoreCount = $currentCoreCount ?: $totalCore;
$defaultLevel = $currentLevel ?? $suggestedLevel;

$coreCountOptions = [];
for ($n = 1; $n <= $totalCore; $n++) {
    $coreCountOptions[$n] = $n === $totalCore ? "ทุกสมรรถนะ ({$n} ตัว)" : "Core 1–{$n} ({$n} ตัว)";
}
$overallLevelOptions = [];
for ($l = 1; $l <= $maxLevelOverall; $l++) {
    $overallLevelOptions[$l] = 'ระดับที่ ' . $l;
}
?>
<div class="workforce-shell">
    <?= $this->render('@app/modules/hr/views/workforce/_menu', ['active' => 'core']) ?>

    <header class="workforce-head">
        <div>
            <h1><?= Html::encode($employee->fullname()) ?></h1>
            <p>
                <?= Html::encode(strip_tags((string) $employee->positionName())) ?>
                · <?= Html::encode($employee->departmentName()) ?>
                · <?= $round ? Html::encode($round->getTitle()) : 'ปีงบประมาณ ' . $fiscalYear ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-arrow-left"></i> กลับไปรายชื่อ',
                ['/hr/competency/index', 'fy' => $fiscalYear, 'rd' => $round?->round_no], [
                'class' => 'btn btn-outline-secondary',
            ]) ?>
            <?= Html::a('<i class="bi bi-sliders"></i> ตั้งค่า Core', ['/hr/competency/setting', 'fy' => $fiscalYear], [
                'class' => 'btn btn-outline-primary',
            ]) ?>
        </div>
    </header>

    <?php if ($rounds !== []): ?>
        <?= $this->render('_round_bar', [
            'fiscalYear' => $fiscalYear, 'rounds' => $rounds, 'round' => $round,
            'copySourceRounds' => [], 'showActions' => false,
        ]) ?>
    <?php endif ?>

    <?php if ($round === null): ?>
        <div class="cp-alert">
            <i class="bi bi-exclamation-triangle"></i>
            <span>
                ปีงบประมาณ <?= $fiscalYear ?> ยังไม่มีรอบประเมิน —
                <?= Html::a('กลับไปสร้างรอบ', ['/hr/competency/index', 'fy' => $fiscalYear], ['class' => 'alert-link']) ?>
            </span>
        </div>
    <?php elseif ($competencies === []): ?>
        <div class="cp-alert">
            <i class="bi bi-exclamation-triangle"></i>
            <span>
                ปีงบประมาณ <?= $fiscalYear ?> ยังไม่มีสมรรถนะหลักที่ประกาศใช้ —
                <?= Html::a('ไปตั้งค่า Core', ['/hr/competency/setting', 'fy' => $fiscalYear], ['class' => 'alert-link']) ?>
            </span>
        </div>
    <?php else: ?>
        <?php if ($assignment && $assignment->evaluator): ?>
            <p class="cp-hint mt-3 mb-0">
                <i class="bi bi-person-check"></i>
                ผู้ประเมินรอบนี้: <strong><?= Html::encode($assignment->evaluator->fullname()) ?></strong>
                · <?= Html::encode($assignment->getStatusLabel()) ?>
            </p>
        <?php else: ?>
            <p class="cp-hint mt-3 mb-0">
                <i class="bi bi-person-dash"></i>
                ยังไม่ได้กำหนดผู้ประเมินของรอบนี้ — กำหนดได้จากหน้ารายชื่อ
            </p>
        <?php endif ?>

        <?= Html::beginForm(['/hr/competency/save-expectation'], 'post', ['id' => 'cp-expect-form']) ?>
        <?= Html::hiddenInput('emp_id', $employee->id) ?>
        <?= Html::hiddenInput('round_id', $round->id) ?>

        <section class="cp-bulk">
            <div class="cp-bulk__head">
                <strong>ตั้งค่ารวมของบุคลากรรายนี้</strong>
                <span>เลือกว่าต้องประเมินกี่สมรรถนะและถึงระดับใด ระบบจะเติมให้ทุกแถวด้านล่าง แล้วปรับรายตัวทับได้</span>
            </div>
            <div class="cp-bulk__controls">
                <div>
                    <label for="cp-core-count">ต้องประเมินกี่ Core</label>
                    <?= Html::dropDownList('core_count', $defaultCoreCount, $coreCountOptions, [
                        'class' => 'form-select', 'id' => 'cp-core-count',
                    ]) ?>
                </div>
                <div>
                    <label for="cp-overall-level">ระดับที่คาดหวัง</label>
                    <?= Html::dropDownList('overall_level', $defaultLevel, $overallLevelOptions, [
                        'class' => 'form-select', 'id' => 'cp-overall-level',
                        'prompt' => '— เลือกระดับ —',
                    ]) ?>
                </div>
                <?= Html::button('<i class="bi bi-arrow-down-circle"></i> เติมให้ทุกแถว', [
                    'type' => 'button', 'class' => 'btn btn-primary text-nowrap', 'id' => 'cp-apply-bulk',
                ]) ?>
                <?php if ($hasSuggestion): ?>
                    <?= Html::button('<i class="bi bi-magic"></i> ใช้ระดับที่ระบบแนะนำ', [
                        'type' => 'button', 'class' => 'btn btn-outline-primary text-nowrap',
                        'id' => 'cp-apply-suggestion', 'data-level' => $suggestedLevel,
                    ]) ?>
                <?php endif ?>
            </div>
        </section>

        <p class="cp-hint mt-2 mb-0">
            <i class="bi bi-info-circle"></i>
            <?php if ($hasSuggestion): ?>
                ระบบแนะนำ <strong>ระดับที่ <?= $suggestedLevel ?></strong> จาก<?= Html::encode($suggestionReason) ?>
                — ส่วนหัวหน้างาน กรรมการบริหาร และผู้อำนวยการ ยังไม่มีข้อมูลในทะเบียนบุคลากร กรุณาปรับเอง
            <?php else: ?>
                ระบบยังแนะนำระดับให้ไม่ได้ — <?= Html::encode(reset($suggestions)['reason'] ?? 'ไม่มีข้อมูลอายุงาน') ?>
            <?php endif ?>
            สมรรถนะที่มีระดับน้อยกว่าที่เลือกจะถูกลดลงให้เท่าระดับสูงสุดที่มีจริงโดยอัตโนมัติ
        </p>

        <section class="cp-panel mt-3">
            <div class="table-responsive">
                <table class="table cp-table cp-table--expect align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:70px">ลำดับ</th>
                            <th>สมรรถนะ</th>
                            <th style="width:200px">ระดับที่คาดหวัง</th>
                            <th style="width:120px">ที่มา</th>
                            <th style="width:230px">หมายเหตุ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($competencies as $index => $competency): ?>
                            <?php
                            $id = (int) $competency->id;
                            $maxLevel = $levelCounts[$id] ?? 0;
                            $expectation = $current[$id] ?? null;
                            $suggested = $suggestions[$id]['level'] ?? null;

                            $levelOptions = [];
                            for ($level = 1; $level <= $maxLevel; $level++) {
                                $levelOptions[$level] = 'ประเมินถึงระดับที่ ' . $level;
                            }
                            ?>
                            <tr class="cp-row" data-position="<?= $index + 1 ?>">
                                <td class="cp-order">Core <?= $index + 1 ?></td>
                                <td>
                                    <div class="cp-name">
                                        <?= Html::encode($competency->name) ?>
                                        <?= Html::a('<i class="bi bi-list-ul"></i>', ['/hr/competency/view', 'id' => $id], [
                                            'class' => 'cp-peek open-modal',
                                            'data-size' => 'modal-xl',
                                            'title' => 'ดูระดับและข้อพฤติกรรมบ่งชี้',
                                        ]) ?>
                                    </div>
                                    <div class="cp-def"><?= Html::encode((string) $competency->definition) ?></div>
                                    <div class="cp-meta">มีทั้งหมด <?= $maxLevel ?> ระดับ</div>
                                </td>
                                <td>
                                    <?php if ($maxLevel === 0): ?>
                                        <span class="cp-count cp-count--empty">ยังไม่มีระดับ</span>
                                    <?php else: ?>
                                        <?= Html::dropDownList("level[$id]", $expectation->expected_level ?? null, $levelOptions, [
                                            'class' => 'form-select form-select-sm cp-level-select',
                                            'prompt' => '— ไม่ประเมินสมรรถนะนี้ —',
                                            'data-max' => $maxLevel,
                                            'data-position' => $index + 1,
                                        ]) ?>
                                    <?php endif ?>
                                </td>
                                <td><span class="cp-origin"></span></td>
                                <td>
                                    <?= Html::textInput("note[$id]", $expectation->note ?? '', [
                                        'class' => 'form-control form-control-sm',
                                        'maxlength' => 255,
                                        'placeholder' => 'เช่น รักษาการหัวหน้าฝ่าย',
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <?= Html::a('ยกเลิก', ['/hr/competency/index', 'fy' => $fiscalYear, 'rd' => $round->round_no],
                ['class' => 'btn btn-light']) ?>
            <?= Html::submitButton('<i class="bi bi-check-lg"></i> บันทึกระดับที่คาดหวัง', ['class' => 'btn btn-primary']) ?>
        </div>
        <?= Html::endForm() ?>

        <p class="cp-hint">
            <i class="bi bi-info-circle"></i>
            เลือก "ไม่ประเมินสมรรถนะนี้" ในแถวใดก็ได้เมื่อสมรรถนะนั้นไม่ใช้กับบุคลากรรายนี้ — จะไม่ถูกนับเข้าคะแนนและไม่ถ่วงน้ำหนัก
        </p>
    <?php endif ?>
</div>
<?php
$this->registerJs(<<<JS
(function () {
    var form = document.getElementById('cp-expect-form');
    if (!form) { return; }

    function selects() { return \$(form).find('.cp-level-select'); }

    /** เติมค่าตามที่ตั้งไว้ด้านบน: Core 1..N ได้ระดับที่เลือก (ไม่เกินระดับสูงสุดที่สมรรถนะนั้นมี) ที่เหลือไม่ประเมิน */
    function applyBulk(level) {
        var count = parseInt(\$('#cp-core-count').val() || 0, 10);
        if (!level) { warning('กรุณาเลือกระดับที่คาดหวังก่อน'); return; }

        var filled = 0, cleared = 0, capped = [];
        selects().each(function () {
            var \$select = \$(this);
            var position = parseInt(\$select.data('position'), 10);
            var max = parseInt(\$select.data('max'), 10) || 0;

            if (position > count || max === 0) {
                \$select.val('');
                cleared++;
                return;
            }
            var value = Math.min(level, max);
            if (value < level) { capped.push(\$select.closest('tr').find('.cp-name').text().trim() + ' → ระดับ ' + value); }
            \$select.val(String(value));
            filled++;
        });

        refreshOrigin();
        var message = 'เติมให้ ' + filled + ' สมรรถนะ' + (cleared > 0 ? ' และเว้นไม่ประเมิน ' + cleared + ' สมรรถนะ' : '');
        if (capped.length) { message += ' · ลดระดับให้ ' + capped.join(', '); }
        success(message + ' — ตรวจสอบแล้วกดบันทึก');
    }

    /** ป้าย "ตามค่ารวม / ปรับเอง / ไม่ประเมิน" ให้เห็นว่าแถวไหนถูกแก้ทับ */
    function refreshOrigin() {
        var count = parseInt(\$('#cp-core-count').val() || 0, 10);
        var level = parseInt(\$('#cp-overall-level').val() || 0, 10);

        selects().each(function () {
            var \$select = \$(this);
            var position = parseInt(\$select.data('position'), 10);
            var max = parseInt(\$select.data('max'), 10) || 0;
            var value = parseInt(\$select.val() || 0, 10);
            var \$row = \$select.closest('tr');
            var \$badge = \$row.find('.cp-origin');

            \$row.toggleClass('cp-row--off', !value);
            if (!value) {
                \$badge.attr('class', 'cp-origin cp-origin--off').text(position > count ? 'ไม่ประเมิน' : 'ยังไม่กำหนด');
                return;
            }
            var expected = level ? Math.min(level, max) : 0;
            var matches = position <= count && value === expected;
            \$badge.attr('class', 'cp-origin ' + (matches ? 'cp-origin--bulk' : 'cp-origin--manual'))
                  .text(matches ? 'ตามค่ารวม' : 'ปรับเอง');
        });
    }

    \$(document).off('click.cpBulk').on('click.cpBulk', '#cp-apply-bulk', function () {
        applyBulk(parseInt(\$('#cp-overall-level').val() || 0, 10));
    });

    \$(document).off('click.cpSuggest').on('click.cpSuggest', '#cp-apply-suggestion', function () {
        var level = parseInt(\$(this).data('level') || 0, 10);
        \$('#cp-overall-level').val(String(level));
        applyBulk(level);
    });

    \$(document).off('change.cpOrigin').on('change.cpOrigin', '#cp-expect-form .cp-level-select, #cp-core-count, #cp-overall-level', refreshOrigin);

    refreshOrigin();
})();
JS);
