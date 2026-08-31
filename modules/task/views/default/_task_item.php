<?php

use app\modules\task\models\Task;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * แถวงานแบบกระชับ ใช้ทั้งในแผงขวาและใน popup งานที่รอดำเนินการ
 *
 * @var yii\web\View $this
 * @var Task $task
 * @var bool $done แสดงแบบงานที่ปิดแล้ว
 * @var bool $inModal อยู่ใน modal อยู่แล้ว ให้สลับเนื้อหาแทนการเปิด modal ซ้อน
 */
$done = $done ?? false;
$inModal = $inModal ?? false;
$age = $task->ageText();
$overdue = $task->overdueDays() > 0;
?>
<div class="d-flex align-items-start gap-2 py-2 task-item" data-task-id="<?= (int) $task->id ?>">

    <?php if ($done): ?>
        <span class="text-success-emphasis lh-1 pt-1" aria-hidden="true"><i class="bi bi-check-circle-fill"></i></span>
    <?php else: ?>
        <button type="button"
                class="btn btn-link p-0 border-0 lh-1 pt-1 text-body-secondary task-complete-btn"
                data-task-id="<?= (int) $task->id ?>"
                data-url="<?= Url::to(['/task/default/complete', 'id' => $task->id]) ?>"
                title="ปิดงานนี้"
                aria-label="ปิดงาน <?= Html::encode($task->title) ?>">
            <i class="bi bi-circle" aria-hidden="true"></i>
        </button>
    <?php endif ?>

    <div class="flex-grow-1 min-width-0">
        <a href="<?= Url::to(['/task/default/update', 'id' => $task->id]) ?>"
           class="<?= $inModal ? 'task-open-edit' : 'open-modal' ?> d-block text-decoration-none <?= $done ? 'text-body-secondary text-decoration-line-through' : 'text-body' ?>"
           data-size="modal-lg" data-pjax="0">
            <?= Html::encode($task->title) ?>
        </a>

        <?php if ($task->detail): ?>
            <div class="small text-body-secondary text-truncate-2"><?= Html::encode($task->detail) ?></div>
        <?php endif ?>

        <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
            <?php if ($age !== null): ?>
                <span class="badge <?= $overdue ? 'bg-danger-subtle text-danger-emphasis' : 'bg-warning-subtle text-warning-emphasis' ?>">
                    <i class="bi bi-clock me-1" aria-hidden="true"></i><?= Html::encode($age) ?>
                </span>
            <?php endif ?>

            <?php if (!$done && $task->priority === Task::PRIORITY_URGENT): ?>
                <span class="badge bg-danger-subtle text-danger-emphasis">ด่วน</span>
            <?php endif ?>

            <?php if ($task->is_waiting): ?>
                <span class="badge bg-secondary-subtle text-secondary-emphasis">รอผู้อื่น</span>
            <?php endif ?>

            <?php if ($task->source_module === Task::SOURCE_DMS): ?>
                <span class="text-body-tertiary small" title="สร้างจากหนังสือ">
                    <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                </span>
            <?php endif ?>
        </div>
    </div>
</div>
