<?php
use app\modules\iacRisk\models\CsaRisk;
use app\modules\iacRisk\services\ContextService;
use app\modules\iacRisk\services\RiskMatrixService;
use yii\helpers\Html;
$isEdit=$model&&!$model->isNewRecord;
$action=array_merge(['save-manual-risk','id'=>$isEdit?$model->id:null],ContextService::query($context));
$level=RiskMatrixService::evaluate($model?->likelihood_score,$model?->impact_score);
?>
<?= Html::beginForm($action,'post',['class'=>'row g-3']) ?>
<div class="col-12"><label class="form-label fw-semibold">ชื่อความเสี่ยง</label><?= Html::textInput('risk_name',$model?->risk_name,['class'=>'form-control','maxlength'=>500,'required'=>true]) ?></div>
<div class="col-md-6"><label class="form-label">สาเหตุ</label><?= Html::textarea('cause',$model?->cause,['class'=>'form-control','rows'=>2]) ?></div>
<div class="col-md-6"><label class="form-label">ผลกระทบ</label><?= Html::textarea('impact',$model?->impact,['class'=>'form-control','rows'=>2]) ?></div>
<div class="col-md-3"><label class="form-label">โอกาสเกิด (1–5)</label><?= Html::dropDownList('likelihood_score',$model?->likelihood_score,array_combine(range(1,5),range(1,5)),['class'=>'form-select js-risk-likelihood','prompt'=>'ยังไม่ประเมิน']) ?></div>
<div class="col-md-3"><label class="form-label">ผลกระทบ (1–5)</label><?= Html::dropDownList('impact_score',$model?->impact_score,array_combine(range(1,5),range(1,5)),['class'=>'form-select js-risk-impact','prompt'=>'ยังไม่ประเมิน']) ?></div>
<div class="col-md-6"><label class="form-label">ผลการควบคุม</label><?= Html::dropDownList('adequacy',$model?->adequacy,CsaRisk::adequacyLabels(),['class'=>'form-select','prompt'=>'ยังไม่ระบุ']) ?></div>
<div class="col-12"><div class="alert <?= $level?'alert-light border':'alert-secondary' ?> mb-0 js-risk-result" role="status"><?php if($level): ?>คะแนนรวม <strong><?= (int)$level['score'] ?></strong> · ระดับความเสี่ยง <span class="badge <?= $level['badge'] ?>"><?= Html::encode($level['label']) ?></span><?php else: ?>เลือกโอกาสเกิดและผลกระทบเพื่อคำนวณระดับความเสี่ยง<?php endif; ?></div></div>
<div class="col-12"><label class="form-label">ความเสี่ยงที่ยังเหลืออยู่</label><?= Html::textarea('residual_risk',$model?->residual_risk,['class'=>'form-control','rows'=>2]) ?></div>
<div class="col-12 d-flex justify-content-end"><?= Html::submitButton($isEdit?'บันทึกการแก้ไข':'เพิ่มเข้าบัญชีความเสี่ยง',['class'=>'btn btn-primary']) ?></div>
<?= Html::endForm() ?>
<?php
$this->registerJs(<<<'JS'
document.querySelectorAll('.js-risk-likelihood, .js-risk-impact').forEach(function(input){
  input.addEventListener('change',function(){
    const form=input.closest('form'); const l=Number(form.querySelector('.js-risk-likelihood').value); const i=Number(form.querySelector('.js-risk-impact').value); const box=form.querySelector('.js-risk-result');
    if(!l||!i){box.textContent='เลือกโอกาสเกิดและผลกระทบเพื่อคำนวณระดับความเสี่ยง';return;}
    const score=l*i; let label='ต่ำ',cls='bg-success-subtle text-success-emphasis';
    if(score>=17){label='สูงมาก';cls='bg-danger text-white';}else if(score>=10){label='สูง';cls='bg-warning text-dark';}else if(score>=4){label='ปานกลาง';cls='bg-warning-subtle text-warning-emphasis';}
    box.innerHTML='คะแนนรวม <strong>'+score+'</strong> · ระดับความเสี่ยง <span class="badge '+cls+'">'+label+'</span>';
  });
});
JS,\yii\web\View::POS_READY,'iac-risk-matrix-calculator');
?>
