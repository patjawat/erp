<?php
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\HealthScreenSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="health-screen-search mb-4">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'options' => ['data-pjax' => 1], // สำหรับกรณีใช้ Pjax
    ]); ?>

    <div class="card border-0 shadow-sm bg-light">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-4">
    <?= $form->field($model, 'emp_id', [
        'showLabels' => false,
        'addon' => [
            'prepend' => [
                'content' => '<i class="fas fa-search text-muted"></i>',
            ],
        ],
    ])->textInput([
        'placeholder' => 'ระบุชื่อพนักงาน หรือ รหัสพนักงาน...',
        'class' => 'form-control border-0 shadow-sm'
    ]) ?>
</div>

                <div class="col-md-3">
                    <?= $form->field($model, 'thai_year', ['showLabels' => false])->widget(Select2::classname(), [
                        'data' => $model->getYearList(), // สร้างฟังก์ชันดึงปีใน Model
                        'options' => ['placeholder' => 'เลือกปีที่ตรวจ...'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>
                </div>

                <div class="col-md-3">
                    <?= $form->field($model, 'checkup_status', ['showLabels' => false])->widget(Select2::classname(), [
                        'data' => [
                            'pending' => 'รอดำเนินการ',
                            'wait_doctor' => 'รอพบแพทย์',
                            'complete' => 'เสร็จสมบูรณ์'
                        ],
                        'options' => ['placeholder' => 'สถานะทั้งหมด...'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>
                </div>

                <div class="col-md-2 d-grid">
                    <div class="btn-group w-100">
                        <?= Html::submitButton('<i class="fas fa-search"></i> ค้นหา', ['class' => 'btn btn-primary shadow-sm']) ?>
                        <?= Html::a('<i class="fas fa-sync"></i>', ['index'], ['class' => 'btn btn-outline-secondary border-0']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<style>
    /* ปรับแต่งให้ Select2 เข้ากับสไตล์ Card */
    .select2-container--krajee-bs5 .select2-selection--single {
        border: none !important;
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important;
        height: 38px !important;
        padding: 6px 12px !important;
    }
</style>