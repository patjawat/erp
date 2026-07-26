<?php

use app\components\widgets\DataSummaryWidget;
use yii\helpers\Html;

$this->title = 'ประวัติรับชำระ';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'utility']) ?><?php $this->endBlock();
$models = $dataProvider->getModels();
?>
<div class="container-fluid py-3">
<div class="d-flex justify-content-between mb-3"><?= Html::a('กลับค่าใช้จ่ายประจำเดือน', ['/housing/utility/monthly'], ['class'=>'btn btn-outline-secondary']) ?></div>
<div class="card shadow-sm"><div class="card-body p-0"><div class="d-none d-lg-block"><table class="table table-hover align-middle mb-0"><thead><tr><th>ใบเสร็จ</th><th>วันที่รับ</th><th>ผู้ชำระ</th><th>เดือน</th><th>วิธีชำระ</th><th class="text-end">จำนวนเงิน</th><th>สถานะ</th><th></th></tr></thead><tbody>
<?php foreach($models as $model): $invoice=$model->allocations[0]->invoice??null; ?><tr><td><strong><?= Html::encode($model->receipt?->receipt_no ?: '—') ?></strong></td><td><?= Yii::$app->formatter->asDatetime($model->paid_at,'php:d/m/Y H:i') ?></td><td><?= Html::encode($invoice?->monthlyAccount?->payer_name ?: 'รหัส '.$model->payer_emp_id) ?></td><td><?= Html::encode($invoice?->monthlyAccount?->period?->name ?: '—') ?></td><td><?= $model->payment_method==='transfer'?'เงินโอน':'เงินสด' ?></td><td class="text-end fw-semibold"><?= Yii::$app->formatter->asDecimal($model->amount,2) ?></td><td><span class="badge <?= $model->status==='confirmed'?'bg-success-subtle text-success-emphasis':'bg-danger-subtle text-danger-emphasis' ?>"><?= $model->status==='confirmed'?'รับชำระแล้ว':'ยกเลิก' ?></span></td><td class="text-end"><?= Html::a('รายละเอียด',['view','id'=>$model->id],['class'=>'btn btn-sm btn-outline-primary']) ?></td></tr><?php endforeach; ?>
<?php if(!$models):?><tr><td colspan="8" class="text-center py-5"><strong>ยังไม่มีประวัติรับชำระ</strong><div class="small text-muted mt-1">รายการจะปรากฏหลังรับชำระจากหน้าค่าใช้จ่ายประจำเดือน</div></td></tr><?php endif;?>
</tbody></table></div>
<ul class="list-group list-group-flush d-lg-none"><?php foreach($models as $model):?><li class="list-group-item p-3"><div class="d-flex justify-content-between"><strong><?= Html::encode($model->receipt?->receipt_no) ?></strong><span><?= Yii::$app->formatter->asDecimal($model->amount,2) ?> บาท</span></div><div class="small text-muted mt-1"><?= Yii::$app->formatter->asDatetime($model->paid_at,'php:d/m/Y H:i') ?></div><?= Html::a('รายละเอียด',['view','id'=>$model->id],['class'=>'btn btn-sm btn-outline-primary mt-2']) ?></li><?php endforeach;?></ul>
</div><div class="card-footer bg-body"><?= DataSummaryWidget::widget(['dataProvider'=>$dataProvider]) ?></div></div></div>
