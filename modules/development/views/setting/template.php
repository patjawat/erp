<?php
use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var bool $hasDefault */
/** @var string|null $defaultUrl */

$this->title = 'Template แบบฟอร์มไปราชการ';
$this->params['breadcrumbs'][] = ['label' => 'ภาพรวมอบรม/ประชุม/ดูงาน', 'url' => ['/development/default/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'แบบฟอร์มไปราชการ', 'url' => ['/development/setting/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2">
    <i class="bi bi-file-earmark-pdf text-primary"></i>
    <h4 class="fw-medium text-body mb-0"><?= Html::encode($this->title) ?></h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/development/views/menu_admin', ['active' => 'setting-form']) ?>
<?php $this->endBlock(); ?>

<?php if (Yii::$app->session->hasFlash('success')): ?>
<div class="alert alert-success alert-dismissible fade show mb-3">
    <?= Yii::$app->session->getFlash('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
<div class="alert alert-danger alert-dismissible fade show mb-3">
    <?= Yii::$app->session->getFlash('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-primary bg-opacity-10 text-primary border-0 py-3 px-4 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 small fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-pdf"></i> จัดการเทมเพลต PDF ใบขอไปราชการ
        </h6>
        <span class="small text-muted">อัปโหลดเทมเพลต PDF และกำหนดตำแหน่งข้อมูลบนฟอร์ม</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 py-3">เทมเพลต</th>
                    <th class="py-3">เทมเพลต PDF</th>
                    <th class="py-3">ตำแหน่งข้อมูล</th>
                    <th class="py-3 text-end pe-4">จัดการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <tr class="table-primary">
                    <td class="px-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">DEFAULT</span>
                            <div>
                                <div class="fw-semibold small">Template กลาง (ใบขอไปราชการ)</div>
                                <div class="text-muted" style="font-size:.75rem;">ใช้สำหรับพิมพ์ใบขอไปราชการ</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if ($hasDefault): ?>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">
                            <i class="bi bi-check-circle me-1"></i>มีไฟล์แล้ว
                        </span>
                        <?php else: ?>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">
                            <i class="bi bi-exclamation-triangle me-1"></i>ยังไม่มี
                        </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($hasDefault): ?>
                        <span class="small text-success"><i class="bi bi-check-circle me-1"></i>กำหนดแล้ว</span>
                        <?php else: ?>
                        <span class="small text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end pe-4">
                        <div class="dropdown">
                            <button class="btn btn-primary btn-sm rounded-3 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical me-1"></i> จัดการ
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><?= Html::a('<i class="bi bi-pencil me-2"></i> แก้ไข', ['/development/setting/template', 'code' => 'default'], ['class' => 'dropdown-item']) ?></li>
                                <?php if ($hasDefault): ?>
                                <li><?= Html::a('<i class="bi bi-geo-alt me-2"></i> ตำแหน่ง', ['/development/setting/positions', 'code' => 'default'], ['class' => 'dropdown-item']) ?></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent border-top py-2 px-4">
        <p class="small text-muted mb-0">
            <i class="bi bi-info-circle me-1"></i>
            อัปโหลดเทมเพลต PDF ต้นแบบ จากนั้นกด <strong>กำหนดตำแหน่ง</strong> เพื่อลากฟิลด์ไปวางบนพื้นที่พิมพ์
        </p>
    </div>
</div>
