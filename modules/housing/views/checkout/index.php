<?php

use app\components\widgets\DataSummaryWidget;
use app\modules\housing\models\Checkout;
use yii\helpers\Html;

$this->title = 'การส่งคืนบ้านพัก';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'checkout']) ?><?php $this->endBlock();
$models = $dataProvider->getModels();
?>
<div class="container-fluid py-3"><div class="card shadow-sm"><div class="card-body p-0">
<div class="d-none d-lg-block"><table class="table table-hover align-middle mb-0"><thead><tr><th>เลขที่</th><th>ผู้พัก</th><th>บ้านพัก/ห้อง</th><th>วันที่ต้องการคืน</th><th>สถานะ</th><th></th></tr></thead><tbody>
<?php foreach ($models as $model): $o=$model->occupancy; ?><tr><td><strong><?= Html::encode($model->checkout_no) ?></strong></td><td><?= Html::encode($model->resident_name) ?></td><td><?= Html::encode(implode(' / ',array_filter([$o?->unit?->building?->name,$o?->unit?->name,$o?->room?->name]))) ?></td><td><?= Yii::$app->formatter->asDate($model->requested_date,'php:d/m/Y') ?></td><td><span class="badge bg-secondary-subtle text-secondary-emphasis"><?= Html::encode(Checkout::statusOptions()[$model->status]) ?></span></td><td class="text-end"><?= Html::a('รายละเอียด',['view','id'=>$model->id],['class'=>'btn btn-sm btn-outline-primary']) ?></td></tr><?php endforeach; ?>
<?php if ($models === []): ?><tr><td colspan="6" class="text-center py-5"><strong>ยังไม่มีคำขอคืนบ้านพัก</strong><div class="small text-muted mt-1">คำขอจากผู้พักจะแสดงที่หน้านี้</div></td></tr><?php endif; ?>
</tbody></table></div>
<ul class="list-group list-group-flush d-lg-none"><?php foreach ($models as $model): ?><li class="list-group-item p-3"><div class="d-flex justify-content-between gap-2"><strong><?= Html::encode($model->checkout_no) ?></strong><span class="badge bg-secondary-subtle text-secondary-emphasis"><?= Html::encode(Checkout::statusOptions()[$model->status]) ?></span></div><div class="mt-2"><?= Html::encode($model->resident_name) ?></div><?= Html::a('ดูรายละเอียด',['view','id'=>$model->id],['class'=>'btn btn-sm btn-outline-primary mt-2']) ?></li><?php endforeach; ?></ul>
</div><div class="card-footer bg-body"><?= DataSummaryWidget::widget(['dataProvider'=>$dataProvider]) ?></div></div></div>
