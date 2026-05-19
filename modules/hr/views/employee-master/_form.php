<?php

use app\modules\hr\models\EmployeePositionGroup;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/** @var yii\web\View $this */

$isType = $type === 'type';
$isGroup = $type === 'group';
$isPosition = $type === 'position';
$groupOptions = $isPosition ? EmployeePositionGroup::listItems() : [];

$titlePlaceholder = [
    'type' => 'ระบุประเภทพนักงาน เช่น ข้าราชการ',
    'group' => 'ระบุกลุ่มตำแหน่ง เช่น บริหาร',
    'position' => 'ระบุตำแหน่ง เช่น นักวิชาการคอมพิวเตอร์',
][$type] ?? 'ระบุข้อมูล';

$contextLabel = [
    'type' => 'master หลัก',
    'group' => 'master กลางแบบอิสระ',
    'position' => 'กำหนดกลุ่มได้',
][$type] ?? 'master data';

$contextColor = $isPosition ? 'info' : 'primary';
?>

<div class="employee-master-form">
    <?php $form = ActiveForm::begin([
        'id' => 'employee-master-form',
    ]); ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-body-tertiary border-0 px-4 py-3">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 flex-shrink-0">
                        <i class="fa-solid <?= Html::encode($config['icon']) ?> fs-5"></i>
                    </div>
                    <div>
                        <div class="d-inline-flex align-items-center gap-2 mb-1">
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary">master</span>
                            <span class="text-muted small"><?= $mode === 'create' ? 'สร้างรายการใหม่' : 'แก้ไขรายการเดิม' ?></span>
                        </div>
                        <h5 class="mb-1 fw-semibold"><?= Html::encode($config['label']) ?></h5>
                        <p class="text-muted small mb-0"><?= Html::encode($config['description']) ?></p>
                    </div>
                </div>

                <span class="badge rounded-pill bg-<?= $contextColor ?> bg-opacity-10 text-<?= $contextColor ?> align-self-start align-self-sm-center">
                    <?= Html::encode($contextLabel) ?>
                </span>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row g-3">
                <?php if ($isPosition): ?>
                    <div class="col-12 col-lg-6">
                        <?= $form->field($model, 'employee_position_group_id')->dropDownList(
                            $groupOptions,
                            [
                                'prompt' => 'เลือกกลุ่มตำแหน่ง...',
                                'class' => 'form-select',
                            ]
                        )->label('กลุ่มตำแหน่งพนักงาน (ใหม่)') ?>
                    </div>
                <?php endif; ?>

                <div class="col-12">
                    <?= $form->field($model, 'title')->textInput([
                        'maxlength' => true,
                        'placeholder' => $titlePlaceholder,
                    ])->label($config['label']) ?>
                </div>

                <div class="col-12 col-md-4">
                    <?= $form->field($model, 'sort')->input('number', [
                        'min' => 0,
                        'step' => 1,
                    ])->label('ลำดับแสดงผล') ?>
                </div>

                <div class="col-12 col-md-4 d-flex align-items-end">
                    <?= $form->field($model, 'active')->checkbox()->label('สถานะใช้งาน') ?>
                </div>
            </div>
            
        </div>

        <div class="card-footer bg-body border-top px-4 py-3">
            <div class="d-flex flex-column flex-sm-row justify-content-center align-items-sm-center gap-3">
                <div class="d-flex flex-wrap gap-2">
                    <?= Html::submitButton('<i class="fa-solid fa-check me-1"></i> บันทึกข้อมูล', [
                        'class' => 'btn btn-primary rounded-3 fw-semibold',
                    ]) ?>
                    <?= Html::button('<i class="fa-solid fa-xmark me-1"></i> ปิด', [
                        'class' => 'btn btn-outline-secondary rounded-3 fw-semibold',
                        'data-bs-dismiss' => 'modal',
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<JS
handleFormSubmit('#employee-master-form', null, async function (response) {
    if (response && response.container) {
        $.pjax.reload({
            container: response.container,
            history: false,
            replace: false,
            timeout: false
        });
    }
});
JS;
$this->registerJs($js, View::POS_END);
?>
