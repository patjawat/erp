<?php
use yii\helpers\Html;

/** @var array $items */
?>

<div class="card shadow-sm mt-3">
    <div class="card-header fw-bold">ประวัติการดำเนินการ</div>
    <div class="card-body p-4">
        <?php if (empty($items)): ?>
            <div class="text-muted">ยังไม่มีประวัติการดำเนินการ</div>
        <?php else: ?>
            <ol class="list-unstyled mb-0">
                <?php foreach ($items as $i => $log): ?>
                    <?php
                    $createdAt = null;
                    $message = '';
                    if (is_object($log)) {
                        $createdAt = $log->created_at ?? null;
                        $message = $log->message ?? '';
                    } elseif (is_array($log)) {
                        $createdAt = $log['created_at'] ?? null;
                        $message = $log['message'] ?? '';
                    }
                    ?>
                    <li class="d-flex gap-3 mb-3">
                        <div class="flex-shrink-0">
                            <div class="border border-primary-subtle bg-primary bg-opacity-10 text-primary rounded-3 p-2">
                                <i class="fa-regular fa-clock"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="fw-medium"><?= Html::encode($message ?: '-') ?></div>
                            </div>
                            <div class="text-muted small mt-1">
                                <?= Html::encode($createdAt ? \Yii::$app->formatter->asDatetime($createdAt) : '-') ?>
                            </div>
                        </div>
                        <?php if ($i !== count($items) - 1): ?>
                            <div class="d-none"></div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>
</div>

