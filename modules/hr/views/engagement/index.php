<?php
use yii\helpers\Html;
use app\components\widgets\DataSummaryWidget;
use app\modules\hr\services\EngagementService as E;
$this->title='รอบสำรวจความผูกพัน';
?>
<?= $this->render('_nav') ?>
<div class="card border-0 shadow-sm rounded-4">
<div class="card-header bg-body border-bottom p-3 d-flex flex-wrap justify-content-between gap-2"><h2 class="h5 mb-0">รอบสำรวจความผูกพัน</h2><?= Html::a('สร้างรอบสำรวจ',['create'],['class'=>'btn btn-primary']) ?></div>
<div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>รอบสำรวจ</th><th>ปีงบประมาณ</th><th>ช่วงรับคำตอบ (เวลาไทย)</th><th>สถานะ</th><th>ดำเนินการ</th></tr></thead><tbody>
<?php foreach($provider->getModels() as $r): ?><tr><td><?= Html::encode($r['title']) ?></td><td><?= (int)$r['fiscal_year'] ?></td><td class="small"><?= E::localDate($r['open_at']) ?><br><?= E::localDate($r['close_at']) ?></td><td><span class="badge bg-primary-subtle text-primary-emphasis"><?= E::statusLabel($r['status']) ?></span></td><td><?= Html::a('จัดการรอบ',['round','id'=>$r['id']],['class'=>'btn btn-sm btn-outline-primary']) ?></td></tr><?php endforeach ?>
<?php if(!$provider->getTotalCount()): ?><tr><td colspan="5" class="p-4 text-center text-body-secondary">ยังไม่มีรอบสำรวจ เริ่มจากสร้างและเผยแพร่แบบสอบถาม แล้วจึงสร้างรอบ</td></tr><?php endif ?>
</tbody></table></div><div class="card-footer bg-body p-3"><?= DataSummaryWidget::widget(['dataProvider'=>$provider]) ?></div></div>
