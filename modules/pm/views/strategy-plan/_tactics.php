<?php

use yii\helpers\Html;
use app\components\RichText;

/**
 * กลยุทธ์ของตัวชี้วัดหนึ่ง พร้อมมาตรการและโครงการที่อยู่ใต้กลยุทธ์
 * ใช้โครงแถวเดียวกับ view.php ผ่าน closure $stRow
 *
 * @var app\modules\pm\models\StrategyIndicator $owner ตัวชี้วัดหลักหรือตัวชี้วัดรอง
 * @var bool $editable
 * @var Closure $stRow
 */

if (!$owner->tactics) {
    return;
}
?>
<?php foreach ($owner->tactics as $tactic): ?>
    <?php
    $leaves = count($tactic->measures) + count($tactic->works);
    $nodeId = 'tac-' . $tactic->id;
    ?>
    <div class="sh-node" data-node="<?= $nodeId ?>">
        <?= $stRow([
            'level' => 'tactic',
            'icon' => 'bi-diagram-3',
            'type' => 'กลยุทธ์',
            'code' => $tactic->code,
            'name' => $tactic->name,
            'count' => $leaves ? $leaves . ' รายการ' : null,
            'node' => $nodeId,
            'collapsible' => (bool) $leaves,
            'add' => $editable ? [
                ['label' => 'มาตรการ', 'url' => ['/pm/strategy-catalog/create', 'type' => 'measure', 'parentId' => $tactic->id]],
                ['label' => 'โครงการ', 'url' => ['/pm/strategy-structure/create', 'type' => 'project', 'parentId' => $tactic->id]],
                ['label' => 'แผนงาน/กิจกรรม', 'url' => ['/pm/strategy-structure/create', 'type' => 'activity', 'parentId' => $tactic->id]],
            ] : [],
            'addTitle' => 'เพิ่มโครงการ/แผนงาน',
            'menu' => $editable ? [
                ['label' => 'แก้ไข', 'url' => ['/pm/strategy-structure/update', 'type' => 'tactic', 'id' => $tactic->id]],
                ['label' => 'ลบ', 'url' => ['/pm/strategy-structure/delete', 'type' => 'tactic', 'id' => $tactic->id], 'options' => ['class' => 'text-danger', 'data-method' => 'post', 'data-confirm' => 'ลบกลยุทธ์นี้? มาตรการ โครงการ และกิจกรรมที่ผูกอยู่จะไม่ถูกลบ แต่จะไม่สังกัดกลยุทธ์ใด']],
            ] : [],
        ]) ?>

        <?php if ($leaves): ?>
            <div class="sh-kids" id="kids-<?= $nodeId ?>">
                <?php foreach ($tactic->measures as $measure): ?>
                    <div class="sh-node" data-year="<?= (int) $measure->fiscal_year ?>">
                        <?= $stRow([
                            'level' => 'measure',
                            'icon' => 'bi-check2-square',
                            'type' => 'มาตรการ',
                            'code' => 'ปี ' . (int) $measure->fiscal_year,
                            'name' => RichText::plain($measure->name, 200),
                        ]) ?>
                    </div>
                <?php endforeach; ?>

                <?php foreach ($tactic->works as $work): ?>
                    <?php $isActivity = $work->isActivity(); ?>
                    <div class="sh-node" data-year="<?= (int) $work->thai_year ?>">
                        <?= $stRow([
                            'level' => $isActivity ? 'activity' : 'project',
                            'icon' => $isActivity ? 'bi-list-task' : 'bi-folder-fill',
                            'type' => $work->workTypeLabel(),
                            'code' => $work->code,
                            'name' => $work->name,
                            'nameHtml' => Html::a(Html::encode($work->name), ['/pm/projects/view', 'id' => $work->id], ['class' => 'sh-link'])
                                . '<span class="sh-meta">ปี ' . (int) $work->thai_year
                                . ($work->budget_total > 0 ? ' · ' . Yii::$app->formatter->asDecimal($work->budget_total, 2) . ' บาท' : '')
                                . '</span>',
                            'menu' => $editable ? [
                                ['label' => 'แก้ไขชื่อ', 'url' => ['/pm/strategy-structure/update', 'type' => $isActivity ? 'activity' : 'project', 'id' => $work->id]],
                            ] : [],
                        ]) ?>
                    </div>
                <?php endforeach; ?>

                <div class="sh-year-empty">ไม่มีมาตรการหรือโครงการในปีที่เลือก</div>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
