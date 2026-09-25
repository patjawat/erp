<?php

use yii\helpers\Html;
use app\components\AppHelper;
use app\modules\purchase\models\Contract;
use app\modules\purchase\models\ContractReceipt;
use app\modules\purchase\models\Bond;
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
        <div class="d-flex flex-wrap gap-2">
            <?php if ($model->isInstallment() && !$model->closed_at): ?>
                <?= Html::a('<i class="bi bi-plus-lg me-1"></i>บันทึกตรวจรับงวดใหม่', ['receipt-create', 'contract_id' => $model->id], ['class' => 'btn btn-sm btn-success']) ?>
                <?php if ($receipts): ?>
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#contract-close-box" aria-expanded="false">
                        <i class="bi bi-lock me-1"></i>ปิดสัญญา
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($model->closed_at): ?>
        <div class="alert alert-secondary rounded-0 border-0 border-bottom mb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <i class="bi bi-lock-fill me-1"></i>
                <span class="fw-medium">ปิดสัญญาเมื่อ <?= AppHelper::convertToThai(substr($model->closed_at, 0, 10)) ?></span>
                <div class="small"><?= Html::encode($model->closed_note) ?></div>
            </div>
            <?= Html::a('<i class="bi bi-unlock me-1"></i>เปิดสัญญาอีกครั้ง', ['reopen', 'id' => $model->id], [
                'class' => 'btn btn-sm btn-outline-secondary',
                'data' => ['method' => 'post', 'confirm' => 'เปิดสัญญาอีกครั้ง? สถานะใบสั่งซื้อจะกลับเป็นค่าก่อนปิด'],
            ]) ?>
        </div>
        <?php
        // ปิดสัญญาแล้ว = ถึงเวลาพิจารณาคืนหลักประกันสัญญา (ยกเว้นใบที่ได้รับยกเว้น/คืน/ยึดไปแล้ว)
        $bondsToReturn = array_filter(Bond::forSource(Bond::SOURCE_CONTRACT, $model->id), fn($bond) => in_array($bond->status, [Bond::STATUS_PENDING, Bond::STATUS_ACTIVE], true));
        ?>
        <?php if ($bondsToReturn): ?>
            <div class="alert alert-warning rounded-0 border-0 border-bottom mb-0 small">
                <i class="bi bi-shield-exclamation me-1"></i>
                <span class="fw-medium">หลักประกันสัญญายังไม่ได้คืน <?= count($bondsToReturn) ?> ใบ</span>
                — ตรวจสอบระยะเวลารับประกันความชำรุดบกพร่องก่อนคืน
                <?php foreach ($bondsToReturn as $bond): ?>
                    <div class="mt-1">
                        <?= Html::encode($bond->typeName()) ?> <?= Html::encode($bond->doc_no ?: '') ?>
                        วงเงิน <?= number_format((float) $bond->amount, 2) ?> บาท
                        <?= Html::a('คืนหลักประกัน', ['/purchase/bond/return', 'id' => $bond->id], ['class' => 'btn btn-sm btn-outline-warning ms-1 py-0']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php elseif ($model->isInstallment() && $receipts): ?>
        <?php $blockers = $model->closeBlockers(); ?>
        <div class="collapse" id="contract-close-box">
            <div class="card-body border-bottom bg-body-tertiary">
                <?php if ($blockers): ?>
                    <div class="small text-danger mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>ยังปิดสัญญาไม่ได้: <?= Html::encode(implode(' · ', $blockers)) ?>
                    </div>
                <?php else: ?>
                    <?= Html::beginForm(['close', 'id' => $model->id], 'post') ?>
                    <div class="small mb-2">
                        ปิดสัญญาเมื่อตรวจรับงวดสุดท้ายแล้ว หรือสัญญาสิ้นสุด —
                        วงเงินที่ไม่ได้ใช้จะบันทึกเป็น <span class="fw-medium">เงินเหลือจ่าย</span>
                        และใบสั่งซื้อจะเปลี่ยนเป็น "ส่งการเงิน"
                    </div>
                    <div class="input-group input-group-sm">
                        <?= Html::textInput('closed_note', '', ['class' => 'form-control', 'maxlength' => 300, 'placeholder' => 'หมายเหตุ เช่น สิ้นสุดสัญญา 30 ก.ย. — ใช้ไม่ครบวงเงิน']) ?>
                        <?= Html::submitButton('<i class="bi bi-lock me-1"></i>ยืนยันปิดสัญญา', [
                            'class' => 'btn btn-secondary',
                            'data' => ['confirm' => 'ยืนยันปิดสัญญา? เงินเหลือจ่าย ' . number_format($remaining + $draft, 2) . ' บาท'],
                        ]) ?>
                    </div>
                    <?= Html::endForm() ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
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
                                    <?php if (!$cancelled && $r->status !== ContractReceipt::STATUS_DRAFT && $r->isGoods()): ?>
                                        <div class="small mt-1">
                                            <?php if ($r->isStocked()): ?>
                                                <span class="badge bg-success-subtle text-success-emphasis"><i class="bi bi-box-seam me-1"></i>เข้าคลัง <?= Html::encode($r->stockOrderNo()) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis"><i class="bi bi-hourglass-split me-1"></i>รอรับเข้าคลัง</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
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
                                    <?php if (in_array($r->status, [ContractReceipt::STATUS_RECEIVED, ContractReceipt::STATUS_SENT_FINANCE], true)): ?>
                                        <?= Html::a('<i class="bi bi-file-earmark-word"></i>', ['receipt-word', 'id' => $r->id], [
                                            'class' => 'btn btn-sm btn-outline-primary mb-1',
                                            'title' => 'บันทึกรายงานผลการตรวจรับงวด (Word)',
                                            'target' => '_blank',
                                            'data-pjax' => 0,
                                        ]) ?>
                                    <?php endif; ?>
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
                                        <?php if (!$model->closed_at && ContractReceiptFinanceSnapshotBuilder::isReturned($inbox ?? null)): ?>
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
