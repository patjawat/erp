<?php

use yii\helpers\Html;
use app\modules\finance\models\FinanceInbox;
use app\modules\finance\models\FinanceInboxReview;

$this->title = $model->source_document_no ?: ('รายการ #' . $model->id);
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'กล่องรับงานบัญชี', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo Html::encode($model->source_system . ' · ' . $model->source_type . ' · รุ่น ' . $model->source_version);
$this->endBlock();
$this->beginBlock('page-action');
echo Html::a('<i class="bi bi-arrow-left me-1" aria-hidden="true"></i>กลับกล่องรับ', ['index'], ['class' => 'btn btn-outline-secondary']);
$this->endBlock();

$messages = $model->validationMessages();
$payload = is_array($model->payload_json) ? $model->payload_json : json_decode((string) $model->payload_json, true);
?>

<div class="row g-3">
    <div class="col-xl-8">
        <section class="card border shadow-sm">
            <div class="card-header bg-body d-flex justify-content-between align-items-center gap-2">
                <h5 class="mb-0">Snapshot จากระบบต้นทาง</h5>
                <span class="badge <?= FinanceInbox::statusBadgeClass($model->status) ?>">
                    <?= Html::encode(FinanceInbox::statusOptions()[$model->status] ?? $model->status) ?>
                </span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-body-secondary">ระบบต้นทาง</dt><dd class="col-sm-8"><?= Html::encode($model->source_system) ?></dd>
                    <dt class="col-sm-4 text-body-secondary">รหัสต้นทาง</dt><dd class="col-sm-8"><?= Html::encode($model->source_id) ?></dd>
                    <dt class="col-sm-4 text-body-secondary">ผู้แทนจำหน่าย</dt><dd class="col-sm-8"><?= Html::encode($model->vendor_name_snapshot ?: 'รอตรวจสอบ') ?></dd>
                    <dt class="col-sm-4 text-body-secondary">รหัสผู้แทนจำหน่าย</dt><dd class="col-sm-8"><?= Html::encode($model->vendor_code_snapshot ?: 'ไม่ระบุ') ?></dd>
                    <dt class="col-sm-4 text-body-secondary">วันที่เอกสาร</dt><dd class="col-sm-8"><?= $model->document_date ? \app\modules\finance\components\ThaiDate::date($model->document_date) : 'ไม่ระบุ' ?></dd>
                    <dt class="col-sm-4 text-body-secondary">ยอดเงิน</dt><dd class="col-sm-8 fw-semibold"><?= $model->amount !== null ? Yii::$app->formatter->asDecimal($model->amount, 2) . ' บาท' : 'ไม่ระบุ' ?></dd>
                </dl>
            </div>
        </section>

        <?php
        $vat = is_array($payload['vat'] ?? null) ? $payload['vat'] : null;
        $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
        $dec = fn($v) => Yii::$app->formatter->asDecimal((float) $v, 2);
        $vatTypeLabel = ['IN' => 'ราคารวมภาษี (VAT in)', 'OUT' => 'ราคายังไม่รวมภาษี (VAT out)', 'NONE' => 'ไม่มีภาษี'];
        ?>
        <section class="card border shadow-sm mt-3">
            <div class="card-header bg-body d-flex justify-content-between align-items-center gap-2">
                <h5 class="mb-0">รายละเอียดสำหรับตรวจสอบ</h5>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#raw-json">
                    <i class="bi bi-code-slash me-1"></i>ข้อมูลดิบ (JSON)
                </button>
            </div>
            <div class="card-body">
                <?php if ($vat): ?>
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-body-secondary">มูลค่าก่อนภาษี</div><div class="fw-semibold text-end"><?= $dec($vat['before_vat'] ?? 0) ?></div></div></div>
                        <div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-body-secondary">ภาษีมูลค่าเพิ่ม</div><div class="fw-semibold text-end"><?= $dec($vat['vat_amount'] ?? 0) ?></div></div></div>
                        <div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-body-secondary">รวมทั้งสิ้น</div><div class="fw-semibold text-end"><?= $dec($vat['after_vat'] ?? 0) ?></div></div></div>
                        <div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-body-secondary">ประเภทภาษี</div><div class="fw-semibold"><?= Html::encode($vatTypeLabel[$vat['type'] ?? ''] ?? ($vat['type'] ?? '-')) ?></div></div></div>
                    </div>
                <?php endif; ?>

                <?php if ($items): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light text-center">
                                <tr><th style="width:44px">#</th><th>รหัสสินค้า</th><th>รายละเอียด</th><th class="text-end" style="width:80px">จำนวน</th><th class="text-end" style="width:110px">ราคา/หน่วย</th><th class="text-end" style="width:120px">จำนวนเงิน</th></tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; $sum = 0; foreach ($items as $it): $sum += (float) ($it['line_amount'] ?? 0); ?>
                                    <tr>
                                        <td class="text-center"><?= $i++ ?></td>
                                        <td class="font-monospace"><?= Html::encode((string) ($it['item_code'] ?? '-')) ?></td>
                                        <td><?= Html::encode((string) ($it['description'] ?? '')) ?: '<span class="text-body-secondary">—</span>' ?></td>
                                        <td class="text-end"><?= $dec($it['quantity'] ?? 0) ?></td>
                                        <td class="text-end"><?= $dec($it['unit_price'] ?? 0) ?></td>
                                        <td class="text-end"><?= $dec($it['line_amount'] ?? 0) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light fw-semibold">
                                <tr><td colspan="5" class="text-end">รวมมูลค่าสินค้า</td><td class="text-end"><?= $dec($sum) ?></td></tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if (!$vat && !$items): ?>
                    <p class="text-body-secondary mb-0">ไม่มีรายละเอียดสินค้า/ภาษีในเอกสารต้นทาง</p>
                <?php endif; ?>

                <div class="collapse mt-3" id="raw-json">
                    <pre class="bg-body-tertiary border rounded p-3 mb-0 overflow-auto small"><code><?= Html::encode(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></code></pre>
                </div>
            </div>
        </section>

        <section class="card border shadow-sm mt-3" aria-labelledby="review-history-heading">
            <div class="card-header bg-body d-flex justify-content-between align-items-center gap-2">
                <h5 class="mb-0" id="review-history-heading">ประวัติการตรวจสอบ</h5>
                <span class="text-body-secondary small"><?= number_format(count($reviews)) ?> รายการ</span>
            </div>
            <?php if (!$reviews): ?>
                <div class="card-body text-body-secondary">ยังไม่มีการตัดสินใจ รายการอยู่ระหว่างรอตรวจสอบ</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($reviews as $review): ?>
                        <div class="list-group-item py-3">
                            <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                                <div>
                                    <strong><?= Html::encode(FinanceInboxReview::decisionOptions()[$review->decision] ?? $review->decision) ?></strong>
                                    <?php if ($review->note): ?>
                                        <div class="text-body-secondary mt-1"><?= nl2br(Html::encode($review->note)) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-body-secondary text-sm-end text-nowrap">
                                    <?= \app\modules\finance\components\ThaiDate::datetime($review->created_at) ?><br>
                                    ผู้ใช้งาน #<?= Html::encode((string) ($review->created_by ?: '-')) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div class="col-xl-4">
        <section class="card border shadow-sm">
            <div class="card-header bg-body"><h5 class="mb-0">ผลตรวจเบื้องต้น</h5></div>
            <div class="card-body">
                <?php if (!$messages): ?>
                    <div class="d-flex gap-2 text-success-emphasis">
                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                        <span>ข้อมูลขั้นต่ำครบ พร้อมให้เจ้าหน้าที่บัญชีตรวจรายละเอียด</span>
                    </div>
                <?php else: ?>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($messages as $message): ?>
                            <li class="mb-2"><?= Html::encode($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>

        <section class="card border shadow-sm mt-3" aria-labelledby="receive-heading">
            <div class="card-header bg-body"><h5 class="mb-0" id="receive-heading">การรับเอกสาร</h5></div>
            <div class="card-body">
                <?php if ($model->payable): ?>
                    <p class="mb-2"><i class="bi bi-check-circle-fill text-success me-1"></i>รับแล้ว — เข้าทะเบียนเจ้าหนี้</p>
                    <?= Html::a('<i class="bi bi-journal-text me-1"></i>' . Html::encode($model->payable->payable_no),
                        ['/finance/payable/view', 'id' => $model->payable->id], ['class' => 'btn btn-outline-primary w-100']) ?>
                <?php elseif (in_array($model->status, [FinanceInbox::STATUS_PENDING_REVIEW, FinanceInbox::STATUS_ACCEPTED], true) && Yii::$app->user->can('financeOperate')): ?>
                    <?php if ($messages): ?>
                        <div class="alert alert-warning small" role="alert">
                            <i class="bi bi-exclamation-triangle me-1"></i>ข้อมูลจากพัสดุยังไม่ครบ รับไม่ได้ — ส่งคืนพัสดุให้แก้ไข
                        </div>
                    <?php else: ?>
                        <?= Html::beginForm(['receive', 'id' => $model->id], 'post') ?>
                        <button type="submit" class="btn btn-success w-100"><i class="bi bi-check2 me-1"></i>รับเอกสาร</button>
                        <?= Html::endForm() ?>
                        <div class="form-text">บิลเข้าทะเบียนเจ้าหนี้ทันที — เลขใบแจ้งหนี้/ภาษีหัก ณ ที่จ่าย แก้ภายหลังได้ที่หน้าบิล</div>
                    <?php endif; ?>

                    <?php if ($model->status === FinanceInbox::STATUS_PENDING_REVIEW): ?>
                        <details class="mt-3">
                            <summary class="small text-body-secondary">ส่งคืนพัสดุ / ไม่รับ</summary>
                            <?= Html::beginForm(['review', 'id' => $model->id], 'post', ['class' => 'mt-2']) ?>
                            <textarea class="form-control form-control-sm mb-2" name="note" rows="2" required
                                      placeholder="เหตุผล / สิ่งที่ต้องแก้ไข"></textarea>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-warning flex-fill" type="submit" name="decision" value="<?= FinanceInboxReview::DECISION_REQUEST_INFORMATION ?>">ส่งคืนให้แก้ไข</button>
                                <button class="btn btn-sm btn-outline-danger flex-fill" type="submit" name="decision" value="<?= FinanceInboxReview::DECISION_REJECT ?>">ไม่รับ</button>
                            </div>
                            <?= Html::endForm() ?>
                        </details>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-body-secondary mb-0"><?= Html::encode(FinanceInbox::statusOptions()[$model->status] ?? $model->status) ?></p>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
