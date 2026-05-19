<?php

use yii\bootstrap5\Html;

$this->title = 'ตั้งค่าบุคลากร';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/hr/employees']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column gap-1">
    <div class="d-inline-flex align-items-center gap-2">
        <span class="badge rounded-pill bg-primary-subtle text-primary">HR Settings</span>
        <span class="text-muted small text-uppercase">Overview</span>
    </div>
    <h4 class="fw-semibold mb-0"><?= Html::encode($this->title) ?></h4>
    <p class="text-muted mb-0">ศูนย์รวมทางลัดสำหรับการตั้งค่าบุคลากรทั้งหมด</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../employees/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('@app/modules/hr/views/employees/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100 rounded-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column gap-2">
                    <div class="d-inline-flex align-items-center gap-2 align-self-start">
                        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary">Legacy</span>
                        <span class="text-muted small">ระบบเดิม</span>
                    </div>
                    <h5 class="mb-1 fw-semibold">ตั้งค่าบุคลากรแบบเดิม</h5>
                    <p class="text-muted mb-3">
                        หน้าตั้งค่าที่อิงกับ <code>categorise</code> และ <code>position</code> เดิม ยังคงเปิดใช้งานได้สำหรับข้อมูลเก่า
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <?= Html::a('<i class="fa-solid fa-user-tag me-1"></i> การตั้งค่าบุคลากร', ['/hr/categorise', 'title' => 'การตั้งค่าบุคลากร'], ['class' => 'btn btn-outline-primary rounded-3 fw-semibold open-modal', 'data' => ['size' => 'modal-md']]) ?>
                        <?= Html::a('<i class="fa-solid fa-briefcase me-1"></i> การกำหนดตำแหน่ง', ['/hr/position', 'title' => 'การตั้งค่าบุคลากร'], ['class' => 'btn btn-outline-primary rounded-3 fw-semibold open-modal', 'data' => ['size' => 'modal-md']]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100 rounded-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column gap-3">
                    <div class="d-inline-flex align-items-center gap-2 align-self-start">
                        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary">New</span>
                        <span class="text-muted small">Master data</span>
                    </div>
                    <h5 class="mb-1 fw-semibold">ข้อมูลหลักพนักงานใหม่</h5>
                    <p class="text-muted mb-3">
                        หน้า CRUD ใหม่สำหรับ <code>employee_type_id</code>, <code>employee_position_group_id</code> และ <code>employee_position_id</code> โดยตำแหน่งสามารถกำหนดกลุ่มเพื่อใช้สรุปข้อมูลบุคลากรได้
                    </p>
                    <div class="d-flex flex-column flex-sm-row flex-wrap gap-2">
                        <?= Html::a('<i class="fa-solid fa-sliders me-1"></i> เปิดหน้าจัดการ', ['/hr/employee-master'], ['class' => 'btn btn-primary rounded-3 fw-semibold', 'data' => ['pjax' => false]]) ?>
                        <?= Html::a('<i class="fa-solid fa-file-import me-1"></i> นำเข้า CSV', ['/hr/employees/import-csv'], ['class' => 'btn btn-outline-primary rounded-3 fw-semibold', 'data' => ['pjax' => false]]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h5 class="mb-1 fw-semibold">เริ่มใช้งาน master data ใหม่</h5>
                    <p class="text-muted mb-0">
                        กำหนดประเภทพนักงานและกลุ่มตำแหน่งแยกกันได้ แล้วสร้างตำแหน่งพร้อมผูกกลุ่มเพื่อใช้แยกสรุปข้อมูล
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <?= Html::a('<i class="fa-solid fa-arrow-right me-1"></i> เปิดหน้าจัดการ', ['/hr/employee-master'], ['class' => 'btn btn-primary rounded-3 fw-semibold', 'data' => ['pjax' => false]]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
