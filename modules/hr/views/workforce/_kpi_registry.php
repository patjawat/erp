<?php

use app\components\widgets\DataSummaryWidget;
use app\modules\hr\models\Organization;
use app\modules\kpi\models\KpiCycle;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $kpiDataProvider */
/** @var array<int, KpiCycle> $kpiByEmployee */
/** @var array<int, int> $kpiItemCounts */
/** @var Organization[] $departments */
/** @var int $currentFy */
/** @var bool $showAll */

$showAll = $showAll ?? false;

$models = $kpiDataProvider->getModels();
$statusLabels = KpiCycle::statusLabels();
$statusClasses = [
    KpiCycle::STATUS_DRAFT => 'is-warning',
    KpiCycle::STATUS_PENDING => 'is-warning',
    KpiCycle::STATUS_ACTIVE => 'is-success',
    KpiCycle::STATUS_CLOSED => 'is-neutral',
];

$keyword = trim((string) Yii::$app->request->get('kpi_q', ''));
$depId = (int) Yii::$app->request->get('kpi_dep');

// จัดกลุ่ม dropdown หน่วยงานตามลำดับชั้น (เว้นวรรคหน้าตาม lvl)
$depOptions = [];
foreach ($departments as $dep) {
    $prefix = str_repeat('— ', max(0, (int) $dep->lvl - 1));
    $depOptions[$dep->id] = $prefix . $dep->name;
}
?>
<section class="jd-registry" aria-labelledby="kpi-registry-title">
    <div class="jd-registry__head">
        <div>
            <h2 id="kpi-registry-title">ภาพรวม KPI รายบุคคล · ปีงบประมาณ <?= (int) $currentFy ?></h2>
            <p>ค้นหาและเข้าถึง KPI รายบุคคลตามหน่วยงาน บันทึกผล และสรุปคะแนนได้จากรายการนี้</p>
        </div>
    </div>

    <?= Html::beginForm(['/hr/workforce/index'], 'get', ['class' => 'jd-registry__search']) ?>
    <?= Html::hiddenInput('section', 'kpi') ?>
    <div class="jd-registry__search-control">
        <i data-lucide="search" aria-hidden="true"></i>
        <?= Html::textInput('kpi_q', $keyword, [
            'class' => 'form-control',
            'placeholder' => 'ค้นหาชื่อบุคลากรหรือตำแหน่ง',
        ]) ?>
    </div>
    <?= Html::dropDownList('kpi_dep', $depId ?: null, $depOptions, [
        'class' => 'form-select',
        'prompt' => 'ทุกหน่วยงาน',
        'style' => 'max-width:260px',
    ]) ?>
    <label class="d-inline-flex align-items-center gap-1 small text-nowrap mb-0">
        <?= Html::checkbox('show_all', $showAll, ['value' => 1, 'class' => 'form-check-input mt-0']) ?>
        แสดงทั้งหมด (รวมผู้ที่ไม่ได้ปฏิบัติงาน)
    </label>
    <?= Html::submitButton('ค้นหา', ['class' => 'btn btn-primary']) ?>
    <?php if ($keyword !== '' || $depId > 0 || $showAll): ?>
        <?= Html::a('ล้าง', ['/hr/workforce/index', 'section' => 'kpi'], ['class' => 'btn btn-outline-secondary']) ?>
    <?php endif; ?>
    <?= Html::endForm() ?>

    <?php if ($models === []): ?>
        <div class="jd-registry__empty">
            <strong>ไม่พบบุคลากรที่ตรงกับเงื่อนไข</strong>
            <span>ลองปรับคำค้นหาหรือเลือกหน่วยงานอื่น</span>
        </div>
    <?php else: ?>
        <div class="d-none d-lg-block">
            <table class="jd-registry__table">
                <thead>
                <tr>
                    <th>บุคลากร</th>
                    <th>หน่วยงาน</th>
                    <th>ตำแหน่ง</th>
                    <th>ชุด KPI ปีล่าสุด</th>
                    <th><span class="visually-hidden">ดำเนินการ</span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($models as $employee): ?>
                    <?php
                    $cycle = $kpiByEmployee[(int) $employee->id] ?? null;
                    $count = $cycle ? ($kpiItemCounts[(int) $cycle->id] ?? 0) : 0;
                    ?>
                    <tr>
                        <td>
                            <strong><?= Html::encode($employee->fullname) ?></strong>
                            <small>รหัสบุคลากร <?= (int) $employee->id ?></small>
                        </td>
                        <td><?= Html::encode($employee->departmentName()) ?></td>
                        <td><?= Html::encode(strip_tags((string) $employee->positionName())) ?></td>
                        <td>
                            <?php if ($cycle): ?>
                                <span class="jd-status <?= $statusClasses[$cycle->status] ?? 'is-neutral' ?>"><?= Html::encode($statusLabels[$cycle->status] ?? $cycle->status) ?></span>
                                <small><?= (int) $count ?> ตัว · ปี <?= (int) $cycle->fiscal_year ?></small>
                            <?php else: ?>
                                <span class="jd-status is-danger">ยังไม่มีชุด KPI</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?= Html::a($cycle ? 'เปิด KPI' : 'จัดทำ KPI', ['/kpi/manage/view', 'emp_id' => $employee->id], ['class' => 'btn btn-sm btn-outline-primary text-nowrap']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <ul class="jd-registry__mobile d-lg-none" role="list">
            <?php foreach ($models as $employee): ?>
                <?php
                $cycle = $kpiByEmployee[(int) $employee->id] ?? null;
                $count = $cycle ? ($kpiItemCounts[(int) $cycle->id] ?? 0) : 0;
                ?>
                <li>
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div><strong><?= Html::encode($employee->fullname) ?></strong><small><?= Html::encode(strip_tags((string) $employee->positionName())) ?></small></div>
                        <span class="jd-status <?= $cycle ? ($statusClasses[$cycle->status] ?? 'is-neutral') : 'is-danger' ?>"><?= Html::encode($cycle ? ($statusLabels[$cycle->status] ?? $cycle->status) : 'ยังไม่มีชุด') ?></span>
                    </div>
                    <dl>
                        <div><dt>หน่วยงาน</dt><dd><?= Html::encode($employee->departmentName()) ?></dd></div>
                        <div><dt>KPI</dt><dd><?= $cycle ? ((int) $count . ' ตัว · ปี ' . (int) $cycle->fiscal_year) : '—' ?></dd></div>
                    </dl>
                    <div class="d-flex gap-2">
                        <?= Html::a($cycle ? 'เปิด KPI' : 'จัดทำ KPI', ['/kpi/manage/view', 'emp_id' => $employee->id], ['class' => 'btn btn-sm btn-outline-primary flex-fill']) ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div class="jd-registry__footer">
        <?= DataSummaryWidget::widget(['dataProvider' => $kpiDataProvider]) ?>
    </div>
</section>
