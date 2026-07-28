<?php

use app\modules\jd\models\JdEmployee;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Employees $employee */
/** @var array $items */

$this->title = 'JD รอลงนามของฉัน';
$this->params['breadcrumbs'][] = 'JD รอลงนามของฉัน';
$roleLabels = ['ผู้จัดทำ' => 'text-bg-secondary', 'ผู้ตรวจสอบ' => 'text-bg-info', 'ผู้อนุมัติ' => 'text-bg-primary'];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h5 class="mb-1 fw-semibold"><i class="bi bi-pen me-1"></i>JD รอลงนามของฉัน</h5>
        <div class="text-muted small">รายการคำอธิบายงานที่รอให้คุณลงนามตามลำดับ</div>
    </div>
    <span class="badge rounded-pill text-bg-warning fs-6"><?= count($items) ?> รายการ</span>
</div>

<?php if (!$items): ?>
    <div class="alert alert-success d-flex align-items-center gap-2">
        <i class="bi bi-check-circle"></i>
        <div>ไม่มี JD ที่รอคุณลงนามในขณะนี้</div>
    </div>
<?php else: ?>
    <div class="list-group shadow-sm">
        <?php foreach ($items as $item): ?>
            <?php
            /** @var JdEmployee $jd */
            $jd = $item['jd'];
            $approve = $item['approve'];
            $data = (array) $approve->data_json;
            $role = trim((string) ($data['role'] ?? 'ผู้ลงนาม'));
            $badge = $roleLabels[$role] ?? 'text-bg-secondary';
            $ownerName = $jd->employee?->fullname ?? ('บุคลากร #' . $jd->emp_id);
            ?>
            <div class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-3 py-3">
                <div class="d-flex align-items-start gap-3">
                    <span class="rounded-circle bg-warning-subtle text-warning-emphasis d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                        <i class="bi bi-file-earmark-text fs-5"></i>
                    </span>
                    <div>
                        <div class="fw-semibold"><?= Html::encode($ownerName) ?></div>
                        <div class="text-muted small">
                            <?= Html::encode($jd->position_title ?: '-') ?> · Revision <?= (int) $jd->revision_no ?>
                        </div>
                        <span class="badge <?= $badge ?> mt-1">ลงนามในฐานะ <?= Html::encode($role) ?></span>
                    </div>
                </div>
                <?= Html::a('<i class="bi bi-pen me-1"></i>ตรวจสอบและลงนาม',
                    ['view', 'emp_id' => $jd->emp_id, 'id' => $jd->id],
                    ['class' => 'btn btn-primary']) ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
