<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use app\modules\approveV3\models\ApproveLevelSetting;

/** @var yii\web\View $this */
/** @var string $system */
/** @var string $systemName */
/** @var app\modules\approveV3\models\ApproveLevelSetting[] $models */
/** @var int $selectedEmpId */
/** @var app\modules\hr\models\Employees|null $selectedEmployee */
/** @var array $resolvedLevels */

$this->title = 'ระดับการอนุมัติ: ' . $systemName;
$this->params['breadcrumbs'][] = ['label' => 'อนุมัติ V3', 'url' => ['/approve-v3/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'ตั้งค่าระดับการอนุมัติ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $systemName;

$typeLabels = ApproveLevelSetting::approverTypeOptions();
$orgLevelLabels = ApproveLevelSetting::orgNodeLevelOptions();
$levelsUrl = Url::to(['levels', 'system' => $system]);
$selectedEmpText = $selectedEmployee ? $selectedEmployee->fullname : '';
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
    <i data-lucide="layers"></i>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex flex-wrap gap-2 align-items-center">
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i> ตั้งค่าทั้งหมด', ['index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
    <?= Html::a('<i class="bi bi-plus-lg me-1"></i> เพิ่มระดับ', ['create-level', 'system' => $system], ['class' => 'btn btn-primary rounded-3']) ?>
</div>
<?php $this->endBlock(); ?>

<!-- เลือกพนักงานเพื่อตรวจสอบระดับการอนุมัติตามโครงสร้าง -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <h6 class="fw-semibold text-body mb-2">ตรวจสอบระดับการอนุมัติตามโครงสร้างองค์กร</h6>
        <p class="text-muted small mb-3">ตามหลัก: เรียกจากระดับลึกสุด (แผนกพนักงาน) ขึ้นมาก่อน แล้วค่อยขึ้นบน — หัวหน้างาน → หัวหน้างาน → หัวหน้ากลุ่มงาน → ตรวจสอบ → ผอ.อนุมัติ (หรือกำหนดผู้อนุมัติแทน)</p>
        <form method="get" action="<?= Html::encode($levelsUrl) ?>" class="row g-2 align-items-end">
            <input type="hidden" name="system" value="<?= Html::encode($system) ?>">
            <div class="col-auto flex-grow-1">
                <label class="form-label small text-muted mb-1">เลือกพนักงาน (ถ้าพนักงานนี้ขอลา จะได้ผู้อนุมัติตามนี้)</label>
                <?= Select2::widget([
                    'name' => 'emp_id',
                    'value' => $selectedEmpId ?: '',
                    'initValueText' => $selectedEmpText,
                    'options' => ['placeholder' => 'เลือกพนักงาน...', 'id' => 'levels-emp-select'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'ajax' => [
                            'url' => Url::to(['/hr/organization/list-employee']),
                            'dataType' => 'json',
                            'delay' => 250,
                            'data' => new JsExpression('function(params) { return {q: params.term}; }'),
                            'processResults' => new JsExpression('function(data) { return {results: data.items}; }'),
                        ],
                        'minimumInputLength' => 1,
                    ],
                ]) ?>
            </div>
            <div class="col-auto">
                <?= Html::submitButton('<i class="bi bi-search me-1"></i> ตรวจสอบ', ['class' => 'btn btn-outline-primary rounded-3']) ?>
            </div>
        </form>
    </div>
</div>

<?php if ($selectedEmpId > 0 && $selectedEmployee): ?>
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-primary bg-opacity-10 border-0">
        <h6 class="mb-0 fw-semibold text-primary">ผลระดับการอนุมัติสำหรับ: <?= Html::encode($selectedEmployee->fullname) ?></h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-nowrap">ระดับ</th>
                        <th class="text-nowrap">ชื่อระดับ</th>
                        <th class="text-nowrap">หน่วยงาน/กลุ่มงาน</th>
                        <th class="text-nowrap">ประเภท</th>
                        <th class="text-nowrap">ผู้อนุมัติ (ตามโครงสร้าง)</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                    <?php if (empty($resolvedLevels)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">ไม่มีระดับการอนุมัติในระบบนี้ หรือยังไม่ได้ตั้งค่า</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($resolvedLevels as $r): ?>
                    <tr>
                        <td><strong><?= (int) $r['level'] ?></strong></td>
                        <td><?= Html::encode($r['label']) ?></td>
                        <td><?= !empty($r['org_node_name']) ? Html::encode($r['org_node_name']) : '<span class="text-muted">—</span>' ?></td>
                        <td><?= Html::encode($typeLabels[$r['approver_type']] ?? $r['approver_type']) ?></td>
                        <td><?= Html::encode($r['approver_display'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-nowrap">ระดับ</th>
                        <th class="text-nowrap">ชื่อระดับ</th>
                        <th class="text-nowrap">ประเภทผู้อนุมัติ</th>
                        <th class="text-nowrap">ระดับในผังองค์กร</th>
                        <th class="text-nowrap">ค่า (บทบาท/พนักงาน)</th>
                        <th class="text-nowrap text-center">ใช้งาน</th>
                        <th class="text-nowrap text-end" style="width: 140px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                    <?php if (empty($models)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">ยังไม่มีระดับการอนุมัติ — กด «เพิ่มระดับ» เพื่อเพิ่ม</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($models as $item): ?>
                    <tr>
                        <td><strong><?= (int) $item->level ?></strong></td>
                        <td><?= Html::encode($item->label) ?></td>
                        <td><?= Html::encode($typeLabels[$item->approver_type] ?? $item->approver_type) ?></td>
                        <td><?= in_array($item->approver_type, [ApproveLevelSetting::TYPE_ORG_LEADER1, ApproveLevelSetting::TYPE_ORG_LEADER2], true)
                            ? (isset($item->org_node_level) && $item->org_node_level !== null && $item->org_node_level !== ''
                                ? Html::encode($orgLevelLabels[$item->org_node_level] ?? 'ระดับ ' . $item->org_node_level)
                                : '<span class="text-muted">แผนกผู้ขอ</span>')
                            : '<span class="text-muted">—</span>' ?></td>
                        <td><?= $item->approver_value ? Html::encode($item->approver_value) : '<span class="text-muted">—</span>' ?></td>
                        <td class="text-center">
                            <?php if ($item->active): ?>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">ใช้งาน</span>
                            <?php else: ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">ปิด</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?= Html::a('แก้ไข', ['update-level', 'id' => $item->id], ['class' => 'btn btn-outline-primary btn-sm rounded-pill']) ?>
                            <?= Html::a('ลบ', ['delete-level', 'id' => $item->id], [
                                'class' => 'btn btn-outline-danger btn-sm rounded-pill',
                                'data' => ['method' => 'post', 'confirm' => 'ต้องการลบระดับนี้หรือไม่?'],
                            ]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
