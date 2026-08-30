<?php

use app\components\ThaiDate;
use app\modules\task\models\Task;
use app\modules\task\models\TaskActivity;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * รายละเอียดงาน พร้อมประวัติความเคลื่อนไหว
 *
 * @var yii\web\View $this
 * @var Task $task
 * @var TaskActivity[] $activities
 */
$this->title = $task->title;
$this->beginBlock('page-title');
echo Html::encode('รายละเอียดงาน');
$this->endBlock();

$statusVariant = [
    Task::STATUS_PENDING => 'secondary',
    Task::STATUS_DOING => 'primary',
    Task::STATUS_DONE => 'success',
    Task::STATUS_CANCELLED => 'secondary',
][$task->status] ?? 'secondary';
?>
<div class="container-fluid px-0">

    <div class="mb-3">
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับไปงานของฉัน', ['/task/default/index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <section class="card bg-body border shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge bg-<?= $statusVariant ?>-subtle text-<?= $statusVariant ?>-emphasis">
                            <?= Html::encode($task->statusLabel()) ?>
                        </span>
                        <?php if ($task->priority === Task::PRIORITY_URGENT): ?>
                            <span class="badge bg-danger-subtle text-danger-emphasis">
                                <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>ด่วน
                            </span>
                        <?php endif ?>
                        <?php if ($task->is_waiting): ?>
                            <span class="badge bg-secondary-subtle text-secondary-emphasis">รอผู้อื่น</span>
                        <?php endif ?>
                    </div>

                    <h1 class="h4 mb-3"><?= Html::encode($task->title) ?></h1>

                    <?php if ($task->detail): ?>
                        <p class="text-body-secondary"><?= nl2br(Html::encode($task->detail)) ?></p>
                    <?php endif ?>

                    <dl class="row mb-0 small">
                        <dt class="col-5 col-sm-4 text-body-secondary fw-normal">หน่วยงานเจ้าของ</dt>
                        <dd class="col-7 col-sm-8"><?= Html::encode($task->ownerUnit->name ?? '-') ?></dd>

                        <dt class="col-5 col-sm-4 text-body-secondary fw-normal">ผู้รับผิดชอบ</dt>
                        <dd class="col-7 col-sm-8">
                            <?= $task->assignee
                                ? Html::encode(trim($task->assignee->fname . ' ' . $task->assignee->lname))
                                : '<span class="text-warning-emphasis">ยังไม่มีผู้รับผิดชอบ</span>' ?>
                        </dd>

                        <dt class="col-5 col-sm-4 text-body-secondary fw-normal">ผู้มอบหมาย</dt>
                        <dd class="col-7 col-sm-8">
                            <?= $task->assigner ? Html::encode(trim($task->assigner->fname . ' ' . $task->assigner->lname)) : '-' ?>
                        </dd>

                        <dt class="col-5 col-sm-4 text-body-secondary fw-normal">กำหนดเสร็จ</dt>
                        <dd class="col-7 col-sm-8">
                            <?= $task->due_date ? Html::encode(ThaiDate::toThaiDate($task->due_date, false)) : 'ไม่ระบุ' ?>
                            <?php if ((int) $task->postpone_count > 0): ?>
                                <span class="text-body-secondary">(เลื่อนมาแล้ว <?= (int) $task->postpone_count ?> ครั้ง)</span>
                            <?php endif ?>
                        </dd>

                        <?php if ($task->source_module === Task::SOURCE_DMS && $task->source_id): ?>
                            <dt class="col-5 col-sm-4 text-body-secondary fw-normal">ต้นเรื่อง</dt>
                            <dd class="col-7 col-sm-8">
                                <?= Html::a(
                                    '<i class="bi bi-file-earmark-text me-1"></i>เปิดหนังสือต้นเรื่อง',
                                    ['/dms/documents/view', 'id' => $task->source_id],
                                    ['class' => 'link-primary text-decoration-none', 'target' => '_blank']
                                ) ?>
                            </dd>
                        <?php endif ?>
                    </dl>
                </div>

                <?php if ($task->isOpen()): ?>
                    <div class="card-footer bg-body-tertiary d-flex flex-wrap gap-2">
                        <?php if ($task->status === Task::STATUS_PENDING): ?>
                            <form method="post" action="<?= Url::to(['/task/default/start', 'id' => $task->id]) ?>">
                                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-play me-1" aria-hidden="true"></i>เริ่มทำ
                                </button>
                            </form>
                        <?php endif ?>
                        <form method="post" action="<?= Url::to(['/task/default/complete', 'id' => $task->id]) ?>" class="d-flex flex-wrap gap-2">
                            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                            <input type="text" name="note" class="form-control form-control-sm w-auto" placeholder="บันทึกเพิ่มเติม (ไม่บังคับ)">
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="bi bi-check-lg me-1" aria-hidden="true"></i>ปิดงาน
                            </button>
                        </form>
                    </div>
                <?php endif ?>
            </section>
        </div>

        <div class="col-12 col-lg-5">
            <section class="card bg-body border shadow-sm">
                <div class="card-header bg-body-tertiary">
                    <h2 class="h6 mb-0"><i class="bi bi-clock-history me-1" aria-hidden="true"></i>ความเคลื่อนไหว</h2>
                </div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($activities as $activity): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between gap-2">
                                <span class="fw-semibold small"><?= Html::encode($activity->actionLabel()) ?></span>
                                <span class="text-body-secondary small text-nowrap">
                                    <?= Html::encode(ThaiDate::toThaiDate($activity->created_at, true, true)) ?>
                                </span>
                            </div>
                            <?php if ($activity->employee): ?>
                                <div class="text-body-secondary small">
                                    โดย <?= Html::encode(trim($activity->employee->fname . ' ' . $activity->employee->lname)) ?>
                                </div>
                            <?php endif ?>
                            <?php if ($activity->note): ?>
                                <div class="small mt-1"><?= Html::encode($activity->note) ?></div>
                            <?php endif ?>
                        </li>
                    <?php endforeach ?>
                    <?php if (!$activities): ?>
                        <li class="list-group-item text-body-secondary">ยังไม่มีความเคลื่อนไหว</li>
                    <?php endif ?>
                </ul>
            </section>
        </div>
    </div>

</div>
