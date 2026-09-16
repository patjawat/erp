<?php use yii\helpers\Html; use app\modules\hr\services\EngagementService as E; use app\components\widgets\DataSummaryWidget; $this->title='แผนปรับปรุงความผูกพัน'; ?>
<?= $this->render('_nav') ?>
<h2 class="h5">แผนปรับปรุงจากผลสำรวจ</h2><p class="text-body-secondary">สร้างแผนจากหน้าผลวิเคราะห์ เก็บประเด็นภาพรวม ไม่คัดลอกข้อมูลที่ระบุผู้ตอบได้</p>
<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>ประเด็น</th><th>กำหนดเสร็จ</th><th>สถานะ</th><th>ติดตาม</th></tr></thead><tbody>
<?php foreach($provider->getModels() as $a): ?><tr><td><?= Html::encode($a['issue_summary']) ?></td><td><?= Html::encode($a['due_date']) ?></td><td><?= E::statusLabel($a['status']) ?></td><td><?= Html::a('บันทึกผล',['action','id'=>$a['id']],['class'=>'btn btn-sm btn-outline-primary']) ?></td></tr><?php endforeach ?>
<?php if(!$provider->getTotalCount()): ?><tr><td colspan="4" class="p-4 text-center text-body-secondary">ยังไม่มีแผนปรับปรุง</td></tr><?php endif ?>
</tbody></table></div><div class="card-footer bg-body p-3"><?= DataSummaryWidget::widget(['dataProvider'=>$provider]) ?></div></div>
