<?php
use yii\helpers\Html;
$this->title=$round['title'];
$postedAnswers=Yii::$app->request->post('answers',[]); if(!is_array($postedAnswers)) $postedAnswers=[];
$this->registerJs("var survey=document.getElementById('engagement-response-form'); if(survey){var dirty=false; survey.addEventListener('change',function(){dirty=true;}); survey.addEventListener('submit',function(){dirty=false;}); window.addEventListener('beforeunload',function(e){if(dirty){e.preventDefault();e.returnValue='';}});}");
?>
<h2 class="h5"><?= Html::encode($round['title']) ?></h2>
<?php if(!$available): ?><p class="alert alert-secondary">รอบนี้ยังไม่เปิดหรือสิ้นสุดเวลารับคำตอบแล้ว</p><?= Html::a('กลับแบบสำรวจของฉัน',['mine'],['class'=>'btn btn-outline-primary']) ?><?php else: ?>
<div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-body p-4"><h3 class="h6">คำชี้แจงก่อนตอบ</h3><p><?= nl2br(Html::encode($round['privacy_notice'])) ?></p><p class="small text-body-secondary mb-0">ข้อมูลเป็นความลับ ระบบยังเชื่อมตัวตนเพื่อยืนยันสิทธิ์ได้ ผู้บริหารดูผลรวมที่ผ่านเกณฑ์จำนวนขั้นต่ำ ไม่เห็นคำตอบรายบุคคล หน้านี้ไม่บันทึกร่าง ส่งแล้วแก้ไขไม่ได้ เลือก “ไม่เกี่ยวข้อง / ไม่ประสงค์ตอบ” ได้ทุกข้อ</p></div></div>
<?= Html::beginForm('','post',['id'=>'engagement-response-form']) ?>
<?= Html::hiddenInput('submission_key',Yii::$app->security->generateRandomString(32)) ?>
<?php $n=0; foreach($dimensions as $d): ?><section class="card border-0 shadow-sm rounded-4 mb-3" aria-labelledby="dim-<?= (int)$d['id'] ?>"><div class="card-body p-3 p-md-4"><h3 class="h5" id="dim-<?= (int)$d['id'] ?>"><?= Html::encode($d['title']) ?></h3>
<?php foreach($d['questions'] as $q): ?><fieldset class="border-top pt-3 mt-3"><legend class="fs-6 fw-semibold"><?= ++$n ?>. <?= Html::encode($q['prompt']) ?></legend><div class="row g-2">
<?php foreach($q['options'] as $o): ?><div class="col-12 col-md-6 col-xl-4"><label class="d-flex align-items-start gap-2 border rounded-3 p-3 h-100"><?= Html::radio('answers['.$q['id'].']',(string)($postedAnswers[$q['id']]??'') === (string)$o['id'],['value'=>$o['id'],'class'=>'form-check-input flex-shrink-0','required'=>true]) ?><span><?= $o['is_missing']?'':(int)$o['score'].' · ' ?><?= Html::encode($o['label']) ?></span></label></div><?php endforeach ?>
</div></fieldset><?php endforeach ?></div></section><?php endforeach ?>
<label class="d-flex gap-2 mb-3"><?= Html::checkbox('acknowledge',false,['class'=>'form-check-input','required'=>true,'value'=>'1']) ?><span>อ่านคำชี้แจงแล้ว และยืนยันส่งคำตอบชุดนี้</span></label><button class="btn btn-primary mb-4">ส่งคำตอบ</button><?= Html::endForm() ?>
<?php endif ?>
