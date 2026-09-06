<?php

use app\modules\qms\models\CycleItem;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\qms\models\Standard $standard */
/** @var int $fiscalYear */
/** @var app\modules\qms\models\Cycle|null $cycle */
/** @var app\modules\qms\models\CycleItem[] $items */
/** @var array<int, app\modules\qms\models\CycleItem[]> $byParent */
/** @var int $reqActive */
/** @var app\modules\qms\models\Cycle|null $prevCycle */
/** @var array $employeeById  id => ชื่อ */

$this->title = 'Checklist: ' . $standard->name;
$sid = (int) $standard->id;
$years = range($fiscalYear + 1, $fiscalYear - 2);

// สรุปความพร้อม — นับเฉพาะข้อย่อย (leaf) ตัดหมวดออก
$countable = 0;
$complete = 0;
foreach ($items as $it) {
    $reqId = (int) $it->requirement_id;
    $isSection = !empty($byParent[$reqId]); // มีลูก = หมวด
    if ($isSection || $it->status === CycleItem::STATUS_NA) {
        continue;
    }
    $countable++;
    if ($it->status === CycleItem::STATUS_COMPLETE) {
        $complete++;
    }
}
$percent = $countable > 0 ? (int) round($complete * 100 / $countable) : 0;

$renderNode = function (int $reqParentId, int $level) use (&$renderNode, $byParent, $employeeById) {
    if (empty($byParent[$reqParentId])) {
        return;
    }
    foreach ($byParent[$reqParentId] as $it) {
        $reqId = (int) $it->requirement_id;
        $isSection = $level === 0 && !empty($byParent[$reqId]);
        $pad = 12 + $level * 22;
        $evCount = count($it->evidences);
        echo '<div class="list-group-item d-flex align-items-center gap-2 ' . ($isSection ? 'bg-body-tertiary' : '') . '" style="padding-left:' . $pad . 'px;">';
        echo '<div class="flex-grow-1 min-w-0">';
        if ($it->requirement && $it->requirement->code) {
            echo '<span class="badge text-bg-light border me-2">' . Html::encode($it->requirement->code) . '</span>';
        }
        echo '<span class="' . ($isSection ? 'fw-semibold' : '') . '">' . Html::encode($it->title_snapshot) . '</span>';
        if (!$isSection) {
            echo ' <span class="badge rounded-pill text-bg-' . $it->statusTone() . ' ms-1">' . $it->statusLabel() . '</span>';
            if ($evCount > 0) {
                echo ' <span class="badge rounded-pill text-bg-light border ms-1"><i class="bi bi-paperclip"></i> ' . $evCount . '</span>';
            }
            if ($it->due_date) {
                echo ' <span class="small text-body-secondary ms-1"><i class="bi bi-calendar-event"></i> ' . Yii::$app->formatter->asDate($it->due_date) . '</span>';
            }
            if ($it->assignee_emp_id && isset($employeeById[(int) $it->assignee_emp_id])) {
                echo ' <span class="small text-body-secondary ms-1"><i class="bi bi-person"></i> ' . Html::encode($employeeById[(int) $it->assignee_emp_id]) . '</span>';
            }
        }
        echo '</div>';
        if (!$isSection) {
            echo Html::a('<i class="bi bi-folder2-open me-1"></i>หลักฐาน', ['item', 'id' => $it->id], ['class' => 'btn btn-sm btn-outline-primary']);
        }
        echo '</div>';
        if (!empty($byParent[$reqId])) {
            $renderNode($reqId, $level + 1);
        }
    }
};
?>
<?php $this->beginBlock('page-title'); ?>Checklist ปี <?= $fiscalYear ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?><?= Html::encode($standard->name) ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <?= Html::a('<i class="bi bi-arrow-left me-1"></i>ทะเบียนมาตรฐาน', ['standards', 'fy' => $fiscalYear], ['class' => 'btn btn-sm btn-outline-secondary mb-2']) ?>
            <h1 class="h4 fw-semibold mb-0">
                <span class="badge me-1" style="background: <?= Html::encode($standard->color ?: '#1a508e') ?>1a; color: <?= Html::encode($standard->color ?: '#1a508e') ?>;"><?= Html::encode($standard->short_name ?: $standard->code) ?></span>
                Checklist
            </h1>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <?= Html::beginForm(['checklist', 'standard_id' => $sid], 'get', ['class' => 'd-flex align-items-center gap-2']) ?>
                <?= Html::hiddenInput('standard_id', $sid) ?>
                <label class="small text-body-secondary mb-0">ปีงบ</label>
                <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), ['class' => 'form-select form-select-sm', 'style' => 'width:auto', 'onchange' => 'this.form.submit()']) ?>
            <?= Html::endForm() ?>
            <?php if ($cycle): ?>
                <?= Html::beginForm(['cycle-sync', 'cycle_id' => $cycle->id], 'post') ?>
                    <?= Html::submitButton('<i class="bi bi-arrow-repeat me-1"></i>ซิงก์ข้อกำหนดใหม่', ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                <?= Html::endForm() ?>
                <?php if ($prevCycle): ?>
                    <?= Html::beginForm(['cycle-copy', 'standard_id' => $sid, 'fy' => $fiscalYear], 'post') ?>
                        <?= Html::submitButton('<i class="bi bi-copy me-1"></i>คัดลอกผู้รับผิดชอบจากปี ' . $prevCycle->fiscal_year, ['class' => 'btn btn-sm btn-outline-secondary', 'data' => ['confirm' => 'คัดลอกผู้รับผิดชอบจากปี ' . $prevCycle->fiscal_year . ' มาเติมข้อที่ยังว่าง?']]) ?>
                    <?= Html::endForm() ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="mb-3"><?= $this->render('@app/modules/qms/menu', ['active' => 'standards']) ?></div>

    <?php if (!$cycle): ?>
        <div class="card border shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-plus fs-1 text-primary"></i>
                <h2 class="h5 fw-semibold mt-2">ยังไม่ได้เปิดรอบปี <?= $fiscalYear ?></h2>
                <p class="text-body-secondary">
                    มาตรฐานนี้มีข้อกำหนดที่ใช้งาน <strong><?= (int) $reqActive ?></strong> ข้อ<br>
                    เปิดรอบเพื่อสร้าง checklist ของปีนี้ (คัดลอกข้อกำหนดทั้งหมดมาให้ติดตาม)
                </p>
                <?php if ($reqActive > 0): ?>
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <?= Html::beginForm(['cycle-open', 'standard_id' => $sid, 'fy' => $fiscalYear], 'post') ?>
                            <?= Html::submitButton('<i class="bi bi-calendar-check me-1"></i>เปิดรอบปี ' . $fiscalYear, ['class' => 'btn btn-primary']) ?>
                        <?= Html::endForm() ?>
                        <?php if ($prevCycle): ?>
                            <?= Html::beginForm(['cycle-copy', 'standard_id' => $sid, 'fy' => $fiscalYear], 'post') ?>
                                <?= Html::submitButton('<i class="bi bi-copy me-1"></i>เปิดรอบ + คัดลอกผู้รับผิดชอบจากปี ' . $prevCycle->fiscal_year, ['class' => 'btn btn-outline-primary']) ?>
                            <?= Html::endForm() ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?= Html::a('ไปเพิ่มข้อกำหนดก่อน', ['requirements', 'standard_id' => $sid], ['class' => 'btn btn-outline-primary']) ?>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="card border shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">ความพร้อมปี <?= $fiscalYear ?></span>
                    <span class="fw-bold"><?= $percent ?>% <span class="text-body-secondary small">(<?= $complete ?>/<?= $countable ?>)</span></span>
                </div>
                <div class="progress" role="progressbar" style="height: 12px;">
                    <div class="progress-bar bg-<?= $percent >= 90 ? 'success' : ($percent >= 50 ? 'primary' : 'warning') ?>" style="width: <?= $percent ?>%"></div>
                </div>
            </div>
        </div>

        <div class="card border shadow-sm">
            <div class="list-group list-group-flush">
                <?php $renderNode(0, 0); ?>
            </div>
        </div>
    <?php endif; ?>
</div>
