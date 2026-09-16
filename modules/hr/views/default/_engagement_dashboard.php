<?php use yii\helpers\Html; ?>
<section class="mb-4" aria-labelledby="engagement-dashboard-title">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h2 id="engagement-dashboard-title" class="h5 mb-0">ความผูกพันบุคลากร</h2><?= Html::a('แบบสำรวจของฉัน',['/hr/engagement/mine'],['class'=>'btn btn-outline-primary']) ?></div>
<?php if($engagement['status']==='not_authorized'): ?><p class="text-body-secondary">ผลวิเคราะห์แสดงเฉพาะผู้ได้รับสิทธิ์ บุคลากรสามารถตรวจแบบสำรวจของตนเองได้</p>
<?php elseif($engagement['status']!=='ok'): ?><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><p class="mb-2"><?= $engagement['status']==='not_connected'?'ยังไม่เปิดใช้งานระบบเก็บผลสำรวจ':'ยังไม่มีผลสำรวจที่เผยแพร่ในปีงบประมาณนี้' ?></p><?= Html::a('เปิดระบบความผูกพัน',['/hr/engagement/report'],['class'=>'btn btn-outline-primary btn-sm']) ?></div></div>
<?php else: ?>
<?= Html::beginForm(['/hr/default/dashboard'],'get',['class'=>'card border-0 shadow-sm rounded-4 mb-3']) ?><div class="card-body"><div class="d-flex flex-column flex-sm-row align-items-sm-end gap-2">
<?php foreach($filters as $key=>$value): ?><?= Html::hiddenInput($key,$value) ?><?php endforeach ?><?= Html::hiddenInput('budget_year',$budgetYear) ?>
<div class="flex-grow-1"><label for="engagement-round-select" class="form-label">เลือกรอบสำรวจ ปีงบประมาณ <?= (int)$budgetYear ?></label><?= Html::dropDownList('engagement_round',$engagement['report']['round']['id']??null,array_column($engagement['rounds'],'title','id'),['id'=>'engagement-round-select','class'=>'form-select','prompt'=>'เลือกรอบที่ต้องการดู']) ?></div><button class="btn btn-primary">แสดงผลสำรวจ</button></div><p class="small text-body-secondary mt-2 mb-0">ผลสำรวจเป็นภาพรวมผู้มีสิทธิ์ทั้งรอบ ไม่เปลี่ยนตามตัวกรองเพศ อายุ หรือตำแหน่งด้านบน</p></div><?= Html::endForm() ?>
<?php if($engagement['report']): ?><?= $this->render('../engagement/_results',['report'=>$engagement['report'],'trend'=>[]]) ?><div class="mt-2"><?= Html::a('แนวโน้มและแผนปรับปรุง',['/hr/engagement/report','id'=>$engagement['report']['round']['id']],['class'=>'btn btn-outline-primary']) ?></div><?php endif ?>
<?php endif ?></section>
