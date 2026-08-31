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
    <div class="mb-3">
        <a href="<?= yii\helpers\Url::to(['/task/default/create', 'date' => $date]) ?>"
           class="task-open-edit btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>เพิ่มงานวันนี้
        </a>
    </div>

    <?php if (!$tasks): ?>
        <p class="text-body-secondary mb-0">ไม่มีงานค้างในวันนี้</p>
    <?php else: ?>
        <?php if ($carried > 0): ?>
            <div class="alert alert-warning py-2 small mb-2" role="status">
                <i class="bi bi-arrow-return-right me-1" aria-hidden="true"></i>
                มี <?= (int) $carried ?> งานที่ทบมาจากวันก่อน เพราะยังไม่ได้ปิด
            </div>
        <?php endif ?>

        <p class="text-body-secondary small mb-2">คลิกที่งานเพื่อแก้ไข หรือกดวงกลมเพื่อปิดงาน</p>
        <div class="d-flex flex-column">
            <?php foreach ($tasks as $task): ?>
                <?= $this->render('_task_item', ['task' => $task, 'inModal' => true]) ?>
            <?php endforeach ?>
        </div>
    <?php endif ?>
</div>
