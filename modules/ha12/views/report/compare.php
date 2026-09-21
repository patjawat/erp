<?php

use app\modules\ha12\models\Ha12Assessment;
use app\modules\ha12\models\Ha12Round;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var int $fiscalYear */
/** @var int[] $years */
/** @var Ha12Round[] $rounds */
/** @var Ha12Round|null $roundA */
/** @var Ha12Round|null $roundB */
/** @var app\modules\ha12\models\Ha12Activity[] $activities */
/** @var array<int,Ha12Assessment> $assessA */
/** @var array<int,Ha12Assessment> $assessB */

$this->title = 'HA12-PCT · เทียบรอบ';
$roundOptions = [];
foreach ($rounds as $r) {
    $roundOptions[$r->id] = $r->displayTitle();
}
$maxLevel = static function (?Ha12Assessment $a): ?int {
    if (!$a) {
        return null;
    }
    $lv = $a->levelArray();
    return $lv ? max($lv) : null;
};
$fmt = static function (?Ha12Assessment $a): string {
    if (!$a || !$a->levelArray()) {
        return '<span class="text-body-tertiary">—</span>';
    }
    return implode(' ', array_map(static fn ($l) => '<span class="badge bg-primary-subtle text-primary-emphasis">' . $l . '</span>', $a->levelArray()));
};
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>เทียบผลประเมินสองรอบ · ปีงบ <?= $fiscalYear ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'report']) ?></div>

    <div class="card border shadow-sm mb-3"><div class="card-body">
        <?= Html::beginForm(['compare'], 'get', ['class' => 'row g-2 align-items-end']) ?>
            <?= Html::hiddenInput('fy', $fiscalYear) ?>
            <div class="col-md-5">
                <label class="form-label small fw-semibold mb-1">รอบที่ 1</label>
                <?= Html::dropDownList('a', $roundA->id ?? null, $roundOptions, ['class' => 'form-select', 'prompt' => '— เลือกรอบ —']) ?>
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-semibold mb-1">รอบที่ 2</label>
                <?= Html::dropDownList('b', $roundB->id ?? null, $roundOptions, ['class' => 'form-select', 'prompt' => '— เลือกรอบ —']) ?>
            </div>
            <div class="col-md-2">
                <?= Html::submitButton('เทียบ', ['class' => 'btn btn-primary w-100']) ?>
            </div>
        <?= Html::endForm() ?>
    </div></div>

    <?php if ($roundA && $roundB): ?>
        <div class="card border shadow-sm">
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <thead class="bg-body-tertiary"><tr>
                        <th style="width:44px;" class="text-center">#</th>
                        <th>กิจกรรม</th>
                        <th class="text-center"><?= Html::encode($roundA->displayTitle()) ?></th>
                        <th class="text-center"><?= Html::encode($roundB->displayTitle()) ?></th>
                        <th class="text-center" style="width:120px;">แนวโน้ม</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($activities as $act): ?>
                        <?php
                        $a = $assessA[$act->id] ?? null;
                        $b = $assessB[$act->id] ?? null;
                        $ma = $maxLevel($a);
                        $mb = $maxLevel($b);
                        $trend = '<span class="text-body-tertiary">—</span>';
                        if ($ma !== null && $mb !== null) {
                            if ($mb > $ma) {
                                $trend = '<span class="text-success"><i class="bi bi-arrow-up-right"></i> ดีขึ้น</span>';
                            } elseif ($mb < $ma) {
                                $trend = '<span class="text-danger"><i class="bi bi-arrow-down-right"></i> ลดลง</span>';
                            } else {
                                $trend = '<span class="text-body-secondary"><i class="bi bi-dash"></i> คงที่</span>';
                            }
                        }
                        ?>
                        <tr>
                            <td class="text-center text-body-secondary" style="font-variant-numeric:tabular-nums;"><?= $act->no ?></td>
                            <td class="fw-semibold"><?= Html::encode($act->name) ?></td>
                            <td class="text-center"><?= $fmt($a) ?></td>
                            <td class="text-center"><?= $fmt($b) ?></td>
                            <td class="text-center"><?= $trend ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <p class="text-body-secondary small mt-2">* เทียบจากผลที่เผยแพร่แล้ว โดยใช้ระดับสูงสุดที่ประเมินในแต่ละกิจกรรม</p>
    <?php else: ?>
        <div class="card border shadow-sm"><div class="card-body text-center py-5 text-body-secondary">เลือกสองรอบเพื่อเทียบผลประเมิน</div></div>
    <?php endif; ?>
</div>
