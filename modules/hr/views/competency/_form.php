<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\hr\models\CompetencyYear;

/** @var yii\web\View $this */
/** @var CompetencyYear $model */
/** @var array $competencyItems */

$form = ActiveForm::begin(['id' => 'competency-year-form']);
?>
<div class="row g-3">
    <div class="col-12">
        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'เช่น บริการด้วยใจ']) ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'competency_id')->dropDownList($competencyItems, [
            'prompt' => '— สร้างใหม่ในทะเบียนกลางจากชื่อด้านบน —',
            'disabled' => !$model->isNewRecord,
        ])->hint($model->isNewRecord
            ? 'เลือกเมื่อสมรรถนะนี้เคยใช้ในปีอื่นแล้ว เพื่อให้ระบบเทียบพัฒนาการข้ามปีของบุคลากรได้'
            : 'สมรรถนะในทะเบียนกลางเปลี่ยนไม่ได้หลังบันทึกแล้ว — หากผูกผิดตัวให้ลบรายการนี้แล้วเพิ่มใหม่') ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'definition')->textarea(['rows' => 5, 'placeholder' => 'คำจำกัดความของสมรรถนะที่จะแสดงบนแบบประเมิน']) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'fiscal_year')->input('number', ['readonly' => !$model->isNewRecord]) ?>
    </div>
    <div class="col-md-3">
        <?= $form->field($model, 'sort_order')->input('number', ['min' => 1]) ?>
    </div>
    <div class="col-md-5">
        <?= $form->field($model, 'status')->dropDownList(CompetencyYear::statusLabels()) ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'note')->textarea(['rows' => 2, 'placeholder' => 'บันทึกภายในของ HR (ไม่แสดงบนแบบประเมิน)']) ?>
    </div>
</div>

<div class="alert alert-light border small mt-3 mb-0">
    <i class="bi bi-info-circle"></i>
    น้ำหนักคะแนนของสมรรถนะไม่ต้องกรอก — แบบประเมินคิดเท่ากันทุกตัว โดยหาร 100 ด้วยจำนวนสมรรถนะที่ประกาศใช้ในปีนั้น
</div>

<div class="d-flex justify-content-between gap-2 mt-4">
    <div>
        <?php if (!$model->isNewRecord): ?>
            <?php // ปุ่มอยู่ในฟอร์ม จึงยิง ajax เอง ไม่ใช้ data-method ของ yii.js ที่จะยึด action ของฟอร์มไปแทน ?>
            <?= Html::button('<i class="bi bi-trash"></i> ลบออกจากปีนี้', [
                'type' => 'button',
                'class' => 'btn btn-outline-danger',
                'id' => 'competency-year-remove',
                'data-url' => Url::to(['/hr/competency/delete', 'id' => $model->id]),
            ]) ?>
        <?php endif ?>
    </div>
    <div class="d-flex gap-2">
        <?= Html::button('ยกเลิก', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?>
        <?= Html::submitButton('บันทึก', ['class' => 'btn btn-primary']) ?>
    </div>
</div>
<?php
ActiveForm::end();

$fiscalYear = (int) $model->fiscal_year;
$this->registerJs(<<<JS
(function () {
    \$(document).off('click.cpRemove').on('click.cpRemove', '#competency-year-remove', function () {
        var url = \$(this).data('url');
        Swal.fire({
            icon: 'warning',
            title: 'ยืนยันการลบ',
            text: 'ลบสมรรถนะนี้ออกจากปีงบประมาณ {$fiscalYear} พร้อมระดับและข้อพฤติกรรมบ่งชี้ทั้งหมดของปีนี้หรือไม่',
            showCancelButton: true,
            confirmButtonText: 'ยืนยันลบ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#b42318'
        }).then(function (result) {
            if (!result.isConfirmed) { return; }
            var data = {};
            data[yii.getCsrfParam()] = yii.getCsrfToken();
            \$.post(url, data, null, 'json')
                .done(async function (response) {
                    await erpHideModal('#main-modal');
                    success((response && response.message) || 'ลบเรียบร้อยแล้ว');
                    setTimeout(function () { window.location.reload(); }, 800);
                })
                .fail(function () { warning('ลบไม่สำเร็จ'); });
        });
    });
})();
handleFormSubmit('#competency-year-form', null, function () { window.location.reload(); });
JS);
