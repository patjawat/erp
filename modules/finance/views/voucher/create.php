<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\finance\models\FinanceVoucher;
$this->title = 'สร้างร่างฎีกา';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'เบิกจ่ายและฎีกา', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title'); echo Html::encode($this->title); $this->endBlock();
$this->beginBlock('sub-title'); ?>ระบุแผนจ่ายโดยไม่แก้ไขยอดที่ฝ่ายบัญชีอนุมัติ<?php $this->endBlock();
?>
<div class="row g-3"><div class="col-xl-8"><section class="card border shadow-sm"><div class="card-header bg-body"><h5 class="mb-0">ข้อมูลสำหรับจัดทำร่าง</h5></div><div class="card-body">
<?php $form = ActiveForm::begin(); ?>
<div class="row g-3"><div class="col-md-6"><?= $form->field($model, 'funding_source')->textInput(['maxlength' => true, 'placeholder' => 'เช่น เงินบำรุง / งบประมาณ'])->label('แหล่งเงิน') ?></div><div class="col-md-6"><?= $form->field($model, 'requested_payment_date')->input('date')->label('วันที่ขอจ่าย') ?></div><div class="col-md-6"><?= $form->field($model, 'payment_method')->dropDownList(FinanceVoucher::paymentMethodOptions())->label('วิธีจ่าย') ?></div><div class="col-12"><?= $form->field($model, 'note')->textarea(['rows' => 3])->label('หมายเหตุ') ?></div></div>
<div class="d-flex gap-2 mt-3"><?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึกร่างฎีกา', ['class' => 'btn btn-primary']) ?><?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?></div>
<?php ActiveForm::end(); ?></div></section></div>
<aside class="col-xl-4"><section class="card border shadow-sm"><div class="card-header bg-body"><h5 class="mb-0">รายการจากบัญชี</h5></div><div class="card-body"><dl class="mb-0"><dt class="text-body-secondary">ทะเบียนเจ้าหนี้</dt><dd><?= Html::encode($payable->payable_no) ?></dd><dt class="text-body-secondary">ผู้รับเงิน</dt><dd><?= Html::encode($payable->vendor_name_snapshot) ?></dd><dt class="text-body-secondary">ใบแจ้งหนี้</dt><dd><?= Html::encode($payable->invoice_no) ?></dd><dt class="text-body-secondary">ยอดสุทธิ</dt><dd class="fs-5 fw-semibold mb-0"><?= number_format($payable->net_amount, 2) ?> บาท</dd></dl></div></section></aside></div>
