<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * เนื้อหา offcanvas ตั้งค่า กลุ่ม/ประเภท/หมวดหมู่ (ตัวกรอง + ค้นหา + รายการ + ลิงก์หน้าเต็ม)
 * โหลดผ่าน AJAX ตอนเปิด offcanvas — ดู JS ใน modules/am/menu.php
 *
 * @var array $cfg config จาก controller
 *   - entity        string       group|type|category
 *   - label         string       ชื่อเอนทิตี
 *   - items         array
 *   - q             string       คำค้นปัจจุบัน
 *   - filterOptions array|null    ตัวเลือก dropdown กรอง (code=>title); null = ไม่มี
 *   - filterLabel   string        ข้อความ option "ทั้งหมด"
 *   - selectedFilter string|null  ค่าที่เลือกใน dropdown
 *   - createUrl     array|null    route ปุ่มเพิ่ม (null = read-only)
 *   - updateUrl     array|null
 *   - deleteUrl     array|null
 *   - indexUrl      array         route หน้า index เต็ม
 */
$q = $cfg['q'] ?? '';
$filterOptions = $cfg['filterOptions'] ?? null;
$filterLabel = $cfg['filterLabel'] ?? 'ทั้งหมด';
$selectedFilter = $cfg['selectedFilter'] ?? '';
$createUrl = $cfg['createUrl'] ?? null;
$indexUrl = $cfg['indexUrl'] ?? ['/am'];
$label = $cfg['label'] ?? '';
?>
<div class="setting-quick d-flex flex-column h-100">
    <div class="row g-2 mb-3">
        <?php if ($filterOptions !== null): ?>
            <div class="col-12">
                <select class="form-select form-select-sm js-setting-quick__filter">
                    <option value=""><?= Html::encode($filterLabel) ?></option>
                    <?php foreach ($filterOptions as $code => $title): ?>
                        <option value="<?= Html::encode($code) ?>" <?= (string) $selectedFilter === (string) $code ? 'selected' : '' ?>><?= Html::encode($title) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <div class="col-12">
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm js-setting-quick__search" placeholder="ค้นหาชื่อ/รหัส..." value="<?= Html::encode($q) ?>">
                <button type="button" class="btn btn-sm btn-outline-secondary text-nowrap js-setting-quick__refresh" title="รีเฟรชรายการ">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
                <?php if ($createUrl !== null): ?>
                    <?= Html::a('<i class="fa-solid fa-circle-plus"></i>', array_merge($createUrl, [
                        'title' => '<i class="fa-solid fa-circle-plus"></i> เพิ่ม' . $label,
                    ]), ['class' => 'btn btn-sm btn-primary text-nowrap open-modal js-setting-quick__add', 'data' => ['size' => 'modal-lg'], 'title' => 'เพิ่มใหม่']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="list-group js-setting-quick__items flex-grow-1 overflow-auto">
        <?= $this->render('_items', ['cfg' => $cfg]) ?>
    </div>

    <div class="border-top pt-2 mt-2 text-end">
        <?= Html::a('<i class="bi bi-box-arrow-up-right me-1"></i> เปิดหน้าเต็ม', Url::to($indexUrl), [
            'class' => 'btn btn-sm btn-link text-decoration-none',
        ]) ?>
    </div>
</div>
