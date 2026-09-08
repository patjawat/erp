<?php use yii\helpers\Html; use app\modules\hr\services\EngagementService as E; $this->title=$round['title']; ?>
<?= $this->render('_nav') ?>
<div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-body p-4"><h2 class="h5"><?= Html::encode($round['title']) ?></h2><p><span class="badge bg-primary-subtle text-primary-emphasis"><?= E::statusLabel($round['status']) ?></span> · <?= E::localDate($round['open_at']) ?> – <?= E::localDate($round['close_at']) ?> (เวลาไทย)</p>
<?php if($round['status']==='draft'): ?><p>ผู้มีสิทธิ์ตามทะเบียนขณะตรวจสอบ <strong><?= number_format(array_sum($preview)) ?> คน</strong> รายชื่อจะถูกตรึงเมื่อเปิดรอบ ผลวิเคราะห์รุ่นนี้แสดงเฉพาะภาพรวมองค์กร</p>
<div class="table-responsive"><table class="table"><thead><tr><th>หน่วยงาน</th><th class="text-end">คน</th></tr></thead><tbody><?php foreach($preview as $label=>$count): ?><tr><td><?= Html::encode($label) ?></td><td class="text-end"><?= number_format($count) ?></td></tr><?php endforeach ?></tbody></table></div>
<?php else: ?><p>ผู้มีสิทธิ์ <?= number_format($total) ?> คน · ส่งคำตอบแล้ว <?= number_format($submitted) ?> คน · <?= $total?number_format($submitted*100/$total,1).'%' : 'ยังไม่มีข้อมูล' ?></p><?php endif ?>
<p class="small text-body-secondary">คำชี้แจง: <?= nl2br(Html::encode($round['privacy_notice'])) ?></p>
<div class="d-flex flex-wrap gap-2">
<?php if(!empty($purged)): ?><p class="alert alert-secondary">ข้อมูลรายชื่อและคำตอบดิบถูกลบตามอายุการเก็บแล้ว จำนวนจากทะเบียนคำตอบด้านบนไม่ใช้แทนผลเดิม กรุณาดูผลรวมที่เผยแพร่ไว้</p><?php endif ?>
<?php if($round['status']==='draft'): ?><?= Html::a('แก้ไขร่างและนโยบาย',['create','id'=>$round['id']],['class'=>'btn btn-outline-secondary']) ?><?php endif ?>
<?php if($round['status']==='draft'): ?><?= Html::beginForm(['transition','id'=>$round['id']],'post') ?><?= Html::hiddenInput('operation','open') ?><button class="btn btn-primary" <?= !$round['policy_confirmed']?'disabled':'' ?>>ยืนยันรายชื่อและเปิดรอบ</button><?= Html::endForm() ?><?php if(!$round['policy_confirmed']): ?><p class="text-warning-emphasis">ร่างนี้ยังไม่ยืนยันนโยบาย ให้แก้ไขร่างและยืนยันนโยบายก่อนเปิดรอบ</p><?php endif ?><?php endif ?>
<?php if($round['status']==='open'): ?><?= Html::beginForm(['transition','id'=>$round['id']],'post') ?><?= Html::hiddenInput('operation','close') ?><button class="btn btn-outline-primary">ปิดรับคำตอบ</button><?= Html::endForm() ?><?php endif ?>
<?php if(Yii::$app->user->can('engagementViewAnalytics')||Yii::$app->user->can('admin')): ?><?= Html::a('ตรวจและเผยแพร่ผล',['report','id'=>$round['id']],['class'=>'btn btn-outline-primary']) ?><?php endif ?>
</div></div></div>
