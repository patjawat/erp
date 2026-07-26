<?php
use app\components\widgets\DataSummaryWidget;
use app\modules\housing\models\MaintenanceRequest;
use yii\helpers\Html;

$this->title = 'ทะเบียนแจ้งซ่อมบ้านพัก';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'maintenance']) ?><?php $this->endBlock();
?>
<style>
.maintenance-page{--m-bg:#f7fafc;--m-border:#dce6f0;--m-ink:#26384a;--m-muted:#60758a;--m-primary:#4c84c6;color:var(--m-ink)}
.maintenance-page .soft-panel{background:#fff;border:1px solid var(--m-border);border-radius:.8rem}
.maintenance-page .summary-band{display:flex;gap:2rem;flex-wrap:wrap;padding:1rem 1.25rem;background:var(--m-bg);border-bottom:1px solid var(--m-border)}
.maintenance-page .status-pill,.maintenance-page .priority-pill{display:inline-flex;padding:.25rem .6rem;border-radius:999px;font-size:.8rem;font-weight:600}
.status-new{color:#356b9d;background:#eef5ff}.status-in_progress{color:#8a6415;background:#fff4dc}.status-completed{color:#287a51;background:#e8f5ee}.status-unable,.status-cancelled{color:#a23a43;background:#fcebec}
.priority-normal{color:#60758a;background:#f1f4f7}.priority-urgent{color:#8a6415;background:#fff4dc}.priority-emergency{color:#a23a43;background:#fcebec}
.maintenance-page .btn-primary{background:var(--m-primary);border-color:var(--m-primary)}
@media(max-width:767.98px){.maintenance-page .table-responsive table{min-width:900px}.maintenance-actions{width:100%}.maintenance-actions .btn{flex:1;min-height:44px}}
</style>
<div class="container-fluid py-3 maintenance-page">
    <?php foreach (['success', 'warning'] as $flash): if (Yii::$app->session->hasFlash($flash)): ?><div class="alert alert-<?= $flash ?>"><?= Html::encode(Yii::$app->session->getFlash($flash)) ?></div><?php endif; endforeach; ?>
    <div class="soft-panel overflow-hidden">
        <div class="p-3 d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div><h1 class="h5 mb-1">ประวัติแจ้งซ่อมบ้านพักและแฟลต</h1><div class="small text-muted">บันทึกปัญหา ผลการซ่อม และค่าใช้จ่ายรวม</div></div>
            <div class="d-flex gap-2 maintenance-actions">
                <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex']) ?><?= Html::dropDownList('building_id', $buildingId, $buildingOptions, ['prompt' => 'ทุกบ้านพัก/แฟลต', 'class' => 'form-select form-select-sm', 'onchange' => 'this.form.submit()']) ?><?= Html::endForm() ?>
                <?= Html::a('<i data-lucide="plus"></i> แจ้งปัญหา', ['create', 'building_id' => $buildingId], ['class' => 'btn btn-primary btn-sm open-modal', 'data-size' => 'modal-xl']) ?>
            </div>
        </div>
        <div class="summary-band"><div><div class="small text-muted">งานที่ยังไม่ปิด</div><strong><?= $openCount ?> รายการ</strong></div><div><div class="small text-muted">ค่าใช้จ่ายสะสม</div><strong><?= Yii::$app->formatter->asDecimal($totalExpense, 2) ?> บาท</strong></div></div>
        <?php if ($dataProvider->models): ?><div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead><tr><th>เลขที่</th><th>บ้านพัก/ตำแหน่ง</th><th>ปัญหา</th><th>วันที่แจ้ง</th><th>ความเร่งด่วน</th><th>สถานะ</th><th class="text-end">ค่าใช้จ่าย</th><th class="text-end">จัดการ</th></tr></thead>
            <tbody><?php foreach ($dataProvider->models as $model): ?><tr>
                <td><strong><?= Html::encode($model->ticket_no) ?></strong></td>
                <td><?= Html::encode($model->building->name ?? '—') ?><div class="small text-muted"><?= Html::encode($model->location_note ?: 'ไม่ระบุจุด') ?></div></td>
                <td><strong><?= Html::encode($model->title) ?></strong><div class="small text-muted"><?= Html::encode(MaintenanceRequest::reporterTypeOptions()[$model->reporter_type] ?? '') ?> · <?= Html::encode(MaintenanceRequest::scopeOptions()[$model->problem_scope] ?? '') ?></div><div class="small text-muted text-truncate" style="max-width:280px"><?= Html::encode($model->description) ?></div></td>
                <td><?= Yii::$app->formatter->asDatetime($model->reported_at, 'php:d/m/Y H:i') ?></td>
                <td><span class="priority-pill priority-<?= Html::encode($model->priority) ?>"><?= Html::encode(MaintenanceRequest::priorityOptions()[$model->priority] ?? $model->priority) ?></span></td>
                <td><span class="status-pill status-<?= Html::encode($model->status) ?>"><?= Html::encode(MaintenanceRequest::statusOptions()[$model->status] ?? $model->status) ?></span></td>
                <td class="text-end"><?= Yii::$app->formatter->asDecimal($model->expense_amount, 2) ?></td>
                <td class="text-end text-nowrap"><?= Html::a('รายละเอียด', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-info']) ?> <?= Html::a('ปรับปรุง', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data-size' => 'modal-xl']) ?></td>
            </tr><?php endforeach; ?></tbody>
        </table></div><?php else: ?><div class="text-center py-5"><i data-lucide="wrench"></i><div class="fw-semibold mt-2">ยังไม่มีประวัติแจ้งซ่อม</div><div class="small text-muted mt-1">เมื่อพบปัญหา ให้กด “แจ้งปัญหา” เพื่อเริ่มบันทึกประวัติ</div></div><?php endif; ?>
        <div class="p-3 border-top"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div>
    </div>
</div>
