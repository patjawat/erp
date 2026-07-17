<?php
use app\modules\medsop\assets\MedSopAsset;
use app\modules\medsop\models\Document;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

MedSopAsset::register($this);
$this->title = $model->isNewRecord ? 'สร้างเอกสารคุณภาพใหม่' : 'แก้ไขเอกสารคุณภาพ';
$organizationItems = ArrayHelper::map($organizations, 'id', static function ($organization) { return str_repeat('– ', max(0, (int) $organization->lvl - 1)) . $organization->name; });
$stepRows = $stepRows ?: [['title' => '', 'description' => '', 'caution' => '']];
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ระบุข้อมูลหลักและเรียงขั้นตอนตามลำดับการปฏิบัติงาน<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><?= Html::a('กลับคลังเอกสาร', ['index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?><?php $this->endBlock(); ?>

<?php $form = ActiveForm::begin(['id' => 'medsop-document-form', 'options' => ['data-medsop-form' => true]]); ?>
<section class="surface-card mb-3" aria-labelledby="document-master-title">
    <div class="surface-card__head"><h2 id="document-master-title" class="surface-card__title">ข้อมูลเอกสาร</h2></div>
    <div class="surface-card__body"><div class="row g-3">
        <div class="col-12 col-lg-6"><?= $form->field($model, 'title')->textInput(['class' => 'form-control form-control-input', 'maxlength' => true]) ?></div>
        <div class="col-12 col-sm-6 col-lg-3"><?= $form->field($model, 'document_type')->radioList(Document::typeOptions(), ['class' => 'seg-control', 'itemOptions' => ['class' => 'btn-check']]) ?></div>
        <div class="col-12 col-sm-6 col-lg-3"><?= $form->field($model, 'organization_id')->dropDownList($organizationItems, ['prompt' => 'เลือกแผนก/ฝ่าย', 'class' => 'form-select form-control-input']) ?></div>
        <div class="col-12 col-lg-6"><?= $form->field($model, 'objective')->textarea(['class' => 'form-control form-control-input', 'rows' => 4]) ?></div>
        <div class="col-12 col-lg-6"><?= $form->field($model, 'scope')->textarea(['class' => 'form-control form-control-input', 'rows' => 4]) ?></div>
    </div></div>
</section>

<section class="surface-card" aria-labelledby="document-steps-title">
    <div class="surface-card__head d-flex justify-content-between align-items-center gap-2"><h2 id="document-steps-title" class="surface-card__title">ขั้นตอนปฏิบัติงาน</h2><button type="button" class="btn btn-sm btn-outline-primary" data-add-step><i class="bi bi-plus-circle me-1"></i> เพิ่มขั้นตอน</button></div>
    <div class="surface-card__body"><div data-step-list>
        <?php foreach ($stepRows as $index => $step): ?>
            <article class="medsop-step-editor" data-step>
                <div class="medsop-step-editor__head"><span class="medsop-step-editor__number" data-step-number><?= $index + 1 ?></span><strong>ขั้นตอนที่ <span data-step-label><?= $index + 1 ?></span></strong><button type="button" class="icon-btn" data-remove-step aria-label="ลบขั้นตอน"><i class="bi bi-x-lg"></i></button></div>
                <div class="row g-3">
                    <div class="col-12"><label>ชื่อขั้นตอน</label><input class="form-control form-control-input" name="steps[<?= $index ?>][title]" value="<?= Html::encode($step['title'] ?? '') ?>" required></div>
                    <div class="col-12 col-lg-6"><label>รายละเอียด</label><textarea class="form-control form-control-input" rows="3" name="steps[<?= $index ?>][description]"><?= Html::encode($step['description'] ?? '') ?></textarea></div>
                    <div class="col-12 col-lg-6"><label>ข้อควรระวัง</label><textarea class="form-control form-control-input" rows="3" name="steps[<?= $index ?>][caution]"><?= Html::encode($step['caution'] ?? '') ?></textarea></div>
                </div>
            </article>
        <?php endforeach; ?>
    </div><p class="balance-hint" data-step-hint aria-live="polite">ต้องมีอย่างน้อย 1 ขั้นตอน</p></div>
</section>
<div class="medsop-actions"><span class="text-muted small">ระบบจะบันทึกเอกสารและทุกขั้นตอนพร้อมกัน</span><div class="d-flex gap-2"><?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-light']) ?><?= Html::submitButton('บันทึกเอกสาร', ['class' => 'btn btn-primary', 'data-save-document' => true]) ?></div></div>
<?php ActiveForm::end(); ?>
