<?php

use yii\helpers\Html;

$this->title = 'เลือก Template สำหรับ JD';
$this->params['breadcrumbs'][] = ['label' => $employee->fullname, 'url' => ['/hr/employees/view', 'id' => $employee->id]];
$this->params['breadcrumbs'][] = ['label' => 'JD ฉบับร่าง', 'url' => ['view', 'emp_id' => $employee->id, 'id' => $jd->id]];
$this->params['breadcrumbs'][] = 'เลือก Template';
?>
<div class="d-flex justify-content-between align-items-start gap-3 mb-3">
    <div>
        <h5 class="mb-1 fw-semibold">เลือก Template เพื่อนำเข้า</h5>
        <div class="text-muted small">ตำแหน่งปัจจุบัน: <?= Html::encode($employee->positionName()) ?> · JD Revision <?= (int) $jd->revision_no ?></div>
    </div>
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับฉบับร่าง', ['view', 'emp_id' => $employee->id, 'id' => $jd->id], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<div class="alert alert-warning py-2">
    การนำเข้าจะใช้โครงสร้างและรายละเอียดจาก Template แทนข้อมูลที่อยู่ในฉบับร่างนี้ โดยไม่กระทบ JD ฉบับปัจจุบันหรือประวัติเดิม
</div>

<?= Html::beginForm(['import-template', 'id' => $jd->id], 'post') ?>
<div class="template-picker">
    <?php if (!$templates): ?>
        <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><h6 class="fw-semibold">ยังไม่มี Template ที่เปิดใช้งาน</h6><p class="text-muted mb-0">สามารถกลับไปกรอกรายละเอียดในโครงสร้างมาตรฐานได้โดยไม่ต้องใช้ Template</p></div></div>
    <?php else: ?>
        <?php foreach ($templates as $template): $recommended = (string) $template->position_code === (string) $employee->position_name; ?>
            <label class="template-option <?= $recommended ? 'is-recommended' : '' ?>">
                <?= Html::radio('template_id', false, ['value' => $template->id, 'required' => true, 'class' => 'form-check-input']) ?>
                <span class="template-option__body">
                    <span class="d-flex flex-wrap align-items-center gap-2">
                        <strong><?= Html::encode($template->name) ?></strong>
                        <?php if ($recommended): ?><span class="badge text-bg-primary">แนะนำตามตำแหน่ง</span><?php endif; ?>
                    </span>
                    <span class="template-option__meta">
                        <?= Html::encode($template->getPositionTitle()) ?> · Revision <?= (int) ($template->revision_no ?: 1) ?> · <?= Html::encode($template->lifecycle_status ?: 'draft') ?>
                    </span>
                </span>
            </label>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php if ($templates): ?>
    <div class="d-flex justify-content-end mt-3">
        <?= Html::submitButton('<i class="bi bi-download me-1"></i>นำเข้า Template ที่เลือก', [
            'class' => 'btn btn-primary',
            'data-confirm' => 'ยืนยันนำเข้า Template และแทนรายละเอียดในฉบับร่างนี้หรือไม่?',
        ]) ?>
    </div>
<?php endif; ?>
<?= Html::endForm() ?>

<?php
$this->registerCss(<<<'CSS'
.template-picker{display:flex;flex-direction:column;gap:.65rem}
.template-option{display:flex;align-items:flex-start;gap:.8rem;padding:.9rem 1rem;border:1px solid rgba(15,23,42,.14);border-radius:10px;background:#fff;cursor:pointer;transition:border-color 120ms,box-shadow 120ms}
.template-option:hover{border-color:rgba(13,110,253,.35)}
.template-option:focus-within{border-color:#0d6efd;box-shadow:0 0 0 3px rgba(13,110,253,.08)}
.template-option.is-recommended{border-color:rgba(13,110,253,.22)}
.template-option .form-check-input{margin-top:.2rem;flex:0 0 auto}
.template-option__body{display:flex;flex-direction:column;gap:.25rem;min-width:0}
.template-option__meta{font-size:.8rem;color:#718096}
CSS);
?>
