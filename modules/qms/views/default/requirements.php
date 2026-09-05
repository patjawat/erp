<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\qms\models\Standard $standard */
/** @var array<int, app\modules\qms\models\Requirement[]> $byParent  parent_id => rows */
/** @var int $total */

$this->title = 'ข้อกำหนด: ' . $standard->name;
$sid = (int) $standard->id;

/** render แบบ recursive: หมวด (parent) → ข้อย่อย */
$renderNode = function (int $parentId, int $level) use (&$renderNode, $byParent, $sid) {
    if (empty($byParent[$parentId])) {
        return;
    }
    foreach ($byParent[$parentId] as $r) {
        $hasChildren = !empty($byParent[(int) $r->id]);
        $isSection = $level === 0;
        $pad = 12 + $level * 24;
        echo '<div class="list-group-item d-flex align-items-center gap-2 ' . ($isSection ? 'bg-body-tertiary' : '') . '" style="padding-left:' . $pad . 'px;">';
        echo '<div class="flex-grow-1 min-w-0">';
        if ($r->code) {
            echo '<span class="badge text-bg-light border me-2">' . Html::encode($r->code) . '</span>';
        }
        echo '<span class="' . ($isSection ? 'fw-semibold' : '') . '">' . Html::encode($r->title) . '</span>';
        if ($r->evidence_hint) {
            echo ' <span class="badge rounded-pill text-bg-info-subtle text-info-emphasis ms-1"><i class="bi bi-paperclip"></i> ' . Html::encode($r->evidence_hint) . '</span>';
        }
        if (!$r->is_active) {
            echo ' <span class="badge text-bg-secondary ms-1">ปิดใช้</span>';
        }
        echo '</div>';
        // ปุ่ม
        echo '<div class="d-flex gap-1">';
        echo Html::a('<i class="bi bi-plus-lg"></i>', ['requirement-form', 'standard_id' => $sid, 'parent' => $r->id], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'เพิ่มข้อย่อย']);
        echo Html::a('<i class="bi bi-pencil"></i>', ['requirement-form', 'standard_id' => $sid, 'id' => $r->id], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'แก้ไข']);
        echo Html::a('<i class="bi bi-trash"></i>', ['requirement-delete', 'id' => $r->id], [
            'class' => 'btn btn-sm btn-outline-danger', 'title' => 'ลบ',
            'data' => ['method' => 'post', 'confirm' => 'ยืนยันลบข้อกำหนดนี้?'],
        ]);
        echo '</div>';
        echo '</div>';
        if ($hasChildren) {
            $renderNode((int) $r->id, $level + 1);
        }
    }
};
?>
<?php $this->beginBlock('page-title'); ?>ข้อกำหนด<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?><?= Html::encode($standard->name) ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <?= Html::a('<i class="bi bi-arrow-left me-1"></i>ทะเบียนมาตรฐาน', ['standards'], ['class' => 'btn btn-sm btn-outline-secondary mb-2']) ?>
            <h1 class="h4 fw-semibold mb-0">
                <span class="badge me-1" style="background: <?= Html::encode($standard->color ?: '#1a508e') ?>1a; color: <?= Html::encode($standard->color ?: '#1a508e') ?>;"><?= Html::encode($standard->short_name ?: $standard->code) ?></span>
                ข้อกำหนด <span class="text-body-secondary fs-6">(<?= (int) $total ?> ข้อ)</span>
            </h1>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-folder-plus me-1"></i>เพิ่มหมวด', ['requirement-form', 'standard_id' => $sid], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="bi bi-plus-lg me-1"></i>เพิ่มข้อกำหนด', ['requirement-form', 'standard_id' => $sid], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <div class="mb-3"><?= $this->render('@app/modules/qms/menu', ['active' => 'standards']) ?></div>

    <?php if ($total === 0): ?>
        <div class="card border shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-list-check fs-1 text-body-secondary"></i>
                <h2 class="h5 fw-semibold mt-2">ยังไม่มีข้อกำหนด</h2>
                <p class="text-body-secondary">เริ่มด้วยการเพิ่ม “หมวด” เช่น การนำองค์กร แล้วเพิ่มข้อย่อยภายใน</p>
                <?= Html::a('<i class="bi bi-folder-plus me-1"></i>เพิ่มหมวด', ['requirement-form', 'standard_id' => $sid], ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    <?php else: ?>
        <div class="card border shadow-sm">
            <div class="list-group list-group-flush">
                <?php $renderNode(0, 0); ?>
            </div>
        </div>
        <p class="small text-body-secondary mt-2"><i class="bi bi-info-circle me-1"></i>ข้อกำหนดเหล่านี้เป็นแม่แบบใช้ซ้ำทุกปี — เปิดรอบปีเพื่อสร้าง checklist และวางหลักฐาน</p>
    <?php endif; ?>
</div>
