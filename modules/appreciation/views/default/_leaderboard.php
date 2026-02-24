<?php

use yii\helpers\Url;
use yii\helpers\Html;

/**
 * @var array $leaderboard รูปแบบ [ ['emp_id' => int, 'total_points' => int, 'employee' => Employees|null ], ... ]
 * @var \app\modules\hr\models\Employees|null $me
 */
?>
<div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
    <div class="card-header bg-primary bg-opacity-10 py-2 px-3 d-flex align-items-center justify-content-between border-bottom">
        <h6 class="mb-0 fw-bold small"><i class="bi bi-trophy text-warning me-1"></i> อันดับรับคำชม</h6>
    </div>
    <div class="card-body p-2">
        <?php if (empty($leaderboard)): ?>
            <p class="small text-muted text-center mb-0 py-3">ยังไม่มีข้อมูล</p>
        <?php else: ?>
            <ol class="list-group list-group-flush list-group-numbered">
                <?php foreach ($leaderboard as $row): $emp = $row['employee'] ?? null; ?>
                    <li class="list-group-item d-flex align-items-center gap-2 border-0 py-2 px-2 <?= $me && $emp && $me->id == $emp->id ? 'bg-primary bg-opacity-10 rounded-2' : '' ?>">
                        <?php if ($emp): ?>
                            <?= Html::img($emp->showAvatar(), ['class' => 'rounded-circle flex-shrink-0', 'width' => '32', 'height' => '32', 'alt' => '']) ?>
                            <span class="small text-truncate flex-grow-1"><?= Html::encode($emp->fullname()) ?></span>
                        <?php else: ?>
                            <span class="small text-muted flex-grow-1">-</span>
                        <?php endif; ?>
                        <span class="badge bg-warning text-dark rounded-pill"><?= number_format($row['total_points']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>
</div>
