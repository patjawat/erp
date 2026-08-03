<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\DataSummaryWidget;
use app\modules\hr\models\ProbationCase;
$this->title = 'ประเมินช่วงทดลองงาน'; echo $this->render('_styles');
$this->beginBlock('page-title'); echo Html::encode($this->title); $this->endBlock();
$models = $dataProvider->getModels();
$badge = fn($s) => match($s){'completed_hire'=>'success','completed_no_hire'=>'danger','waiting_acknowledgement'=>'primary','waiting_decision'=>'warning','cancelled'=>'secondary',default=>'warning'};
?>
<div class="probation-shell">
 <header class="probation-head"><div><h1><?= Html::encode($this->title) ?></h1><p class="text-body-secondary">ติดตามการประเมินเดือนที่ 1, 2 และ 3 พร้อมขั้นตอนที่ต้องดำเนินการถัดไป</p></div><div class="d-flex gap-2"><?php if($isHr): ?><?= Html::a('จัดการ Template',['/hr/probation-template/index'],['class'=>'btn btn-outline-secondary']) ?><?= Html::a('มอบหมายการประเมิน',['assign'],['class'=>'btn btn-primary']) ?><?php endif ?></div></header>
 <section class="probation-card overflow-hidden">
  <form class="probation-toolbar" method="get" action="<?= Url::to(['index']) ?>"><input class="form-control" name="q" value="<?= Html::encode($q) ?>" placeholder="ค้นหาชื่อหรือรหัสบุคลากร" aria-label="ค้นหาบุคลากร"><select class="form-select" name="status" aria-label="กรองสถานะ"><option value="">ทุกสถานะ</option><?php foreach(ProbationCase::statusOptions() as $k=>$v): ?><option value="<?= $k ?>" <?= $status===$k?'selected':'' ?>><?= Html::encode($v) ?></option><?php endforeach ?></select><button class="btn btn-outline-primary">ค้นหา</button></form>
  <?php if(!$models): ?><div class="probation-empty"><h2 class="h5">ยังไม่มีรายการประเมินทดลองงาน</h2><p class="text-body-secondary">เมื่อ HR มอบหมายการประเมิน รายการและงานถัดไปจะแสดงที่นี่</p><?php if($isHr): ?><?= Html::a('มอบหมายการประเมิน',['assign'],['class'=>'btn btn-primary']) ?><?php endif ?></div><?php else: ?>
  <div class="d-none d-lg-block"><table class="table probation-table"><thead><tr><th>บุคลากร</th><th>วิชาชีพ</th><th>วันที่เริ่มงาน</th><th>รอบปัจจุบัน</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead><tbody><?php foreach($models as $m): $round=$m->currentRound(); ?><tr><td><div class="probation-person"><img class="probation-avatar" src="<?= Html::encode($m->employee->ShowAvatar()) ?>" alt=""><span><strong><?= Html::encode($m->employee->fullname) ?></strong><small class="text-body-secondary">รหัส <?= $m->employee_id ?></small></span></div></td><td><?= Html::encode($m->employee->employeePositionGroup->title ?? '—') ?></td><td class="probation-numeric"><?= Yii::$app->formatter->asDate($m->start_date,'php:d/m/Y') ?></td><td><?= $round?'เดือนที่ '.$round->month_no:'ครบ 3 เดือน' ?></td><td><span class="badge bg-<?= $badge($m->status) ?>-subtle text-<?= $badge($m->status) ?>-emphasis"><?= Html::encode($m->statusLabel) ?></span></td><td class="text-end"><?= Html::a('ดูรายละเอียด',['view','id'=>$m->id],['class'=>'btn btn-sm btn-outline-primary']) ?></td></tr><?php endforeach ?></tbody></table></div>
  <ul class="probation-mobile d-lg-none" role="list"><?php foreach($models as $m): $round=$m->currentRound(); ?><li><div class="d-flex justify-content-between gap-2"><strong><?= Html::encode($m->employee->fullname) ?></strong><span class="badge bg-<?= $badge($m->status) ?>-subtle text-<?= $badge($m->status) ?>-emphasis"><?= Html::encode($m->statusLabel) ?></span></div><div class="text-body-secondary small mt-2"><?= Html::encode($m->employee->employeePositionGroup->title ?? 'ไม่ระบุวิชาชีพ') ?> · <?= $round?'เดือนที่ '.$round->month_no:'ครบ 3 เดือน' ?></div><div class="probation-mobile-actions d-grid mt-3"><?= Html::a('ดูรายละเอียด',['view','id'=>$m->id],['class'=>'btn btn-outline-primary']) ?></div></li><?php endforeach ?></ul>
  <footer class="card-footer bg-body"><?= DataSummaryWidget::widget(['dataProvider'=>$dataProvider]) ?></footer><?php endif ?>
 </section>
</div>
