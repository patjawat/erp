<?php

use yii\helpers\Html;

/**
 * แผงรายชื่อทีมงานด้านซ้าย — ติ๊กเพื่อดูงานของคนนั้นบนปฏิทินและในรายการ
 *
 * แสดงเฉพาะคนในสายหน่วยงานตัวเอง ตามกติกาสิทธิ์ที่ตกลงไว้
 * ตัวผู้ใช้เองอยู่บนสุดและติ๊กไว้เสมอ
 *
 * @var yii\web\View $this
 * @var app\modules\hr\models\Employees $me
 * @var app\modules\hr\models\Employees[] $people
 * @var int[] $selected
 */
?>
<div class="d-flex flex-column gap-2">

    <label class="visually-hidden" for="task-people-search">ค้นหาชื่อ</label>
    <div class="input-group input-group-sm">
        <span class="input-group-text bg-body"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" class="form-control" id="task-people-search" placeholder="ค้นหาชื่อ" autocomplete="off">
    </div>

    <div class="d-flex justify-content-between align-items-center px-1">
        <span class="small fw-semibold text-body-secondary">ทีมงาน</span>
        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none small" id="task-people-only-me">
            เฉพาะฉัน
        </button>
    </div>

    <div class="task-people-list overflow-auto">
        <?php foreach ($people as $person): ?>
            <?php
            $id = (int) $person->id;
            $name = trim($person->fname . ' ' . $person->lname);
            $isMe = $id === (int) $me->id;
            ?>
            <div class="form-check py-1 task-person" data-name="<?= Html::encode(mb_strtolower($name)) ?>">
                <input class="form-check-input task-person-check" type="checkbox"
                       value="<?= $id ?>" id="person-<?= $id ?>"
                    <?= in_array($id, $selected, true) ? 'checked' : '' ?>>
                <label class="form-check-label d-flex align-items-center gap-2 <?= $isMe ? 'fw-semibold' : '' ?>"
                       for="person-<?= $id ?>">
                    <span class="text-truncate"><?= Html::encode($name) ?></span>
                    <?php if ($isMe): ?>
                        <span class="badge bg-primary-subtle text-primary-emphasis">ฉัน</span>
                    <?php endif ?>
                </label>
            </div>
        <?php endforeach ?>

        <p class="text-body-secondary small mb-0 py-2 d-none" id="task-people-empty">ไม่พบชื่อที่ค้นหา</p>
    </div>
</div>
