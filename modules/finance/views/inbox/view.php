<?php

use yii\helpers\Html;
use app\modules\finance\models\FinanceInbox;
use app\modules\finance\models\FinanceInboxReview;

$this->title = $model->source_document_no ?: ('รายการ #' . $model->id);
$this->params['breadcrumbs'][] = ['label' => 'บัญชี', 'url' => ['/accounting/dashboard']];
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
                    <dt class="col-sm-4 text-body-secondary">วันที่เอกสาร</dt><dd class="col-sm-8"><?= $model->document_date ? Yii::$app->formatter->asDate($model->document_date, 'php:d/m/Y') : 'ไม่ระบุ' ?></dd>
                    <dt class="col-sm-4 text-body-secondary">ยอดเงิน</dt><dd class="col-sm-8 fw-semibold"><?= $model->amount !== null ? Yii::$app->formatter->asDecimal($model->amount, 2) . ' บาท' : 'ไม่ระบุ' ?></dd>
                </dl>
            </div>
        </section>

        <section class="card border shadow-sm mt-3">
            <div class="card-header bg-body"><h5 class="mb-0">ข้อมูลดิบสำหรับตรวจสอบ</h5></div>
            <div class="card-body">
                <pre class="bg-body-tertiary border rounded p-3 mb-0 overflow-auto"><code><?= Html::encode(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></code></pre>
            </div>
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

        <div class="alert alert-secondary mt-3 mb-0">
            การรับรองในขั้นนี้ยืนยันเฉพาะความครบถ้วนของเอกสาร ยังไม่สร้างเจ้าหนี้และยังไม่ลงบัญชี
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-xl-8">
        <section class="card border shadow-sm" aria-labelledby="review-history-heading">
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
                                    <?= Yii::$app->formatter->asDatetime($review->created_at, 'php:d/m/Y H:i') ?><br>
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
        <section class="card border shadow-sm" aria-labelledby="review-decision-heading">
            <div class="card-header bg-body"><h5 class="mb-0" id="review-decision-heading">ผลการตรวจสอบ</h5></div>
            <div class="card-body">
                <?php if ($model->status === FinanceInbox::STATUS_PENDING_REVIEW && Yii::$app->user->can('accountingPrepare')): ?>
                    <?= Html::beginForm(['review', 'id' => $model->id], 'post') ?>
                    <label class="form-label" for="finance-review-note">หมายเหตุหรือเหตุผล</label>
                    <textarea class="form-control" id="finance-review-note" name="note" rows="4"
                              placeholder="ระบุสิ่งที่ต้องแก้ไขหรือเหตุผลที่ไม่รับรายการ"></textarea>
                    <div class="form-text mb-3">จำเป็นเมื่อขอข้อมูลเพิ่มเติมหรือไม่รับรายการ</div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-success" type="submit" name="decision" value="<?= FinanceInboxReview::DECISION_ACCEPT ?>"
                                <?= $messages ? 'disabled' : '' ?>>
                            <i class="bi bi-check-circle me-1" aria-hidden="true"></i>รับรองรายการ
                        </button>
                        <button class="btn btn-outline-warning" type="submit" name="decision" value="<?= FinanceInboxReview::DECISION_REQUEST_INFORMATION ?>">
                            <i class="bi bi-arrow-return-left me-1" aria-hidden="true"></i>ขอข้อมูลเพิ่มเติม
                        </button>
                        <button class="btn btn-outline-danger" type="submit" name="decision" value="<?= FinanceInboxReview::DECISION_REJECT ?>">
                            <i class="bi bi-x-circle me-1" aria-hidden="true"></i>ไม่รับรายการ
                        </button>
                    </div>
                    <?= Html::endForm() ?>
                <?php elseif ($model->status !== FinanceInbox::STATUS_PENDING_REVIEW): ?>
                    <div class="d-flex gap-2 align-items-start">
                        <i class="bi bi-lock" aria-hidden="true"></i>
                        <div>
                            <strong>ดำเนินการแล้ว</strong>
                            <div class="text-body-secondary">หากต้องแก้ไข ให้ระบบต้นทางส่งเอกสารเป็นรุ่นใหม่</div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="d-flex gap-2 align-items-start">
                        <i class="bi bi-lock" aria-hidden="true"></i>
                        <div><strong>รอผู้จัดทำบัญชีตรวจสอบ</strong><div class="text-body-secondary">คุณมีสิทธิ์ดูข้อมูล แต่ไม่มีสิทธิ์บันทึกผลการตรวจสอบ</div></div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php if ($model->status === FinanceInbox::STATUS_ACCEPTED): ?>
            <section class="card border shadow-sm mt-3">
                <div class="card-header bg-body"><h5 class="mb-0">ทะเบียนเจ้าหนี้</h5></div>
                <div class="card-body">
                    <?php if ($model->payable): ?>
                        <p class="text-body-secondary">สร้างร่างทะเบียนเจ้าหนี้จากรายการนี้แล้ว</p>
                        <?= Html::a(
                            '<i class="bi bi-eye me-1" aria-hidden="true"></i>ดู ' . Html::encode($model->payable->payable_no),
                            ['/accounting/payable/view', 'id' => $model->payable->id],
                            ['class' => 'btn btn-outline-primary']
                        ) ?>
                    <?php elseif (Yii::$app->user->can('accountingPrepare')): ?>
                        <p class="text-body-secondary">ตรวจข้อมูลใบแจ้งหนี้ การวางบิล และผู้ขายก่อนสร้างร่าง</p>
                        <?= Html::a(
                            '<i class="bi bi-file-earmark-plus me-1" aria-hidden="true"></i>สร้างร่างทะเบียนเจ้าหนี้',
                            ['/accounting/payable/create', 'inbox_id' => $model->id],
                            ['class' => 'btn btn-primary']
                        ) ?>
                    <?php else: ?>
                        <div class="d-flex gap-2 align-items-start text-body-secondary">
                            <i class="bi bi-lock" aria-hidden="true"></i><span>รอผู้จัดทำบัญชีสร้างร่างทะเบียนเจ้าหนี้</span>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>
