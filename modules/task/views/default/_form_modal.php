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
 * ช่องที่แก้ได้อยู่บนสุด ส่วนข้อมูลที่แก้ไม่ได้ (ต้นเรื่อง ผู้มอบหมาย ประวัติ)
 * อยู่ล่างและพับไว้ เพื่อให้เปิดมาแล้วลงมือแก้ได้ทันที
 *
 * @var yii\web\View $this
 * @var Task $task
 * @var app\modules\task\models\TaskActivity[] $activities
 * @var bool $canEdit
 * @var bool $canPickPerson
 * @var app\modules\hr\models\Employees[] $members
 */
$age = $task->ageText();
$overdue = $task->overdueDays() > 0;

// ปุ่มลัดต้องใส่ค่าเป็น วว/ดด/พ.ศ. ให้ตรงกับรูปแบบที่ DatepickerThai ใช้
$quickDates = [
    'วันนี้' => AppHelper::convertToThai(date('Y-m-d')),
    'พรุ่งนี้' => AppHelper::convertToThai(date('Y-m-d', strtotime('+1 day'))),
    'อีก 3 วัน' => AppHelper::convertToThai(date('Y-m-d', strtotime('+3 days'))),
    'สัปดาห์หน้า' => AppHelper::convertToThai(date('Y-m-d', strtotime('+7 days'))),
];
?>
<form id="task-edit-form" class="task-form" method="post"
      action="<?= Url::to(['/task/default/update', 'id' => $task->id]) ?>"
      data-task-id="<?= (int) $task->id ?>">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <?php if ($age !== null): ?>
        <div class="mb-3">
            <span class="badge <?= $overdue ? 'bg-danger-subtle text-danger-emphasis' : 'bg-warning-subtle text-warning-emphasis' ?>">
                <i class="bi bi-clock me-1" aria-hidden="true"></i><?= Html::encode($age) ?>
            </span>
            <?php if ((int) $task->postpone_count > 0): ?>
                <span class="badge bg-warning-subtle text-warning-emphasis">
                    เลื่อนมาแล้ว <?= (int) $task->postpone_count ?> ครั้ง
                </span>
            <?php endif ?>
        </div>
    <?php endif ?>

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
        <textarea class="form-control" id="task-f-detail" name="detail" rows="2"
                  <?= $canEdit ? '' : 'disabled' ?>><?= Html::encode((string) $task->detail) ?></textarea>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6">
            <label class="form-label" for="task-f-due">กำหนดเสร็จ</label>
            <?php if ($canEdit): ?>
                <div class="btn-group btn-group-sm flex-wrap mb-2" role="group" aria-label="ปุ่มลัดกำหนดเสร็จ">
                    <?php foreach ($quickDates as $label => $value): ?>
                        <button type="button" class="btn btn-outline-secondary task-quick-date"
                                data-date="<?= $value ?>"><?= Html::encode($label) ?></button>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
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

        <div class="col-12 col-sm-6">
            <label class="form-label" for="task-f-status">สถานะ</label>
            <select class="form-select" id="task-f-status" name="status" <?= $canEdit ? '' : 'disabled' ?>>
                <?php foreach (Task::statusLabels() as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $task->status === $value ? 'selected' : '' ?>>
                        <?= Html::encode($label) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6">
            <span class="form-label d-block">ความสำคัญ</span>
            <div class="btn-group" role="group" aria-label="ความสำคัญ">
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

        <div class="col-12 col-sm-6">
            <label class="form-label" for="task-f-assignee">ผู้รับผิดชอบ</label>
            <?php if ($canPickPerson && $canEdit): ?>
                <select class="form-select" id="task-f-assignee" name="assignee_emp_id">
                    <option value="">ยังไม่ระบุ (รอหัวหน้าจ่ายงาน)</option>
                    <?php foreach ($members as $member): ?>
                        <option value="<?= (int) $member->id ?>"
                            <?= (int) $task->assignee_emp_id === (int) $member->id ? 'selected' : '' ?>>
                            <?= Html::encode(trim($member->fname . ' ' . $member->lname)) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            <?php else: ?>
                <input type="text" class="form-control" id="task-f-assignee" disabled
                       value="<?= Html::encode($task->assignee ? trim($task->assignee->fname . ' ' . $task->assignee->lname) : 'ยังไม่ระบุ') ?>">
            <?php endif ?>
        </div>
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" value="1" name="is_waiting" id="task-f-waiting"
            <?= $task->is_waiting ? 'checked' : '' ?> <?= $canEdit ? '' : 'disabled' ?>>
        <label class="form-check-label" for="task-f-waiting">
            ติดรออยู่ที่คนอื่น
            <span class="text-body-secondary small d-block">ติ๊กไว้แล้วระบบจะไม่นับว่างานนี้ถูกลืม</span>
        </label>
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

    <div class="border-top pt-2 small">
        <div class="row g-1 text-body-secondary">
            <div class="col-5 col-sm-4">หน่วยงานเจ้าของ</div>
            <div class="col-7 col-sm-8 text-body"><?= Html::encode($task->ownerUnit->name ?? '-') ?></div>

            <div class="col-5 col-sm-4">ผู้มอบหมาย</div>
            <div class="col-7 col-sm-8 text-body">
                <?= $task->assigner ? Html::encode(trim($task->assigner->fname . ' ' . $task->assigner->lname)) : '-' ?>
            </div>

            <?php if ($task->source_module === Task::SOURCE_DMS && $task->source_id): ?>
                <div class="col-5 col-sm-4">ต้นเรื่อง</div>
                <div class="col-7 col-sm-8">
                    <?= Html::a(
                        '<i class="bi bi-file-earmark-text me-1"></i>เปิดหนังสือ',
                        ['/dms/documents/view', 'id' => $task->source_id],
                        ['class' => 'link-primary text-decoration-none', 'target' => '_blank', 'data-pjax' => '0']
                    ) ?>
                </div>
            <?php endif ?>
        </div>

        <?php if ($activities): ?>
            <button class="btn btn-sm btn-link text-decoration-none p-0 mt-2 d-flex align-items-center gap-1 collapsed"
                    type="button" data-bs-toggle="collapse" data-bs-target="#taskFormActivities"
                    aria-expanded="false" aria-controls="taskFormActivities">
                <i class="bi bi-chevron-right task-chevron" aria-hidden="true"></i>
                ความเคลื่อนไหว (<?= count($activities) ?>)
            </button>
            <div class="collapse" id="taskFormActivities">
                <ul class="list-unstyled mb-0 mt-2">
                    <?php foreach ($activities as $activity): ?>
                        <li class="d-flex justify-content-between gap-2 py-1 border-bottom">
                            <span>
                                <?= Html::encode($activity->actionLabel()) ?>
                                <?php if ($activity->employee): ?>
                                    <span class="text-body-secondary">
                                        · <?= Html::encode(trim($activity->employee->fname . ' ' . $activity->employee->lname)) ?>
                                    </span>
                                <?php endif ?>
                                <?php if ($activity->note): ?>
                                    <span class="d-block text-body-secondary"><?= Html::encode($activity->note) ?></span>
                                <?php endif ?>
                            </span>
                            <span class="text-body-secondary text-nowrap">
                                <?= Html::encode(ThaiDate::toThaiDate($activity->created_at, true, true)) ?>
                            </span>
                        </li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>
    </div>
</form>

<script>
// widget DatepickerThai ใช้ registerJs ซึ่งไม่ทำงานตอน inject ผ่าน AJAX จึงต้อง init เอง
(function () {
    if (typeof thaiDatepicker === 'function') {
        try { thaiDatepicker('#task-f-due'); } catch (e) {}
    }
})();
</script>
