<?php

use app\components\widgets\DataSummaryWidget;
use app\modules\housing\models\HousingRequest;
use yii\helpers\Html;

$this->title = 'คำขอบ้านพัก';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'request']) ?><?php $this->endBlock();
?>
<div class="container-fluid py-3">
<div class="card border-0 shadow-sm">
<div class="card-header bg-body d-flex flex-wrap justify-content-between align-items-start gap-2">
<div><div class="fw-semibold">รายการคำขอ</div><div class="small text-body-secondary">ตรวจสอบ บันทึกผลมติ และจัดสรรที่พัก</div></div>
<?= Html::a('<i class="bi bi-plus-lg"></i> สร้างคำขอ', ['create'], ['class' => 'btn btn-primary']) ?>
</div>
<div class="card-body border-bottom py-2">
<div class="d-flex flex-wrap gap-2">
<?= Html::a('ทั้งหมด', ['index'], ['class' => 'btn btn-sm ' . (!$status ? 'btn-primary' : 'btn-outline-secondary')]) ?>
<?php foreach ([HousingRequest::STATUS_SUBMITTED, HousingRequest::STATUS_STAFF_REVIEW, HousingRequest::STATUS_COMMITTEE_REVIEW, HousingRequest::STATUS_APPROVED] as $filterStatus): ?>
<?= Html::a(Html::encode(HousingRequest::statusOptions()[$filterStatus]), ['index', 'status' => $filterStatus], ['class' => 'btn btn-sm ' . ($status === $filterStatus ? 'btn-primary' : 'btn-outline-secondary')]) ?>
<?php endforeach; ?>
</div>
</div>
<div class="card-body p-0">
<div class="d-none d-lg-block"><table class="table table-hover align-middle mb-0"><thead><tr><th>เลขคำขอ</th><th>ผู้ขอ/เพศ</th><th>ตำแหน่ง/หน่วยงาน</th><th>ประเภท</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead><tbody>
<?php foreach ($dataProvider->models as $model): $profile = $employeeProfiles[(int)$model->emp_id] ?? null; ?><tr>
<td class="fw-semibold text-nowrap"><?= Html::encode($model->request_no) ?></td>
<td><strong><?= Html::encode($profile['name'] ?? 'บุคลากร #' . $model->emp_id) ?></strong><div class="small text-body-secondary"><?= Html::encode($profile['gender'] ?? 'ไม่ระบุ') ?></div></td>
<td><div><?= Html::encode($profile['position'] ?? 'ไม่ระบุ') ?></div><div class="small text-body-secondary"><?= Html::encode($profile['department'] ?? 'ไม่ระบุ') ?></div></td>
<td><?= Html::encode(HousingRequest::typeOptions()[$model->request_type] ?? '') ?></td><td><span class="badge bg-body-secondary text-body"><?= Html::encode(HousingRequest::statusOptions()[$model->status] ?? $model->status) ?></span></td><td class="text-end"><?= Html::a('ดำเนินการ', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<ul class="list-group list-group-flush d-lg-none"><?php foreach ($dataProvider->models as $model): $profile = $employeeProfiles[(int)$model->emp_id] ?? null; ?><li class="list-group-item py-3"><?= Html::a(Html::encode($model->request_no), ['view', 'id' => $model->id], ['class' => 'fw-semibold text-decoration-none']) ?><div class="mt-1 fw-semibold"><?= Html::encode($profile['name'] ?? 'บุคลากร #' . $model->emp_id) ?></div><div class="small text-body-secondary"><?= Html::encode(implode(' · ', array_filter([$profile['gender'] ?? null, $profile['position'] ?? null, $profile['department'] ?? null]))) ?></div><div class="small mt-1"><?= Html::encode(HousingRequest::statusOptions()[$model->status] ?? '') ?></div></li><?php endforeach; ?></ul>
<?php if (!$dataProvider->totalCount): ?><div class="text-center py-5"><div class="fw-semibold">ยังไม่มีคำขอ</div></div><?php endif; ?>
</div><div class="card-footer bg-body"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div>
</div></div>
