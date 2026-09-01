<?php

/**
 * รายการงานของวันหนึ่ง เปิดจากชิปตัวเลขบนปฏิทิน
 *
 * ถ้าเป็นวันนี้ จะรวมงานที่ยังไม่ปิดจากวันก่อน ๆ มาด้วย
 * เพราะงานที่ค้างไม่ควรหายไปกับวันที่ผ่านไปแล้ว
 *
 * คลิกงานในนี้แล้วฟอร์มแก้ไขจะมาแทนที่เนื้อหาใน popup เดิม
 * (ไม่ซ้อน modal เพราะ backdrop จะทับกันจนกดปิดยาก)
 *
 * @var yii\web\View $this
 * @var app\modules\task\models\Task[] $tasks
 * @var string $date
 * @var int $carried จำนวนงานที่ทบมาจากวันก่อน
 */
$carried = $carried ?? 0;
?>
<div class="task-day-popup" data-date="<?= $date ?>">

    <?php // ใช้ task-open-edit ไม่ใช่ open-modal เพราะอยู่ใน modal อยู่แล้ว ต้องสลับเนื้อหาไม่ใช่เปิดซ้อน ?>
    <div class="d-flex align-items-start justify-content-between gap-3 mb-2">
        <div>
            <h2 class="h6 mb-1">งานที่รอดำเนินการ</h2>
            <p class="small text-body-secondary mb-0">
                <?= count($tasks) ?> งาน
                <?php if ($carried > 0): ?>
                    · <span class="text-danger-emphasis"><?= (int) $carried ?> งานทบจากวันก่อน</span>
                <?php endif ?>
            </p>
        </div>
        <a href="<?= yii\helpers\Url::to(['/task/default/create', 'date' => $date]) ?>"
           class="task-open-edit btn btn-sm btn-outline-primary flex-shrink-0">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>เพิ่มงาน
        </a>
    </div>

    <?php if (!$tasks): ?>
        <p class="text-body-secondary mb-0">ไม่มีงานค้างในวันนี้</p>
    <?php else: ?>
        <div class="task-day-list d-flex flex-column border-top mt-3">
            <?php foreach ($tasks as $task): ?>
                <?= $this->render('_task_item', ['task' => $task, 'inModal' => true]) ?>
            <?php endforeach ?>
        </div>
    <?php endif ?>
</div>
