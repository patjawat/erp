<?php
use yii\helpers\Html;

$this->title = $evaluation->roleLabel . ' เดือนที่ ' . $evaluation->round->month_no;
echo $this->render('_styles');
$previous = array_filter($evaluation->round->evaluations, static fn($item) => $item->status === 'submitted' && $item->id !== $evaluation->id);
$this->registerCss(<<<CSS
.probation-score-control{display:grid;grid-template-columns:2.75rem minmax(5rem,7rem) 2.75rem;align-items:stretch}.probation-score-control .btn{display:grid;place-items:center;min-height:2.75rem;padding:0}.probation-score-control .probation-score-input{border-radius:0;text-align:center;font-size:1rem;font-weight:700}.probation-score-control .btn:first-child{border-radius:var(--bs-border-radius) 0 0 var(--bs-border-radius)}.probation-score-control .btn:last-child{border-radius:0 var(--bs-border-radius) var(--bs-border-radius) 0}.probation-score-help{display:flex;align-items:flex-start;gap:.65rem;padding:.75rem 1rem;border-radius:var(--probation-radius-sm);background:var(--bs-primary-bg-subtle);color:var(--bs-primary-text-emphasis)}
@media(max-width:575.98px){.probation-score-control{grid-template-columns:3rem minmax(0,1fr) 3rem;width:100%}}
CSS);
$this->registerJs(<<<JS
document.querySelectorAll('.probation-score-input').forEach(function (input) {
    input.addEventListener('focus', function () { this.select(); });
    input.addEventListener('click', function () { this.select(); });
});
document.querySelectorAll('[data-score-action]').forEach(function (button) {
    button.addEventListener('click', function () {
        var input = document.getElementById(this.dataset.scoreTarget);
        if (!input) return;
        if (input.value === '') input.value = '0';
        this.dataset.scoreAction === 'increase' ? input.stepUp() : input.stepDown();
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.focus();
    });
});
JS);
?>
<div class="probation-shell">
    <header class="probation-head"><div><h1><?= Html::encode($this->title) ?></h1><p class="text-body-secondary"><?= Html::encode($evaluation->round->case->employee->fullname) ?> · <?= Html::encode($evaluation->round->case->template->name) ?></p></div></header>
    <form method="post">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
        <div class="probation-detail-grid">
            <main class="probation-card p-3 p-md-4">
                <div class="probation-score-help mb-3"><i data-lucide="info" aria-hidden="true"></i><span>กรอกคะแนนเป็นจำนวนเต็ม คลิกช่องแล้วพิมพ์ตัวเลขใหม่ได้ทันที หรือใช้ปุ่ม − / + เพื่อปรับครั้งละ 1 คะแนน</span></div>
                <?php $category = null; foreach ($evaluation->round->case->template->items as $item): ?>
                    <?php if ($category !== $item->category): $category = $item->category; ?><h2 class="h6 mt-4 mb-1"><?= Html::encode($category) ?></h2><?php endif ?>
                    <div class="probation-score-grid">
                        <div><label for="score-<?= $item->id ?>" class="fw-semibold"><?= Html::encode($item->question) ?></label><small class="d-block text-body-secondary">คะแนนเต็ม <?= number_format($item->max_score, 0) ?></small></div>
                        <div class="probation-score-control">
                            <button class="btn btn-outline-secondary" type="button" data-score-action="decrease" data-score-target="score-<?= $item->id ?>" aria-label="ลดคะแนนข้อ <?= $item->id ?>"><i data-lucide="minus" aria-hidden="true"></i></button>
                            <input id="score-<?= $item->id ?>" class="form-control probation-score-input" type="number" name="scores[<?= $item->id ?>]" min="0" max="<?= (float)$item->max_score ?>" step="1" inputmode="numeric" placeholder="0" autocomplete="off" required>
                            <button class="btn btn-outline-secondary" type="button" data-score-action="increase" data-score-target="score-<?= $item->id ?>" aria-label="เพิ่มคะแนนข้อ <?= $item->id ?>"><i data-lucide="plus" aria-hidden="true"></i></button>
                        </div>
                    </div>
                <?php endforeach ?>
                <div class="probation-form-actions"><?= Html::a('ยกเลิก', ['view', 'id' => $evaluation->round->case_id], ['class' => 'btn btn-outline-secondary']) ?><button class="btn btn-primary" type="submit">ส่งผลประเมิน</button></div>
            </main>
            <aside><div class="probation-card p-3 probation-sticky"><h2 class="h6">คะแนนก่อนหน้า</h2><?php if (!$previous): ?><p class="text-body-secondary mb-0">ยังไม่มีคะแนนก่อนหน้า</p><?php else: ?><?php foreach ($previous as $item): ?><div class="d-flex justify-content-between py-2 border-bottom"><span><?= Html::encode($item->roleLabel) ?></span><strong class="probation-numeric"><?= number_format($item->percent_score, 2) ?>%</strong></div><?php endforeach ?><?php endif ?><p class="small text-body-secondary mt-3 mb-0">เมื่อส่งแล้วคะแนนจะถูกล็อก หากต้องแก้ไขให้ติดต่อ HR พร้อมระบุเหตุผล</p></div></aside>
        </div>
    </form>
</div>
