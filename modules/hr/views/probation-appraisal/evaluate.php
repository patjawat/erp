<?php

use yii\helpers\Html;

$this->title = $evaluation->roleLabel . ' เดือนที่ ' . $evaluation->round->month_no;
echo $this->render('_styles');

$previous = array_filter(
    $evaluation->round->evaluations,
    static fn($item) => $item->status === 'submitted' && $item->id !== $evaluation->id
);
$existingScores = [];
foreach ($evaluation->scores as $score) {
    $existingScores[(int) $score->template_item_id] = (int) $score->score;
}

$scaleLabels = [
    1 => 'ไม่ผ่าน',
    2 => 'ต้องปรับปรุง',
    3 => 'พอใช้',
    4 => 'ดี',
    5 => 'ดีมาก',
];
$groupedItems = [];
foreach ($evaluation->round->case->template->items as $item) {
    $categoryName = trim((string) $item->category) ?: 'หัวข้อทั่วไป';
    $groupedItems[$categoryName][] = $item;
}
$questionNumber = 0;

$this->registerCss(<<<CSS
.probation-score-grid{grid-template-columns:minmax(0,1fr) minmax(15rem,20rem)}
.probation-scale-guide{display:grid;grid-template-columns:repeat(5,minmax(5.5rem,1fr));gap:1.5rem;padding:1.25rem 1.5rem;border:1px solid var(--bs-border-color);border-radius:var(--probation-radius-sm);background:var(--bs-tertiary-bg)}
.probation-scale-guide>div{display:flex;min-width:0;flex-direction:column;align-items:center;text-align:center;color:var(--score-ink)}
.probation-scale-circle{display:grid;width:4.5rem;height:4.5rem;place-items:center;border:2px solid var(--score-border);border-radius:50%;background:var(--score-soft);font-size:1.5rem;font-weight:800;line-height:1}
.probation-scale-guide small{display:block;margin-top:.65rem;color:inherit;font-size:.82rem;font-weight:700;line-height:1.3;text-wrap:balance}
.probation-score-help{display:flex;align-items:flex-start;gap:.65rem;padding:.75rem 1rem;border-radius:var(--probation-radius-sm);background:var(--bs-primary-bg-subtle);color:var(--bs-primary-text-emphasis)}
.probation-category{overflow:hidden;border:1px solid var(--bs-border-color);border-radius:var(--probation-radius);background:var(--bs-body-bg)}
.probation-category+.probation-category{margin-top:1.5rem}
.probation-category-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.25rem;background:var(--bs-tertiary-bg);border-bottom:1px solid var(--bs-border-color)}
.probation-category-title{display:flex;align-items:center;gap:.75rem;min-width:0}
.probation-category-icon{display:grid;flex:0 0 2.5rem;width:2.5rem;height:2.5rem;place-items:center;border-radius:50%;background:var(--bs-primary);color:var(--bs-white);font-weight:700}
.probation-category-head h2{margin:0;font-size:1.125rem;font-weight:700;text-wrap:balance}
.probation-category-count{flex:0 0 auto;padding:.3rem .65rem;border-radius:999px;background:var(--bs-body-bg);color:var(--bs-secondary-color);font-size:.8125rem;font-weight:600}
.probation-question{display:grid;grid-template-columns:minmax(0,1fr) minmax(16rem,19rem);align-items:center;gap:1.5rem;padding:1.25rem}
.probation-question+.probation-question{border-top:1px solid var(--bs-border-color)}
.probation-question-copy{display:flex;align-items:flex-start;gap:.75rem;min-width:0}
.probation-question-number{display:grid;flex:0 0 2rem;width:2rem;height:2rem;place-items:center;border-radius:50%;background:var(--bs-secondary-bg);color:var(--bs-emphasis-color);font-size:.875rem;font-weight:700}
.probation-question-text{padding-top:.25rem;line-height:1.55;text-wrap:pretty}
.score-1{--score-soft:#fff1f2;--score-border:#fda4af;--score-ink:#9f1239;--score-solid:#be123c}.score-2{--score-soft:#fff7ed;--score-border:#fdba74;--score-ink:#9a3412;--score-solid:#c2410c}.score-3{--score-soft:#fefce8;--score-border:#fde047;--score-ink:#713f12;--score-solid:#a16207}.score-4{--score-soft:#ecfeff;--score-border:#67e8f9;--score-ink:#155e75;--score-solid:#0e7490}.score-5{--score-soft:#ecfdf5;--score-border:#6ee7b7;--score-ink:#065f46;--score-solid:#047857}
.probation-rating{display:flex;align-items:center;justify-content:flex-end;gap:.65rem}
.probation-rating .btn{display:grid;width:2.75rem;height:2.75rem;min-height:0;flex:0 0 2.75rem;place-items:center;padding:0;border:2px solid var(--score-border);border-radius:50%;background:var(--score-soft);color:var(--score-ink)}
.probation-rating .score-value{font-size:1rem;font-weight:800;line-height:1}
.probation-rating .btn:hover{border-color:var(--score-solid);background:var(--score-soft);color:var(--score-ink);box-shadow:0 0 0 2px var(--score-border)}
.probation-rating .btn-check:checked+.btn{border-color:var(--score-solid);background:var(--score-solid);color:#fff;box-shadow:0 0 0 3px var(--score-soft)}
.probation-rating .btn:focus-visible{outline:3px solid var(--bs-primary-border-subtle);outline-offset:2px}
@media(max-width:767.98px){
  .probation-scale-guide{display:flex;justify-content:center;gap:1.25rem .75rem;padding:1rem}.probation-scale-guide>div{flex:0 0 calc((100% - 1.5rem)/3)}.probation-scale-circle{width:4rem;height:4rem;font-size:1.3rem}.probation-scale-guide small{margin-top:.5rem;font-size:.75rem}
  .probation-category-head{align-items:flex-start;padding:1rem}.probation-category-title{align-items:flex-start}.probation-category-count{margin-top:.3rem}
  .probation-question{grid-template-columns:1fr;gap:1rem;padding:1rem}.probation-rating{justify-content:flex-start;width:100%}.probation-rating .btn{width:2.65rem;height:2.65rem;flex-basis:2.65rem}
}
CSS);
$this->registerJs(<<<JS
document.getElementById('probation-evaluation-form')?.addEventListener('submit', function () {
    if (!this.checkValidity()) return;
    var button = this.querySelector('[data-submit-evaluation]');
    if (!button || button.disabled) return;
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> กำลังส่งผลประเมิน';
});
JS);
?>
<div class="probation-shell">
    <header class="probation-head">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
            <p class="text-body-secondary">
                <?= Html::encode($evaluation->round->case->employee->fullname) ?>
                · <?= Html::encode($evaluation->round->case->template->name) ?>
            </p>
        </div>
    </header>

    <form id="probation-evaluation-form" method="post">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
        <div class="probation-detail-grid">
            <main class="probation-card p-3 p-md-4">
                <div class="probation-score-help mb-3">
                    <i data-lucide="info" aria-hidden="true"></i>
                    <span>เลือกคะแนน 1–5 ให้ครบทุกรายการ โดย 1 คือไม่ผ่าน และ 5 คือดีมาก</span>
                </div>
                <div class="probation-scale-guide mb-4" aria-label="ความหมายของระดับคะแนน">
                    <?php foreach ($scaleLabels as $score => $label): ?>
                        <div class="score-<?= $score ?>">
                            <span class="probation-scale-circle"><?= $score ?></span>
                            <small><?= Html::encode($label) ?></small>
                        </div>
                    <?php endforeach ?>
                </div>

                <div class="probation-categories">
                    <?php $categoryNumber = 0; foreach ($groupedItems as $categoryName => $categoryItems): $categoryNumber++; ?>
                        <section class="probation-category" aria-labelledby="category-<?= md5($categoryName) ?>">
                            <header class="probation-category-head">
                                <div class="probation-category-title">
                                    <span class="probation-category-icon" aria-hidden="true"><?= $categoryNumber ?></span>
                                    <h2 id="category-<?= md5($categoryName) ?>"><?= Html::encode($categoryName) ?></h2>
                                </div>
                                <span class="probation-category-count"><?= count($categoryItems) ?> รายการ</span>
                            </header>

                            <div class="probation-question-list">
                                <?php foreach ($categoryItems as $item): $questionNumber++; ?>
                                    <div class="probation-question">
                                        <div class="probation-question-copy">
                                            <span class="probation-question-number" aria-hidden="true"><?= $questionNumber ?></span>
                                            <span id="question-<?= $item->id ?>" class="probation-question-text"><?= Html::encode($item->question) ?></span>
                                        </div>
                                        <div class="probation-rating" role="radiogroup" aria-labelledby="question-<?= $item->id ?>">
                                            <?php foreach ($scaleLabels as $score => $label): $inputId = 'score-' . $item->id . '-' . $score; ?>
                                                <input class="btn-check" id="<?= $inputId ?>" type="radio" name="scores[<?= $item->id ?>]" value="<?= $score ?>" <?= ($existingScores[(int) $item->id] ?? null) === $score ? 'checked' : '' ?> required>
                                                <label class="btn score-<?= $score ?>" for="<?= $inputId ?>" title="<?= Html::encode($label) ?>">
                                                    <span class="score-value"><?= $score ?></span>
                                                    <span class="visually-hidden"><?= Html::encode($label) ?></span>
                                                </label>
                                            <?php endforeach ?>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </section>
                    <?php endforeach ?>
                </div>

                <div class="mt-4">
                    <label class="form-label fw-semibold" for="evaluation-comment">ความคิดเห็นและข้อเสนอแนะ</label>
                    <textarea class="form-control" id="evaluation-comment" name="comment" rows="4" maxlength="2000" required placeholder="ระบุจุดเด่น สิ่งที่ควรพัฒนา หรือข้อเสนอแนะสำหรับเดือนนี้"><?= Html::encode((string) $evaluation->comment) ?></textarea>
                    <div class="form-text">ความเห็นนี้จะแสดงในเอกสารประเมินของเดือนนี้</div>
                </div>

                <div class="probation-form-actions">
                    <?= Html::a('ยกเลิก', ['view', 'id' => $evaluation->round->case_id], ['class' => 'btn btn-outline-secondary']) ?>
                    <button class="btn btn-primary" type="submit" data-submit-evaluation>ส่งผลประเมิน</button>
                </div>
            </main>

            <aside>
                <div class="probation-card p-3 probation-sticky">
                    <h2 class="h6">คะแนนก่อนหน้า</h2>
                    <?php if (!$previous): ?>
                        <p class="text-body-secondary mb-0">ยังไม่มีคะแนนก่อนหน้า</p>
                    <?php else: ?>
                        <?php foreach ($previous as $item): ?>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span><?= Html::encode($item->roleLabel) ?></span>
                                <strong class="probation-numeric"><?= number_format($item->percent_score, 2) ?>%</strong>
                            </div>
                        <?php endforeach ?>
                    <?php endif ?>
                    <p class="small text-body-secondary mt-3 mb-0">เมื่อส่งแล้วคะแนนจะถูกล็อก หากต้องการแก้ไขให้ติดต่อ HR พร้อมระบุเหตุผล</p>
                </div>
            </aside>
        </div>
    </form>
</div>
