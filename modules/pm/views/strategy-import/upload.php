<?php
use yii\helpers\Html;
$this->title='นำเข้าข้อมูลยุทธศาสตร์';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu',['active'=>'strategy']) ?><?php $this->endBlock();
?>
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-3 mb-4"><div><h2 class="h5 mb-1">นำเข้าจาก Excel</h2><p class="text-muted mb-0"><?= Html::encode($plan->name) ?> · รุ่น <?= (int)$plan->version ?></p></div><?= Html::a('<i data-lucide="file-down" class="me-1"></i> ดาวน์โหลด Template',['template'],['class'=>'btn btn-outline-primary','data-pjax'=>0]) ?></div>
<div class="row g-4"><div class="col-12 col-lg-7"><div class="card border-0 shadow-sm"><div class="card-body p-4">
<?= Html::beginForm(['upload','planId'=>$plan->id],'post',['enctype'=>'multipart/form-data']) ?>
<label for="strategy-file" class="form-label fw-semibold">ไฟล์แผนยุทธศาสตร์</label><?= Html::fileInput('strategy_file',null,['id'=>'strategy-file','class'=>'form-control','accept'=>'.xlsx,.xls','required'=>true]) ?><div class="form-text">รองรับไฟล์ .xlsx และ .xls ตามโครงสร้าง M1.S1 ถึง M6.S1</div>
<div class="alert alert-info mt-4 mb-0"><div class="fw-semibold mb-1">ระบบจะยังไม่บันทึกเข้าทะเบียนทันที</div><div class="small">ข้อมูลจะถูกพักไว้เพื่อตรวจจำนวนรายการ คำเตือน และตัวอย่างข้อมูลก่อนกดยืนยันนำเข้า</div></div>
</div><div class="card-footer bg-body d-flex justify-content-between p-3"><?= Html::a('ยกเลิก',['/pm/strategy-plan/view','id'=>$plan->id],['class'=>'btn btn-outline-secondary']) ?><?= Html::submitButton('<i data-lucide="scan-search" class="me-1"></i> ตรวจสอบไฟล์',['class'=>'btn btn-primary']) ?></div><?= Html::endForm() ?></div></div>
<div class="col-12 col-lg-5"><div class="card border-0 shadow-sm"><div class="card-body p-4"><h3 class="h6">โครงสร้างที่รองรับ</h3><ol class="small text-muted mb-4 ps-3"><li class="mb-2">วิสัยทัศน์และพันธกิจ</li><li class="mb-2">เป้าประสงค์ KPI หลัก/รอง และ RCA</li><li class="mb-2">มาตรการ โครงการ และผู้รับผิดชอบ ปี 2568–2572</li><li>Baseline เป้าหมาย และผลงานรายปี</li></ol><div class="border-top pt-3"><div class="small fw-semibold mb-1">ยังไม่มีไฟล์สำหรับกรอก?</div><div class="small text-muted mb-3">ดาวน์โหลด Template ที่จัดชื่อชีตและคอลัมน์ให้ตรงกับระบบแล้ว</div><?= Html::a('ดาวน์โหลด Template Excel',['template'],['class'=>'btn btn-outline-primary w-100','data-pjax'=>0]) ?></div></div></div></div></div>
