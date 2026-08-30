<?php

use app\components\ThaiDate;
use app\modules\task\models\Task;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * แถวงานหนึ่งรายการ
 *
 * @var yii\web\View $this
 * @var Task $task
 * @var bool $highlight เน้นเมื่ออยู่ในกลุ่ม "ต้องสนใจตอนนี้"
 */
$highlight = $highlight ?? false;
$today = date('Y-m-d');
$overdue = $task->due_date && $task->due_date < $today;
$dueToday = $task->due_date === $today;
?>
<li class="list-group-item px-3 py-3">
    <div class="d-flex align-items-start gap-3">

        <form method="post" action="<?= Url::to(['/task/default/complete', 'id' => $task->id]) ?>" class="flex-shrink-0">
            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
            <button type="submit"
                    class="btn btn-outline-success btn-sm rounded-circle"
                    title="ปิดงานนี้"
                    aria-label="ปิดงาน <?= Html::encode($task->title) ?>">
                <i class="bi bi-check-lg" aria-hidden="true"></i>
            </button>
        </form>

        <div class="flex-grow-1 min-width-0">
            <a href="<?= Url::to(['/task/default/view', 'id' => $task->id]) ?>"
               class="d-block text-body text-decoration-none <?= $highlight ? 'fw-semibold' : '' ?>">
                <?= Html::encode($task->title) ?>
            </a>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-1 small text-body-secondary">
                <?php if ($task->priority === Task::PRIORITY_URGENT): ?>
                    <span class="badge bg-danger-subtle text-danger-emphasis">
                        <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>ด่วน
                    </span>
                <?php endif ?>

                <?php if ($task->due_date): ?>
                    <span class="<?= $overdue ? 'text-danger-emphasis fw-semibold' : ($dueToday ? 'text-warning-emphasis fw-semibold' : '') ?>">
                        <i class="bi bi-calendar-event me-1" aria-hidden="true"></i>
                        <?php if ($overdue): ?>
                            เลยกำหนด <?= Html::encode(ThaiDate::toThaiDate($task->due_date, false, true)) ?>
                        <?php elseif ($dueToday): ?>
                            ครบกำหนดวันนี้
                        <?php else: ?>
                            <?= Html::encode(ThaiDate::toThaiDate($task->due_date, false, true)) ?>
                        <?php endif ?>
                    </span>
                <?php else: ?>
                    <span class="text-body-tertiary">ไม่ระบุกำหนด</span>
                <?php endif ?>

                <?php if ($task->is_waiting): ?>
                    <span class="badge bg-secondary-subtle text-secondary-emphasis">
                        <i class="bi bi-hourglass-split me-1" aria-hidden="true"></i>รอผู้อื่น
                    </span>
                <?php endif ?>

                <?php if ((int) $task->postpone_count >= 2): ?>
                    <span class="badge bg-warning-subtle text-warning-emphasis">
                        เลื่อนมาแล้ว <?= (int) $task->postpone_count ?> ครั้ง
                    </span>
                <?php endif ?>

                <?php if ($task->source_module === Task::SOURCE_DMS): ?>
                    <span class="badge bg-info-subtle text-info-emphasis">
                        <i class="bi bi-file-earmark-text me-1" aria-hidden="true"></i>จากหนังสือ
                    </span>
                <?php endif ?>
            </div>
        </div>

    </div>
</li>
