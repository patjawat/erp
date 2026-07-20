<?php

use app\modules\jd\models\JdEmployee;
use yii\helpers\Html;

$isPersisted = !$jd->isNewRecord;
$isDraft = $isPersisted && $jd->status === JdEmployee::STATUS_DRAFT;
$canManage = Yii::$app->user->can('hr') || Yii::$app->user->can('admin');
$labels = JdEmployee::statusLabels();
$this->title = 'คำอธิบายงาน (JD) · ' . $employee->fullname;
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนบุคลากร', 'url' => ['/hr/employees/index']];
$this->params['breadcrumbs'][] = ['label' => $employee->fullname, 'url' => ['/hr/employees/view', 'id' => $employee->id]];
$this->params['breadcrumbs'][] = 'คำอธิบายงาน';
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
    <div>
        <h5 class="mb-1 fw-semibold">คำอธิบายงาน · <?= Html::encode($employee->fullname) ?></h5>
        <?php if ($isPersisted): ?>
            <div class="text-muted small">
                Revision <?= (int) $jd->revision_no ?> · <?= Html::encode($jd->position_title ?: $employee->positionName()) ?> · <?= Html::encode($labels[$jd->status] ?? $jd->status) ?>
            </div>
        <?php else: ?>
            <div class="text-muted small">ยังไม่มี JD ฉบับปัจจุบัน</div>
        <?php endif; ?>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับโปรไฟล์', ['/hr/employees/view', 'id' => $employee->id, 'name' => 'job_description_current'], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::a('<i class="bi bi-clock-history me-1"></i>ดูประวัติ', ['/hr/employees/view', 'id' => $employee->id, 'name' => 'job_description_history'], ['class' => 'btn btn-outline-secondary']) ?>
        <?php if ($canManage && !$isPersisted): ?>
            <?= Html::a('<i class="bi bi-file-earmark-plus me-1"></i>สร้าง JD ฉบับร่าง', ['create-draft', 'emp_id' => $employee->id], [
                'class' => 'btn btn-primary',
                'data-method' => 'post',
            ]) ?>
        <?php endif; ?>
        <?php if ($canManage && $isPersisted && !$isDraft): ?>
            <?= Html::a('<i class="bi bi-plus-lg me-1"></i>เพิ่ม JD ใหม่', ['create-draft', 'emp_id' => $employee->id], ['class' => 'btn btn-primary', 'data-method' => 'post']) ?>
        <?php endif; ?>
        <?php if ($canManage && $isDraft): ?>
            <?= Html::a('<i class="bi bi-download me-1"></i>เลือกนำเข้า Template', ['select-template', 'id' => $jd->id], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::beginForm(['activate', 'id' => $jd->id], 'post', ['class' => 'd-inline']) ?>
            <?= Html::hiddenInput('effective_from', date('Y-m-d')) ?>
            <?= Html::submitButton('<i class="bi bi-check-circle me-1"></i>ประกาศใช้วันนี้', [
                'class' => 'btn btn-success',
                'data-confirm' => 'เมื่อประกาศใช้ ฉบับปัจจุบันเดิมจะถูกปิดและเก็บไว้ในประวัติ ยืนยันหรือไม่?',
            ]) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
    </div>
</div>

<?php if ($isDraft): ?>
    <div class="alert alert-warning py-2"><strong>ฉบับร่าง</strong> ตรวจสอบรายละเอียดและ KPI เป้าหมายให้ครบก่อนประกาศใช้ ข้อมูลฉบับเดิมจะยังไม่เปลี่ยนจนกว่าจะประกาศใช้ฉบับนี้</div>
<?php endif; ?>

<?php if (!$templateForPosition && !$isPersisted): ?>
    <div class="alert alert-info">ยังไม่มี Template สถานะพร้อมใช้งานสำหรับตำแหน่ง “<?= Html::encode($employee->positionName()) ?>”</div>
<?php endif; ?>

<?php if ($isPersisted && $jd->sections): ?>
    <?= $this->render('_document', ['jd' => $jd, 'editable' => $isDraft && $canManage]) ?>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <h6 class="fw-semibold mb-2">ยังไม่มีรายละเอียดคำอธิบายงาน</h6>
            <p class="text-muted mb-3">เริ่มจาก Template ของตำแหน่งเพื่อเก็บโครงสร้าง หน้าที่ บทบาท และ KPI เป้าหมาย</p>
            <?php if ($canManage): ?>
                <?= Html::a('<i class="bi bi-file-earmark-plus me-1"></i>สร้าง JD ฉบับร่าง', ['create-draft', 'emp_id' => $employee->id], ['class' => 'btn btn-primary', 'data-method' => 'post']) ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
