<?php

use yii\helpers\Html;
use app\modules\jd\models\JdTemplate;

/** @var yii\web\View $this */
/** @var JdTemplate $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Template JD', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$badge = fn(bool $active) => $active
    ? '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">ใช้งาน</span>'
    : '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">ปิดใช้</span>';

$row = function (string $label, ?string $value, bool $nl2br = false) {
    $display = trim((string) $value) !== ''
        ? ($nl2br ? nl2br(Html::encode($value)) : Html::encode($value))
        : '<span class="text-muted">—</span>';
    return <<<HTML
        <div class="mb-3">
            <div class="text-muted small mb-1">{$label}</div>
            <div>{$display}</div>
        </div>
        HTML;
};
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2">
    <i class="bi bi-file-earmark-text fs-4 text-primary"></i>
    <h4 class="fw-medium mb-0"><?= Html::encode($model->name) ?></h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex flex-wrap gap-2">
    <?= Html::a('<i class="bi bi-ui-checks-grid me-1"></i>จัดทำเนื้อหา 10 หมวด', ['structure', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    <?= Html::a('<i class="bi bi-pencil me-1"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
    <?= Html::a('<i class="bi bi-plus-lg me-1"></i> เพิ่มหัวข้อ',
        ['add-section', 'id' => $model->id, 'title' => 'เพิ่มหัวข้อ: ' . $model->name],
        ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
    <?= Html::beginForm(['copy', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?>
    <?= Html::submitButton('<i class="bi bi-copy me-1"></i>คัดลอก Template', ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::endForm() ?>
    <?= Html::beginForm(['new-revision', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?>
    <?= Html::submitButton('<i class="bi bi-clock-history me-1"></i>สร้าง Revision ใหม่', ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::endForm() ?>
</div>
<?php $this->endBlock(); ?>

<!-- ── Header card ── -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-md-4">
                <div class="text-muted small">ตำแหน่งงาน</div>
                <div class="fw-medium"><?= Html::encode($model->getPositionTitle()) ?></div>
                <?php if ($model->job_code): ?>
                    <div class="text-muted small">รหัส: <?= Html::encode($model->job_code) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-2">
                <div class="text-muted small">ระดับ</div>
                <div><?= Html::encode($model->job_level ?: '—') ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">แผนก / ฝ่าย</div>
                <div><?= Html::encode($model->department ?: '—') ?></div>
            </div>
            <div class="col-md-2">
                <div class="text-muted small">สถานะ</div>
                <div><?= $badge((bool) $model->is_active) ?></div>
            </div>
            <div class="col-md-1 text-end">
                <?php if ($model->headcount): ?>
                    <div class="text-muted small">อัตรา</div>
                    <div class="fw-medium"><?= $model->headcount ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── Tabs ── -->
<ul class="nav nav-tabs mb-0" id="jdViewTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="vt1-tab" data-bs-toggle="tab" data-bs-target="#vt1" type="button" role="tab">
            <i class="bi bi-tag me-1"></i> ข้อมูลพื้นฐาน
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="vt2-tab" data-bs-toggle="tab" data-bs-target="#vt2" type="button" role="tab">
            <i class="bi bi-list-task me-1"></i> หน้าที่ (<?= count($model->sections) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="vt3-tab" data-bs-toggle="tab" data-bs-target="#vt3" type="button" role="tab">
            <i class="bi bi-person-check me-1"></i> คุณสมบัติ
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="vt4-tab" data-bs-toggle="tab" data-bs-target="#vt4" type="button" role="tab">
            <i class="bi bi-lightbulb me-1"></i> สมรรถนะ + KPI
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="vt5-tab" data-bs-toggle="tab" data-bs-target="#vt5" type="button" role="tab">
            <i class="bi bi-cash-coin me-1"></i> ค่าตอบแทน
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="vt6-tab" data-bs-toggle="tab" data-bs-target="#vt6" type="button" role="tab">
            <i class="bi bi-graph-up-arrow me-1"></i> สภาพแวดล้อม + HR
        </button>
    </li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom-3 p-4 shadow-sm bg-white mb-4" id="jdViewTabContent">

    <!-- ─── Tab 1: ข้อมูลพื้นฐาน + วัตถุประสงค์ ─── -->
    <div class="tab-pane fade show active" id="vt1" role="tabpanel">
        <div class="row g-0">
            <div class="col-md-6">
                <?= $row('รายงานตรงต่อ', $model->report_to) ?>
                <?= $row('มีผู้ใต้บังคับบัญชา', $model->has_subordinates ? 'มี' : 'ไม่มี') ?>
                <?= $row('สร้างเมื่อ', $model->created_at) ?>
                <?= $row('แก้ไขล่าสุด', $model->updated_at) ?>
            </div>
        </div>
        <?php if (!empty($model->job_purpose)): ?>
            <div class="mt-2">
                <div class="text-muted small mb-1">วัตถุประสงค์ของตำแหน่ง (Job Purpose)</div>
                <div class="p-3 bg-primary bg-opacity-10 rounded-3"><?= nl2br(Html::encode($model->job_purpose)) ?></div>
            </div>
        <?php else: ?>
            <div class="text-muted small fst-italic">ยังไม่ได้ระบุวัตถุประสงค์ของตำแหน่ง — <?= Html::a('แก้ไข', ['update', 'id' => $model->id]) ?></div>
        <?php endif; ?>
    </div>

    <!-- ─── Tab 2: หน้าที่ความรับผิดชอบ (Sections) ─── -->
    <div class="tab-pane fade" id="vt2" role="tabpanel">
        <?php if (empty($model->sections)): ?>
            <div class="text-center text-muted py-4">
                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                ยังไม่มีหัวข้อ —
                <?= Html::a('<i class="bi bi-plus-lg me-1"></i> เพิ่มหัวข้อ',
                    ['add-section', 'id' => $model->id, 'title' => 'เพิ่มหัวข้อ: ' . $model->name],
                    ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-nowrap" style="width:60px;">ลำดับ</th>
                            <th>หัวข้อ</th>
                            <th class="text-end" style="width:120px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle table-group-divider">
                        <?php foreach ($model->sections as $s): ?>
                        <tr>
                            <td class="text-center fw-medium"><?= $s->sort_order ?></td>
                            <td>
                                <div class="fw-medium"><?= Html::encode($s->title) ?></div>
                                <?php if (!empty($s->content)): ?>
                                    <div class="small text-muted mt-1">
                                        <?= nl2br(Html::encode(mb_substr($s->content, 0, 150))) ?><?= mb_strlen($s->content) > 150 ? '…' : '' ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?= Html::a('<i class="bi bi-pencil"></i>',
                                    ['update-section', 'id' => $s->id, 'title' => 'แก้ไขหัวข้อ: ' . $s->title],
                                    ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data' => ['size' => 'modal-lg'], 'title' => 'แก้ไข']) ?>
                                <?= Html::a('<i class="bi bi-trash"></i>', ['delete-section', 'id' => $s->id], [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'data'  => ['method' => 'post', 'confirm' => 'ยืนยันลบหัวข้อนี้?'],
                                ]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- ─── Tab 3: คุณสมบัติที่ต้องการ ─── -->
    <div class="tab-pane fade" id="vt3" role="tabpanel">
        <div class="row g-4">
            <div class="col-12">
                <?= $row('การศึกษา', $model->edu_requirement, true) ?>
            </div>
            <div class="col-md-2">
                <?= $row('ประสบการณ์ขั้นต่ำ', $model->exp_years !== null ? $model->exp_years . ' ปี' : null) ?>
            </div>
            <div class="col-md-10">
                <?= $row('รายละเอียดประสบการณ์', $model->exp_detail, true) ?>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-primary text-white py-2 px-3">
                        <h6 class="mb-0 small fw-normal text-white">ทักษะเฉพาะทาง (Hard Skills)</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($model->hard_skills)): ?>
                            <div><?= nl2br(Html::encode($model->hard_skills)) ?></div>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-primary text-white py-2 px-3">
                        <h6 class="mb-0 small fw-normal text-white">ทักษะด้านพฤติกรรม (Soft Skills)</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($model->soft_skills)): ?>
                            <div><?= nl2br(Html::encode($model->soft_skills)) ?></div>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── Tab 4: สมรรถนะ + KPI ─── -->
    <div class="tab-pane fade" id="vt4" role="tabpanel">
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">Core</span>
                    <span class="fw-medium">Core Competency</span>
                </div>
                <?php if (!empty($model->core_competency)): ?>
                    <div class="p-3 rounded-3 border"><?= nl2br(Html::encode($model->core_competency)) ?></div>
                <?php else: ?>
                    <span class="text-muted small">—</span>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">Functional</span>
                    <span class="fw-medium">Functional Competency</span>
                </div>
                <?php if (!empty($model->functional_competency)): ?>
                    <div class="p-3 rounded-3 border"><?= nl2br(Html::encode($model->functional_competency)) ?></div>
                <?php else: ?>
                    <span class="text-muted small">—</span>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">Leadership</span>
                    <span class="fw-medium">Leadership Competency</span>
                </div>
                <?php if (!empty($model->leadership_competency)): ?>
                    <div class="p-3 rounded-3 border"><?= nl2br(Html::encode($model->leadership_competency)) ?></div>
                <?php else: ?>
                    <span class="text-muted small">—</span>
                <?php endif; ?>
            </div>
        </div>
        <hr>
        <h6 class="fw-medium mb-3">ตัวชี้วัดผลงาน (KPIs)</h6>
        <?php if (!empty($model->kpis)): ?>
            <div class="p-3 rounded-3 border"><?= nl2br(Html::encode($model->kpis)) ?></div>
        <?php else: ?>
            <span class="text-muted">—</span>
        <?php endif; ?>
    </div>

    <!-- ─── Tab 5: ค่าตอบแทน ─── -->
    <div class="tab-pane fade" id="vt5" role="tabpanel">
        <?php if ($model->salary_min || $model->salary_max): ?>
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-primary text-white py-2 px-3">
                    <h6 class="mb-0 small fw-normal text-white">ช่วงเงินเดือน (Salary Band)</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <div class="text-muted small">ต่ำสุด</div>
                            <div class="fs-5 fw-medium"><?= number_format((int)$model->salary_min) ?> <span class="small text-muted">บาท</span></div>
                        </div>
                        <div class="text-muted">—</div>
                        <div>
                            <div class="text-muted small">สูงสุด</div>
                            <div class="fs-5 fw-medium"><?= number_format((int)$model->salary_max) ?> <span class="small text-muted">บาท</span></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-primary text-white py-2 px-3">
                        <h6 class="mb-0 small fw-normal text-white">สวัสดิการหลัก</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($model->benefits)): ?>
                            <div><?= nl2br(Html::encode($model->benefits)) ?></div>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-primary text-white py-2 px-3">
                        <h6 class="mb-0 small fw-normal text-white">ค่าตอบแทนผันแปร</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($model->variable_pay)): ?>
                            <div><?= nl2br(Html::encode($model->variable_pay)) ?></div>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── Tab 6: สภาพแวดล้อม + เส้นทางอาชีพ + HR Analytics ─── -->
    <div class="tab-pane fade" id="vt6" role="tabpanel">
        <div class="row g-3 mb-4">
            <div class="col-12">
                <h6 class="fw-medium text-muted mb-3">สภาพแวดล้อมการทำงาน</h6>
            </div>
            <div class="col-md-3">
                <?php
                    $workTypeMap = JdTemplate::workTypeOptions();
                    $workTypeLabel = $model->work_type ? ($workTypeMap[$model->work_type] ?? $model->work_type) : null;
                ?>
                <?= $row('รูปแบบการทำงาน', $workTypeLabel) ?>
            </div>
            <div class="col-md-5"><?= $row('สถานที่ปฏิบัติงาน', $model->work_location) ?></div>
            <div class="col-md-4"><?= $row('เวลาทำงาน / กะ', $model->work_hours) ?></div>
            <?php if (!empty($model->work_conditions)): ?>
                <div class="col-12"><?= $row('สภาพแวดล้อมพิเศษ', $model->work_conditions, true) ?></div>
            <?php endif; ?>
        </div>
        <hr>
        <div class="row g-3 mb-4">
            <div class="col-12">
                <h6 class="fw-medium text-muted mb-3">เส้นทางความก้าวหน้า (Career Path)</h6>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-primary text-white py-2 px-3">
                        <h6 class="mb-0 small fw-normal text-white">แนวดิ่ง (Vertical)</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($model->career_vertical)): ?>
                            <div><?= nl2br(Html::encode($model->career_vertical)) ?></div>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-primary text-white py-2 px-3">
                        <h6 class="mb-0 small fw-normal text-white">แนวราบ (Lateral)</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($model->career_lateral)): ?>
                            <div><?= nl2br(Html::encode($model->career_lateral)) ?></div>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row g-3">
            <div class="col-12">
                <h6 class="fw-medium text-muted mb-3">ข้อมูล HR Analytics</h6>
            </div>
            <?php
                $empTypeMap = JdTemplate::employmentTypeOptions();
                $empTypeLabel = $model->employment_type ? ($empTypeMap[$model->employment_type] ?? $model->employment_type) : null;
            ?>
            <div class="col-md-3"><?= $row('ประเภทการจ้าง', $empTypeLabel) ?></div>
            <div class="col-md-2"><?= $row('Headcount (อัตรา)', $model->headcount !== null ? (string)$model->headcount : null) ?></div>
            <div class="col-md-4"><?= $row('ผู้อนุมัติ JD', $model->jd_approved_by) ?></div>
            <div class="col-md-3"><?= $row('วันที่อนุมัติ JD', $model->jd_approved_at ? date('d/m/' . (date('Y', strtotime($model->jd_approved_at)) + 543), strtotime($model->jd_approved_at)) : null) ?></div>
        </div>
    </div>

</div><!-- end tab-content -->

<p class="mt-3">
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับรายการ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</p>
