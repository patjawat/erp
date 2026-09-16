<?php
use yii\helpers\Html;
use app\modules\hr\services\EngagementService as E;
$this->title='ผลวิเคราะห์ความผูกพัน'; $options=[];
foreach($rounds as $r) $options[$r['id']]=$r['title'].' · '.$r['fiscal_year'];
?>
<?= $this->render('_nav') ?>
<div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-body"><h2 class="h5">ผลวิเคราะห์ความผูกพัน</h2>
<?= Html::beginForm(['report'],'get',['class'=>'d-flex flex-column flex-sm-row gap-2 align-items-sm-end']) ?><div class="flex-grow-1"><label for="report-round" class="form-label">รอบสำรวจ</label><?= Html::dropDownList('id',$report['round']['id']??null,$options,['id'=>'report-round','class'=>'form-select','prompt'=>'เลือกรอบสำรวจ']) ?></div><button class="btn btn-primary">แสดงผล</button><?= Html::endForm() ?>
<p class="small text-body-secondary mt-3 mb-0">แสดงภาพรวมองค์กรตามรายชื่อที่ตรึงในรอบ ไม่ใช้ตัวกรองเพศ อายุ หรือหน่วยงานจาก dashboard บุคลากร</p></div></div>
<?php if(!$report): ?><p class="alert alert-secondary">ยังไม่มีรอบที่ปิดรับคำตอบ</p><?php elseif($report['round']['status']==='closed'): ?>
<div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4"><h3 class="h5">ตรวจสอบก่อนเผยแพร่ผล</h3><p>ระบบจะคำนวณคะแนนตามแบบที่ล็อกไว้ ปกปิดผลที่มีผู้ตอบใช้คำนวณต่ำกว่า <?= (int)$report['round']['minimum_group_size'] ?> คน และเผยแพร่เฉพาะผลรวม การเผยแพร่ครั้งนี้ไม่สามารถแก้คำตอบย้อนหลังได้</p>
<?= Html::beginForm(['transition','id'=>$report['round']['id']],'post') ?><?= Html::hiddenInput('operation','publish') ?><button class="btn btn-primary">คำนวณและเผยแพร่ผลรวม</button><?= Html::endForm() ?></div></div>
<?php else: ?>
<?= $this->render('_results',['report'=>$report,'trend'=>$trend]) ?>
<div class="d-flex flex-wrap gap-2 mt-3">
<?php if(Yii::$app->user->can('engagementExportAggregate')||Yii::$app->user->can('admin')): ?><?= Html::a('ส่งออกผลรวม CSV',['export','id'=>$report['round']['id']],['class'=>'btn btn-outline-primary']) ?><?php endif ?>
<?php if(Yii::$app->user->can('engagementManageAction')||Yii::$app->user->can('admin')): ?><?= Html::a('สร้างแผนปรับปรุง',['action','round_id'=>$report['round']['id']],['class'=>'btn btn-primary']) ?><?php endif ?>
</div><?php endif ?>
