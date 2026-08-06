<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord ? 'สร้าง Template' : 'แก้ไข Template';
?>
<h1 class="h4 mb-3"><?= Html::encode($this->title) ?></h1>

<section class="card bg-body border shadow-sm">
    <div class="card-body p-3 p-md-4">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row g-3">
            <div class="col-md-7">
                <?= $form->field($model, 'name')->textInput(['maxlength' => 200]) ?>
            </div>
            <div class="col-md-5">
                <?= $form->field($model, 'position_group_id')->dropDownList($positionGroups, ['prompt' => 'เลือกวิชาชีพ']) ?>
            </div>

            <?php if ($model->isNewRecord): ?>
                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        ระบบจะกำหนด Revision ถัดไปของกลุ่มวิชาชีพให้อัตโนมัติ
                    </div>
                </div>
            <?php else: ?>
                <div class="col-md-4">
                    <?= $form->field($model, 'revision_no')->input('number', ['min' => 1]) ?>
                </div>
            <?php endif; ?>

            <div class="col-12">
                <?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2">
            <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::submitButton('บันทึก Template', ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</section>
