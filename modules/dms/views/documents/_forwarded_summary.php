<?php

use yii\helpers\Html;

/** @var array $forwardedDepts */
?>
<div class="d-flex align-items-start gap-2">
    <span class="d-inline-flex align-items-center text-success-emphasis small fw-semibold flex-shrink-0">
        <i class="fa-regular fa-paper-plane me-1"></i>ส่งถึง
        <?php if (!empty($forwardedDepts)): ?>
            <span class="badge text-bg-light text-muted ms-1 small">(<?= count($forwardedDepts) ?>)</span>
        <?php endif; ?>
    </span>
    <div class="flex-grow-1 min-width-0 d-flex flex-wrap gap-1">
        <?php if (empty($forwardedDepts)): ?>
            <span class="small text-muted fst-italic">ยังไม่ได้ส่ง</span>
        <?php else: ?>
            <?php foreach ($forwardedDepts as $org): ?>
                <span class="badge text-bg-light border border-success-subtle text-success rounded-pill px-2 py-1 small d-inline-flex align-items-center gap-1">
                    <i class="fa-regular fa-building"></i>
                    <?= Html::encode($org->name) ?>
                </span>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
