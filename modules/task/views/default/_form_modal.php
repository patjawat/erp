<?php

use app\components\AppHelper;
use app\components\ThaiDate;
use app\modules\task\models\Task;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * แก้ไขงานใน popup — เปิดผ่าน modal กลาง (#main-modal)
 *
 * ข้อมูลผู้มอบหมาย ผู้รับผิดชอบ และหน่วยงานแสดงครั้งเดียวในส่วนสรุป
 * ฟอร์มเก็บเฉพาะค่าที่ผู้ใช้ต้องแก้เป็นประจำ และพับประวัติไว้ด้านล่าง
 *
 * @var yii\web\View $this
 * @var Task $task
 * @var app\modules\task\models\TaskActivity[] $activities
 * @var bool $canEdit
 */
$age = $task->ageText();
$overdue = $task->overdueDays() > 0;
$assignerName = $task->assigner ? trim($task->assigner->fname . ' ' . $task->assigner->lname) : 'สร้างด้วยตนเอง';
$assigneeName = $task->assignee ? trim($task->assignee->fname . ' ' . $task->assignee->lname) : 'ยังไม่ระบุผู้รับผิดชอบ';
$statusClasses = [
    Task::STATUS_PENDING => 'bg-warning-subtle text-warning-emphasis',
    Task::STATUS_DOING => 'bg-info-subtle text-info-emphasis',
    Task::STATUS_DONE => 'bg-success-subtle text-success-emphasis',
    Task::STATUS_CANCELLED => 'bg-secondary-subtle text-secondary-emphasis',
];
?>
<form id="task-edit-form" class="task-form" method="post"
      action="<?= Url::to(['/task/default/update', 'id' => $task->id]) ?>"
      data-task-id="<?= (int) $task->id ?>">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <section class="task-detail-hero mb-3" aria-label="สรุปงาน">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <span class="task-status-chip <?= $statusClasses[$task->status] ?? 'bg-secondary-subtle text-secondary-emphasis' ?>">
                <?= Html::encode($task->statusLabel()) ?>
            </span>
            <?php if ($age !== null): ?>
            <span class="task-status-chip <?= $overdue ? 'bg-danger-subtle text-danger-emphasis' : 'bg-warning-subtle text-warning-emphasis' ?>">
                <i class="bi bi-clock me-1" aria-hidden="true"></i><?= Html::encode($age) ?>
            </span>
            <?php elseif ($task->due_date): ?>
                <span class="task-status-chip bg-body text-body-secondary">
                    <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>
                    กำหนด <?= Html::encode(ThaiDate::toThaiDate($task->due_date, false)) ?>
                </span>
            <?php endif ?>
            <?php if ($task->priority === Task::PRIORITY_URGENT): ?>
                <span class="task-status-chip bg-danger-subtle text-danger-emphasis">ด่วน</span>
            <?php endif ?>
            <?php if ($task->is_waiting): ?>
                <span class="task-status-chip bg-secondary-subtle text-secondary-emphasis">รอผู้อื่น</span>
            <?php endif ?>
            <?php if ((int) $task->postpone_count > 0): ?>
                <span class="task-status-chip bg-warning-subtle text-warning-emphasis">
                    เลื่อนมาแล้ว <?= (int) $task->postpone_count ?> ครั้ง
                </span>
            <?php endif ?>
        </div>

        <div class="row g-3">
            <div class="col-12 col-sm-6">
                <div class="task-detail-person">
                    <span class="task-detail-person-icon"><i class="bi bi-person-up" aria-hidden="true"></i></span>
                    <div class="min-width-0">
                        <div class="text-body-secondary small">ผู้มอบหมาย</div>
                        <div class="fw-semibold text-truncate" title="<?= Html::encode($assignerName) ?>"><?= Html::encode($assignerName) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6">
                <div class="task-detail-person">
                    <span class="task-detail-person-icon"><i class="bi bi-person-check" aria-hidden="true"></i></span>
                    <div class="min-width-0">
                        <div class="text-body-secondary small">ผู้รับผิดชอบ</div>
                        <div class="fw-semibold text-truncate" title="<?= Html::encode($assigneeName) ?>"><?= Html::encode($assigneeName) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-start gap-2 mt-3 pt-3 border-top">
            <i class="bi bi-diagram-3 text-body-secondary" aria-hidden="true"></i>
            <div class="small">
                <span class="text-body-secondary">หน่วยงานเจ้าของ</span>
                <span class="fw-medium ms-1"><?= Html::encode($task->ownerUnit->name ?? 'ไม่ระบุ') ?></span>
            </div>
        </div>
    </section>

    <?php if (!$canEdit): ?>
        <div class="alert alert-secondary py-2">
            คุณดูงานนี้ได้ แต่แก้ไขได้เฉพาะผู้รับผิดชอบหรือหัวหน้าหน่วยงานเจ้าของงาน
        </div>
    <?php endif ?>

    <div class="mb-3">
        <label class="form-label" for="task-f-title">ชื่องาน</label>
        <input type="text" class="form-control" id="task-f-title" name="title" maxlength="255" required
               value="<?= Html::encode($task->title) ?>" <?= $canEdit ? '' : 'disabled' ?>>
    </div>

    <div class="mb-3">
        <label class="form-label" for="task-f-detail">รายละเอียด</label>
        <textarea class="form-control" id="task-f-detail" name="detail" rows="4"
                  <?= $canEdit ? '' : 'disabled' ?>><?= Html::encode((string) $task->detail) ?></textarea>
    </div>

    <div class="row g-3 align-items-end mb-3">
        <div class="col-12 col-md-4">
            <label class="form-label" for="task-f-due">กำหนดเสร็จ</label>
            <?= DatepickerThai::widget([
                'name' => 'due_date',
                'value' => $task->due_date ? AppHelper::convertToThai($task->due_date) : '',
                'options' => array_merge([
                    'id' => 'task-f-due',
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                    'placeholder' => 'วว/ดด/พ.ศ.',
                ], $canEdit ? [] : ['disabled' => true]),
            ]) ?>
        </div>

        <div class="col-12 col-md-5">
            <span class="form-label d-block">สถานะ</span>
            <div class="btn-group btn-group-sm flex-wrap" role="radiogroup" aria-label="สถานะงาน">
                <?php foreach (Task::statusLabels() as $value => $label): ?>
                    <?php $statusId = 'task-f-status-' . $value; ?>
                    <input type="radio" class="btn-check" name="status" id="<?= $statusId ?>"
                           value="<?= Html::encode($value) ?>"
                           <?= $task->status === $value ? 'checked' : '' ?> <?= $canEdit ? '' : 'disabled' ?>>
                    <label class="btn btn-outline-secondary" for="<?= $statusId ?>"><?= Html::encode($label) ?></label>
                <?php endforeach ?>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <span class="form-label d-block">ความสำคัญ</span>
            <div class="btn-group" role="radiogroup" aria-label="ความสำคัญ">
                <input type="radio" class="btn-check" name="priority" id="task-f-normal"
                       value="<?= Task::PRIORITY_NORMAL ?>"
                    <?= $task->priority === Task::PRIORITY_NORMAL ? 'checked' : '' ?> <?= $canEdit ? '' : 'disabled' ?>>
                <label class="btn btn-outline-secondary" for="task-f-normal">ปกติ</label>

                <input type="radio" class="btn-check" name="priority" id="task-f-urgent"
                       value="<?= Task::PRIORITY_URGENT ?>"
                    <?= $task->priority === Task::PRIORITY_URGENT ? 'checked' : '' ?> <?= $canEdit ? '' : 'disabled' ?>>
                <label class="btn btn-outline-danger" for="task-f-urgent">ด่วน</label>
            </div>
        </div>
    </div>

    <?php if ($canEdit): ?>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2 me-1" aria-hidden="true"></i>บันทึก
            </button>
            <?php if ($task->isOpen()): ?>
                <button type="button" class="btn btn-success task-action-btn"
                        data-url="<?= Url::to(['/task/default/complete', 'id' => $task->id]) ?>">
                    <i class="bi bi-check-lg me-1" aria-hidden="true"></i>ปิดงานเลย
                </button>
            <?php endif ?>
            <span class="small align-self-center" id="task-form-msg" role="status" aria-live="polite"></span>
        </div>
    <?php endif ?>

    <?php if ($activities): ?>
        <section class="task-detail-section small" aria-labelledby="task-activity-heading">
            <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 collapsed"
                    type="button" data-bs-toggle="collapse" data-bs-target="#taskFormActivities"
                    aria-expanded="false" aria-controls="taskFormActivities">
                <i class="bi bi-chevron-right task-chevron" aria-hidden="true"></i>
                <span class="h6 mb-0" id="task-activity-heading">ความเคลื่อนไหว</span>
                <span class="badge bg-secondary-subtle text-secondary-emphasis"><?= count($activities) ?></span>
            </button>
            <div class="collapse" id="taskFormActivities">
                <ol class="list-unstyled mb-0 mt-3">
                    <?php foreach ($activities as $activity): ?>
                        <li class="task-activity-line d-flex flex-column flex-sm-row justify-content-between gap-1 gap-sm-3 pb-3">
                            <span class="fw-medium">
                                <?= Html::encode($activity->actionLabel()) ?>
                                <?php if ($activity->employee): ?>
                                    <span class="fw-normal text-body-secondary">
                                        · <?= Html::encode(trim($activity->employee->fname . ' ' . $activity->employee->lname)) ?>
                                    </span>
                                <?php endif ?>
                                <?php if ($activity->note): ?>
                                    <span class="d-block fw-normal text-body-secondary mt-1"><?= Html::encode($activity->note) ?></span>
                                <?php endif ?>
                            </span>
                            <span class="text-body-secondary text-nowrap">
                                <?= Html::encode(ThaiDate::toThaiDate($activity->created_at, true, true)) ?>
                            </span>
                        </li>
                    <?php endforeach ?>
                </ol>
            </div>
        </section>
    <?php endif ?>
</form>

<script>
// widget DatepickerThai ใช้ registerJs ซึ่งไม่ทำงานตอน inject ผ่าน AJAX จึงต้อง init เอง
(function () {
    if (typeof thaiDatepicker === 'function') {
        try { thaiDatepicker('#task-f-due'); } catch (e) {}
    }
})();
</script>
