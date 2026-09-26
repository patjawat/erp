<?php

use app\modules\finance\models\FinanceLoanAccount;
use app\modules\finance\models\FinanceLoanExpenseType;
use app\modules\finance\models\FinanceLoanItemKind;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var string $tab */
/** @var string $tabLabel */
/** @var yii\db\ActiveRecord $model */

$isNew = $model->isNewRecord;
$this->title = ($isNew ? 'เพิ่ม' : 'แก้ไข') . $tabLabel;
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ตั้งค่าเงินยืม', 'url' => ['index', 'tab' => $tab]];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2"><i class="bi bi-gear fs-4" aria-hidden="true"></i><h4 class="mb-0"><?= Html::encode($this->title) ?></h4></div>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ตั้งค่าเงินยืม<?php $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/finance/menu', ['active' => 'loan']); $this->endBlock();

$codeHint = $isNew
    ? 'ภาษาอังกฤษตัวเล็ก ตัวเลข และ _ เท่านั้น ตั้งแล้วแก้ไม่ได้'
    : 'รหัสแก้ไม่ได้ เพราะใช้อ้างอิงตอนนำเข้าไฟล์';
?>

<section class="card border" style="max-width: 48rem">
    <div class="card-body">
        <?php $form = ActiveForm::begin(); ?>

        <?php if ($model instanceof FinanceLoanExpenseType): ?>
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'autofocus' => $isNew]) ?>
            <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'disabled' => !$isNew, 'class' => 'form-control font-monospace'])->hint($codeHint) ?>
            <div class="row g-3">
                <div class="col-sm-4"><?= $form->field($model, 'due_days')->textInput(['type' => 'number', 'min' => 0, 'max' => 365]) ?></div>
                <div class="col-sm-8"><?= $form->field($model, 'due_basis')->dropDownList(FinanceLoanExpenseType::basisOptions()) ?></div>
            </div>
            <?= $form->field($model, 'estimate_form')->dropDownList(FinanceLoanExpenseType::formOptions())
                ->hint('แบบเดินทางไปราชการมีหัวข้อเบี้ยเลี้ยง ที่พัก พาหนะตายตัว ส่วนแบบทั่วไปผู้ใช้เพิ่มรายการเอง') ?>

        <?php elseif ($model instanceof FinanceLoanItemKind): ?>
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'autofocus' => $isNew]) ?>
            <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'disabled' => !$isNew, 'class' => 'form-control font-monospace'])->hint($codeHint) ?>
            <?= $form->field($model, 'register_column')->dropDownList(FinanceLoanItemKind::registerColumnOptions())
                ->hint('ทะเบียนคุมมียอดแค่ 4 ช่อง รายการนี้จะไปรวมยอดในช่องที่เลือก') ?>
            <div class="row g-3">
                <div class="col-sm-6">
                    <?= $form->field($model, 'has_persons')->checkbox() ?>
                    <?= $form->field($model, 'person_unit_name')->textInput(['maxlength' => true, 'placeholder' => 'คน / ห้อง']) ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model, 'has_units')->checkbox() ?>
                    <?= $form->field($model, 'unit_name')->textInput(['maxlength' => true, 'placeholder' => 'วัน / คืน / มื้อ / กิโลเมตร']) ?>
                </div>
            </div>
            <p class="small text-body-secondary">ถ้าไม่เลือกทั้งสองช่อง ผู้ใช้จะกรอกยอดเงินของรายการนี้ได้อย่างเดียว</p>

        <?php elseif ($model instanceof FinanceLoanAccount): ?>
            <?= $form->field($model, 'account_no')->textInput(['maxlength' => true, 'autofocus' => $isNew, 'class' => 'form-control font-monospace', 'placeholder' => '433-100-7049']) ?>
            <?= $form->field($model, 'name')->textInput(['maxlength' => true])->hint('ชื่อที่ปรากฏในสัญญายืมเงิน') ?>
            <?= $form->field($model, 'bank_name')->textInput(['maxlength' => true]) ?>
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-sm-4"><?= $form->field($model, 'sort_order')->textInput(['type' => 'number']) ?></div>
            <div class="col-sm-8 d-flex align-items-center"><?= $form->field($model, 'is_active')->checkbox() ?></div>
        </div>

        <div class="d-flex gap-2 justify-content-end border-top pt-3">
            <?= Html::a('ยกเลิก', ['index', 'tab' => $tab], ['class' => 'btn rounded-pill btn-outline-secondary']) ?>
            <?= Html::submitButton('<i class="bi bi-check2-circle me-1"></i> บันทึก', ['class' => 'btn rounded-pill btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</section>
