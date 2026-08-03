<?php

use app\modules\jd\models\JdEmployee;
use yii\helpers\Html;

$items = $model->jdHistory;
$labels = JdEmployee::statusLabels();
?>
<div class="d-flex justify-content-between align-items-start gap-3 mb-3">
    <div><h5 class="mb-1 fw-semibold">ประวัติคำอธิบายงาน</h5><div class="text-muted small">เก็บทุก Revision ตามช่วงเวลาที่มีผลใช้งาน</div></div>
    <?php if (empty($isManagedProfile) && (Yii::$app->user->can('hr') || Yii::$app->user->can('admin'))): ?>
        <?= Html::a('<i class="bi bi-plus-lg me-1"></i>สร้าง JD', ['/jd/employee-jd/create-draft', 'emp_id' => $model->id], ['class' => 'btn btn-sm btn-primary', 'data-method' => 'post']) ?>
    <?php endif; ?>
</div>
<div class="card border-0 shadow-sm">
    <?php if (!$items): ?>
        <div class="card-body text-center py-5"><h6 class="fw-semibold">ยังไม่มีประวัติ JD</h6><p class="text-muted mb-0">เมื่อประกาศใช้ JD ระบบจะแสดงประวัติที่นี่</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>ฉบับ</th><th>ตำแหน่ง</th><th>ช่วงที่มีผล</th><th>Template</th><th>สถานะ</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="text-nowrap">Revision <?= (int) $item->revision_no ?></td>
                        <td><?= Html::encode($item->position_title ?: '—') ?></td>
                        <td class="text-nowrap"><?= $item->effective_from ? Yii::$app->formatter->asDate($item->effective_from, 'php:d/m/Y') : '—' ?> – <?= $item->effective_to ? Yii::$app->formatter->asDate($item->effective_to, 'php:d/m/Y') : ($item->status === JdEmployee::STATUS_ACTIVE ? 'ปัจจุบัน' : '—') ?></td>
                        <td><?= Html::encode($item->template?->name ?: 'จัดทำเฉพาะบุคคล') ?></td>
                        <td><span class="badge rounded-pill <?= $item->status === JdEmployee::STATUS_ACTIVE ? 'text-bg-success' : ($item->status === JdEmployee::STATUS_DRAFT ? 'text-bg-warning' : 'text-bg-secondary') ?>"><?= Html::encode($labels[$item->status] ?? $item->status) ?></span></td>
                        <td class="text-end"><?= Html::a('ดูรายละเอียด', ['/jd/employee-jd/view', 'emp_id' => $model->id, 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-primary']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
