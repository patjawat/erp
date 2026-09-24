<?php

use yii\helpers\Html;
use app\modules\finance\models\FinanceCashAccount;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinanceChequeTemplate $tpl */

$this->title = 'สร้างแม่แบบเช็ค';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'พิมพ์เช็ค', 'url' => ['/finance/cheque']];
$this->params['breadcrumbs'][] = ['label' => 'แม่แบบเช็ค', 'url' => ['/finance/cheque/template']];
$this->params['breadcrumbs'][] = 'สร้างใหม่';
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'cheque']);
$this->endBlock();
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <?php if ($tpl->hasErrors()): ?>
            <div class="alert alert-danger"><?= implode('<br>', $tpl->getErrorSummary(true)) ?></div>
        <?php endif; ?>
        <div class="card shadow-sm">
            <div class="card-header fw-semibold"><i class="bi bi-plus-square me-1"></i>สร้างแม่แบบเช็คธนาคารใหม่</div>
            <div class="card-body">
                <?= Html::beginForm(['create-template'], 'post') ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small">ธนาคาร <span class="text-danger">*</span></label>
                        <select name="bank_name" class="form-select" required>
                            <option value="">— เลือกธนาคาร —</option>
                            <?php foreach (FinanceCashAccount::BANKS as $b): ?>
                                <option value="<?= Html::encode($b) ?>" <?= $tpl->bank_name === $b ? 'selected' : '' ?>><?= Html::encode($b) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">รหัสธนาคาร (ถ้ามี)</label>
                        <input type="text" name="bank_code" class="form-control" value="<?= Html::encode($tpl->bank_code) ?>" placeholder="เช่น BAAC / KTB">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">ชื่อแม่แบบ <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= Html::encode($tpl->name) ?>" placeholder="เช่น เช็ค ธ.ก.ส. แบบ ก" required>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small">กว้าง (มม.)</label>
                        <input type="number" step="0.1" name="page_width_mm" class="form-control" value="<?= Html::encode($tpl->page_width_mm) ?>">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small">สูง (มม.)</label>
                        <input type="number" step="0.1" name="page_height_mm" class="form-control" value="<?= Html::encode($tpl->page_height_mm) ?>">
                    </div>
                </div>
                <div class="form-text mt-2">สร้างแล้วจะเข้าหน้าปรับตำแหน่งทันที (ใช้พิกัดตั้งต้น ปรับกับเช็คจริงได้)</div>
                <div class="mt-3 d-flex gap-2">
                    <?= Html::submitButton('<i class="bi bi-arrow-right-circle me-1"></i>สร้าง & ปรับตำแหน่ง', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('ยกเลิก', ['template'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>
