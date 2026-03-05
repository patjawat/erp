<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var app\modules\hr\models\Employees $employee */
/** @var app\modules\jd\models\JdEmployee $jd */
/** @var app\modules\jd\models\JdTemplate|null $templateForPosition */
$this->title = 'คำอธิบายงาน (JD) — ' . $employee->fullname;
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนบุคลากร', 'url' => ['/hr/employees/index']];
$this->params['breadcrumbs'][] = ['label' => $employee->fullname, 'url' => ['/hr/employees/view', 'id' => $employee->id]];
$this->params['breadcrumbs'][] = 'คำอธิบายงาน (JD)';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">คำอธิบายงาน (Job Description) — <?= Html::encode($employee->fullname) ?></h5>
    <div class="d-flex gap-2">
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับโปรไฟล์', ['/hr/employees/view', 'id' => $employee->id], ['class' => 'btn btn-outline-secondary']) ?>
        <?php if ($templateForPosition && count($jd->sections) === 0): ?>
            <?= Html::a('<i class="bi bi-download me-1"></i> โหลดจาก Template ตำแหน่งปัจจุบัน', ['load-template', 'emp_id' => $employee->id], ['class' => 'btn btn-primary', 'data-method' => 'post']) ?>
        <?php elseif ($templateForPosition): ?>
            <?= Html::a('<i class="bi bi-download me-1"></i> โหลด Template ใหม่ (แทนที่)', ['load-template', 'emp_id' => $employee->id], [
                'class' => 'btn btn-outline-primary',
                'data' => ['method' => 'post', 'confirm' => 'จะแทนที่หัวข้อปัจจุบันด้วย template?'],
            ]) ?>
        <?php endif; ?>
        <?= Html::a('<i class="bi bi-plus-lg me-1"></i> เพิ่มหัวข้อ', ['add-section', 'emp_id' => $employee->id], ['class' => 'btn btn-primary']) ?>
    </div>
</div>

<?php if (!$templateForPosition && $employee->position_name): ?>
<div class="alert alert-info py-2">
    <small>ยังไม่มี template สำหรับตำแหน่ง "<?= Html::encode($employee->positionName ? $employee->positionName->title : $employee->position_name) ?>" — สร้าง template ได้ที่ <?= Html::a('Template JD', ['/jd/template/index']) ?></small>
</div>
<?php endif; ?>

<?php if (count($jd->sections) === 0 && !$jd->template_id): ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body text-center py-5">
        <p class="text-muted mb-3">ยังไม่มีคำอธิบายงาน</p>
        <?php if ($templateForPosition): ?>
            <?= Html::a('<i class="bi bi-download me-1"></i> โหลดจาก Template ตำแหน่งปัจจุบัน', ['load-template', 'emp_id' => $employee->id], ['class' => 'btn btn-primary', 'data-method' => 'post']) ?>
        <?php else: ?>
            <?= Html::a('<i class="bi bi-plus-lg me-1"></i> เพิ่มหัวข้อ', ['add-section', 'emp_id' => $employee->id], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-primary text-white py-2 px-3">
        <h6 class="mb-0 small fw-normal text-white">หัวข้อคำอธิบายงาน</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-nowrap">ลำดับ</th>
                        <th>หัวข้อ</th>
                        <th class="text-end">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider align-middle">
                    <?php foreach ($jd->sections as $s): ?>
                    <tr>
                        <td><?= $s->sort_order ?></td>
                        <td>
                            <strong><?= Html::encode($s->title) ?></strong>
                            <?php if (!empty($s->content)): ?>
                                <div class="small text-muted mt-1"><?= nl2br(Html::encode(mb_substr($s->content, 0, 150))) ?><?= mb_strlen($s->content) > 150 ? '…' : '' ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?= Html::a('<i class="bi bi-pencil"></i>', ['update-section', 'id' => $s->id], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'แก้ไข']) ?>
                            <?= Html::a('<i class="bi bi-trash"></i>', ['delete-section', 'id' => $s->id], [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'title' => 'ลบ',
                                'data' => ['method' => 'post', 'confirm' => 'ยืนยันลบหัวข้อนี้?'],
                            ]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
