<?php

use yii\helpers\Html;
use app\components\ThaiDateHelper;

/** @var array $readers รายการ DocumentsDetail ที่ name='read' (มี ->employee และ ->doc_read) */
$max = 5;
$count = count($readers);
$shown = array_slice($readers, 0, $max);
$remaining = max(0, $count - $max);
?>
<div class="d-flex align-items-center gap-2">
    <span class="d-inline-flex align-items-center text-info-emphasis small fw-semibold flex-shrink-0">
        <i class="fa-regular fa-eye me-1"></i>อ่านแล้ว
        <span class="badge text-bg-light text-muted ms-1 small">(<?= $count ?>)</span>
    </span>
    <div class="flex-grow-1 min-width-0">
        <?php if ($count === 0): ?>
            <span class="small text-muted fst-italic">ยังไม่มีผู้อ่าน</span>
        <?php else: ?>
            <button type="button" class="btn btn-link p-0 text-decoration-none d-inline-flex align-items-center gap-1" data-bs-toggle="offcanvas" data-bs-target="#offcanvasReadHistory" aria-controls="offcanvasReadHistory">
                <span class="d-inline-flex">
                    <?php foreach ($shown as $i => $r): ?>
                        <?php
                        $emp = $r->employee ?? null;
                        $name = $emp ? $emp->fullname : '-';
                        $date = '';
                        try { $date = ThaiDateHelper::formatThaiDate($r->doc_read) . ' ' . (explode(' ', (string) $r->doc_read)[1] ?? ''); } catch (\Throwable $th) {}
                        $title = trim($name . ' · ' . $date);
                        $img = $emp ? $emp->showAvatar() : '';
                        ?>
                        <?php if ($img): ?>
                            <img src="<?= Html::encode($img) ?>"
                                class="rounded-circle border border-2 border-white"
                                style="width:28px;height:28px;object-fit:cover;<?= $i > 0 ? 'margin-left:-8px;' : '' ?>"
                                title="<?= Html::encode($title) ?>"
                                data-bs-toggle="tooltip"
                                alt="<?= Html::encode($name) ?>">
                        <?php else: ?>
                            <span class="d-inline-flex align-items-center justify-content-center bg-secondary text-white rounded-circle border border-2 border-white"
                                style="width:28px;height:28px;<?= $i > 0 ? 'margin-left:-8px;' : '' ?>"
                                title="<?= Html::encode($title) ?>"
                                data-bs-toggle="tooltip">
                                <i class="fa-solid fa-user small"></i>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </span>
                <?php if ($remaining > 0): ?>
                    <span class="badge text-bg-light text-muted rounded-pill small ms-1">+<?= $remaining ?></span>
                <?php endif; ?>
                <span class="text-muted small ms-1">ดูทั้งหมด</span>
            </button>
        <?php endif; ?>
    </div>
</div>
