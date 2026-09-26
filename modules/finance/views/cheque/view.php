<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\finance\models\FinanceCheque;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinanceCheque $cheque */

$this->title = 'เช็คเลขที่ ' . $cheque->cheque_no;
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนคุมเช็ค', 'url' => ['/finance/cheque']];
$this->params['breadcrumbs'][] = $cheque->cheque_no;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'cheque']);
$this->endBlock();

$badge = [
    FinanceCheque::STATUS_DRAFT => 'bg-secondary-subtle text-secondary-emphasis',
    FinanceCheque::STATUS_PRINTED => 'bg-info-subtle text-info-emphasis',
    FinanceCheque::STATUS_HANDED => 'bg-primary-subtle text-primary-emphasis',
    FinanceCheque::STATUS_CLEARED => 'bg-success-subtle text-success-emphasis',
    FinanceCheque::STATUS_BOUNCED => 'bg-warning-subtle text-warning-emphasis',
    FinanceCheque::STATUS_VOID => 'bg-danger-subtle text-danger-emphasis',
];
$nextLabelBtn = [
    FinanceCheque::STATUS_PRINTED => ['พิมพ์แล้ว', 'btn-info'],
    FinanceCheque::STATUS_HANDED => ['ส่งมอบผู้รับ', 'btn-primary'],
    FinanceCheque::STATUS_CLEARED => ['ขึ้นเงินแล้ว', 'btn-success'],
    FinanceCheque::STATUS_BOUNCED => ['เช็คคืน (เด้ง)', 'btn-warning'],
];
$thDate = fn($d) => $d ? (date_create($d) ? date_create($d)->format('d/m/') . ((int) date_create($d)->format('Y') + 543) : $d) : '–';
?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'info' => 'info'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-cash-stack me-1"></i>รายละเอียดเช็ค</span>
                <span class="badge <?= $badge[$cheque->status] ?? 'bg-light' ?> fs-6"><?= Html::encode($cheque->statusLabel()) ?></span>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th style="width:40%">เลขที่เช็ค</th><td><?= Html::encode($cheque->cheque_no) ?><?= $cheque->cheque_book_no ? ' (เล่ม ' . Html::encode($cheque->cheque_book_no) . ')' : '' ?></td></tr>
                    <tr><th>วันที่สั่งจ่าย</th><td><?= $thDate($cheque->cheque_date) ?></td></tr>
                    <tr><th>จ่ายให้</th><td><?= Html::encode($cheque->payee_name) ?></td></tr>
                    <tr><th>จำนวนเงิน</th><td class="fw-bold"><?= number_format((float) $cheque->amount, 2) ?> บาท</td></tr>
                    <tr><th>ตัวอักษร</th><td><?= Html::encode($cheque->amount_text) ?></td></tr>
                    <tr><th>บัญชีจ่าย</th><td><?= $cheque->cashAccount ? Html::encode($cheque->cashAccount->label()) : '–' ?></td></tr>
                    <tr><th>ธนาคาร/แม่แบบ</th><td><?= $cheque->template ? Html::encode($cheque->template->bank_name . ' — ' . $cheque->template->name) : '–' ?></td></tr>
                    <tr><th>รูปแบบเช็ค</th><td><?= Html::encode(FinanceCheque::formTypeOptions()[$cheque->form_type] ?? $cheque->form_type) ?></td></tr>
                    <?php if ($cheque->payment_id): ?>
                        <tr><th>รอบจ่ายเจ้าหนี้</th><td><a href="<?= Url::to(['/finance/payable/letter', 'id' => $cheque->payment_id]) ?>">หนังสือนำส่ง #<?= (int) $cheque->payment_id ?></a></td></tr>
                    <?php endif; ?>
                    <?php if ($cheque->isVoid()): ?>
                        <tr class="table-danger"><th>เหตุผลยกเลิก</th><td><?= Html::encode($cheque->void_reason) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="card-footer d-flex gap-2 flex-wrap">
                <a href="<?= Url::to(['print', 'id' => $cheque->id]) ?>" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>พิมพ์เช็ค</a>
                <?php if ($cheque->status === FinanceCheque::STATUS_DRAFT): ?>
                    <a href="<?= Url::to(['update', 'id' => $cheque->id]) ?>" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i>แก้ไข</a>
                <?php endif; ?>
                <a href="<?= Url::to(['index']) ?>" class="btn btn-link">← กลับทะเบียน</a>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-arrow-right-circle me-1"></i>เดินสถานะ</div>
            <div class="card-body">
                <?php $next = $cheque->nextStatuses(); ?>
                <?php if ($cheque->isVoid()): ?>
                    <p class="text-danger mb-0"><i class="bi bi-x-octagon me-1"></i>เช็คนี้ถูกยกเลิกแล้ว</p>
                <?php elseif (!$next): ?>
                    <p class="text-muted mb-0">สิ้นสุดวงจร (<?= Html::encode($cheque->statusLabel()) ?>) ไม่มีขั้นถัดไป</p>
                <?php else: ?>
                    <div class="d-flex gap-2 flex-wrap">
                    <?php foreach ($next as $ns): [$lbl, $cls] = $nextLabelBtn[$ns] ?? [$ns, 'btn-secondary']; ?>
                        <?= Html::beginForm(['status', 'id' => $cheque->id], 'post') ?>
                            <?= Html::hiddenInput('to', $ns) ?>
                            <?= Html::submitButton('<i class="bi bi-check2 me-1"></i>' . Html::encode($lbl), ['class' => 'btn ' . $cls]) ?>
                        <?= Html::endForm() ?>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$cheque->isVoid()): ?>
        <div class="card shadow-sm border-danger-subtle">
            <div class="card-header fw-semibold text-danger"><i class="bi bi-x-octagon me-1"></i>ยกเลิกเช็ค (เช็คเสีย/พิมพ์ผิด)</div>
            <div class="card-body">
                <?= Html::beginForm(['void', 'id' => $cheque->id], 'post', ['onsubmit' => 'return confirm("ยืนยันยกเลิกเช็คนี้? เลขเช็คจะยังคงอยู่ในทะเบียน")']) ?>
                    <div class="input-group">
                        <input type="text" name="reason" class="form-control" placeholder="เหตุผลการยกเลิก" required>
                        <?= Html::submitButton('ยกเลิกเช็ค', ['class' => 'btn btn-danger']) ?>
                    </div>
                    <div class="form-text">เลขที่เช็คจะถูกคงไว้ในทะเบียนเพื่อกันเลขหาย</div>
                <?= Html::endForm() ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
