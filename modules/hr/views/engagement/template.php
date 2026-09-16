<?php
use yii\helpers\Html;
$this->title=$version?'ตรวจแบบสอบถาม':'สร้างแบบสอบถาม';
$locked=$version && $version['status']!=='draft';
$groups=['engagement'=>'ผลความผูกพัน','leadership'=>'หัวหน้างาน','recognition'=>'การยอมรับและชื่นชม','development'=>'โอกาสพัฒนา','role'=>'ความชัดเจนของบทบาท','workload'=>'ภาระงาน','environment'=>'สภาพแวดล้อมการทำงาน'];
$existing=[]; foreach($dimensions as $d) { $existing[$d['code']]=$d; $groups[$d['code']]=$d['title']; }
$posted=Yii::$app->request->post('dimensions',[]);
?>
<?= $this->render('_nav') ?>
<?= Html::beginForm('','post',['class'=>'card border-0 shadow-sm rounded-4']) ?><div class="card-body p-3 p-md-4">
<h2 class="h5">แบบสอบถามความผูกพัน · สเกล 1–5</h2>
<p class="text-body-secondary">พิมพ์หนึ่งคำถามต่อบรรทัด ใช้คำถามในทิศทางบวก เช่น คะแนนสูงหมายถึงผลที่ดี แต่ละข้อมีตัวเลือกไม่เกี่ยวข้อง / ไม่ประสงค์ตอบ ด้านที่ไม่มีคำถามจะไม่ถูกใช้</p>
<p class="small text-body-secondary">สูตร: เฉลี่ยคำตอบรายด้านต่อคนเมื่อมีคำตอบอย่างน้อย 80% แล้วเฉลี่ยผู้ตอบ น้ำหนักแต่ละด้านเท่ากัน คะแนนรวมใช้เฉพาะด้านผลความผูกพัน ไม่รวมปัจจัยสนับสนุน</p>
<label class="form-label" for="template-title">ชื่อแบบสำรวจ</label><?= Html::textInput('title',Yii::$app->request->post('title',$title),['id'=>'template-title','class'=>'form-control mb-3','required'=>true,'maxlength'=>255,'readonly'=>(bool)$version]) ?>
<?php foreach($groups as $code=>$label): $d=$existing[$code]??null; $value=$posted[$code]['questions']??($d?implode("\n",array_column($d['questions'],'prompt')):''); ?>
<fieldset class="border-top pt-3 mt-3"><legend class="h6"><?= Html::encode($label) ?> <span class="small text-body-secondary"><?= $code==='engagement'?'(ใช้คำนวณคะแนนรวม)':'(ปัจจัยสนับสนุน)' ?></span></legend>
<?= Html::hiddenInput('dimensions['.$code.'][title]',$label) ?>
<label for="questions-<?= Html::encode($code) ?>" class="form-label small">คำถาม<?= $code==='engagement'?' (ต้องมีอย่างน้อย 1 ข้อ)':'' ?></label>
<?= Html::textarea('dimensions['.$code.'][questions]',$value,['id'=>'questions-'.$code,'class'=>'form-control','rows'=>3,'readonly'=>$locked,'required'=>$code==='engagement']) ?>
</fieldset><?php endforeach ?>
<div class="d-flex gap-2 mt-4"><?php if(!$locked): ?><button class="btn btn-primary">บันทึกแบบร่าง</button><?php endif ?><?= Html::a('กลับรายการ',['templates'],['class'=>'btn btn-outline-secondary']) ?></div>
</div><?= Html::endForm() ?>
