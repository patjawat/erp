<?php

use yii\helpers\Html;
use app\components\AppHelper;
use app\modules\purchase\models\Contract;
use app\modules\purchase\models\ContractReceipt;
use app\modules\finance\models\FinanceInbox;
use app\modules\finance\services\ContractReceiptFinanceSnapshotBuilder;

/**
 * การ์ด "ตรวจรับรายงวด" บนหน้าสัญญา
 *
 * @var yii\web\View $this
 * @var Contract $model
 */

$receipts = $model->receipts;
if (!$model->isInstallment() && !$receipts) {
    ?>
    <div class="card mb-3" id="receipts">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="small text-muted">
                <i class="bi bi-info-circle me-1"></i>
                สัญญานี้ตรวจรับครั้งเดียว — ถ้าออกใบสั่งซื้อเต็มวงเงินแต่ตรวจรับ/เรียกเก็บเป็นรายเดือน
                ให้แก้ไขสัญญาแล้วเลือก "รูปแบบการตรวจรับ" เป็นรายงวด
            </div>
            <?= Html::a('<i class="bi bi-pencil me-1"></i>แก้ไขสัญญา', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
        </div>
    </div>
    <?php
    return;
}

$budget = (float) $model->budget;
$received = 0.0;
$draft = 0.0;
$fine = 0.0;
foreach ($receipts as $r) {
    if (in_array($r->status, [ContractReceipt::STATUS_RECEIVED, ContractReceipt::STATUS_SENT_FINANCE], true)) {
        $received += (float) $r->amount;
        $fine += (float) $r->fine_amount;
    } elseif ($r->status === ContractReceipt::STATUS_DRAFT) {
        $draft += (float) $r->amount;
    }
}
$remaining = $budget - $received - $draft;
$pctReceived = $budget > 0 ? $received / $budget * 100 : 0;
$pctDraft = $budget > 0 ? $draft / $budget * 100 : 0;
$date = fn($v) => $v ? AppHelper::convertToThai($v) : '—';
?>
<div class="card mb-3" id="receipts">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0">
            <i class="bi bi-clipboard-check me-1"></i>ตรวจรับรายงวด
            <span class="badge text-bg-info fw-normal ms-1"><?= Html::encode(Contract::billingModeList()[$model->billing_mode] ?? $model->billing_mode) ?></span>
        </h6>
        <?php if ($model->isInstallment()): ?>
            <?= Html::a('<i class="bi bi-plus-lg me-1"></i>บันทึกตรวจรับงวดใหม่', ['receipt-create', 'contract_id' => $model->id], ['class' => 'btn btn-sm btn-success']) ?>
        <?php endif; ?>
    </div>
    <div class="card-body border-bottom">
        <div class="row g-2 text-center small">
            <div class="col-6 col-md-3">
                <div class="text-muted">วงเงินสัญญา</div>
                <div class="fw-semibold"><?= number_format($budget, 2) ?></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted">ตรวจรับแล้ว</div>
                <div class="fw-semibold text-success"><?= number_format($received, 2) ?></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted">ร่าง (ยังไม่ยืนยัน)</div>
                <div class="fw-semibold"><?= number_format($draft, 2) ?></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted">คงเหลือ</div>
                <div class="fw-semibold <?= $remaining < 0 ? 'text-danger' : '' ?>"><?= number_format($remaining, 2) ?></div>
            </div>
        </div>
        <div class="progress mt-2" style="height:8px" role="progressbar" aria-label="ใช้วงเงินสัญญา">
            <div class="progress-bar bg-success" style="width:<?= min(100, round($pctReceived, 2)) ?>%"></div>
            <div class="progress-bar bg-secondary" style="width:<?= min(100 - min(100, $pctReceived), round($pctDraft, 2)) ?>%"></div>
        </div>
        <div class="small text-muted mt-1">
            ตรวจรับแล้ว <?= number_format($pctReceived, 1) ?>% ของวงเงิน
            <?php if ($fine > 0): ?> · ค่าปรับรวม <span class="text-danger"><?= number_format($fine, 2) ?></span> บาท<?php endif; ?>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (!$receipts): ?>
            <div class="text-center text-muted py-4">ยังไม่มีการตรวจรับงวด</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th class="text-center" style="width:52px">งวด</th>
                            <th>ช่วงผลงาน</th>
                            <th>ใบแจ้งหนี้</th>
                            <th>วันตรวจรับ</th>
                            <th class="text-end">ยอดเรียกเก็บ</th>
                            <th class="text-end">หัก ณ ที่จ่าย</th>
                            <th>สถานะ</th>
                            <th style="width:1%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($receipts as $r): ?>
                            <?php $b = ContractReceipt::statusBadge($r->status); $cancelled = $r->status === ContractReceipt::STATUS_CANCELLED; ?>
                            <tr class="<?= $cancelled ? 'text-muted text-decoration-line-through' : '' ?>">
                                <td class="text-center"><?= (int) $r->seq ?></td>
                                <td class="small"><?= $date($r->period_start) ?> – <?= $date($r->period_end) ?></td>
                                <td class="small">
                                    <?= Html::encode($r->invoice_no ?: '—') ?>
                                    <?php if ($r->invoice_date): ?><div class="text-muted"><?= $date($r->invoice_date) ?></div><?php endif; ?>
                                </td>
                                <td class="small"><?= $date($r->receive_date) ?></td>
                                <td class="text-end">
                                    <?= number_format((float) $r->amount, 2) ?>
                                    <?php if ((float) $r->fine_amount > 0): ?>
                                        <div class="small text-danger">ปรับ <?= number_format((float) $r->fine_amount, 2) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end small"><?= (float) $r->wht_amount > 0 ? number_format((float) $r->wht_amount, 2) : '—' ?></td>
                                <td>
                                    <span class="badge text-bg-<?= $b['color'] ?>"><?= $b['label'] ?></span>
                                    <?php if ($r->status === ContractReceipt::STATUS_SENT_FINANCE): ?>
                                        <?php $inbox = ContractReceiptFinanceSnapshotBuilder::latestInbox((int) $r->id); ?>
                                        <?php if ($inbox): ?>
                                            <div class="small mt-1">
                                                การเงิน:
                                                <span class="badge <?= FinanceInbox::statusBadgeClass($inbox->status) ?>"><?= Html::encode(FinanceInbox::statusOptions()[$inbox->status] ?? $inbox->status) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap">
                                    <?php if ($r->status === ContractReceipt::STATUS_RECEIVED && Yii::$app->user->can('accountingInboxReceive')): ?>
                                        <?= Html::a('<i class="bi bi-send-check me-1"></i>ส่งการเงิน', ['/finance/inbox/receive-contract-receipt', 'id' => $r->id], [
                                            'class' => 'btn btn-sm btn-primary mb-1',
                                            'data' => ['method' => 'post', 'confirm' => 'ส่งงวดที่ ' . $r->seq . ' ยอด ' . number_format((float) $r->amount, 2) . ' บาท เข้ากล่องรอรับของการเงิน ?'],
                                        ]) ?>
                                    <?php endif; ?>
                                    <?php if ($r->status === ContractReceipt::STATUS_SENT_FINANCE): ?>
                                        <?php if (!empty($inbox) && Yii::$app->user->can('financeView')): ?>
                                            <?= Html::a('<i class="bi bi-eye"></i>', ['/finance/inbox/view', 'id' => $inbox->id], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'ดูรายการในกล่องรอรับ']) ?>
                                        <?php endif; ?>
                                        <?php if (ContractReceiptFinanceSnapshotBuilder::isReturned($inbox ?? null)): ?>
                                            <?= Html::a('<i class="bi bi-arrow-counterclockwise me-1"></i>เปิดแก้ไขเพื่อส่งใหม่', ['receipt-status', 'id' => $r->id, 'to' => ContractReceipt::STATUS_RECEIVED], [
                                                'class' => 'btn btn-sm btn-outline-warning',
                                                'data' => ['method' => 'post', 'confirm' => 'การเงินตีกลับงวดที่ ' . $r->seq . ' — เปิดแก้ไขแล้วส่งใหม่ ?'],
                                            ]) ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if ($r->isEditable()): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">จัดการ</button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><?= Html::a('<i class="bi bi-pencil me-1"></i>แก้ไข', ['receipt-update', 'id' => $r->id], ['class' => 'dropdown-item']) ?></li>
                                                <?php if ($r->status === ContractReceipt::STATUS_DRAFT): ?>
                                                    <li><?= Html::a('<i class="bi bi-check2-circle me-1"></i>ยืนยันตรวจรับ', ['receipt-status', 'id' => $r->id, 'to' => ContractReceipt::STATUS_RECEIVED], [
                                                        'class' => 'dropdown-item text-success',
                                                        'data' => ['method' => 'post', 'confirm' => 'ยืนยันตรวจรับงวดที่ ' . $r->seq . ' ?'],
                                                    ]) ?></li>
                                                    <li><?= Html::a('<i class="bi bi-trash me-1"></i>ลบร่าง', ['receipt-delete', 'id' => $r->id], [
                                                        'class' => 'dropdown-item text-danger',
                                                        'data' => ['method' => 'post', 'confirm' => 'ลบร่างงวดที่ ' . $r->seq . ' ?'],
                                                    ]) ?></li>
                                                <?php else: ?>
                                                    <li><?= Html::a('<i class="bi bi-arrow-counterclockwise me-1"></i>ถอยกลับเป็นร่าง', ['receipt-status', 'id' => $r->id, 'to' => ContractReceipt::STATUS_DRAFT], [
                                                        'class' => 'dropdown-item',
                                                        'data' => ['method' => 'post', 'confirm' => 'ถอยงวดที่ ' . $r->seq . ' กลับเป็นร่าง ?'],
                                                    ]) ?></li>
                                                    <li><?= Html::a('<i class="bi bi-x-circle me-1"></i>ยกเลิกงวด', ['receipt-status', 'id' => $r->id, 'to' => ContractReceipt::STATUS_CANCELLED], [
                                                        'class' => 'dropdown-item text-danger',
                                                        'data' => ['method' => 'post', 'confirm' => 'ยกเลิกงวดที่ ' . $r->seq . ' ? ยอดของงวดนี้จะคืนเข้าวงเงินคงเหลือ'],
                                                    ]) ?></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
