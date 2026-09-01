<?php

use app\modules\task\models\Task;
use app\components\ThaiDate;
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
$assignerName = $task->assigner ? trim($task->assigner->fname . ' ' . $task->assigner->lname) : 'สร้างด้วยตนเอง';
$assigneeName = $task->assignee ? trim($task->assignee->fname . ' ' . $task->assignee->lname) : 'รอผู้รับผิดชอบ';
?>
<article class="d-flex align-items-start gap-2 task-item <?= $done ? 'is-done' : '' ?>"
         data-task-id="<?= (int) $task->id ?>">

    <?php if ($done): ?>
        <span class="task-check is-complete text-success-emphasis" aria-hidden="true"><i class="bi bi-check-circle-fill"></i></span>
    <?php else: ?>
        <button type="button"
                class="btn btn-link p-0 border-0 task-check text-body-secondary task-complete-btn"
                data-task-id="<?= (int) $task->id ?>"
                data-url="<?= Url::to(['/task/default/complete', 'id' => $task->id]) ?>"
                title="ปิดงานนี้"
                aria-label="ปิดงาน <?= Html::encode($task->title) ?>">
            <i class="bi bi-circle" aria-hidden="true"></i>
        </button>
    <?php endif ?>

    <div class="flex-grow-1 min-width-0">
        <a href="<?= Url::to(['/task/default/update', 'id' => $task->id]) ?>"
           class="<?= $inModal ? 'task-open-edit' : 'open-modal' ?> task-item-title d-block text-decoration-none <?= $done ? 'text-body-secondary text-decoration-line-through' : 'text-body' ?>"
           data-size="modal-lg" data-pjax="0">
            <?= Html::encode($task->title) ?>
        </a>

        <?php if ($task->detail): ?>
            <div class="task-item-detail text-body-secondary text-truncate-2"><?= Html::encode($task->detail) ?></div>
        <?php endif ?>

        <div class="task-item-people text-body-secondary">
            <span title="ผู้มอบหมาย"><i class="bi bi-person-up me-1" aria-hidden="true"></i><?= Html::encode($assignerName) ?></span>
            <i class="bi bi-arrow-right-short text-body-tertiary" aria-hidden="true"></i>
            <span title="ผู้รับผิดชอบ"><?= Html::encode($assigneeName) ?></span>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
            <?php if ($age !== null): ?>
                <span class="task-status-chip <?= $overdue ? 'bg-danger-subtle text-danger-emphasis' : 'bg-warning-subtle text-warning-emphasis' ?>">
                    <i class="bi bi-clock me-1" aria-hidden="true"></i><?= Html::encode($age) ?>
                </span>
            <?php elseif ($task->due_date): ?>
                <span class="task-status-chip bg-body-tertiary text-body-secondary">
                    <i class="bi bi-calendar3 me-1" aria-hidden="true"></i><?= Html::encode(ThaiDate::toThaiDate($task->due_date, false)) ?>
                </span>
            <?php endif ?>

            <?php if (!$done && $task->priority === Task::PRIORITY_URGENT): ?>
                <span class="task-status-chip bg-danger-subtle text-danger-emphasis">ด่วน</span>
            <?php endif ?>

            <?php if ($task->is_waiting): ?>
                <span class="task-status-chip bg-secondary-subtle text-secondary-emphasis">รอผู้อื่น</span>
            <?php elseif (!$done && $task->status === Task::STATUS_DOING): ?>
                <span class="task-status-chip bg-info-subtle text-info-emphasis">กำลังทำ</span>
            <?php endif ?>

            <?php if ($task->source_module === Task::SOURCE_DMS): ?>
                <span class="task-status-chip bg-body-tertiary text-body-secondary" title="สร้างจากหนังสือ">
                    <i class="bi bi-file-earmark-text me-1" aria-hidden="true"></i>หนังสือ
                </span>
            <?php endif ?>
        </div>
    </div>
</article>
