<?php

use yii\helpers\Html;
use app\modules\hr\models\CompetencyLevel;
use app\modules\hr\models\CompetencyYear;

/** @var yii\web\View $this */
/** @var CompetencyYear $model */
/** @var CompetencyLevel[] $levels */

echo $this->render('_styles');
?>
<div class="mb-3">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="cp-badge cp-badge--<?= Html::encode($model->status) ?>"><?= Html::encode($model->getStatusLabel()) ?></span>
        <span class="text-muted small">ปีงบประมาณ <?= (int) $model->fiscal_year ?> · ลำดับที่ <?= (int) $model->sort_order ?></span>
    </div>
    <?php if ($model->definition): ?>
        <p class="mt-2 mb-0" style="color:#475467;line-height:1.65">
            <strong>คำจำกัดความ:</strong> <?= Html::encode($model->definition) ?>
        </p>
    <?php endif ?>
    <?php if ($model->note): ?>
        <div class="cp-note mt-2"><i class="bi bi-exclamation-triangle"></i> <?= Html::encode($model->note) ?></div>
    <?php endif ?>
</div>

<?php if ($levels === []): ?>
    <div class="cp-empty">
        <i class="bi bi-inbox"></i>
        <p>ยังไม่ได้กำหนดระดับสมรรถนะของรายการนี้</p>
    </div>
<?php else: ?>
    <?php foreach ($levels as $level): ?>
        <div class="cp-level">
            <div class="cp-level__head">
                <span class="cp-level__no">ระดับที่ <?= (int) $level->level_no ?></span>
                <span class="cp-level__desc"><?= Html::encode((string) $level->description) ?></span>
            </div>
            <?php if ($level->indicators === []): ?>
                <table><tr><td class="text-muted">ยังไม่มีข้อพฤติกรรมบ่งชี้</td></tr></table>
            <?php else: ?>
                <table>
                    <?php foreach ($level->indicators as $indicator): ?>
                        <?php $scale = $indicator->scale ?>
                        <tr>
                            <td class="cp-ind__no"><?= Html::encode((string) $indicator->indicator_no) ?></td>
                            <td><?= Html::encode($indicator->text) ?></td>
                            <td class="cp-ind__scale">
                                <?php if ($scale): ?>
                                    <span class="cp-scale"><?= Html::encode($scale->name) ?></span>
                                    <div class="cp-scale__opts">
                                        <?= Html::encode(implode(' · ', array_map(
                                            static fn ($option): string => $option->score . '=' . $option->label,
                                            $scale->options
                                        ))) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="cp-scale cp-scale--default">มาตรฐาน 5 ระดับ</span>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </table>
            <?php endif ?>
        </div>
    <?php endforeach ?>
<?php endif ?>

<div class="d-flex justify-content-end mt-3">
    <?= Html::button('ปิด', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?>
</div>
