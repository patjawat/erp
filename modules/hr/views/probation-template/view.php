<?php
use yii\helpers\Html;

$this->title = $model->name;
$categories = [];
foreach ($model->items as $item) $categories[$item->category][] = $item;
$this->registerCss(<<<CSS
.template-category{border-bottom:1px solid var(--bs-border-color)}.template-category:last-child{border-bottom:0}.template-category__head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.25rem;background:var(--bs-tertiary-bg)}.template-category__list{margin:0;padding:0;list-style:none}.template-item{display:grid;grid-template-columns:2.5rem minmax(0,1fr) auto;gap:.75rem;align-items:center;padding:.75rem 1.25rem;border-top:1px solid var(--bs-border-color)}.template-item__number{display:grid;place-items:center;width:2rem;height:2rem;border-radius:50%;background:var(--bs-tertiary-bg);color:var(--bs-secondary-color);font-size:.8rem;font-weight:600;font-variant-numeric:tabular-nums}.template-scale-note{white-space:nowrap}
@media(max-width:575.98px){.template-page-head{align-items:stretch!important;flex-direction:column}.template-page-actions{display:grid!important}.template-page-actions .btn{min-height:44px}.template-page-actions form,.template-page-actions form .btn{width:100%}.template-category__head{align-items:flex-start}.template-item{grid-template-columns:2.25rem minmax(0,1fr);padding:.75rem}.template-item__action{grid-column:2}.template-item__action .btn{min-height:44px}}
CSS);
?>
<div class="d-flex align-items-start justify-content-between gap-3 mb-3 template-page-head">
    <div>
        <h1 class="h4 mb-1"><?= Html::encode($model->name) ?></h1>
        <p class="text-body-secondary mb-0"><?= Html::encode($model->positionGroup->title ?? '') ?> · revision <?= $model->revision_no ?> · <?= Html::encode($model->statusLabel) ?></p>
    </div>
    <div class="d-flex flex-wrap gap-2 template-page-actions">
        <?= Html::a('กลับรายการ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        <?php if ($model->status === 'draft'): ?>
            <?= Html::a('แก้ไข Template', ['form', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('เพิ่มหมวด', ['category', 'template_id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::beginForm(['activate', 'id' => $model->id], 'post') ?>
            <?= Html::submitButton('เปิดใช้งาน', ['class' => 'btn btn-success']) ?>
            <?= Html::endForm() ?>
        <?php else: ?>
            <?= Html::a('สร้าง Revision ใหม่', ['revision', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php endif ?>
    </div>
</div>

<section class="card bg-body border shadow-sm overflow-hidden">
    <?php if (!$model->items): ?>
        <div class="text-center p-5">
            <h2 class="h5">ยังไม่มีหมวดการประเมิน</h2>
            <p class="text-body-secondary">เพิ่มหมวดและรายการประเมินก่อนเปิดใช้งาน</p>
            <?php if ($model->status === 'draft'): ?><?= Html::a('เพิ่มหมวดแรก', ['category', 'template_id' => $model->id], ['class' => 'btn btn-primary']) ?><?php endif ?>
        </div>
    <?php else: ?>
        <?php $number = 1; foreach ($categories as $category => $items): ?>
            <section class="template-category" aria-labelledby="category-<?= $number ?>">
                <header class="template-category__head">
                    <div>
                        <h2 id="category-<?= $number ?>" class="h6 mb-1"><?= Html::encode($category) ?></h2>
                        <span class="small text-body-secondary"><?= count($items) ?> รายการประเมิน</span>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary-emphasis template-scale-note">สเกล 1–5</span>
                </header>
                <ol class="template-category__list">
                    <?php foreach ($items as $item): ?>
                        <li class="template-item">
                            <span class="template-item__number"><?= $number++ ?></span>
                            <span><?= Html::encode($item->question) ?></span>
                            <span class="template-item__action"><?php if ($model->status === 'draft'): ?><?= Html::a('แก้ไข', ['item', 'template_id' => $model->id, 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?><?php endif ?></span>
                        </li>
                    <?php endforeach ?>
                </ol>
            </section>
        <?php endforeach ?>
        <footer class="card-footer bg-body d-flex justify-content-between align-items-center gap-3">
            <span class="text-body-secondary">รวมทั้งหมด</span>
            <strong><?= count($model->items) ?> รายการ · คะแนนเต็มรายการละ 5 คะแนน</strong>
        </footer>
    <?php endif ?>
</section>
