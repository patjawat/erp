<?php use yii\helpers\Html; use app\modules\hr\services\EngagementService as E; $this->title='แบบสอบถามความผูกพัน'; ?>
<?= $this->render('_nav') ?>
<div class="d-flex flex-wrap justify-content-between gap-2 mb-3"><h2 class="h5">แบบสอบถามและเวอร์ชัน</h2><?= Html::a('สร้างแบบสอบถาม',['template'],['class'=>'btn btn-primary']) ?></div>
<p class="text-body-secondary">แบบที่เผยแพร่แล้วแก้ข้อคำถามไม่ได้ หากต้องปรับให้สร้างเวอร์ชันใหม่ การเปิดแบบให้บุคลากรตอบต้องสร้างรอบสำรวจแยกต่างหาก</p>
<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>ชื่อแบบ</th><th>เวอร์ชัน</th><th>สถานะ</th><th>ดำเนินการ</th></tr></thead><tbody>
<?php foreach($versions as $v): ?><tr><td><?= Html::encode($v['title']) ?></td><td><?= (int)$v['version_no'] ?></td><td><?= E::statusLabel($v['status']) ?></td><td><div class="d-flex flex-wrap gap-2">
<?= Html::a($v['status']==='draft'?'ตรวจ / แก้ไข':'ดูคำถาม',['template','id'=>$v['id']],['class'=>'btn btn-sm btn-outline-primary']) ?>
<?php if($v['status']==='draft'): ?><?= Html::beginForm(['publish-template','id'=>$v['id']],'post') ?><button class="btn btn-sm btn-primary">เผยแพร่และล็อกแบบ</button><?= Html::endForm() ?><?php endif ?>
<?= Html::beginForm(['clone-template','id'=>$v['id']],'post') ?><button class="btn btn-sm btn-outline-secondary">สร้างเวอร์ชันถัดไป</button><?= Html::endForm() ?>
</div></td></tr><?php endforeach ?>
<?php if(!$versions): ?><tr><td colspan="4" class="text-center text-body-secondary p-4">ยังไม่มีแบบสอบถาม กรุณาใช้เครื่องมือที่ HR และทีมคุณภาพตรวจสอบแล้ว</td></tr><?php endif ?>
</tbody></table></div></div>
