<?php
use yii\helpers\Html;
use app\modules\iacRisk\services\ContextService;
$this->title='ปค.4';
?>
<?php $this->beginBlock('page-title'); ?>ปค.4<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>รายงานผลการประเมินองค์ประกอบของการควบคุมภายใน<?php $this->endBlock(); ?>
<?= $this->render('_context',['context'=>$context]) ?><div class="mb-3"><?= $this->render('@app/modules/iacRisk/menu',['active'=>'pk4','context'=>$context]) ?></div>
<?php if(!(int)$context['orgUnitId']&&($context['canScopeAllUnits']??false)): ?><div class="alert alert-info">กรุณาเลือกหน่วยงานเพื่อจัดทำ ปค.4 รายหน่วยงาน</div><?php endif; ?>
<?php if((int)$context['orgUnitId']&&!$selected&&$canEdit): ?><section class="card bg-body border shadow-sm"><div class="card-body text-center py-5"><h2 class="h5">ยังไม่มีแบบ ปค.4 ของหน่วยงานในปีนี้</h2><?= Html::beginForm(array_merge(['create-pk4'],ContextService::query($context)),'post') ?><?= Html::submitButton('เริ่มจัดทำ ปค.4',['class'=>'btn btn-primary mt-3']) ?><?= Html::endForm() ?></div></section><?php endif; ?>
<?php if($selected): ?>
<?php $signatureType=$selected->signature_type?:'system';$systemSignatureUrl=$selected->signer?->SignatureShow(); ?>
<section class="card bg-body border shadow-sm"><div class="card-header bg-body-tertiary border-bottom d-flex justify-content-between gap-3"><div><h2 class="h5 fw-semibold mb-1"><?= Html::encode($selected->orgUnit?->name?:'หน่วยงาน') ?></h2><p class="small text-body-secondary mb-0">ปีงบประมาณ <?= (int)$selected->fiscal_year ?> · ฉบับร่าง</p></div><div class="d-flex flex-wrap gap-2"><?= Html::a('<i class="bi bi-file-earmark-word me-1"></i> Word',['pk4-docx','id'=>$selected->id],['class'=>'btn btn-outline-primary']) ?><?= Html::a('<i class="bi bi-file-earmark-pdf me-1"></i> PDF',['pk4-pdf','id'=>$selected->id],['class'=>'btn btn-outline-danger','target'=>'_blank','rel'=>'noopener']) ?></div></div>
<div class="card-body"><?= Html::beginForm(array_merge(['save-pk4','id'=>$selected->id],ContextService::query($context)),'post',['id'=>'pk4-form']) ?>
<div class="table-responsive"><table class="table table-bordered align-middle"><thead><tr><th style="width:32%">องค์ประกอบการควบคุมภายใน</th><th>ผลการประเมิน / ข้อสรุป</th></tr></thead><tbody><?php foreach($selected->items as $item): ?><tr><td class="fw-semibold align-top"><?= Html::encode($item->component_name) ?></td><td><?= Html::textarea('items['.$item->component_code.']',$item->evaluation_summary,['class'=>'form-control border-0','rows'=>6,'placeholder'=>'สรุปผลการประเมินขององค์ประกอบนี้']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<label class="form-label fw-semibold">สรุปผลการประเมินภาพรวม</label><?= Html::textarea('summary',$selected->summary,['class'=>'form-control','rows'=>5]) ?>
<div class="row g-3 mt-2"><div class="col-md-6"><label class="form-label fw-semibold">ชื่อหัวหน้าหน่วยงาน</label><?= Html::textInput('signer_name',$selected->signer_name,['class'=>'form-control','placeholder'=>'ชื่อ-นามสกุล']) ?></div><div class="col-md-6"><label class="form-label fw-semibold">ตำแหน่ง</label><?= Html::textInput('signer_position',$selected->signer_position,['class'=>'form-control','placeholder'=>'ตำแหน่งหัวหน้าหน่วยงาน']) ?></div></div>
<div class="mt-4"><label class="form-label fw-semibold">ลายเซ็นหัวหน้าหน่วยงาน</label><div class="d-flex gap-2 mb-3"><button type="button" class="btn btn-sm <?= $signatureType==='canvas'?'btn-primary':'btn-outline-primary' ?> pk4-sig-tab" data-type="canvas"><i class="bi bi-pen me-1"></i> เซ็นสด</button><button type="button" class="btn btn-sm <?= $signatureType==='system'?'btn-primary':'btn-outline-primary' ?> pk4-sig-tab" data-type="system"><i class="bi bi-person-badge me-1"></i> ลายเซ็นระบบ</button></div>
<div id="pk4-sig-canvas-panel" class="<?= $signatureType==='system'?'d-none':'' ?>"><div class="border rounded bg-white overflow-hidden" style="touch-action:none;max-width:560px"><canvas id="pk4-sig-canvas" width="560" height="180" style="display:block;width:100%;height:180px;cursor:crosshair"></canvas></div><button type="button" id="pk4-sig-clear" class="btn btn-sm btn-outline-secondary mt-2"><i class="bi bi-eraser me-1"></i> ล้างลายเซ็น</button></div>
<div id="pk4-sig-system-panel" class="<?= $signatureType==='canvas'?'d-none':'' ?>"><?php if($systemSignatureUrl): ?><div class="border rounded bg-body-tertiary p-3 d-inline-block"><img src="<?= Html::encode($systemSignatureUrl) ?>" alt="ลายเซ็นระบบ" style="max-width:300px;max-height:120px"></div><div class="form-text">ใช้ภาพลายเซ็นที่บันทึกในระบบบุคลากร</div><?php else: ?><div class="alert alert-warning py-2 px-3 d-inline-flex gap-2"><i class="bi bi-exclamation-triangle-fill"></i>ไม่พบลายเซ็นในระบบ กรุณาเลือกเซ็นสด</div><?php endif; ?></div>
<?= Html::hiddenInput('signature_type',$signatureType,['id'=>'pk4-signature-type']) ?><?= Html::hiddenInput('signature_data',$selected->signature_data?:'',['id'=>'pk4-signature-data']) ?></div>
<div class="d-flex justify-content-end mt-3"><?= Html::submitButton('บันทึกฉบับร่าง',['class'=>'btn btn-primary','disabled'=>!$canEdit]) ?></div><?= Html::endForm() ?></div></section>
<?php
$existingSignature=json_encode($selected->signature_data?:'');
$this->registerJs(<<<JS
(function(){
 const canvas=document.getElementById('pk4-sig-canvas'); if(!canvas)return; const ctx=canvas.getContext('2d'); let drawing=false,dirty=false;
 function point(e){const r=canvas.getBoundingClientRect(),t=e.touches?e.touches[0]:e;return{x:(t.clientX-r.left)*(canvas.width/r.width),y:(t.clientY-r.top)*(canvas.height/r.height)}}
 function start(e){drawing=true;dirty=true;const p=point(e);ctx.beginPath();ctx.moveTo(p.x,p.y);e.preventDefault()}
 function move(e){if(!drawing)return;const p=point(e);ctx.lineWidth=2;ctx.lineCap='round';ctx.strokeStyle='#111';ctx.lineTo(p.x,p.y);ctx.stroke();e.preventDefault()}
 function stop(){drawing=false}
 canvas.addEventListener('mousedown',start);canvas.addEventListener('mousemove',move);window.addEventListener('mouseup',stop);canvas.addEventListener('touchstart',start,{passive:false});canvas.addEventListener('touchmove',move,{passive:false});canvas.addEventListener('touchend',stop);
 const existing=$existingSignature;if(existing){const img=new Image();img.onload=()=>ctx.drawImage(img,0,0,canvas.width,canvas.height);img.src=existing}
 document.querySelectorAll('.pk4-sig-tab').forEach(btn=>btn.addEventListener('click',function(){const type=this.dataset.type;document.getElementById('pk4-signature-type').value=type;document.getElementById('pk4-sig-canvas-panel').classList.toggle('d-none',type!=='canvas');document.getElementById('pk4-sig-system-panel').classList.toggle('d-none',type!=='system');document.querySelectorAll('.pk4-sig-tab').forEach(b=>{b.classList.toggle('btn-primary',b===this);b.classList.toggle('btn-outline-primary',b!==this)})}));
 document.getElementById('pk4-sig-clear').addEventListener('click',()=>{ctx.clearRect(0,0,canvas.width,canvas.height);dirty=true;document.getElementById('pk4-signature-data').value=''});
 document.getElementById('pk4-form').addEventListener('submit',()=>{if(document.getElementById('pk4-signature-type').value==='canvas'&&dirty)document.getElementById('pk4-signature-data').value=canvas.toDataURL('image/png')});
})();
JS);
?>
<?php endif; ?>
<?php if(!$selected&&$models): ?><div class="d-flex flex-column gap-2"><?php foreach($models as $model): ?><div class="card"><div class="card-body d-flex justify-content-between"><span><?= Html::encode($model->orgUnit?->name?:$model->org_unit_id) ?></span><?= Html::a('เปิด ปค.4',array_merge(['pk4'],ContextService::query($context),['org_unit_id'=>$model->org_unit_id]),['class'=>'btn btn-sm btn-outline-primary']) ?></div></div><?php endforeach; ?></div><?php endif; ?>
