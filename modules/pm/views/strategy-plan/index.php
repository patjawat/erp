<?php
use yii\helpers\Html;
use app\components\widgets\DataSummaryWidget;
$this->title = 'ทะเบียนแผนยุทธศาสตร์';
$canManage = Yii::$app->user->can('pmStrategyManage');
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'strategy']) ?><?php $this->endBlock();
?>
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-3 mb-4">
 <div><h2 class="h5 mb-1">ชุดแผนทั้งหมด</h2><p class="text-muted mb-0">แหล่งข้อมูลกลางสำหรับวิสัยทัศน์ พันธกิจ และโครงสร้างยุทธศาสตร์แต่ละรุ่น</p></div>
 <?php if ($canManage): ?><?= Html::a('<i data-lucide="plus" class="me-1"></i> สร้างชุดแผน', ['create'], ['class'=>'btn btn-primary']) ?><?php endif; ?>
</div>
<div class="card border-0 shadow-sm mb-3"><div class="card-body">
<?= Html::beginForm(['index'], 'get', ['class'=>'row g-3 align-items-end']) ?>
<div class="col-12 col-md-6"><label class="form-label fw-semibold">ค้นหา</label><?= Html::textInput('q',$q,['class'=>'form-control','placeholder'=>'รหัสหรือชื่อแผนยุทธศาสตร์']) ?></div>
<div class="col-12 col-md-3"><label class="form-label fw-semibold">สถานะ</label><?= Html::dropDownList('status',$status,\app\modules\pm\models\StrategyPlan::statusList(),['class'=>'form-select','prompt'=>'ทุกสถานะ']) ?></div>
<div class="col-12 col-md-auto d-flex gap-2"><?= Html::submitButton('ค้นหา',['class'=>'btn btn-primary']) ?><?= Html::a('ล้างค่า',['index'],['class'=>'btn btn-outline-secondary']) ?></div>
<?= Html::endForm() ?>
</div></div>
<div class="card border-0 shadow-sm overflow-hidden">
<div class="card-body p-0"><div class="table-responsive d-none d-md-block"><table class="table align-middle mb-0"><thead class="table-light"><tr><th class="ps-4">ชุดแผน</th><th>ช่วงปี</th><th>สถานะ</th><th class="text-end pe-4">จัดการ</th></tr></thead><tbody>
<?php foreach ($dataProvider->models as $model): ?><tr><td class="ps-4"><div class="fw-semibold"><?= Html::encode($model->name) ?></div><div class="small text-muted"><?= Html::encode($model->code) ?> · รุ่น <?= (int)$model->version ?></div></td><td><?= (int)$model->start_year ?>–<?= (int)$model->end_year ?></td><td><span class="badge <?= $model->status==='published'?'bg-success-subtle text-success':'bg-secondary-subtle text-secondary' ?>"><?= Html::encode($model::statusList()[$model->status]??$model->status) ?></span></td><td class="text-end pe-4"><?= Html::a('เปิดดู',['view','id'=>$model->id],['class'=>'btn btn-sm btn-outline-primary']) ?></td></tr><?php endforeach; ?>
<?php if (!$dataProvider->count): ?><tr><td colspan="4" class="text-center text-muted py-5">ยังไม่มีชุดแผนยุทธศาสตร์</td></tr><?php endif; ?>
</tbody></table></div><div class="d-md-none p-3 d-grid gap-3"><?php foreach($dataProvider->models as $model): ?><article class="border rounded-3 p-3"><div class="d-flex justify-content-between align-items-start gap-2"><div><div class="fw-semibold"><?= Html::encode($model->name) ?></div><div class="small text-muted mt-1"><?= Html::encode($model->code) ?> · รุ่น <?= (int)$model->version ?></div></div><span class="badge <?= $model->status==='published'?'bg-success-subtle text-success':'bg-secondary-subtle text-secondary' ?>"><?= Html::encode($model::statusList()[$model->status]??$model->status) ?></span></div><div class="small text-muted my-3">พ.ศ. <?= (int)$model->start_year ?>–<?= (int)$model->end_year ?></div><?= Html::a('เปิดดูรายละเอียด',['view','id'=>$model->id],['class'=>'btn btn-outline-primary w-100']) ?></article><?php endforeach; ?><?php if(!$dataProvider->count): ?><div class="text-center text-muted py-4">ยังไม่มีชุดแผนยุทธศาสตร์</div><?php endif; ?></div></div><div class="card-footer bg-body px-4 py-3"><?= DataSummaryWidget::widget(['dataProvider'=>$dataProvider]) ?></div></div>
