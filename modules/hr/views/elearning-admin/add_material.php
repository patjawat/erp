<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\ElearningMaterial $model */
/** @var app\modules\hr\models\ElearningCourse $course */

$this->title = 'เพิ่มสื่อการสอน: ' . $course->title;
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนบุคลากร', 'url' => ['/hr/employees']];
$this->params['breadcrumbs'][] = ['label' => 'จัดการ E-learning', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $course->title, 'url' => ['view', 'id' => $course->id]];
$this->params['breadcrumbs'][] = 'เพิ่มสื่อการสอน';
?>

<div class="elearning-material-create">
    <?php $this->beginBlock('page-title'); ?>
    เพิ่มสื่อการเรียนรู้ใหม่
    <?php $this->endBlock(); ?>

    <?php $this->beginBlock('navbar_menu'); ?>
    <?= $this->render('@app/modules/hr/views/employees/menu', ['active' => 'employees']) ?>
    <?php $this->endBlock(); ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-graduation-cap text-primary me-2"></i> หลักสูตร: <?= Html::encode($course->title) ?></h5>
            
            <?php $form = ActiveForm::begin(); ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'เช่น บทนำความรู้เบื้องต้นเรื่อง IC, วิดีโอสาธิตการล้างมือ 7 ขั้นตอน'])->label('ชื่อสื่อการสอน <span class="text-danger">*</span>') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'type')->dropDownList([
                        'video_url' => 'วิดีโอ (YouTube/Vimeo URL)',
                        'pdf_file' => 'เอกสารดาวน์โหลด (PDF File URL)',
                        'slide_link' => 'สไลด์ประกอบการสอน (Google Slide หรือลิงก์)',
                    ])->label('ประเภทสื่อ <span class="text-danger">*</span>') ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'sort_order')->textInput(['type' => 'number'])->label('ลำดับการแสดงผล') ?>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <?= $form->field($model, 'file_path')->textInput(['maxlength' => true, 'placeholder' => 'ระบุ URL ลิงก์ตรงของไฟล์ PDF หรือ ลิงก์วิดีโอ YouTube (เช่น https://www.youtube.com/watch?v=...)'])->label('ลิงก์ไฟล์ หรือ URL สื่อการเรียนรู้ <span class="text-danger">*</span>') ?>
                </div>
            </div>

            <div class="form-group mt-4 d-flex justify-content-end gap-2">
                <?= Html::a('ย้อนกลับ', ['view', 'id' => $course->id], ['class' => 'btn btn-light rounded-pill px-4']) ?>
                <?= Html::submitButton('บันทึกสื่อการสอน', ['class' => 'btn btn-primary rounded-pill px-4']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
