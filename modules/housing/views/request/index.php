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
<div class="card-header bg-body"><div class="fw-semibold">รายการคำขอ</div><div class="small text-muted">ตรวจสอบ บันทึกผลมติ และจัดสรรที่พัก</div></div>
<div class="card-body p-0">
<div class="d-none d-lg-block"><table class="table table-hover align-middle mb-0"><thead><tr><th>เลขคำขอ</th><th>ผู้ขอ</th><th>ประเภท</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead><tbody>
<?php foreach ($dataProvider->models as $model): ?><tr><td class="fw-semibold"><?= Html::encode($model->request_no) ?></td><td><?= Html::encode($employeeNames[(int)$model->emp_id] ?? 'บุคลากร #' . $model->emp_id) ?></td><td><?= Html::encode(HousingRequest::typeOptions()[$model->request_type] ?? '') ?></td><td><span class="badge bg-body-secondary text-body"><?= Html::encode(HousingRequest::statusOptions()[$model->status] ?? $model->status) ?></span></td><td class="text-end"><?= Html::a('ดำเนินการ', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<ul class="list-group list-group-flush d-lg-none"><?php foreach ($dataProvider->models as $model): ?><li class="list-group-item py-3"><?= Html::a(Html::encode($model->request_no), ['view', 'id' => $model->id], ['class' => 'fw-semibold text-decoration-none']) ?><div class="small mt-1"><?= Html::encode($employeeNames[(int)$model->emp_id] ?? 'บุคลากร #' . $model->emp_id) ?></div><div class="small text-muted"><?= Html::encode(HousingRequest::statusOptions()[$model->status] ?? '') ?></div></li><?php endforeach; ?></ul>
<?php if (!$dataProvider->totalCount): ?><div class="text-center py-5"><div class="fw-semibold">ยังไม่มีคำขอ</div></div><?php endif; ?>
</div><div class="card-footer bg-body"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div>
</div></div>
