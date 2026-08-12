<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\WhtRate $model */

$this->title = 'แก้ไขอัตราภาษีหัก ณ ที่จ่าย';
$this->params['breadcrumbs'][] = ['label' => 'ตั้งค่าอัตราภาษีหัก ณ ที่จ่าย', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-percent"></i> <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?= Html::encode($model->title) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับหน้าตั้งค่า', ['index'], [
    'class' => 'btn btn-sm btn-outline-secondary',
]) ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="alert alert-warning small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    ตัวเลขที่แก้ตรงนี้จะถูกนำไปคำนวณและพิมพ์ลงร่างสัญญาทันทีสำหรับสัญญาที่บันทึกหลังจากนี้
                    สัญญาที่บันทึกไว้แล้วยังใช้อัตราเดิมจนกว่าจะเปิดแก้ไขและบันทึกซ้ำ
                </div>

                <?php $form = ActiveForm::begin(); ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'rate')->input('number', ['step' => '0.01', 'min' => 0, 'max' => 100]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'threshold')->input('number', ['step' => '0.01', 'min' => 0]) ?>
                    </div>
                    <div class="col-12">
                        <?= $form->field($model, 'law_ref')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-12">
                        <?= $form->field($model, 'note')->textarea(['rows' => 2])
                            ->hint('ลบข้อความเตือน "ยังไม่ผ่านการยืนยันจากงานการเงิน" ออกเมื่อตรวจสอบแล้ว') ?>
                    </div>
                    <div class="col-12">
                        <?= $form->field($model, 'active')->checkbox() ?>
                    </div>
                </div>

                <div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-3">
                    <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึก', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">ใช้กับ</h6></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-6">ประเภทสัญญา</dt>
                    <dd class="col-6 text-end"><?= Html::encode($model->contractTypeName()) ?></dd>

                    <dt class="col-6">ผู้รับเงิน</dt>
                    <dd class="col-6 text-end"><?= Html::encode($model->partyTypeName()) ?></dd>
                </dl>
                <div class="small text-muted mt-2">
                    คู่ประเภทสัญญากับผู้รับเงินเป็นตัวชี้ว่าจะใช้แถวไหน จึงแก้ไม่ได้
                </div>
            </div>
        </div>
    </div>
</div>
