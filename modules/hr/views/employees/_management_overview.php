<?php use yii\helpers\Html; ?>
<section class="card border-0 shadow-sm" aria-labelledby="work-profile-overview">
    <div class="card-body p-4">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
            <div><span class="badge bg-primary-subtle text-primary-emphasis mb-2">แฟ้มบริหารบุคลากร</span><h2 id="work-profile-overview" class="h5 mb-1">ภาพรวมการทำงาน</h2><p class="text-body-secondary mb-0">ข้อมูลที่ใช้ติดตามหน้าที่ ผลงาน และการพัฒนาของบุคลากร</p></div>
            <i data-lucide="briefcase-business" class="text-primary" aria-hidden="true"></i>
        </div>
        <div class="row g-3">
            <div class="col-md-6"><div class="p-3 rounded-3 bg-body-tertiary h-100"><small class="text-body-secondary d-block mb-1">ตำแหน่ง</small><strong><?= Html::encode(strip_tags($model->positionName())) ?></strong></div></div>
            <div class="col-md-6"><div class="p-3 rounded-3 bg-body-tertiary h-100"><small class="text-body-secondary d-block mb-1">หน่วยงาน</small><strong><?= Html::encode($model->departmentName() ?: 'ไม่ระบุ') ?></strong></div></div>
            <div class="col-md-6"><div class="p-3 rounded-3 bg-body-tertiary h-100"><small class="text-body-secondary d-block mb-1">ประเภทตำแหน่ง</small><strong><?= Html::encode($model->positionTypeName() ?: 'ไม่ระบุ') ?></strong></div></div>
            <div class="col-md-6"><div class="p-3 rounded-3 bg-body-tertiary h-100"><small class="text-body-secondary d-block mb-1">สถานะการปฏิบัติงาน</small><strong><?= Html::encode($model->statusName()) ?></strong></div></div>
        </div>
        <div class="alert alert-secondary d-flex gap-2 mt-4 mb-0" role="note"><i data-lucide="lock-keyhole" aria-hidden="true"></i><span>ข้อมูลส่วนตัว เงินเดือน สุขภาพ และเอกสารส่วนบุคคลไม่ได้แสดงในมุมมองนี้</span></div>
    </div>
</section>
