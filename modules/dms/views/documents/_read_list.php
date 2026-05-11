<?php

use yii\helpers\Html;
use app\components\ThaiDateHelper;

/** @var array $readers */
?>
<?php if (empty($readers)): ?>
    <div class="text-center py-5 text-muted">
        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3" style="width:56px;height:56px;">
            <i class="fa-regular fa-eye-slash fs-4 opacity-50"></i>
        </div>
        <div class="small">ยังไม่มีผู้อ่านเอกสารนี้</div>
    </div>
<?php else: ?>
    <ul class="list-unstyled mb-0">
        <?php foreach ($readers as $r): ?>
            <?php
            $emp = $r->employee ?? null;
            $name = $emp ? $emp->fullname : '-';
            $dept = $emp ? $emp->departmentName() : '';
            $img = $emp ? $emp->showAvatar() : '';
            $thaiDate = '';
            try {
                $thaiDate = ThaiDateHelper::formatThaiDate($r->doc_read);
                $thaiDate .= ' ' . (explode(' ', (string) $r->doc_read)[1] ?? '');
            } catch (\Throwable $th) {
                $thaiDate = $r->doc_read;
            }
            ?>
            <li class="d-flex align-items-center gap-3 px-3 py-3 border-bottom border-light-subtle">
                <?php if ($img): ?>
                    <img src="<?= Html::encode($img) ?>" class="rounded-circle border flex-shrink-0" style="width:40px;height:40px;object-fit:cover;" alt="<?= Html::encode($name) ?>">
                <?php else: ?>
                    <span class="d-inline-flex align-items-center justify-content-center bg-secondary text-white rounded-circle flex-shrink-0" style="width:40px;height:40px;"><i class="fa-solid fa-user"></i></span>
                <?php endif; ?>
                <div class="flex-grow-1 min-width-0">
                    <div class="fw-semibold text-dark text-truncate"><?= Html::encode($name) ?></div>
                    <?php if ($dept): ?>
                        <div class="small text-muted text-truncate"><i class="fa-regular fa-building me-1"></i><?= Html::encode($dept) ?></div>
                    <?php endif; ?>
                    <div class="small text-muted"><i class="fa-regular fa-clock me-1"></i><?= Html::encode($thaiDate) ?></div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
