<?php

use app\modules\task\models\Task;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * งานของฉัน
 *
 * หน้านี้นำด้วยงานที่กำลังจะมีปัญหา ไม่ใช่งานใหม่
 * เพราะงานใหม่ผู้ใช้เพิ่งรับมาและจำได้อยู่แล้ว สิ่งที่จำไม่ได้คือของเก่าที่จมอยู่ข้างล่าง
 *
 * @var yii\web\View $this
 * @var array $groups
 * @var Task[] $waitingAssign
 * @var app\modules\hr\models\Employees $me
 * @var int $doneToday
 */
$this->title = 'งานของฉัน';
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();

$totalOpen = count($groups['attention']) + $groups['attentionMore']
    + count($groups['today']) + count($groups['week']) + count($groups['later']);

$sections = [
    ['key' => 'today', 'label' => 'วันนี้', 'icon' => 'bi-sun'],
    ['key' => 'week', 'label' => 'สัปดาห์นี้', 'icon' => 'bi-calendar-week'],
    ['key' => 'later', 'label' => 'ภายหลัง', 'icon' => 'bi-three-dots'],
];
?>
<div class="container-fluid px-0">

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $variant): ?>
        <?php if (Yii::$app->session->hasFlash($flash)): ?>
            <div class="alert alert-<?= $variant ?> alert-dismissible fade show" role="alert">
                <?= Html::encode(Yii::$app->session->getFlash($flash)) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
            </div>
        <?php endif ?>
    <?php endforeach ?>

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">งานของฉัน</h1>
            <p class="text-body-secondary mb-0">
                ค้างอยู่ <?= number_format($totalOpen) ?> งาน
                <?php if ($doneToday > 0): ?>
                    · ปิดไปแล้ววันนี้ <?= number_format($doneToday) ?>
                <?php endif ?>
            </p>
        </div>
    </div>

    <?php if ($totalOpen === 0 && !$waitingAssign): ?>
        <section class="card bg-body border shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-check2-circle fs-1 text-success-emphasis d-block mb-2" aria-hidden="true"></i>
                <p class="h5 mb-1">ไม่มีงานค้าง</p>
                <p class="text-body-secondary mb-0">งานที่มอบหมายถึงคุณจะขึ้นที่นี่</p>
            </div>
        </section>
    <?php endif ?>

    <?php if ($groups['attention']): ?>
        <section class="card bg-body border border-danger-subtle shadow-sm mb-3">
            <div class="card-header bg-danger-subtle text-danger-emphasis d-flex justify-content-between align-items-center">
                <h2 class="h6 mb-0">
                    <i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>ต้องสนใจตอนนี้
                </h2>
                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                    <?= count($groups['attention']) + $groups['attentionMore'] ?>
                </span>
            </div>
            <ul class="list-group list-group-flush">
                <?php foreach ($groups['attention'] as $task): ?>
                    <?= $this->render('_task_row', ['task' => $task, 'highlight' => true]) ?>
                <?php endforeach ?>
            </ul>
            <?php if ($groups['attentionMore'] > 0): ?>
                <div class="card-footer bg-body-tertiary text-body-secondary small">
                    ยังมีอีก <?= $groups['attentionMore'] ?> งานที่ต้องสนใจ — จัดการ 3 งานข้างบนให้เสร็จก่อน
                </div>
            <?php endif ?>
        </section>
    <?php endif ?>

    <?php if ($waitingAssign): ?>
        <section class="card bg-body border shadow-sm mb-3">
            <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h6 mb-1">
                        <i class="bi bi-inbox me-1" aria-hidden="true"></i>งานรอจ่ายในหน่วยงาน
                    </h2>
                    <p class="text-body-secondary small mb-0">ส่งถึงหน่วยแล้วแต่ยังไม่มีผู้รับผิดชอบ</p>
                </div>
                <span class="badge bg-secondary-subtle text-secondary-emphasis"><?= count($waitingAssign) ?></span>
            </div>
            <ul class="list-group list-group-flush">
                <?php foreach ($waitingAssign as $task): ?>
                    <li class="list-group-item px-3 py-3">
                        <div class="fw-semibold mb-1"><?= Html::encode($task->title) ?></div>
                        <div class="text-body-secondary small mb-2">
                            <i class="bi bi-diagram-3 me-1" aria-hidden="true"></i>
                            <?= Html::encode($task->ownerUnit->name ?? '-') ?>
                        </div>
                        <form method="post" action="<?= Url::to(['/task/default/assign', 'id' => $task->id]) ?>"
                              class="d-flex flex-wrap gap-2">
                            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                            <select name="assignee_emp_id" class="form-select form-select-sm w-auto" required aria-label="เลือกผู้รับผิดชอบ">
                                <option value="">เลือกผู้รับผิดชอบ</option>
                                <?php foreach (\app\modules\hr\models\Employees::find()->where(['department' => $task->owner_unit_id])->orderBy(['fname' => SORT_ASC])->all() as $member): ?>
                                    <option value="<?= (int) $member->id ?>"><?= Html::encode(trim($member->fname . ' ' . $member->lname)) ?></option>
                                <?php endforeach ?>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-person-check me-1" aria-hidden="true"></i>จ่ายงาน
                            </button>
                        </form>
                    </li>
                <?php endforeach ?>
            </ul>
        </section>
    <?php endif ?>

    <?php foreach ($sections as $section): ?>
        <?php if (!$groups[$section['key']]) {
            continue;
        } ?>
        <section class="card bg-body border shadow-sm mb-3">
            <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                <h2 class="h6 mb-0">
                    <i class="bi <?= $section['icon'] ?> me-1" aria-hidden="true"></i><?= Html::encode($section['label']) ?>
                </h2>
                <span class="badge bg-secondary-subtle text-secondary-emphasis"><?= count($groups[$section['key']]) ?></span>
            </div>
            <ul class="list-group list-group-flush">
                <?php foreach ($groups[$section['key']] as $task): ?>
                    <?= $this->render('_task_row', ['task' => $task]) ?>
                <?php endforeach ?>
            </ul>
        </section>
    <?php endforeach ?>

</div>
