<?php

use yii\helpers\Html;
use app\components\RichText;

/**
 * กลยุทธ์ของตัวชี้วัดหนึ่ง พร้อมมาตรการและโครงการที่อยู่ใต้กลยุทธ์
 *
 * @var app\modules\pm\models\StrategyIndicator $owner ตัวชี้วัดหลักหรือตัวชี้วัดรอง
 * @var bool $editable
 */

if (!$owner->tactics) {
    return;
}
?>
<ul class="mt-1 mb-0 list-unstyled ps-3 border-start">
<?php foreach ($owner->tactics as $tactic): ?>
    <li class="mb-2">
        <span class="badge bg-secondary-subtle text-secondary me-1">กลยุทธ์</span>
        <?php if ($tactic->code): ?><span class="text-muted"><?= Html::encode($tactic->code) ?></span> <?php endif; ?>
        <?= Html::encode($tactic->name) ?>
        <?php if ($editable): ?>
            <span class="d-inline-flex flex-wrap gap-2 ms-2">
                <?= Html::a('แก้ไข', ['/pm/strategy-structure/update', 'type' => 'tactic', 'id' => $tactic->id], ['class' => 'small']) ?>
                <?= Html::a('+ มาตรการ', ['/pm/strategy-catalog/create', 'type' => 'measure', 'parentId' => $tactic->id], ['class' => 'small']) ?>
                <?= Html::a('+ โครงการ', ['/pm/strategy-structure/create', 'type' => 'project', 'parentId' => $tactic->id], ['class' => 'small']) ?>
                <?= Html::a('ลบ', ['/pm/strategy-structure/delete', 'type' => 'tactic', 'id' => $tactic->id], ['class' => 'small text-danger', 'data-method' => 'post', 'data-confirm' => 'ลบกลยุทธ์นี้? มาตรการและโครงการที่ผูกอยู่จะไม่ถูกลบ แต่จะไม่สังกัดกลยุทธ์ใด']) ?>
            </span>
        <?php endif; ?>

        <?php if ($tactic->measures): ?>
            <ul class="small text-muted mt-1 mb-0 ps-3">
            <?php foreach ($tactic->measures as $measure): ?>
                <li>มาตรการ ปี <?= (int) $measure->fiscal_year ?> · <?= Html::encode(RichText::plain($measure->name, 120)) ?></li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($tactic->projects): ?>
            <ul class="small mt-1 mb-0 ps-3">
            <?php foreach ($tactic->projects as $project): ?>
                <li>
                    <span class="badge bg-success-subtle text-success me-1">โครงการ</span>
                    <span class="text-muted"><?= Html::encode($project->code) ?></span>
                    <?= Html::a(Html::encode($project->name), ['/pm/projects/view', 'id' => $project->id], ['class' => 'text-decoration-none']) ?>
                    <span class="text-muted">· ปี <?= (int) $project->thai_year ?></span>
                    <?php if ($editable): ?><?= Html::a('แก้ไขชื่อ', ['/pm/strategy-structure/update', 'type' => 'project', 'id' => $project->id], ['class' => 'small ms-2']) ?><?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </li>
<?php endforeach; ?>
</ul>
