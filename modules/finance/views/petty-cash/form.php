<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinancePettyCash $model */

$isNew = $model->isNewRecord;
$this->title = ($isNew ? 'สร้าง' : 'แก้ไข') . 'กองเงินสดย่อย';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'เงินสดย่อย', 'url' => ['index']];
$this->params['breadcrumbs'][] = $isNew ? 'สร้างกอง' : 'แก้ไข';

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-wallet2 fs-4" aria-hidden="true"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'petty']);
$this->endBlock();
?>

<div class="card shadow-sm">
    <div class="card-body">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row g-3">
            <div class="col-md-3"><?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'เช่น PC-01']) ?></div>
            <div class="col-md-9"><?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'เช่น เงินสดย่อยงานการเงิน']) ?></div>
            <div class="col-md-6"><?= $form->field($model, 'custodian_name')->textInput(['maxlength' => true]) ?></div>
            <div class="col-md-6"><?= $form->field($model, 'unit')->textInput(['maxlength' => true]) ?></div>
            <div class="col-md-4"><?= $form->field($model, 'float_amount')->textInput(['inputmode' => 'decimal']) ?></div>
            <div class="col-md-4"><?= $form->field($model, 'fiscal_year')->textInput(['type' => 'number']) ?></div>
            <div class="col-md-4"><?= $form->field($model, 'is_active')->dropDownList([1 => 'ใช้งาน', 0 => 'ปิดใช้']) ?></div>
            <div class="col-12"><?= $form->field($model, 'note')->textarea(['rows' => 2]) ?></div>
        </div>
        <div class="d-flex gap-2 mt-3">
            <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึก', ['class' => 'btn btn-success']) ?>
            <a href="<?= Url::to($isNew ? ['index'] : ['view', 'id' => $model->id]) ?>" class="btn btn-outline-secondary">ยกเลิก</a>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
