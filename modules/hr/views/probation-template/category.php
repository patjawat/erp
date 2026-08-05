<?php
use yii\helpers\Html;

$this->title = 'เพิ่มหมวดการประเมิน';
$questions = $questions ?: [''];
$this->registerCss(<<<CSS
.template-item-list{display:grid;gap:.75rem}.template-item-row{display:grid;grid-template-columns:2.5rem minmax(0,1fr) 2.75rem;gap:.5rem;align-items:start}.template-item-number{display:grid;place-items:center;min-height:2.5rem;border:1px solid var(--bs-border-color);border-radius:var(--bs-border-radius);background:var(--bs-tertiary-bg);font-variant-numeric:tabular-nums}.template-item-remove{min-height:2.5rem}.template-category-help{max-width:70ch}
@media(max-width:575.98px){.template-item-row{grid-template-columns:2.25rem minmax(0,1fr) 2.5rem}.template-category-actions{display:grid}.template-category-actions .btn{min-height:44px}}
CSS);
$this->registerJs(<<<JS
(function () {
    var list = document.getElementById('template-item-list');
    var addButton = document.getElementById('add-template-item');
    if (!list || !addButton) return;
    function renumber() {
        list.querySelectorAll('.template-item-row').forEach(function (row, index) {
            row.querySelector('.template-item-number').textContent = index + 1;
            row.querySelector('textarea').setAttribute('aria-label', 'รายการประเมินที่ ' + (index + 1));
        });
        list.querySelectorAll('[data-remove-item]').forEach(function (button) {
            button.disabled = list.children.length === 1;
        });
    }
    addButton.addEventListener('click', function () {
        var row = document.createElement('div');
        row.className = 'template-item-row';
        row.innerHTML = '<span class="template-item-number" aria-hidden="true"></span><textarea class="form-control" name="questions[]" rows="2" required></textarea><button class="btn btn-outline-danger template-item-remove" type="button" data-remove-item aria-label="ลบรายการ"><i data-lucide="trash-2" aria-hidden="true"></i></button>';
        list.appendChild(row);
        renumber();
        row.querySelector('textarea').focus();
        if (window.lucide) window.lucide.createIcons();
    });
    list.addEventListener('click', function (event) {
        var button = event.target.closest('[data-remove-item]');
        if (!button || list.children.length === 1) return;
        button.closest('.template-item-row').remove();
        renumber();
    });
    renumber();
})();
JS);
?>
<div class="d-flex align-items-start justify-content-between gap-3 mb-3">
    <div>
        <h1 class="h4 mb-1"><?= Html::encode($this->title) ?></h1>
        <p class="text-body-secondary mb-0 template-category-help"><?= Html::encode($template->name) ?> · ตั้งชื่อหมวดหนึ่งครั้ง แล้วเพิ่มรายการประเมินได้หลายรายการ</p>
    </div>
    <?= Html::a('กลับ Template', ['view', 'id' => $template->id], ['class' => 'btn btn-outline-secondary']) ?>
</div>
<form method="post">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
    <section class="card bg-body border shadow-sm">
        <div class="card-body p-3 p-md-4">
            <div class="mb-4">
                <label class="form-label fw-semibold" for="template-category">ชื่อหมวด</label>
                <input id="template-category" class="form-control" name="category" maxlength="150" value="<?= Html::encode($category) ?>" placeholder="เช่น ความรู้และทักษะในงาน" required autofocus>
            </div>
            <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                <label class="form-label fw-semibold mb-0">รายการประเมินในหมวด</label>
                <button id="add-template-item" class="btn btn-sm btn-outline-primary" type="button"><i data-lucide="plus" aria-hidden="true"></i> เพิ่มรายการ</button>
            </div>
            <p class="small text-body-secondary mb-3">แต่ละรายการใช้คะแนนสเกล 1–5</p>
            <div id="template-item-list" class="template-item-list">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="template-item-row">
                        <span class="template-item-number" aria-hidden="true"><?= $index + 1 ?></span>
                        <textarea class="form-control" name="questions[]" rows="2" aria-label="รายการประเมินที่ <?= $index + 1 ?>" required><?= Html::encode($question) ?></textarea>
                        <button class="btn btn-outline-danger template-item-remove" type="button" data-remove-item aria-label="ลบรายการ"><i data-lucide="trash-2" aria-hidden="true"></i></button>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
        <footer class="card-footer bg-body d-flex justify-content-end gap-2 template-category-actions">
            <?= Html::a('ยกเลิก', ['view', 'id' => $template->id], ['class' => 'btn btn-outline-secondary']) ?>
            <button class="btn btn-primary" type="submit">บันทึกหมวดและรายการ</button>
        </footer>
    </section>
</form>
