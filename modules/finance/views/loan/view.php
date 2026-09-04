<?php

use app\modules\finance\models\FinanceLoan;
use app\modules\finance\models\FinanceLoanFollowup;
use app\modules\finance\models\FinanceLoanItemKind;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var FinanceLoan $model */

$this->title = $model->contract_no;
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนเงินยืม', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<div class="d-flex flex-wrap align-items-center gap-2">
    <h4 class="mb-0"><?= Html::encode($model->contract_no) ?></h4>
    <span class="badge bg-secondary-subtle text-secondary-emphasis"><?= Html::encode($model->statusLabel()) ?></span>
    <span class="badge <?= $model->dueBadgeClass() ?>"><?= Html::encode($model->dueLabel()) ?></span>
</div>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?><?= Html::encode($model->borrower_name) ?><?= $model->borrower_position ? ' · ' . Html::encode($model->borrower_position) : '' ?><?php $this->endBlock();

$date = fn($value, $fallback = '—') => $value ? Yii::$app->formatter->asDate($value, 'php:d/m/Y') : $fallback;
$registerTotals = $model->registerTotals();
$columns = FinanceLoanItemKind::registerColumnOptions();
?>

<div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับทะเบียน', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach ($model->allowedTransitions() as $target => $label): ?>
            <?php
            $blocker = $model->transitionBlocker($target);
            $isCancel = $target === FinanceLoan::STATUS_CANCELLED;
            $class = $isCancel ? 'btn btn-outline-danger' : 'btn btn-success';
            if ($target === FinanceLoan::STATUS_REQUESTED || $target === FinanceLoan::STATUS_REVIEWED) {
                $class = 'btn btn-outline-secondary';
            }
            ?>
            <?= Html::beginForm(['transition', 'id' => $model->id, 'to' => $target], 'post', ['class' => 'd-inline']) ?>
                <?php if ($target === FinanceLoan::STATUS_RECEIVED): ?>
                    <input type="hidden" name="received_at" value="<?= Html::encode($model->received_at ?: date('Y-m-d')) ?>">
                <?php endif; ?>
                <?= Html::submitButton(Html::encode($label), [
                    'class' => $class,
                    'disabled' => (bool) $blocker,
                    'title' => $blocker ?: null,
                    'data-confirm' => $isCancel ? 'ยกเลิกใบยืม ' . $model->contract_no . ' ใช่หรือไม่' : null,
                ]) ?>
            <?= Html::endForm() ?>
        <?php endforeach; ?>
        <?= Html::a('<i class="bi bi-printer me-1"></i> พิมพ์เอกสาร', ['/finance/loan-document/index', 'loan_id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
        <?= Html::a('<i class="bi bi-pencil me-1"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::beginForm(['delete', 'id' => $model->id], 'post', ['class' => 'd-inline'])
            . Html::submitButton('<i class="bi bi-trash"></i><span class="visually-hidden">ลบ</span>', [
                'class' => 'btn btn-outline-danger',
                'title' => 'ลบใบยืม',
                'data-confirm' => 'ลบใบยืม ' . $model->contract_no . ' พร้อมบรรทัดประมาณการทั้งหมดหรือไม่',
            ])
            . Html::endForm() ?>
    </div>
</div>

<?php $blockedNotes = array_filter(array_map(fn($target) => $model->transitionBlocker($target), array_keys($model->allowedTransitions()))); ?>
<?php if ($blockedNotes): ?>
<div class="alert alert-warning d-flex align-items-start gap-2" role="status">
    <i class="bi bi-exclamation-triangle mt-1" aria-hidden="true"></i>
    <div><?= Html::encode(implode(' · ', array_unique($blockedNotes))) ?></div>
</div>
<?php endif; ?>

<div class="row g-3">
<div class="col-12 col-xl-8">

    <section class="card border mb-3" aria-labelledby="detail-heading">
        <div class="card-header bg-body"><h5 class="mb-0" id="detail-heading">รายละเอียดใบยืม</h5></div>
        <div class="card-body"><dl class="row mb-0 g-3">
            <div class="col-md-6">
                <dt class="text-body-secondary small fw-normal">ผู้ยืม</dt>
                <dd class="fw-semibold mb-0"><?= Html::encode($model->borrower_name) ?></dd>
                <dd class="text-body-secondary mb-0 small"><?= Html::encode($model->borrower_position ?: 'ไม่ระบุตำแหน่ง') ?><?= $model->borrower_emp_id ? ' · ผูกทะเบียนบุคลากรแล้ว' : '' ?></dd>
            </div>
            <div class="col-md-3">
                <dt class="text-body-secondary small fw-normal">ประเภทค่าใช้จ่าย</dt>
                <dd class="mb-0"><?= Html::encode($model->expenseType->name ?? '—') ?></dd>
            </div>
            <div class="col-md-3">
                <dt class="text-body-secondary small fw-normal">ยืมจากบัญชี</dt>
                <dd class="mb-0"><?= Html::encode($model->account ? $model->account->displayName() : '—') ?></dd>
            </div>
            <div class="col-12">
                <dt class="text-body-secondary small fw-normal">วัตถุประสงค์ในการยืม</dt>
                <dd class="mb-0"><?= nl2br(Html::encode($model->purpose)) ?></dd>
            </div>
            <div class="col-md-6">
                <dt class="text-body-secondary small fw-normal">บันทึกขออนุมัติ</dt>
                <dd class="mb-0"><?= Html::encode($model->request_document_no ?: '—') ?><?= $model->request_document_date ? ' ลงวันที่ ' . $date($model->request_document_date) : '' ?></dd>
            </div>
            <div class="col-md-3">
                <dt class="text-body-secondary small fw-normal">ปีงบประมาณ</dt>
                <dd class="mb-0"><?= Html::encode($model->fiscal_year) ?></dd>
            </div>
            <div class="col-md-3">
                <dt class="text-body-secondary small fw-normal">ที่มาของข้อมูล</dt>
                <dd class="mb-0"><?= Html::encode(FinanceLoan::sourceOptions()[$model->source_ref_type] ?? $model->source_ref_type) ?></dd>
            </div>
        </dl></div>
    </section>

    <section class="card border mb-3" aria-labelledby="estimate-heading">
        <div class="card-header bg-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0" id="estimate-heading">ประมาณการค่าใช้จ่าย</h5>
            <span class="text-body-secondary small"><?= count($model->items) ?> รายการ</span>
        </div>
        <?php if ($model->items): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr>
                    <th>รายการ</th>
                    <th>วิธีคิด</th>
                    <th>รวมในช่อง</th>
                    <th class="text-end">เป็นเงิน</th>
                </tr></thead>
                <tbody>
                <?php foreach ($model->items as $item): ?>
                    <tr>
                        <td><?= Html::encode($item->displayName()) ?></td>
                        <td class="text-body-secondary small"><?= Html::encode($item->calculationText() ?: '—') ?></td>
                        <td class="small"><?= Html::encode($columns[$item->registerColumn()] ?? '—') ?></td>
                        <td class="text-end font-monospace text-nowrap"><?= number_format($item->amount, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot><tr class="bg-body-tertiary">
                    <th colspan="3" class="text-end">รวมเป็นเงินยืมทั้งสิ้น</th>
                    <th class="text-end font-monospace"><?= number_format($model->approved_amount, 2) ?></th>
                </tr></tfoot>
            </table>
        </div>
        <?php else: ?>
        <div class="card-body text-center text-body-secondary py-4">
            ยังไม่มีบรรทัดประมาณการ ยอดเงินยืมจึงเป็น 0 — กด “แก้ไข” เพื่อเพิ่มรายการ
        </div>
        <?php endif; ?>
    </section>

    <?php
    $canSettle = in_array($model->status, [FinanceLoan::STATUS_RECEIVED, FinanceLoan::STATUS_CLEARED, FinanceLoan::STATUS_COMPLETED], true);
    ?>
    <section class="card border" aria-labelledby="settlement-heading">
        <div class="card-header bg-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0" id="settlement-heading">การส่งใช้เงินยืม</h5>
            <?php if ($canSettle && $model->status !== FinanceLoan::STATUS_COMPLETED): ?>
                <?= Html::a('<i class="bi bi-plus-lg me-1"></i> บันทึกการส่งใช้', ['settle', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
            <?php endif; ?>
        </div>
        <?php if ($model->settlements): ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr>
                    <th>ครั้งที่</th><th>วันที่ส่งใช้</th>
                    <th class="text-end">ใบสำคัญ</th><th class="text-end">เงินสด</th><th class="text-end">คงค้าง</th>
                    <th>เลขที่ บร./บค.</th>
                    <th style="width:92px"><span class="visually-hidden">จัดการ</span></th>
                </tr></thead>
                <tbody>
                <?php foreach ($model->settlements as $settlement): ?>
                    <tr>
                        <td><?= (int) $settlement->seq ?></td>
                        <td class="text-nowrap"><?= $date($settlement->settled_at) ?></td>
                        <td class="text-end font-monospace"><?= number_format($settlement->voucher_amount, 2) ?></td>
                        <td class="text-end font-monospace"><?= number_format($settlement->cash_amount, 2) ?></td>
                        <td class="text-end font-monospace<?= (float) $settlement->balance_after <= 0 ? ' text-success-emphasis' : '' ?>"><?= number_format($settlement->balance_after, 2) ?></td>
                        <td><?= Html::encode($settlement->receipt_no ?: '—') ?></td>
                        <td class="text-nowrap">
                            <?= Html::a('<i class="bi bi-pencil"></i>', ['settle-update', 'id' => $settlement->id], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'แก้ไข', 'aria-label' => 'แก้ไขการส่งใช้ครั้งที่ ' . (int) $settlement->seq]) ?>
                            <?= Html::beginForm(['settle-delete', 'id' => $settlement->id], 'post', ['class' => 'd-inline'])
                                . Html::submitButton('<i class="bi bi-trash"></i>', [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'title' => 'ลบ',
                                    'aria-label' => 'ลบการส่งใช้ครั้งที่ ' . (int) $settlement->seq,
                                    'data-confirm' => 'ลบการส่งใช้ครั้งที่ ' . (int) $settlement->seq . ' และคำนวณยอดคงค้างใหม่หรือไม่',
                                ])
                                . Html::endForm() ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot><tr class="bg-body-tertiary">
                    <th colspan="2" class="text-end">รวมส่งใช้แล้ว</th>
                    <th class="text-end font-monospace"><?= number_format($model->voucher_amount, 2) ?></th>
                    <th class="text-end font-monospace"><?= number_format($model->cash_return_amount, 2) ?></th>
                    <th class="text-end font-monospace"><?= number_format($model->outstanding_amount, 2) ?></th>
                    <th colspan="2"></th>
                </tr></tfoot>
            </table>
        </div>
        <?php elseif ($canSettle): ?>
        <div class="card-body text-body-secondary small">ยังไม่มีการส่งใช้ กด “บันทึกการส่งใช้” เมื่อผู้ยืมนำใบสำคัญหรือเงินสดมาคืน</div>
        <?php else: ?>
        <div class="card-body text-body-secondary small">
            บันทึกการส่งใช้ได้หลังจากบันทึกรับเช็คแล้ว — สถานะปัจจุบันคือ “<?= Html::encode($model->statusLabel()) ?>”
        </div>
        <?php endif; ?>
    </section>

</div>

<div class="col-12 col-xl-4">
    <section class="card border mb-3" aria-labelledby="amount-heading">
        <div class="card-header bg-body"><h5 class="mb-0" id="amount-heading">สรุปยอดเงิน</h5></div>
        <div class="card-body">
            <?php foreach ($columns as $key => $label): ?>
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-body-secondary"><?= Html::encode($label) ?></span>
                <span class="font-monospace"><?= number_format($registerTotals[$key] ?? 0, 2) ?></span>
            </div>
            <?php endforeach; ?>
            <div class="d-flex justify-content-between py-3 fw-semibold">
                <span>ยอดเงินยืม</span>
                <span class="font-monospace"><?= number_format($model->approved_amount, 2) ?></span>
            </div>
            <div class="bg-body-tertiary border rounded p-3">
                <div class="d-flex justify-content-between mb-2"><span>ใบสำคัญ</span><span class="font-monospace"><?= number_format($model->voucher_amount, 2) ?></span></div>
                <div class="d-flex justify-content-between mb-2"><span>เงินสดคืน</span><span class="font-monospace"><?= number_format($model->cash_return_amount, 2) ?></span></div>
                <div class="d-flex justify-content-between fw-semibold border-top pt-2">
                    <span>ยอดคงเหลือ</span>
                    <span class="font-monospace"><?= number_format($model->outstanding_amount, 2) ?></span>
                </div>
            </div>
        </div>
    </section>

    <section class="card border mb-3" aria-labelledby="schedule-heading">
        <div class="card-header bg-body"><h5 class="mb-0" id="schedule-heading">กำหนดเวลา</h5></div>
        <div class="card-body"><dl class="row mb-0 g-2 small">
            <dt class="col-6 text-body-secondary fw-normal">วันที่ยืม</dt><dd class="col-6 mb-0 text-end"><?= $date($model->borrowed_at) ?></dd>
            <dt class="col-6 text-body-secondary fw-normal">วันที่รับเงิน</dt><dd class="col-6 mb-0 text-end"><?= $date($model->received_at) ?></dd>
            <dt class="col-6 text-body-secondary fw-normal">เริ่มดำเนินการ</dt><dd class="col-6 mb-0 text-end"><?= $date($model->activity_start_at) ?></dd>
            <dt class="col-6 text-body-secondary fw-normal">ดำเนินการเสร็จ</dt><dd class="col-6 mb-0 text-end"><?= $date($model->activity_end_at) ?></dd>
            <dt class="col-6 fw-semibold">กำหนดการคืน</dt><dd class="col-6 mb-0 text-end fw-semibold"><?= $date($model->due_at, 'ยังไม่กำหนด') ?></dd>
        </dl>
        <div class="alert alert-light border mt-3 mb-0 py-2 px-3 small" role="status"><?= Html::encode($model->dueRuleText()) ?></div>
        </div>
    </section>

    <?php $canFollowup = !$model->isClosed() && (float) $model->outstanding_amount > 0; ?>
    <section class="card border" aria-labelledby="followup-heading">
        <div class="card-header bg-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0" id="followup-heading">การติดตาม</h5>
            <?php if ($canFollowup): ?>
                <?= Html::a('<i class="bi bi-envelope-paper me-1"></i> ออกหนังสือ', ['followup', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-danger']) ?>
            <?php endif; ?>
        </div>
        <?php if ($model->followups): ?>
        <ul class="list-group list-group-flush">
            <?php foreach ($model->followups as $followup): ?>
            <?php $isLetter = $followup->channel === FinanceLoanFollowup::CHANNEL_LETTER; ?>
            <li class="list-group-item">
                <div class="d-flex justify-content-between gap-2">
                    <span class="small fw-semibold">
                        <i class="bi <?= $isLetter ? 'bi-envelope-paper' : 'bi-send' ?> me-1" aria-hidden="true"></i>
                        <?= Html::encode($followup->channelLabel()) ?><?= $followup->letter_seq ? ' ครั้งที่ ' . (int) $followup->letter_seq : '' ?>
                    </span>
                    <span class="small text-body-secondary text-nowrap"><?= $followup->notified_at ? Yii::$app->formatter->asDatetime($followup->notified_at, 'php:d/m/Y H:i') : '—' ?></span>
                </div>
                <div class="small text-body-secondary">
                    <?= Html::encode($followup->stageLabel()) ?>
                    <?= $followup->letter_no ? ' · ' . Html::encode($followup->letter_no) : '' ?>
                    <?= $followup->new_due_at ? ' · กำหนดใหม่ ' . $date($followup->new_due_at) : '' ?>
                    <?= $followup->recipient ? ' · แจ้ง ' . Html::encode($followup->recipient) : '' ?>
                </div>
                <div class="mt-2 d-flex gap-2">
                    <?php if ($isLetter): ?>
                        <?= Html::a('<i class="bi bi-printer me-1"></i> พิมพ์หนังสือ', '#', [
                            'class' => 'btn btn-sm btn-outline-primary open-modal',
                            'data' => [
                                'url' => \yii\helpers\Url::to(['/finance/loan-document/open-letter', 'followup_id' => $followup->id]),
                                'size' => 'modal-xl',
                            ],
                        ]) ?>
                    <?php endif; ?>
                    <?= Html::beginForm(['followup-delete', 'id' => $followup->id], 'post', ['class' => 'd-inline'])
                        . Html::submitButton('<i class="bi bi-trash"></i>', [
                            'class' => 'btn btn-sm btn-outline-secondary',
                            'title' => 'ลบรายการติดตามนี้',
                            'aria-label' => 'ลบรายการติดตาม',
                            'data-confirm' => 'ลบรายการติดตามนี้ออกจากประวัติหรือไม่',
                        ])
                        . Html::endForm() ?>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <div class="card-body text-body-secondary small">
            <?= $canFollowup
                ? 'ยังไม่มีประวัติการติดตาม ระบบจะเตือนผู้ยืมทาง Telegram ให้อัตโนมัติเมื่อใกล้ครบกำหนดและเมื่อเกินกำหนด'
                : 'ไม่มียอดค้าง จึงไม่ต้องติดตาม' ?>
        </div>
        <?php endif; ?>
    </section>
</div>
</div>

<?php if (trim((string) $model->note) !== ''): ?>
<section class="card border mt-3" aria-labelledby="note-heading">
    <div class="card-header bg-body"><h5 class="mb-0" id="note-heading">หมายเหตุ</h5></div>
    <div class="card-body mb-0"><?= nl2br(Html::encode($model->note)) ?></div>
</section>
<?php endif; ?>
