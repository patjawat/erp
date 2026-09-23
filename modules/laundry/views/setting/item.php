<?php

use app\modules\laundry\models\LaundryItem;
use app\modules\laundry\models\LaundryItemCategory;
use yii\helpers\Html;

/** @var LaundryItem[] $items */
/** @var LaundryItemCategory[] $categories */
/** @var array $grouped  [['name' => หมวด, 'items' => [['model' => LaundryItem], ...]], ...] */
$this->title = 'ตั้งค่า — ประเภทผ้า';
$canManage = Yii::$app->user->can('laundry.manage');
$categoryOptions = [];
foreach ($categories as $c) {
    if ($c->is_active) {
        $categoryOptions[$c->id] = $c->name;
    }
}
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'setting']) ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-collection me-2"></i>ประเภทผ้า</h1>
        <div class="text-body-secondary small">ผ้าห่ม / ผ้าปูเตียง / เสื้อ / กางเกง ฯลฯ แยกหมวด (ผ้าของ รพ. / ผ้าจากหน่วยงานภายนอก) — ใช้ตอนตรวจนับ / คลัง / จ่ายผ้า</div>
    </div>

    <?php foreach (['error' => 'danger', 'success' => 'success'] as $k => $c): ?>
        <?php if (Yii::$app->session->hasFlash($k)): ?><div class="alert alert-<?= $c ?> d-flex align-items-center"><i class="bi bi-<?= $c === 'danger' ? 'exclamation-triangle' : 'check-circle' ?> me-2"></i><?= Html::encode(Yii::$app->session->getFlash($k)) ?></div><?php endif; ?>
    <?php endforeach; ?>

    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-header bg-body px-4 py-3"><h2 class="h6 fw-semibold mb-0"><i class="bi bi-plus-circle me-2"></i>เพิ่มประเภทผ้า</h2></div>
                <div class="card-body">
                    <?php if ($canManage): ?>
                        <?= Html::beginForm(['item-save'], 'post', ['class' => 'row g-3']) ?>
                            <div class="col-12">
                                <label class="form-label">ชื่อประเภทผ้า</label>
                                <?= Html::textInput('item_name', '', ['class' => 'form-control', 'maxlength' => 255, 'placeholder' => 'เช่น ผ้าห่ม', 'required' => true, 'autofocus' => true]) ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label">หมวด</label>
                                <?= Html::dropDownList('category_id', array_key_first($categoryOptions), $categoryOptions, ['prompt' => '— ไม่ระบุหมวด —', 'class' => 'form-select']) ?>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <?= Html::checkbox('is_active', true, ['class' => 'form-check-input', 'id' => 'itemActive']) ?>
                                    <label class="form-check-label" for="itemActive">เปิดใช้งาน</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <?= Html::submitButton('<i class="bi bi-save me-1"></i>เพิ่ม', ['class' => 'btn btn-primary']) ?>
                            </div>
                        <?= Html::endForm() ?>
                    <?php else: ?>
                        <div class="text-body-secondary small">ดูอย่างเดียว</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-body px-4 py-3"><h2 class="h6 fw-semibold mb-0"><i class="bi bi-tags me-2"></i>หมวดผ้า</h2></div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($categories as $c): ?>
                            <li class="list-group-item px-3">
                                <?php if ($canManage): ?>
                                    <div class="d-flex align-items-center gap-1">
                                        <?= Html::beginForm(['category-save'], 'post', ['class' => 'd-flex align-items-center gap-1 flex-grow-1']) ?>
                                            <?= Html::hiddenInput('id', $c->id) ?>
                                            <?= Html::input('number', 'sort_order', (int) $c->sort_order, ['class' => 'form-control form-control-sm', 'style' => 'width:60px', 'min' => 0, 'title' => 'ลำดับ']) ?>
                                            <?= Html::textInput('name', $c->name, ['class' => 'form-control form-control-sm' . ($c->is_active ? '' : ' text-body-secondary'), 'required' => true, 'maxlength' => 150]) ?>
                                            <?= Html::submitButton('<i class="bi bi-save"></i>', ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'บันทึก']) ?>
                                        <?= Html::endForm() ?>
                                        <?php if ($c->is_active): ?>
                                            <?= Html::beginForm(['category-delete'], 'post', ['class' => 'd-inline']) ?>
                                                <?= Html::hiddenInput('id', $c->id) ?>
                                                <?= Html::submitButton('<i class="bi bi-trash"></i>', ['class' => 'btn btn-sm btn-outline-danger', 'title' => 'ลบ/ปิดใช้', 'data-confirm' => 'ลบหมวดนี้? (ถ้ามีประเภทผ้าอยู่จะปิดใช้แทน)']) ?>
                                            <?= Html::endForm() ?>
                                        <?php else: ?>
                                            <?= Html::beginForm(['category-save'], 'post', ['class' => 'd-inline']) ?>
                                                <?= Html::hiddenInput('id', $c->id) ?>
                                                <?= Html::hiddenInput('name', $c->name) ?>
                                                <?= Html::hiddenInput('sort_order', $c->sort_order) ?>
                                                <?= Html::hiddenInput('is_active', 1) ?>
                                                <?= Html::submitButton('<i class="bi bi-play"></i>', ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'เปิดใช้งาน']) ?>
                                            <?= Html::endForm() ?>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <?= Html::encode($c->name) ?>
                                <?php endif; ?>
                                <?php if (!$c->is_active): ?><span class="badge text-bg-secondary mt-1">ปิดใช้</span><?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                        <?php if ($canManage): ?>
                            <li class="list-group-item px-3 bg-body-tertiary">
                                <?= Html::beginForm(['category-save'], 'post', ['class' => 'd-flex align-items-center gap-1']) ?>
                                    <?= Html::input('number', 'sort_order', count($categories) + 1, ['class' => 'form-control form-control-sm', 'style' => 'width:60px', 'min' => 0, 'title' => 'ลำดับ']) ?>
                                    <?= Html::textInput('name', '', ['class' => 'form-control form-control-sm', 'placeholder' => 'เพิ่มหมวดใหม่', 'required' => true, 'maxlength' => 150]) ?>
                                    <?= Html::submitButton('<i class="bi bi-plus-lg"></i>', ['class' => 'btn btn-sm btn-primary', 'title' => 'เพิ่มหมวด']) ?>
                                <?= Html::endForm() ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-body px-4 py-3"><h2 class="h6 fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>ประเภทผ้า (<?= count($items) ?>)</h2></div>
                <div class="card-body p-0"><div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light"><tr><th class="ps-4" style="width:60px">#</th><th>ประเภทผ้า / หมวด</th><th>สถานะ</th><th class="pe-4 text-end">จัดการ</th></tr></thead>
                        <tbody>
                        <?php foreach ($grouped as $g): ?>
                            <tr class="table-group-divider"><td colspan="4" class="ps-4 fw-semibold text-primary bg-body-tertiary"><i class="bi bi-tag me-1"></i><?= Html::encode($g['name']) ?> <span class="text-body-secondary fw-normal small">(<?= count($g['items']) ?>)</span></td></tr>
                            <?php foreach ($g['items'] as $i => $row): $it = $row['model']; ?>
                            <tr>
                                <td class="ps-4 text-body-secondary"><?= $i + 1 ?></td>
                                <td>
                                    <?php if ($canManage): ?>
                                        <?= Html::beginForm(['item-save'], 'post', ['class' => 'd-flex flex-wrap align-items-center gap-2']) ?>
                                            <?= Html::hiddenInput('id', $it->id) ?>
                                            <?= Html::hiddenInput('is_active', $it->is_active) ?>
                                            <?= Html::textInput('item_name', $it->item_name, ['class' => 'form-control form-control-sm', 'style' => 'max-width:240px', 'required' => true]) ?>
                                            <?= Html::dropDownList('category_id', $it->category_id ?: '', $categoryOptions + ($it->category_id && !isset($categoryOptions[$it->category_id]) ? [$it->category_id => ($it->category->name ?? '#' . $it->category_id) . ' (ปิดใช้)'] : []), ['prompt' => '— ไม่ระบุหมวด —', 'class' => 'form-select form-select-sm', 'style' => 'max-width:220px', 'title' => 'หมวด']) ?>
                                            <?= Html::submitButton('<i class="bi bi-save"></i>', ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'บันทึก']) ?>
                                        <?= Html::endForm() ?>
                                    <?php else: ?>
                                        <?= Html::encode($it->item_name) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($it->is_active): ?><span class="badge text-bg-success">ใช้งาน</span><?php else: ?><span class="badge text-bg-secondary">ปิด</span><?php endif; ?>
                                </td>
                                <td class="pe-4 text-end text-nowrap">
                                    <?php if ($canManage): ?>
                                        <?= Html::beginForm(['item-save'], 'post', ['class' => 'd-inline']) ?>
                                            <?= Html::hiddenInput('id', $it->id) ?>
                                            <?= Html::hiddenInput('item_name', $it->item_name) ?>
                                            <?= Html::hiddenInput('is_active', $it->is_active ? 0 : 1) ?>
                                            <?= Html::submitButton($it->is_active ? '<i class="bi bi-pause"></i>' : '<i class="bi bi-play"></i>', ['class' => 'btn btn-sm btn-outline-secondary', 'title' => $it->is_active ? 'ปิดใช้งาน' : 'เปิดใช้งาน']) ?>
                                        <?= Html::endForm() ?>
                                        <?= Html::beginForm(['item-delete'], 'post', ['class' => 'd-inline']) ?>
                                            <?= Html::hiddenInput('id', $it->id) ?>
                                            <?= Html::submitButton('<i class="bi bi-trash"></i>', ['class' => 'btn btn-sm btn-outline-danger', 'title' => 'ลบ', 'data-confirm' => 'ลบประเภทผ้านี้?']) ?>
                                        <?= Html::endForm() ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        <?php if (!$items): ?><tr><td colspan="4" class="text-center text-body-secondary py-4"><i class="bi bi-inbox me-1"></i>ยังไม่มีประเภทผ้า</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div></div>
            </div>
        </div>
    </div>
</div>
