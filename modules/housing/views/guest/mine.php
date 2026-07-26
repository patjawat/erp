<?php
use app\components\widgets\DataSummaryWidget;
use yii\helpers\Html;
$this->title = 'บุคคลภายนอกเข้าพัก';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
?>
<div class="container-fluid py-3">
<?php if (Yii::$app->session->hasFlash('success')): ?><div class="alert alert-success"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div><?php endif; ?>
<div class="card border-0 shadow-sm"><div class="card-header bg-body d-flex justify-content-between align-items-center"><div><div class="fw-semibold">คำขอของฉัน</div><div class="small text-body-secondary">แจ้งบุคคลภายนอกก่อนเข้าพัก</div></div><?php if ($context['mode'] === 'resident'): ?><?= Html::a('แจ้งบุคคลภายนอก', ['create'], ['class' => 'btn btn-primary btn-sm']) ?><?php endif; ?></div>
<div class="card-body p-0"><ul class="list-group list-group-flush"><?php foreach ($dataProvider->models as $model): ?><li class="list-group-item py-3"><div class="d-flex justify-content-between gap-2"><strong><?= Html::encode($model->guest_name) ?></strong><span class="badge bg-body-secondary text-body"><?= Html::encode($model->status) ?></span></div><div class="small text-body-secondary mt-1"><?= Html::encode($model->start_date . ' ถึง ' . $model->end_date) ?> · <?= Html::encode($model->request_no) ?></div></li><?php endforeach; ?></ul><?php if (!$dataProvider->totalCount): ?><div class="text-center py-5"><div class="fw-semibold">ยังไม่มีคำขอบุคคลภายนอก</div></div><?php endif; ?></div>
<div class="card-footer bg-body"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div></div></div>
