<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $active */

$active = $active ?? '';
?>
<nav class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-end gap-1" aria-label="เมนูผู้ช่วย AI">
    <?= Html::a(
        '<i class="bi bi-chat-dots me-1" aria-hidden="true"></i>สนทนา',
        ['/ai/chat/index'],
        [
            'class' => 'btn btn-sm px-3 ' . ($active === 'chat' ? 'btn-primary' : 'btn-light'),
            'aria-current' => $active === 'chat' ? 'page' : null,
        ]
    ) ?>
    <?= Html::a(
        '<i class="bi bi-clock-history me-1" aria-hidden="true"></i>ประวัติ',
        ['/ai/chat/index', '#' => 'ai-conversations'],
        ['class' => 'btn btn-sm btn-light px-3']
    ) ?>
    <?= Html::button(
        '<i class="bi bi-plug me-1" aria-hidden="true"></i>การเชื่อมต่อ',
        [
            'type' => 'button',
            'class' => 'btn btn-sm btn-light px-3',
            'data-bs-toggle' => 'offcanvas',
            'data-bs-target' => '#openrouter-connection',
            'aria-controls' => 'openrouter-connection',
        ]
    ) ?>
    <?= Html::a(
        '<i class="bi bi-plus-lg me-1" aria-hidden="true"></i>เริ่มบทสนทนาใหม่',
        ['/ai/chat/index'],
        ['class' => 'btn btn-sm btn-success px-3']
    ) ?>
</nav>
