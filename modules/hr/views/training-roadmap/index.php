<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use app\modules\hr\models\EmployeeTrainingPlan;

$this->title = 'TRM Management';
echo $this->render('_styles');
echo $this->render('@app/modules/hr/views/workforce/_styles');
$this->beginBlock('page-title'); echo Html::encode($this->title); $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/hr/menu', ['active' => 'training-roadmap']); $this->endBlock();

$unassigned = $unassignedProvider->getModels();
$plans = $planProvider->getModels();
?>
<div class="trm-shell" id="roadmap-assignments">
    <?= $this->render('@app/modules/hr/views/workforce/_menu', ['active' => 'trm']) ?>

    <header class="trm-page-head">
        <div>
            <h1>Training Roadmap</h1>
            <p>มอบหมายและติดตามเส้นทางฝึกของบุคลากรใหม่ โดย HR ดูภาพรวมและหัวหน้าปรับแผนรายบุคคล</p>
        </div>
        <?= Html::a('<i class="bi bi-person-plus me-1"></i> มอบหมายรายบุคคล', ['assign'], [
            'class' => 'btn btn-primary open-modal',
            'data-size' => 'modal-lg',
        ]) ?>
    </header>

    <section class="trm-ops-metrics" aria-label="ตัวชี้วัด Training Roadmap">
        <div class="trm-ops-metric"><span>บุคลากรใหม่ 90 วัน</span><strong><?= number_format($metrics['new_hires']) ?></strong><small>ตั้งแต่ <?= Html::encode($newHireSince) ?></small></div>
        <div class="trm-ops-metric is-warning"><span>ยังไม่ได้รับ TRM</span><strong><?= number_format($metrics['unassigned']) ?></strong><small>ควรตรวจสอบและมอบหมาย</small></div>
        <div class="trm-ops-metric is-progress"><span>กำลังดำเนินการ</span><strong><?= number_format($metrics['in_progress']) ?></strong><small>รวมรอเริ่มและรอประเมิน</small></div>
        <div class="trm-ops-metric is-danger"><span>เกินกำหนด</span><strong><?= number_format($metrics['overdue']) ?></strong><small>ยังไม่ปิดแผน</small></div>
    </section>

    <section class="trm-card mt-3">
        <div class="trm-section-head">
            <div>
                <h2>บุคลากรใหม่ที่ยังไม่ได้รับ TRM</h2>
                <div class="trm-meta mt-1">เลือกได้หลายคนเมื่อใช้ Roadmap และวันเริ่มต้นเดียวกัน</div>
            </div>
            <?= Html::a('จัดการ TRM Template', ['templates'], ['class' => 'btn btn-sm btn-outline-secondary', 'data-pjax' => '0']) ?>
        </div>

        <?php if ($unassigned): ?>
            <?= Html::beginForm(['bulk-assign'], 'post', ['class' => 'trm-bulk-form']) ?>
            <div class="trm-bulk-bar">
                <label class="trm-check-all"><input type="checkbox" id="trm-select-all"> เลือกทั้งหมด</label>
                <?= Html::dropDownList('roadmap_id', null, $roadmapItems, [
                    'class' => 'form-select',
                    'prompt' => $roadmapItems ? 'เลือก TRM สำหรับผู้ที่เลือก' : 'ยังไม่มี TRM Template',
                    'required' => true,
                    'disabled' => !$roadmapItems,
                    'aria-label' => 'เลือก Training Roadmap',
                ]) ?>
                <?= Html::input('date', 'start_date', date('Y-m-d'), ['class' => 'form-control', 'required' => true, 'aria-label' => 'วันที่เริ่มแผน']) ?>
                <?= Html::submitButton('<i class="bi bi-send-check me-1"></i> มอบหมายที่เลือก', [
                    'class' => 'btn btn-primary',
                    'disabled' => !$roadmapItems,
                ]) ?>
            </div>
            <div class="table-responsive">
                <table class="table trm-table align-middle">
                    <thead><tr><th class="trm-check-col"><span class="visually-hidden">เลือก</span></th><th>บุคลากร</th><th>หน่วยงาน</th><th>วันที่เริ่มงาน</th><th class="text-end">ดำเนินการ</th></tr></thead>
                    <tbody>
                    <?php foreach ($unassigned as $employee): ?>
                        <tr>
                            <td class="trm-check-col"><input class="form-check-input trm-employee-check" type="checkbox" name="emp_ids[]" value="<?= (int) $employee->id ?>" aria-label="เลือก <?= Html::encode($employee->fullname) ?>"></td>
                            <td><div class="fw-semibold"><?= Html::encode($employee->fullname) ?></div><div class="trm-meta">รหัส <?= (int) $employee->id ?></div></td>
                            <td><?= Html::encode($employee->departmentName()) ?></td>
                            <td><?= Html::encode($employee->join_date ?: '-') ?></td>
                            <td class="text-end"><?= Html::a('มอบหมาย', ['assign', 'emp_id' => $employee->id], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data-size' => 'modal-lg']) ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <?= Html::endForm() ?>
            <div class="trm-pagination"><?= LinkPager::widget(['pagination' => $unassignedProvider->pagination]) ?></div>
        <?php else: ?>
            <div class="trm-empty trm-empty--compact">
                <span class="trm-empty__icon"><i data-lucide="circle-check-big"></i></span>
                <h3>บุคลากรใหม่ได้รับ TRM ครบแล้ว</h3>
                <p>เมื่อมีบุคลากรเริ่มงานใหม่ ระบบจะแสดงในคิวนี้จนกว่าจะได้รับการมอบหมาย</p>
            </div>
        <?php endif ?>
    </section>

    <section class="trm-card mt-3">
        <div class="trm-section-head">
            <div><h2>แผนที่กำลังติดตาม</h2><div class="trm-meta mt-1">ค้นหา ตรวจความก้าวหน้า และเปิดแผนรายบุคคล</div></div>
        </div>
        <form class="trm-toolbar" action="<?= Url::to(['index']) ?>" method="get">
            <input class="form-control flex-grow-1" name="q" value="<?= Html::encode($q) ?>" placeholder="ค้นหาชื่อหรือนามสกุล" aria-label="ค้นหาบุคลากร">
            <select class="form-select trm-status-filter" name="status" aria-label="กรองสถานะ">
                <option value="">สถานะที่กำลังดำเนินการ</option>
                <?php foreach (EmployeeTrainingPlan::statusOptions() as $key => $label): ?>
                    <option value="<?= Html::encode($key) ?>" <?= $planStatus === $key ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                <?php endforeach ?>
            </select>
            <button class="btn btn-outline-primary" type="submit">ค้นหา</button>
        </form>
        <?php if ($plans): ?>
            <div class="table-responsive">
                <table class="table trm-table align-middle">
                    <thead><tr><th>บุคลากร</th><th>Training Roadmap</th><th>ระยะเวลา</th><th>ความก้าวหน้า</th><th>สถานะ</th><th class="text-end">ดำเนินการ</th></tr></thead>
                    <tbody>
                    <?php foreach ($plans as $plan): ?>
                        <tr>
                            <td><div class="fw-semibold"><?= Html::encode($plan->employee?->fullname ?? '-') ?></div><div class="trm-meta"><?= Html::encode($plan->employee?->departmentName() ?? '-') ?></div></td>
                            <td><span class="trm-code"><?= Html::encode($plan->roadmap?->code ?? '-') ?></span><div class="trm-meta"><?= Html::encode($plan->roadmap?->title ?? '-') ?></div></td>
                            <td><?= Html::encode($plan->start_date) ?><div class="trm-meta">ถึง <?= Html::encode($plan->target_end_date ?: '-') ?></div></td>
                            <td class="trm-progress-cell"><div class="d-flex justify-content-between trm-meta mb-1"><span>ความก้าวหน้า</span><strong><?= (int) $plan->progress_percent ?>%</strong></div><div class="trm-progress"><span style="width:<?= min(100, (float) $plan->progress_percent) ?>%"></span></div></td>
                            <td><span class="trm-status trm-status--<?= Html::encode($plan->status) ?>"><?= Html::encode(EmployeeTrainingPlan::statusOptions()[$plan->status] ?? $plan->status) ?></span></td>
                            <td class="text-end"><?= Html::a('เปิดแผน', ['plan', 'id' => $plan->id], ['class' => 'btn btn-sm btn-outline-primary', 'data-pjax' => '0']) ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <div class="trm-pagination"><?= LinkPager::widget(['pagination' => $planProvider->pagination]) ?></div>
        <?php else: ?>
            <div class="trm-empty trm-empty--compact"><h3>ไม่พบแผนตามเงื่อนไข</h3><p>ลองเปลี่ยนคำค้นหาหรือสถานะที่เลือก</p></div>
        <?php endif ?>
    </section>
</div>
<?php
$this->registerJs(<<<JS
const selectAll = document.getElementById('trm-select-all');
if (selectAll) {
    selectAll.addEventListener('change', function () {
        document.querySelectorAll('.trm-employee-check').forEach(function (checkbox) {
            checkbox.checked = selectAll.checked;
        });
    });
}
JS);
?>
