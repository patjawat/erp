<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinanceCheque $cheque */
/** @var array $accounts */
/** @var array $templates */

$this->title = 'ออกเช็คใหม่';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนคุมเช็ค', 'url' => ['/finance/cheque']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo 'คีย์รายละเอียดเช็ค → บันทึกเข้าทะเบียน → กดพิมพ์ลงเช็ค';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'cheque']);
$this->endBlock();

$dateVal = $cheque->cheque_date
    ? (date_create($cheque->cheque_date) ? date_create($cheque->cheque_date)->format('d/m/') . ((int) date_create($cheque->cheque_date)->format('Y') + 543) : '')
    : date('d/m/') . ((int) date('Y') + 543);
$err = fn($attr) => $cheque->hasErrors($attr) ? '<div class="text-danger small mt-1">' . Html::encode($cheque->getFirstError($attr)) . '</div>' : '';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <?php if ($cheque->hasErrors()): ?>
            <div class="alert alert-danger"><?= implode('<br>', $cheque->getErrorSummary(true)) ?></div>
        <?php endif; ?>
        <div class="card shadow-sm">
            <div class="card-header fw-semibold"><i class="bi bi-cash-stack me-1"></i>ออกเช็คใหม่</div>
            <div class="card-body">
                <?= Html::beginForm(['create'], 'post') ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small">บัญชีจ่าย <span class="text-danger">*</span></label>
                        <select name="cash_account_id" class="form-select">
                            <option value="">— เลือกบัญชีจ่าย —</option>
                            <?php foreach ($accounts as $aid => $al): ?>
                                <option value="<?= $aid ?>" <?= (int) $cheque->cash_account_id === (int) $aid ? 'selected' : '' ?>><?= Html::encode($al) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">แม่แบบเช็ค (สำหรับพิมพ์)</label>
                        <select name="template_id" class="form-select">
                            <option value="">— ไม่ระบุ (ใช้แม่แบบที่ใช้งาน) —</option>
                            <?php foreach ($templates as $tid => $tl): ?>
                                <option value="<?= $tid ?>" <?= (int) $cheque->template_id === (int) $tid ? 'selected' : '' ?>><?= Html::encode($tl) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">เลขที่เช็ค <span class="text-danger">*</span></label>
                        <input type="text" name="cheque_no" class="form-control" value="<?= Html::encode($cheque->cheque_no) ?>" required>
                        <?= $err('cheque_no') ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">เล่มเช็ค</label>
                        <input type="text" name="cheque_book_no" class="form-control" value="<?= Html::encode($cheque->cheque_book_no) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">วันที่สั่งจ่าย</label>
                        <input type="text" name="cheque_date" class="form-control" value="<?= Html::encode($dateVal) ?>" placeholder="วว/ดด/ปปปป">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small">จ่ายให้ (ชื่อผู้รับ) <span class="text-danger">*</span></label>
                        <input type="text" name="payee_name" class="form-control" value="<?= Html::encode($cheque->payee_name) ?>" required>
                        <?= $err('payee_name') ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">จำนวนเงิน (บาท) <span class="text-danger">*</span></label>
                        <input type="text" inputmode="decimal" name="amount" class="form-control text-end" value="<?= $cheque->amount ? Html::encode(number_format((float) $cheque->amount, 2)) : '' ?>" required>
                        <?= $err('amount') ?>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_ac_payee" id="ac" value="1" <?= $cheque->is_ac_payee ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ac">พิมพ์ขีดคร่อม A/C PAYEE ONLY</label>
                        </div>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึกเข้าทะเบียน', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>
