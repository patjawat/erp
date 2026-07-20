<?php

use yii\helpers\Html;

/** @var app\modules\am\models\AssetCategory[] $categories */
/** @var array $assetTypes รหัส => ชื่อ ประเภทครุภัณฑ์ (สำหรับ dropdown กรอง) */
/** @var string $q */
/** @var string|null $selectedCategoryId */
?>
<div class="category-quick-list">
    <div class="row g-2 mb-3">
        <div class="col-12">
            <select class="form-select form-select-sm category-quick-list__type-filter">
                <option value="">ทุกประเภทครุภัณฑ์</option>
                <?php foreach ($assetTypes as $code => $title): ?>
                    <option value="<?= Html::encode($code) ?>" <?= (string) $selectedCategoryId === (string) $code ? 'selected' : '' ?>><?= Html::encode($title) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm category-quick-list__search" placeholder="ค้นหาชื่อ/รหัสหมวดหมู่..." value="<?= Html::encode($q) ?>">
                <button type="button" class="btn btn-sm btn-outline-secondary text-nowrap category-quick-list__refresh" title="รีเฟรชรายการ (กดถ้าเพิ่งแก้ไขแล้วรายการยังไม่อัพเดต)">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
                <?= Html::a('<i class="fa-solid fa-circle-plus"></i>', ['/am/asset-category/create', 'title' => '<i class="fa-solid fa-circle-plus"></i> เพิ่มหมวดหมู่ครุภัณฑ์'], ['class' => 'btn btn-sm btn-primary text-nowrap open-modal category-quick-list__modal-trigger', 'data' => ['size' => 'modal-lg'], 'title' => 'เพิ่มหมวดหมู่ใหม่']) ?>
            </div>
        </div>
    </div>
    <div class="list-group category-quick-list__items">
        <?= $this->render('_quick_list_items', ['categories' => $categories]) ?>
    </div>
</div>
