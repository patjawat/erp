<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use app\modules\approveV2\models\ApproveLevelSetting;

/** @var yii\web\View $this */
/** @var app\modules\approveV2\models\ApproveLevelSetting $model */
/** @var string $system */

$typeOptions = ApproveLevelSetting::approverTypeOptions();
$systemName = ApproveLevelSetting::systemOptions()[$system] ?? $system;
$isNew = $model->isNewRecord;
?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <?php $form = ActiveForm::begin([
            'fieldConfig' => [
                'labelOptions' => ['class' => 'form-label fw-medium text-body'],
                'inputOptions' => ['class' => 'form-control'],
                'errorOptions' => ['class' => 'invalid-feedback'],
            ],
        ]); ?>

        <?php if ($isNew): ?>
        <div class="mb-3">
            <label class="form-label fw-medium text-body">ระบบ</label>
            <p class="form-control-plaintext text-muted"><?= Html::encode($systemName) ?> (<?= Html::encode($system) ?>)</p>
        </div>
        <?php endif; ?>

        <?= $form->field($model, 'system')->hiddenInput()->label(false) ?>

        <div class="row g-3">
            <div class="col-md-3">
                <?= $form->field($model, 'level')->textInput(['type' => 'number', 'min' => 1])
                    ->hint('ลำดับขั้นการอนุมัติ (1, 2, 3...) — ใช้ในระบบจริง: ระดับ 1 อนุมัติก่อน ระดับ 2 ตามมา ไม่ซ้ำในแต่ละระบบ') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'title')->textInput(['placeholder' => 'เช่น หัวหน้างาน, หัวหน้ากลุ่มงาน, ผู้อำนวยการ'])
                    ->label('ชื่อขั้นอนุมัติ')
                    ->hint('ชื่อที่ใช้แสดงผลในลำดับการอนุมัติ') ?>
            </div>
            <div class="col-md-5">
                <?= $form->field($model, 'label')->textInput(['placeholder' => 'เช่น เห็นชอบ, ผ่าน, อนุมัติ'])->label('คำที่ใช้ลงความเห็น') ?>
            </div>
        </div>

        <?= $form->field($model, 'approver_type')->dropdownList($typeOptions, [
            'class' => 'form-select',
            'prompt' => '-- เลือกประเภทผู้อนุมัติ --',
        ]) ?>

        <div id="org-node-level-wrap" class="<?= in_array($model->approver_type, [ApproveLevelSetting::TYPE_ORG_LEADER1, ApproveLevelSetting::TYPE_ORG_LEADER2], true) ? '' : 'd-none' ?>">
            <?= $form->field($model, 'org_node_level')->dropdownList(ApproveLevelSetting::orgNodeLevelOptions(), [
                'class' => 'form-select',
            ])->label('ใช้ผู้อนุมัติจากระดับในผังองค์กร') ?>
            <p class="text-muted small">ใช้เฉพาะหัวหน้า/ผู้ควบคุม/ประสานงาน (leader1) จากผังที่ <a href="<?= \yii\helpers\Url::to(['/hr/organization/diagram']) ?>">/hr/organization/diagram</a> — ระดับ 1 = ประเภท, ระดับ 2 = กลุ่มงาน</p>
        </div>

        <div id="approver-value-wrap" class="<?= in_array($model->approver_type, [ApproveLevelSetting::TYPE_ROLE, ApproveLevelSetting::TYPE_FIXED], true) ? '' : 'd-none' ?>">
            <?= $form->field($model, 'approver_value')->textInput([
                'placeholder' => 'บทบาท: ใส่ชื่อ role เช่น leave — ระบุพนักงาน: ใส่รหัส emp_id',
                'id' => 'approvelevelsetting-approver_value',
            ])->label('ค่า (ชื่อบทบาท หรือรหัสพนักงาน)') ?>
        </div>

        <?= $form->field($model, 'sort_order')->textInput(['type' => 'number', 'value' => $model->sort_order ?: 0])
            ->hint('สำหรับเรียงแสดงในหน้ากำหนดเท่านั้น (ตัวเลขน้อยแสดงก่อน) — ไม่กระทบลำดับการอนุมัติจริง (ใช้ "ระดับ" ด้านบน)') ?>
        <?= $form->field($model, 'active')->dropdownList([0 => 'ปิด', 1 => 'ใช้งาน'], ['class' => 'form-select']) ?>

        <div class="d-flex gap-2 pt-2">
            <?= Html::submitButton('บันทึก', ['class' => 'btn btn-primary rounded-3']) ?>
            <?= Html::a('ยกเลิก', ['levels', 'system' => $system], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$js = <<<JS
(function(){
    var valueWrap = document.getElementById('approver-value-wrap');
    var orgWrap = document.getElementById('org-node-level-wrap');
    var sel = document.querySelector('select[name="ApproveLevelSetting[approver_type]"]');
    if (!sel) return;
    function toggle() {
        var v = sel.value;
        if (valueWrap) valueWrap.classList.toggle('d-none', v !== 'role' && v !== 'fixed');
        if (orgWrap) orgWrap.classList.toggle('d-none', v !== 'org_leader1' && v !== 'org_leader2');
    }
    sel.addEventListener('change', toggle);
    toggle();
})();
JS;
$this->registerJs($js);
?>
