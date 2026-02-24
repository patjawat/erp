<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'แก้ไขรายการลงเวลา #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'ลงเวลา', 'url' => ['/attendance/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'รายการลงเวลา', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i> ย้อนกลับ', ['/attendance/default/index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
<?= Html::a('<i class="bi bi-eye me-1"></i> ดู', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-primary btn-sm']) ?>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-primary text-white py-2 px-3">
        <h6 class="mb-0 small fw-normal">แก้ไขรายการลงเวลา</h6>
    </div>
    <div class="card-body p-4">
        <?php $form = ActiveForm::begin(['id' => 'checkin-update-form']); ?>
        <div class="row g-3">
            <div class="col-12">
                <p class="text-muted small mb-0">พนักงาน: <strong><?= Html::encode($model->employee ? $model->employee->fname . ' ' . $model->employee->lname : '-') ?></strong></p>
            </div>
            <div class="col-12 col-md-6">
                <?= $form->field($model, 'checkin_at')->textInput([
                    'type' => 'datetime-local',
                    'class' => 'form-control',
                    'value' => $model->checkin_at ? date('Y-m-d\TH:i', strtotime($model->checkin_at)) : '',
                ])->label('วันเวลา') ?>
            </div>
            <div class="col-12 col-md-6">
                <?= $form->field($model, 'check_type')->dropdownList([
                    'in' => 'บันทึกเข้า',
                    'out' => 'บันทึกออก',
                ], ['class' => 'form-select'])->label('ประเภทการลง') ?>
            </div>
            <div class="col-12 col-md-6">
                <?= $form->field($model, 'method')->dropdownList([
                    'manual' => 'กดลงเวลา',
                    'qrcode' => 'สแกน QR',
                    'photo' => 'ถ่ายรูป',
                ], ['class' => 'form-select'])->label('วิธีลงเวลา') ?>
            </div>
            <div class="col-12 col-md-6">
                <?= $form->field($model, 'status')->dropdownList([
                    'pending' => 'รออนุมัติ',
                    'approved' => 'อนุมัติแล้ว',
                    'rejected' => 'ไม่อนุมัติ',
                ], ['class' => 'form-select'])->label('สถานะ') ?>
            </div>
            <div class="col-12">
                <?= $form->field($model, 'comment')->textarea(['rows' => 2, 'class' => 'form-control'])->label('ความเห็น (ไม่บังคับ)') ?>
            </div>
            <div class="col-12">
                <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('ยกเลิก', ['/attendance/default/index'], ['class' => 'btn btn-outline-secondary ms-2']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
