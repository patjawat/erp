<?php

use app\components\AppHelper;
use app\modules\finance\models\FinancePayable;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $mode */
/** @var array $vendors */
/** @var string $vendor */
/** @var FinancePayable[] $rows */

$this->title = 'รับวางบิล';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนคุมเจ้าหนี้', 'url' => ['/finance/payable']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'billing']);
$this->endBlock();

$fmt = fn($v) => number_format((float) $v, 2);
$thDate = fn($d) => $d ? AppHelper::convertToThai($d) : '–';
?>

<?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<?php if ($mode === 'vendors'): ?>
    <?php
    // ขั้นตอนของบิลก่อนมาถึงหน้านี้ — ให้ผู้ใช้เห็นว่าบิลมาจากไหน
    $steps = [
        ['icon' => 'bi-box-seam', 'label' => 'พัสดุส่งการเงิน'],
        ['icon' => 'bi-inbox', 'label' => 'รับรอง & ตั้งเจ้าหนี้', 'note' => 'ร่าง'],
        ['icon' => 'bi-send', 'label' => 'ส่งตรวจอนุมัติ'],
        ['icon' => 'bi-check2-circle', 'label' => 'อนุมัติเข้าทะเบียน'],
        ['icon' => 'bi-receipt', 'label' => 'รับวางบิล', 'current' => true],
        ['icon' => 'bi-cash-stack', 'label' => 'จ่ายชำระ'],
    ];
    ?>
    <section class="card border mb-3" aria-label="ขั้นตอนของบิลเจ้าหนี้">
        <div class="card-body py-3">
            <ol class="list-unstyled d-flex flex-wrap align-items-center gap-2 mb-0 small">
                <?php foreach ($steps as $i => $s): ?>
                    <?php if ($i > 0): ?><li aria-hidden="true" class="text-body-secondary"><i class="bi bi-chevron-right"></i></li><?php endif; ?>
                    <li class="px-2 py-1 rounded-pill border <?= !empty($s['current']) ? 'bg-primary text-white border-primary' : 'bg-body' ?>"
                        <?= !empty($s['current']) ? 'aria-current="step"' : '' ?>>
                        <i class="bi <?= $s['icon'] ?> me-1"></i><?= Html::encode($s['label']) ?>
                    </li>
                <?php endforeach; ?>
            </ol>
            <div class="form-text mt-2">หน้านี้แสดงเฉพาะบิลที่ <strong>อนุมัติเข้าทะเบียนแล้ว</strong> และผู้ขายยังไม่มาวางบิล — ติ๊กบิลที่ผู้ขายนำมาวาง ระบบจะคำนวณวันครบกำหนดใหม่จากวันวางบิล แล้วบิลจะพร้อมดึงไปจ่าย</div>
        </div>
    </section>

    <?php
    $notApproved = $notApproved ?? [];
    $pendingLabels = [
        FinancePayable::STATUS_DRAFT => 'ร่าง (ยังไม่ส่งตรวจ)',
        FinancePayable::STATUS_PENDING_APPROVAL => 'รอตรวจอนุมัติ',
        FinancePayable::STATUS_NEEDS_REVISION => 'ส่งกลับแก้ไข',
    ];
    ?>
    <?php if (array_sum($notApproved) > 0): ?>
        <div class="alert alert-warning d-flex flex-wrap align-items-center gap-2">
            <i class="bi bi-hourglass-split"></i>
            <span>มีบิลที่ยังไม่อนุมัติเข้าทะเบียน <strong><?= number_format(array_sum($notApproved)) ?></strong> รายการ จึงยังไม่ขึ้นในหน้านี้:</span>
            <?php foreach ($pendingLabels as $st => $lbl): ?>
                <?php if (!empty($notApproved[$st])): ?>
                    <a href="<?= Url::to(['index', 'status' => $st]) ?>" class="btn btn-sm btn-outline-dark">
                        <?= Html::encode($lbl) ?> <span class="badge text-bg-dark ms-1"><?= number_format($notApproved[$st]) ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <section class="card border shadow-sm">
        <div class="card-header bg-body"><h5 class="mb-0">เจ้าหนี้ที่มีบิลรอวางบิล</h5></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>เจ้าหนี้</th><th class="text-center">จำนวนบิล</th><th class="text-center">ใบแจ้งหนี้เก่าสุด</th><th class="text-end">ยอดรวม</th><th></th></tr></thead>
                <tbody>
                    <?php if (!$vendors): ?>
                        <tr><td colspan="5" class="text-center text-body-secondary py-4">ไม่มีบิลรอวางบิล</td></tr>
                    <?php endif; ?>
                    <?php foreach ($vendors as $v): ?>
                        <tr>
                            <td class="fw-semibold"><?= Html::encode($v['vendor']) ?></td>
                            <td class="text-center"><?= (int) $v['bills'] ?></td>
                            <td class="text-center"><?= $thDate($v['first_date']) ?></td>
                            <td class="text-end fw-semibold"><?= $fmt($v['total']) ?></td>
                            <td class="text-end">
                                <a href="<?= Url::to(['billing', 'vendor' => $v['vendor']]) ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-receipt me-1"></i>รับวางบิล
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php else: ?>
    <div class="mb-3"><a href="<?= Url::to(['billing']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>เลือกเจ้าหนี้อื่น</a></div>

    <?= Html::beginForm(['billing'], 'post', ['id' => 'billing-form']) ?>
    <?= Html::hiddenInput('vendor', $vendor) ?>
    <div class="card border mb-3">
        <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-building me-1"></i><?= Html::encode($vendor) ?></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label small mb-1">วันที่รับวางบิล</label>
                    <?= DatepickerThai::widget([
                        'name' => 'billing_date',
                        'value' => AppHelper::convertToThai(date('Y-m-d')),
                        'options' => ['class' => 'form-control form-control-sm', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.'],
                    ]) ?></div>
                <div class="col-md-4"><label class="form-label small mb-1">เลขที่ใบวางบิล (ของผู้ขาย)</label>
                    <input type="text" class="form-control form-control-sm" name="billing_ref" maxlength="60" placeholder="ถ้ามี"></div>
            </div>
        </div>
    </div>

    <section class="card border shadow-sm">
        <div class="card-header bg-body d-flex justify-content-between align-items-center">
            <h5 class="mb-0">บิลรอวางบิล</h5>
            <span class="text-body-secondary small"><?= count($rows) ?> บิล</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th style="width:40px" class="text-center"><input type="checkbox" class="form-check-input" id="check-all" checked></th>
                        <th>เลขทะเบียน</th><th>ใบแจ้งหนี้</th><th class="text-center">วันที่ใบแจ้งหนี้</th>
                        <th class="text-center">เครดิต (วัน)</th><th class="text-end">ยอดสุทธิ</th></tr>
                </thead>
                <tbody>
                    <?php if (!$rows): ?>
                        <tr><td colspan="6" class="text-center text-body-secondary py-4">ไม่มีบิลรอวางบิล</td></tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $p): ?>
                        <tr>
                            <td class="text-center"><input type="checkbox" class="form-check-input bill-check" name="ids[]" value="<?= $p->id ?>" checked></td>
                            <td><?= Html::a(Html::encode($p->payable_no), ['view', 'id' => $p->id]) ?></td>
                            <td><?= Html::encode($p->invoice_no) ?></td>
                            <td class="text-center"><?= $thDate($p->invoice_date) ?></td>
                            <td class="text-center"><?= (int) $p->credit_days ?></td>
                            <td class="text-end fw-semibold"><?= $fmt($p->net_amount) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($rows): ?>
            <div class="card-footer d-flex justify-content-end">
                <?= Html::submitButton('<i class="bi bi-check2-square me-1"></i> บันทึกรับวางบิล', ['class' => 'btn btn-primary']) ?>
            </div>
        <?php endif; ?>
    </section>
    <?= Html::endForm() ?>

    <?php
    $this->registerJs(<<<JS
(function(){
  const all = document.getElementById('check-all');
  if(all){ all.addEventListener('change', function(){
    document.querySelectorAll('.bill-check').forEach(c => c.checked = all.checked);
  }); }
})();
JS, \yii\web\View::POS_END);
    ?>
<?php endif; ?>
