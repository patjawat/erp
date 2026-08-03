<?php use yii\helpers\Html; ?>
<section class="card border-0 shadow-sm" aria-labelledby="annual-appraisal-title">
    <div class="card-body p-4">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-4"><div><h2 id="annual-appraisal-title" class="h5 mb-1">การประเมินผลการปฏิบัติงาน</h2><p class="text-body-secondary mb-0"><?= Html::encode($model->fullname) ?> · รอบประเมินประจำปี</p></div><i data-lucide="calendar-check-2" class="text-primary" aria-hidden="true"></i></div>
        <div class="alert alert-primary d-flex gap-3 mb-0" role="status"><i data-lucide="construction" aria-hidden="true"></i><div><strong class="d-block mb-1">เตรียมพัฒนาระบบประเมินรายปี</strong><span>ส่วนนี้จะรองรับการประเมินปีละ 2 ครั้ง และจะแยกข้อมูลออกจากการประเมินทดลองงาน</span></div></div>
    </div>
</section>
