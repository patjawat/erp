<?php

use yii\helpers\Html;
use app\components\AppHelper;
use app\modules\purchase\models\Bond;
use app\modules\purchase\models\Contract;
use app\modules\purchase\components\BondCalculator;
use app\modules\purchase\components\ContractCalculator;
use app\modules\purchase\components\ContractWordExporter;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Contract $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'บริหารสัญญา', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$badge = Contract::statusBadge($model->status);
$milestones = $model->milestones;
$fine = $model->fineInfo();
$wht = $model->whtInfo();
$diff = $model->diffFromOrder();
$ratio = ContractCalculator::fineRatio((float) $model->fine_amount, (float) $model->budget);

$bonds = Bond::forSource(Bond::SOURCE_CONTRACT, $model->id);
$bondPolicy = BondCalculator::policyFor((float) $model->budget, $model->contract_type);
// นับเฉพาะใบที่ยังเดินอยู่ ใบที่คืนไปแล้วไม่ถือว่าสัญญานี้ยังมีหลักประกันค้ำอยู่
$bondOpen = array_filter($bonds, function ($bond) {
    return in_array($bond->status, Bond::openStatuses(), true);
});

$date = function ($value) {
    return $value ? AppHelper::convertToThai($value) : '—';
};
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-file-earmark-check"></i> <?= Html::encode($this->title) ?>
    <span class="badge text-bg-<?= $badge['color'] ?> align-middle"><?= $badge['label'] ?></span>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?= Html::encode($model->typeName()) ?> เลขที่ <?= Html::encode($model->contract_no ?: ($model->doc_no ?: '—')) ?>
· ปีงบประมาณ <?= $model->thai_year ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<div class="d-flex flex-wrap gap-2">
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i>ทะเบียนสัญญา', ['index'], ['class' => 'btn btn-sm btn-outline-secondary rounded-pill px-3']) ?>
    <?= Html::a('<i class="bi bi-file-earmark-word me-1"></i>ร่างสัญญา', ['word', 'id' => $model->id], [
        'class' => 'btn btn-sm btn-outline-primary rounded-pill px-3',
        'target' => '_blank',
    ]) ?>
    <?php if ((float) $model->fine_amount > 0): ?>
        <?= Html::a('<i class="bi bi-file-earmark-word me-1"></i>แจ้งค่าปรับ', [
            'word',
            'id' => $model->id,
            'type' => ContractWordExporter::DOC_FINE,
        ], [
            'class' => 'btn btn-sm btn-outline-danger rounded-pill px-3',
            'target' => '_blank',
        ]) ?>
    <?php endif; ?>
    <?= Html::a('<i class="bi bi-pencil me-1"></i>แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary rounded-pill px-3']) ?>
    <?= Html::a('<i class="bi bi-trash me-1"></i>ลบ', ['delete', 'id' => $model->id], [
        'class' => 'btn btn-sm btn-outline-danger rounded-pill px-3',
        'data' => [
            'confirm' => 'ยืนยันการลบสัญญา "' . $model->title . '" ?',
            'method' => 'post',
        ],
    ]) ?>
</div>
<?php $this->endBlock(); ?>

<?php if ($diff): ?>
    <div class="alert alert-warning">
        <div class="fw-medium mb-2">
            <i class="bi bi-exclamation-triangle me-1"></i>
            ค่าในสัญญาต่างจากใบสั่งซื้อที่ผูกไว้
        </div>
        <div class="small mb-2">
            สัญญาเก็บสำเนาของตัวเองเพื่อให้ค่าปรับที่คำนวณไว้ไม่เปลี่ยนตามใบสั่งซื้อ
            ความต่างนี้จึงไม่ใช่ข้อผิดพลาดเสมอไป แต่ต้องตรวจว่าเป็นค่าที่ตั้งใจ
        </div>
        <table class="table table-sm mb-0 w-auto">
            <thead>
                <tr>
                    <th>รายการ</th>
                    <th>ในสัญญา</th>
                    <th>ในใบสั่งซื้อ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($diff as $row): ?>
                    <tr>
                        <td><?= Html::encode($row['label']) ?></td>
                        <td class="fw-medium"><?= Html::encode($row['contract'] ?: '—') ?></td>
                        <td><?= Html::encode($row['order']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if ($wht['rate'] === null): ?>
    <div class="alert alert-danger d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <i class="bi bi-exclamation-octagon me-1"></i>
            <?= Html::encode($wht['reason']) ?> — เอกสารที่พิมพ์ออกไปจะไม่มีจำนวนภาษีหัก ณ ที่จ่าย
        </div>
        <?= Html::a('ไปที่หน้าตั้งค่าอัตราภาษี', ['/purchase/wht-rate'], ['class' => 'btn btn-sm btn-danger rounded-pill px-3']) ?>
    </div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-8">

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">ข้อมูลสัญญา</h6></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">ประเภทสัญญา</dt>
                    <dd class="col-sm-8"><?= Html::encode($model->typeName()) ?></dd>

                    <dt class="col-sm-4">คู่สัญญา</dt>
                    <dd class="col-sm-8">
                        <?= Html::encode($model->partyName()) ?>
                        <span class="text-muted small">
                            (<?= Html::encode(\app\modules\purchase\models\WhtRate::partyTypeList()[$model->party_type] ?? '—') ?>)
                        </span>
                    </dd>

                    <dt class="col-sm-4">วงเงินตามสัญญา</dt>
                    <dd class="col-sm-8">
                        <span class="fw-semibold"><?= number_format((float) $model->budget, 2) ?></span> บาท
                        <span class="text-muted small">
                            <?= (int) $model->vat_included === 1 ? '(รวม VAT แล้ว)' : '(ยังไม่รวม VAT)' ?>
                        </span>
                    </dd>

                    <dt class="col-sm-4">เลขที่โครงการ e-GP</dt>
                    <dd class="col-sm-8"><?= Html::encode($model->egp_no ?: '—') ?></dd>

                    <dt class="col-sm-4">ใบสั่งซื้อที่ผูก</dt>
                    <dd class="col-sm-8">
                        <?php if ($model->order_id && $model->order): ?>
                            <?= Html::encode($model->order->po_number ?: ('id ' . $model->order_id)) ?>
                        <?php else: ?>
                            <span class="text-muted">ไม่ได้ผูกกับใบสั่งซื้อในระบบ</span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-4">TOR ที่ใช้</dt>
                    <dd class="col-sm-8">
                        <?php if ($model->tor_id && $model->tor): ?>
                            <?= Html::a(
                                Html::encode($model->tor->doc_no ?: $model->tor->title),
                                ['/purchase/tor/view', 'id' => $model->tor_id]
                            ) ?>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-4">ผู้บันทึก</dt>
                    <dd class="col-sm-8"><?= Html::encode($model->empName()) ?></dd>

                    <?php if ($model->note): ?>
                        <dt class="col-sm-4">หมายเหตุภายใน</dt>
                        <dd class="col-sm-8"><?= nl2br(Html::encode($model->note)) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">กำหนดเวลา</h6></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">วันลงนามในสัญญา</dt>
                    <dd class="col-sm-8"><?= $date($model->sign_date) ?></dd>

                    <dt class="col-sm-4">วันเริ่มนับระยะเวลา</dt>
                    <dd class="col-sm-8"><?= $date($model->start_date) ?></dd>

                    <dt class="col-sm-4">วันครบกำหนดส่งมอบ</dt>
                    <dd class="col-sm-8">
                        <?= $date($model->end_date) ?>
                        <?php if ($model->isOverdue()): ?>
                            <span class="badge text-bg-danger ms-1">
                                เลยกำหนด <?= abs((int) $model->daysToDue()) ?> วัน
                            </span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-4">วันที่ส่งมอบจริง</dt>
                    <dd class="col-sm-8"><?= $date($model->delivery_date) ?></dd>

                    <dt class="col-sm-4">วันที่ตรวจรับ</dt>
                    <dd class="col-sm-8"><?= $date($model->receive_date) ?></dd>

                    <dt class="col-sm-4">สิ้นสุดการรับประกัน</dt>
                    <dd class="col-sm-8"><?= $date($model->warranty_end) ?></dd>
                </dl>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">งวดงาน</h6>
                <span class="badge text-bg-secondary"><?= count($milestones) ?> งวด</span>
            </div>
            <div class="card-body p-0">
                <?php if ($milestones): ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width:56px">งวด</th>
                                    <th>รายละเอียด</th>
                                    <th class="text-end" style="width:120px">วงเงิน</th>
                                    <th style="width:120px">กำหนดส่ง</th>
                                    <th style="width:120px">ส่งมอบจริง</th>
                                    <th class="text-end" style="width:110px">ค่าปรับ</th>
                                    <th style="width:110px">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($milestones as $ms): ?>
                                    <?php $msBadge = $ms::statusBadge($ms->status); ?>
                                    <tr>
                                        <td class="text-center"><?= (int) $ms->seq ?></td>
                                        <td class="small"><?= Html::encode($ms->detail ?: '—') ?></td>
                                        <td class="text-end"><?= number_format((float) $ms->amount, 2) ?></td>
                                        <td class="small"><?= $date($ms->due_date) ?></td>
                                        <td class="small"><?= $date($ms->delivered_date) ?></td>
                                        <td class="text-end">
                                            <?php if ((float) $ms->fine_amount > 0): ?>
                                                <span class="text-danger"><?= number_format((float) $ms->fine_amount, 2) ?></span>
                                                <div class="small text-muted"><?= (int) $ms->fine_days ?> วัน</div>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge text-bg-<?= $msBadge['color'] ?>"><?= $msBadge['label'] ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">สัญญานี้ไม่ได้แบ่งงวดงาน</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0">
                    หลักประกัน
                    <span class="badge text-bg-secondary"><?= count($bonds) ?></span>
                </h6>
                <?= Html::a('<i class="bi bi-plus-circle me-1"></i>บันทึกหลักประกัน', [
                    '/purchase/bond/create',
                    'contract_id' => $model->id,
                ], ['class' => 'btn btn-sm btn-success rounded-pill px-3']) ?>
            </div>
            <div class="card-body">
                <?php if (!$bondPolicy['configured'] && (float) $model->budget > 0): ?>
                    <div class="alert alert-danger d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <i class="bi bi-exclamation-octagon me-1"></i>
                            <?= Html::encode($bondPolicy['reason']) ?>
                        </div>
                        <?= Html::a('ไปที่หน้าตั้งค่าเกณฑ์', ['/purchase/bond-policy'], [
                            'class' => 'btn btn-sm btn-danger rounded-pill px-3',
                        ]) ?>
                    </div>
                <?php elseif ($bondPolicy['required'] && !$bondOpen): ?>
                    <div class="alert alert-warning">
                        <div class="fw-medium">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            สัญญานี้เข้าเกณฑ์ต้องวางหลักประกัน
                            <?= rtrim(rtrim(number_format((float) $bondPolicy['rate'], 2), '0'), '.') ?>%
                            ของวงเงิน = <?= number_format((float) $bondPolicy['amount'], 2) ?> บาท
                            แต่ยังไม่มีหลักประกันในทะเบียน
                        </div>
                        <?php if ($bondPolicy['law']): ?>
                            <div class="small text-muted mt-1">อ้างอิง: <?= Html::encode($bondPolicy['law']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php elseif (!$bondPolicy['required'] && $bondPolicy['configured']): ?>
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-1"></i>
                        <?= Html::encode($bondPolicy['reason']) ?>
                        <?php if ($bondPolicy['law']): ?>
                            <div class="text-muted mt-1">อ้างอิง: <?= Html::encode($bondPolicy['law']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($bonds): ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width:150px">ประเภท / รูปแบบ</th>
                                    <th style="min-width:140px">เลขที่ / ผู้ออก</th>
                                    <th style="width:120px" class="text-end">วงเงิน</th>
                                    <th style="width:110px">วางเมื่อ</th>
                                    <th style="width:130px">สิ้นอายุ</th>
                                    <th style="width:110px">สถานะ</th>
                                    <th style="width:90px" class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bonds as $bond): ?>
                                    <?php
                                    $bondBadge = Bond::statusBadge($bond->status);
                                    $bondState = $bond->expiryState();
                                    ?>
                                    <tr class="<?= $bondState === BondCalculator::STATE_EXPIRED ? 'table-danger' : ($bondState === BondCalculator::STATE_NEAR ? 'table-warning' : '') ?>">
                                        <td class="small">
                                            <?= Html::encode($bond->typeName()) ?>
                                            <div class="text-muted"><?= Html::encode($bond->bondFormName()) ?></div>
                                        </td>
                                        <td class="small">
                                            <?= Html::encode($bond->doc_ref ?: '—') ?>
                                            <div class="text-muted"><?= Html::encode($bond->issuer ?: '') ?></div>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($bond->status === Bond::STATUS_EXEMPT): ?>
                                                <span class="text-muted">ยกเว้น</span>
                                            <?php else: ?>
                                                <?= number_format((float) $bond->amount, 2) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small"><?= $date($bond->place_date) ?></td>
                                        <td class="small">
                                            <?= $date($bond->expiry_date) ?>
                                            <?php if ($bondState === BondCalculator::STATE_EXPIRED): ?>
                                                <div class="text-danger fw-semibold">สิ้นอายุแล้ว</div>
                                            <?php elseif ($bondState === BondCalculator::STATE_NEAR): ?>
                                                <div class="text-warning-emphasis">เหลือ <?= (int) $bond->daysToExpiry() ?> วัน</div>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge text-bg-<?= $bondBadge['color'] ?>"><?= $bondBadge['label'] ?></span></td>
                                        <td class="text-end">
                                            <?= Html::a('<i class="bi bi-pencil"></i>', ['/purchase/bond/update', 'id' => $bond->id], [
                                                'class' => 'btn btn-sm btn-outline-secondary',
                                                'title' => 'แก้ไขหลักประกัน',
                                            ]) ?>
                                        </td>
                                    </tr>
                                    <?php if ($bond->status === Bond::STATUS_EXEMPT && $bond->exempt_reason): ?>
                                        <tr>
                                            <td colspan="7" class="small text-muted">
                                                เหตุผลที่ยกเว้น: <?= Html::encode($bond->exempt_reason) ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-3">ยังไม่มีหลักประกันที่ผูกกับสัญญาฉบับนี้</div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (trim(strip_tags((string) $model->extra_term)) !== ''): ?>
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">เงื่อนไขเพิ่มเติม</h6></div>
                <div class="card-body">
                    <?= $model->extra_term // ผ่าน HtmlPurifier ตอนบันทึกแล้ว ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ─── สรุปตัวเลข ──────────────────────────────────────────────── -->
    <div class="col-lg-4">

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">ค่าปรับ</h6></div>
            <div class="card-body">
                <?php if ($fine['days'] > 0 && $fine['amount'] > 0): ?>
                    <div class="display-6 fw-semibold text-danger mb-1">
                        <?= number_format($fine['amount'], 2) ?>
                    </div>
                    <div class="text-muted mb-3">บาท · ล่าช้า <?= $fine['days'] ?> วัน</div>

                    <dl class="row small mb-0">
                        <dt class="col-6">ฐานที่ใช้คิด</dt>
                        <dd class="col-6 text-end"><?= Html::encode(Contract::fineBaseList()[$model->fine_base] ?? '—') ?></dd>

                        <dt class="col-6">อัตรา</dt>
                        <dd class="col-6 text-end"><?= rtrim(rtrim(number_format((float) $model->fine_rate, 4), '0'), '.') ?>% ต่อวัน</dd>

                        <dt class="col-6">คำนวณได้</dt>
                        <dd class="col-6 text-end"><?= number_format($fine['raw'], 2) ?> บาท</dd>

                        <dt class="col-6">คิดเป็นสัดส่วน</dt>
                        <dd class="col-6 text-end"><?= number_format($ratio, 2) ?>% ของวงเงิน</dd>
                    </dl>

                    <?php if ($fine['capped']): ?>
                        <div class="alert alert-danger small mt-3 mb-0">
                            ค่าปรับที่คำนวณได้เกินวงเงินตามสัญญา ระบบจำกัดไว้เท่าวงเงิน
                        </div>
                    <?php endif; ?>

                    <?php if ($ratio >= ContractCalculator::WARN_RATIO): ?>
                        <div class="alert alert-warning small mt-3 mb-0">
                            ค่าปรับถึงร้อยละ <?= number_format(ContractCalculator::WARN_RATIO, 0) ?> ของวงเงิน
                            อยู่ในเกณฑ์ที่ต้องพิจารณาบอกเลิกสัญญา
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-muted">
                        ไม่มีค่าปรับ — ส่งมอบภายในกำหนด หรือยังไม่ได้ระบุวันที่ส่งมอบจริง
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">ภาษีหัก ณ ที่จ่าย</h6></div>
            <div class="card-body">
                <?php if ($wht['rate'] === null): ?>
                    <div class="text-danger small"><?= Html::encode($wht['reason']) ?></div>
                <?php else: ?>
                    <div class="h4 fw-semibold mb-1"><?= number_format($wht['amount'], 2) ?> <span class="fs-6 fw-normal text-muted">บาท</span></div>
                    <dl class="row small mb-0">
                        <dt class="col-6">อัตรา</dt>
                        <dd class="col-6 text-end"><?= rtrim(rtrim(number_format($wht['rate'], 2), '0'), '.') ?>%</dd>

                        <dt class="col-6">ฐานคำนวณ</dt>
                        <dd class="col-6 text-end"><?= number_format($wht['base'], 2) ?> บาท</dd>
                    </dl>
                    <div class="small text-muted mt-2"><?= Html::encode($wht['reason']) ?></div>
                    <?php if (!empty($wht['law'])): ?>
                        <div class="small text-muted"><?= Html::encode($wht['law']) ?></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
