<?php
use yii\helpers\Html;
$this->title='สร้างรอบสำรวจ'; $p=Yii::$app->request->post(); $options=[];
if (!$p && !empty($round)) {
    $p=$round;
    foreach(['open_at','close_at'] as $key) $p[$key]=(new DateTimeImmutable($round[$key],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Bangkok'))->format('Y-m-d\TH:i');
    $p['policy_confirmed']=(string)$round['policy_confirmed'];
}
foreach($versions as $v) $options[$v['id']]=$v['title'].' · เวอร์ชัน '.$v['version_no'];
?>
<?= $this->render('_nav') ?>
<?= Html::beginForm('','post',['class'=>'card border-0 shadow-sm rounded-4']) ?><div class="card-body p-3 p-md-4"><h2 class="h5">กำหนดรอบและนโยบายการเก็บข้อมูล</h2>
<?php if(!$options): ?><p class="alert alert-warning">ต้องเผยแพร่แบบสอบถามก่อนสร้างรอบ</p><?php endif ?>
<div class="row g-3">
<div class="col-md-8"><label for="round-title" class="form-label">ชื่อรอบสำรวจ</label><?= Html::textInput('title',$p['title']??'',['id'=>'round-title','class'=>'form-control','required'=>true,'maxlength'=>255]) ?></div>
<div class="col-md-4"><label for="fy" class="form-label">ปีงบประมาณ พ.ศ.</label><?= Html::input('number','fiscal_year',$p['fiscal_year']??'',['id'=>'fy','class'=>'form-control','required'=>true,'min'=>2500,'max'=>2700]) ?></div>
<div class="col-12"><label for="version" class="form-label">แบบสอบถาม</label><?= Html::dropDownList('version_id',$p['version_id']??null,$options,['id'=>'version','class'=>'form-select','prompt'=>'เลือกเวอร์ชันที่ตรวจสอบแล้ว','required'=>true]) ?></div>
<?php foreach(['open_at'=>'เปิดรับคำตอบ (เวลาไทย)','close_at'=>'ปิดรับคำตอบ (เวลาไทย)'] as $key=>$label): ?><div class="col-md-6"><label for="<?= $key ?>" class="form-label"><?= $label ?></label><?= Html::input('datetime-local',$key,$p[$key]??'',['id'=>$key,'class'=>'form-control','required'=>true]) ?></div><?php endforeach ?>
<div class="col-md-6"><label for="min-group" class="form-label">จำนวนผู้ตอบขั้นต่ำที่แสดงคะแนนได้</label><?= Html::input('number','minimum_group_size',$p['minimum_group_size']??5,['id'=>'min-group','class'=>'form-control','min'=>5,'max'=>100,'required'=>true]) ?><p class="small text-body-secondary mt-1">ค่าเริ่มต้นเพื่อเตรียมระบบ ต้องยืนยันตามนโยบายองค์กรก่อนเปิดรอบ</p></div>
<div class="col-md-6"><label for="retention" class="form-label">อายุการเก็บข้อมูลหลังปิดรอบ (วัน)</label><?= Html::input('number','retention_days',$p['retention_days']??'',['id'=>'retention','class'=>'form-control','min'=>30,'max'=>3650,'required'=>true]) ?></div>
<div class="col-12"><label for="notice" class="form-label">คำชี้แจงผู้ตอบและผู้ดูแลข้อมูล</label><?= Html::textarea('privacy_notice',$p['privacy_notice']??'',['id'=>'notice','class'=>'form-control','rows'=>5,'required'=>true,'minlength'=>30,'maxlength'=>5000]) ?><p class="small text-body-secondary mt-1">ระบุวัตถุประสงค์ ผู้เข้าถึงข้อมูล ระยะเวลาเก็บ ช่องทางติดต่อ และชี้แจงว่าเป็นข้อมูลลับที่ระบบยังเชื่อมตัวตนได้ ไม่ใช่นิรนามอย่างสมบูรณ์</p></div>
<div class="col-12"><label class="d-flex gap-2"><?= Html::checkbox('policy_confirmed',($p['policy_confirmed']??'')==='1',['value'=>'1','class'=>'form-check-input flex-shrink-0']) ?><span>HR และผู้รับผิดชอบได้ยืนยันแบบสำรวจ สูตร นโยบายความลับ และอายุข้อมูลแล้ว</span></label><p class="small text-body-secondary mt-1">ยังไม่ยืนยันสามารถบันทึกร่างได้ แต่จะเปิดรับคำตอบไม่ได้</p></div>
</div><button class="btn btn-primary mt-3" <?= !$options?'disabled':'' ?>>สร้างร่างรอบสำรวจ</button></div><?= Html::endForm() ?>
