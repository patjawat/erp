<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinanceChequeBook $book */
/** @var array $accounts */

$this->title = 'รับเล่มเช็คเข้า';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนเล่มเช็ค', 'url' => ['/finance/cheque/book-index']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'cheque']);
$this->endBlock();

$err = fn($a) => $book->hasErrors($a) ? '<div class="text-danger small mt-1">' . Html::encode($book->getFirstError($a)) . '</div>' : '';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <?php if ($book->hasErrors()): ?>
            <div class="alert alert-danger"><?= implode('<br>', $book->getErrorSummary(true)) ?></div>
        <?php endif; ?>
        <div class="card shadow-sm">
            <div class="card-header fw-semibold"><i class="bi bi-journals me-1"></i>รับเล่มเช็คเข้าทะเบียน</div>
            <div class="card-body">
                <?= Html::beginForm(['book-create'], 'post') ?>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small">บัญชีจ่าย <span class="text-danger">*</span></label>
                        <select name="cash_account_id" class="form-select" required>
                            <option value="">— เลือกบัญชีจ่าย —</option>
                            <?php foreach ($accounts as $aid => $al): ?>
                                <option value="<?= $aid ?>" <?= (int) $book->cash_account_id === (int) $aid ? 'selected' : '' ?>><?= Html::encode($al) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('cash_account_id') ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">เลข/ชื่อเล่ม</label>
                        <input type="text" name="book_no" class="form-control" value="<?= Html::encode($book->book_no) ?>" placeholder="เช่น 12">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">คำนำหน้าเลข</label>
                        <input type="text" name="prefix" class="form-control" value="<?= Html::encode($book->prefix) ?>" placeholder="(ถ้ามี)">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">เลขเริ่ม <span class="text-danger">*</span></label>
                        <input type="text" inputmode="numeric" name="start_no" class="form-control text-end" value="<?= Html::encode($book->start_no) ?>" required>
                        <?= $err('start_no') ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">เลขสุดท้าย <span class="text-danger">*</span></label>
                        <input type="text" inputmode="numeric" name="end_no" class="form-control text-end" value="<?= Html::encode($book->end_no) ?>" required>
                        <?= $err('end_no') ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">จำนวนหลัก</label>
                        <input type="number" name="number_width" class="form-control text-end" value="<?= Html::encode($book->number_width) ?>" placeholder="อัตโนมัติ">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">วันที่รับเล่ม</label>
                        <input type="text" name="received_date" class="form-control" value="<?= Html::encode($book->received_date) ?>" placeholder="วว/ดด/ปปปป">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small">หมายเหตุ</label>
                        <input type="text" name="note" class="form-control" value="<?= Html::encode($book->note) ?>">
                    </div>
                </div>
                <div class="form-text mt-2">ระบุช่วงเลขตามเล่มเช็คจริง เช่น เริ่ม 10225123 สุดท้าย 10225172 (50 ใบ) — ระบบจะรันเลขในเล่มให้ตอนออกเช็ค</div>
                <div class="mt-3 d-flex gap-2">
                    <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึกรับเล่ม', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('ยกเลิก', ['book-index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>
