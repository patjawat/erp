<?php

use app\components\ThaiDate;
use yii\helpers\Html;

/**
 * แผงรายการงานด้านขวา — โหลดใหม่ผ่าน AJAX เมื่อเปลี่ยนตัวกรองหรือคลิกวันในปฏิทิน
 *
 * @var yii\web\View $this
 * @var array $lists  ['open' => Task[], 'done' => Task[]]
 * @var string|null $date วันที่ที่กำลังกรองอยู่
 */
$open = $lists['open'];
$done = $lists['done'];
?>
<?php if ($date !== null): ?>
    <div class="d-flex align-items-center justify-content-between gap-2 mb-2 px-1">
        <span class="small fw-semibold">
            <i class="bi bi-funnel me-1" aria-hidden="true"></i>
            <?= Html::encode(ThaiDate::toThaiDate($date, false)) ?>
        </span>
        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" id="task-clear-date">ล้างตัวกรอง</button>
    </div>
<?php endif ?>

<div class="task-open-list">
    <?php if (!$open): ?>
        <p class="text-body-secondary small mb-0 px-1 py-3">
            <?= $date !== null ? 'ไม่มีงานในวันนี้' : 'ไม่มีงานค้าง' ?>
        </p>
    <?php endif ?>

    <?php foreach ($open as $task): ?>
        <?= $this->render('_task_item', ['task' => $task]) ?>
    <?php endforeach ?>
</div>

<?php if ($done): ?>
    <div class="border-top mt-2 pt-2">
        <button class="btn btn-sm btn-link text-decoration-none p-0 d-flex align-items-center gap-1 collapsed"
                type="button" data-bs-toggle="collapse" data-bs-target="#taskDoneList"
                aria-expanded="false" aria-controls="taskDoneList">
            <i class="bi bi-chevron-right task-chevron" aria-hidden="true"></i>
            เสร็จสมบูรณ์ (<?= count($done) ?>)
        </button>
        <div class="collapse" id="taskDoneList">
            <div class="pt-1">
                <?php foreach ($done as $task): ?>
                    <?= $this->render('_task_item', ['task' => $task, 'done' => true]) ?>
                <?php endforeach ?>
            </div>
            <p class="text-body-tertiary small mb-0 mt-1">แสดงงานที่ปิดในช่วง 30 วันที่ผ่านมา</p>
        </div>
    </div>
<?php endif ?>
