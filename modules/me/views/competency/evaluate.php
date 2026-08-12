<?php

use yii\helpers\Html;
use app\modules\hr\models\AppraisalRound;
use app\modules\hr\models\CompetencyEvaluation;
use app\modules\hr\models\CompetencyScale;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\CompetencyAssignment $assignment */
/** @var app\modules\hr\models\Employees $employee */
/** @var AppraisalRound $round */
/** @var CompetencyEvaluation $evaluation */
/** @var app\modules\hr\models\CompetencyYear[] $competencies */
/** @var array $indicatorMap */
/** @var array<int, int> $expected */
/** @var array<int, array{score:int, by:string}> $scores */
/** @var array $levelDescriptions */
/** @var int|null $next */

$this->title = 'ประเมิน · ' . $employee->fullname();
echo $this->render('_styles');
$this->beginBlock('page-title'); echo Html::encode($this->title); $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/me/menu', ['active' => 'competency']); $this->endBlock();

$locked = $evaluation->isLocked() || $round->status !== AppraisalRound::STATUS_OPEN;
$defaultScale = CompetencyScale::defaultScale();
$defaultOptions = [];
foreach ($defaultScale?->options ?? [] as $option) {
    $defaultOptions[(int) $option->score] = $option->label;
}
?>
<div class="ev-shell">
    <header class="ev-head">
        <div>
            <h1><?= Html::encode($employee->fullname()) ?></h1>
            <p>
                <?= Html::encode(strip_tags((string) $employee->positionName())) ?>
                · <?= Html::encode($employee->departmentName()) ?>
                · <?= Html::encode($round->getTitle()) ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-arrow-left"></i> กลับไปรายชื่อ',
                ['/me/competency/index', 'rd' => $round->round_no], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </header>

    <?php if ($locked): ?>
        <div class="ev-alert">
            <i class="bi bi-lock-fill"></i>
            <span>
                <?= $evaluation->isLocked() ? 'ส่งผลประเมินไปแล้ว' : 'รอบนี้ไม่ได้เปิดให้ประเมิน' ?>
                — ดูได้อย่างเดียว แก้ไขคะแนนไม่ได้
            </span>
        </div>
    <?php endif ?>

    <?php if ($expected === []): ?>
        <div class="ev-alert">
            <i class="bi bi-exclamation-triangle"></i>
            <span>HR ยังไม่ได้กำหนดระดับที่คาดหวังของบุคลากรรายนี้ — ยังให้คะแนนไม่ได้</span>
        </div>
    <?php else: ?>
        <?= Html::beginForm(['/me/competency/save', 'id' => $assignment->id], 'post', ['id' => 'ev-form']) ?>

        <div class="ev-guide">
            <i class="bi bi-info-circle"></i>
            <span>
                ให้คะแนนทุกข้อพฤติกรรมตั้งแต่ระดับที่ 1 ถึงระดับที่คาดหวัง —
                ผลรวมคะแนนแสดงอยู่ด้านล่าง ตรวจสอบแล้วจึงกดบันทึก
            </span>
        </div>

        <?php foreach ($competencies as $index => $competency): ?>
            <?php
            $competencyId = (int) $competency->id;
            $expectedLevel = $expected[$competencyId] ?? 0;
            if ($expectedLevel < 1) {
                continue; // ไม่ได้ประเมินสมรรถนะนี้สำหรับคนนี้
            }
            ?>
            <section class="ev-comp" data-competency="<?= $competencyId ?>"
                     data-name="<?= Html::encode($competency->name) ?>">
                <header class="ev-comp__head">
                    <div>
                        <span class="ev-comp__no">Core <?= $index + 1 ?></span>
                        <strong><?= Html::encode($competency->name) ?></strong>
                    </div>
                    <span class="ev-comp__expect">ประเมินถึงระดับที่ <?= $expectedLevel ?></span>
                    <span class="ev-comp__score" data-comp-score>—</span>
                </header>
                <?php if ($competency->definition): ?>
                    <p class="ev-comp__def"><?= Html::encode($competency->definition) ?></p>
                <?php endif ?>

                <?php for ($levelNo = 1; $levelNo <= $expectedLevel; $levelNo++): ?>
                    <?php
                    $indicators = $indicatorMap[$competencyId][$levelNo] ?? [];
                    if ($indicators === []) {
                        continue;
                    }
                    $blockId = 'lv-' . $competencyId . '-' . $levelNo;
                    ?>
                    <div class="ev-level is-expanded" id="<?= $blockId ?>" data-expanded="1">
                        <button type="button" class="ev-level__head ev-toggle" data-target="#<?= $blockId ?>"
                                aria-expanded="true" aria-controls="<?= $blockId ?>-items">
                            <i class="bi bi-chevron-down ev-level__caret" aria-hidden="true"></i>
                            <span>
                                <strong>ระดับที่ <?= $levelNo ?></strong>
                                <small><?= Html::encode($levelDescriptions[$competencyId][$levelNo] ?? '') ?></small>
                            </span>
                            <span class="ev-level__count" data-level-count>
                                <span class="visually-hidden">ให้คะแนนแล้ว</span>0/<?= count($indicators) ?>
                            </span>
                        </button>

                        <ul class="ev-items" role="list" id="<?= $blockId ?>-items">
                            <?php foreach ($indicators as $indicator): ?>
                                <?php
                                $indicatorId = (int) $indicator->id;
                                $scale = $indicator->scale;
                                $options = $defaultOptions;
                                if ($scale) {
                                    $options = [];
                                    foreach ($scale->options as $option) {
                                        $options[(int) $option->score] = $option->label;
                                    }
                                }
                                $current = $scores[$indicatorId] ?? null;
                                ?>
                                <li class="ev-item<?= $scale ? ' ev-item--scale' : '' ?>">
                                    <span class="ev-item__no"><?= Html::encode((string) $indicator->indicator_no) ?></span>
                                    <span class="ev-item__text">
                                        <?= Html::encode($indicator->text) ?>
                                        <?php if ($scale): ?>
                                            <em>ใช้มาตรวัดเฉพาะ: <?= Html::encode($scale->name) ?></em>
                                        <?php endif ?>
                                    </span>
                                    <span class="ev-item__control">
                                        <?= Html::dropDownList("item_score[$indicatorId]",
                                            $current['score'] ?? null, $options, [
                                                'class' => 'form-select form-select-sm ev-item-select',
                                                'prompt' => '— เลือกคะแนน —',
                                                'disabled' => $locked,
                                            ]) ?>
                                    </span>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endfor ?>
            </section>
        <?php endforeach ?>

        <section class="ev-comp">
            <header class="ev-comp__head"><strong>ข้อเสนอแนะของผู้ประเมิน</strong></header>
            <?= Html::textarea('comment', (string) $evaluation->comment, [
                'class' => 'form-control', 'rows' => 3, 'disabled' => $locked,
                'placeholder' => 'จุดเด่น จุดที่ควรพัฒนา หรือข้อเสนอแนะเพิ่มเติม',
            ]) ?>
        </section>

        <section class="ev-total" id="ev-total">
            <header class="ev-total__head">
                <strong>สรุปคะแนนก่อนบันทึก</strong>
                <span id="ev-total-progress">ให้คะแนนแล้ว 0/0 ข้อ</span>
            </header>
            <ul class="ev-total__list" role="list" id="ev-total-list"></ul>
            <div class="ev-total__grand">
                <span>คะแนนสมรรถนะรวม (เต็ม 100)</span>
                <strong id="ev-total-score">—</strong>
            </div>
            <p class="ev-total__note">
                <i class="bi bi-info-circle"></i>
                คะแนนแต่ละสมรรถนะ = คะแนนที่ได้ ÷ คะแนนเต็มของระดับที่ประเมิน × 100 ·
                คะแนนรวมคือค่าเฉลี่ยของสมรรถนะที่ประเมิน (น้ำหนักเท่ากันทุกตัว)
            </p>
        </section>

        <?php if (!$locked): ?>
            <div class="ev-actions">
                <?= Html::a('ยกเลิก', ['/me/competency/index', 'rd' => $round->round_no], ['class' => 'btn btn-light']) ?>
                <?= Html::submitButton('<i class="bi bi-check-lg"></i> บันทึก', ['class' => 'btn btn-outline-primary']) ?>
                <?php if ($next): ?>
                    <?= Html::submitButton('<i class="bi bi-arrow-right"></i> บันทึกและไปคนถัดไป', [
                        'class' => 'btn btn-primary', 'name' => 'go_next', 'value' => $next,
                    ]) ?>
                <?php endif ?>
            </div>
        <?php endif ?>
        <?= Html::endForm() ?>
    <?php endif ?>
</div>
<?php
$this->registerJs(<<<JS
(function () {
    // ---- ยุบ/กางระดับด้วยไอคอน ----
    \$(document).off('click.evToggle').on('click.evToggle', '.ev-toggle', function () {
        var \$level = \$(\$(this).data('target'));
        var expanded = \$level.attr('data-expanded') !== '1';
        \$level.attr('data-expanded', expanded ? '1' : '0').toggleClass('is-expanded', expanded);
        \$(this).attr('aria-expanded', expanded ? 'true' : 'false');
    });

    // ---- ผลรวมคะแนนแบบสด ใช้สูตรเดียวกับฝั่งเซิร์ฟเวอร์ ----
    function recalc() {
        var rows = [];
        var ratedAll = 0, expectedAll = 0, sum = 0, scoredCount = 0;

        \$('.ev-comp[data-competency]').each(function () {
            var \$comp = \$(this);
            var got = 0, full = 0, rated = 0, expected = 0;

            \$comp.find('.ev-item-select').each(function () {
                expected++;
                var value = parseInt(\$(this).val() || 0, 10);
                if (value > 0) { got += value; full += 5; rated++; }
            });

            \$comp.find('.ev-level').each(function () {
                var \$level = \$(this);
                var total = \$level.find('.ev-item-select').length;
                var filled = \$level.find('.ev-item-select').filter(function () { return \$(this).val(); }).length;
                \$level.find('[data-level-count]').html('<span class="visually-hidden">ให้คะแนนแล้ว</span>'
                    + filled + '/' + total);
                \$level.toggleClass('is-done', total > 0 && filled === total);
            });

            var percent = full > 0 ? (got / full * 100) : null;
            \$comp.find('[data-comp-score]')
                 .text(percent === null ? '—' : percent.toFixed(2))
                 .toggleClass('is-set', percent !== null);

            ratedAll += rated;
            expectedAll += expected;
            if (percent !== null) { sum += percent; scoredCount++; }
            rows.push({ name: \$comp.data('name'), percent: percent, rated: rated, expected: expected });
        });

        var html = rows.map(function (row) {
            return '<li><span>' + row.name + '</span>'
                + '<em>' + row.rated + '/' + row.expected + ' ข้อ</em>'
                + '<strong>' + (row.percent === null ? '—' : row.percent.toFixed(2)) + '</strong></li>';
        }).join('');
        \$('#ev-total-list').html(html);
        \$('#ev-total-progress').text('ให้คะแนนแล้ว ' + ratedAll + '/' + expectedAll + ' ข้อ');

        var grand = scoredCount > 0 ? (sum / scoredCount) : null;
        \$('#ev-total-score').text(grand === null ? '—' : grand.toFixed(2));
        \$('#ev-total').toggleClass('is-complete', expectedAll > 0 && ratedAll === expectedAll);
    }

    \$(document).off('change.evScore').on('change.evScore', '.ev-item-select', recalc);
    recalc();
})();
JS);
