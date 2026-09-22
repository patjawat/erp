<?php

use app\components\AppHelper;
use app\modules\finance\models\FinancePettyCashTxn;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinancePettyCash $fund */
/** @var app\modules\finance\models\FinancePettyCashTxn[] $txns */
/** @var app\modules\finance\models\FinancePettyCashTxn $newTxn */

$this->title = $fund->name;
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'เงินสดย่อย', 'url' => ['index']];
$this->params['breadcrumbs'][] = $fund->name;

$canOperate = Yii::$app->user->can('financeOperate');
$money = fn ($v) => number_format((float) $v, 2);
$onHand = $fund->balanceOnHand();
$unrei = $fund->unreimbursed();

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-wallet2 fs-4" aria-hidden="true"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('sub-title');
echo Html::encode(trim(($fund->code ? $fund->code . ' · ' : '') . ($fund->unit ? $fund->unit . ' · ' : '') . ($fund->custodian_name ?: ''), ' ·'));
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'petty']);
$this->endBlock();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>กองทั้งหมด
    </a>
    <div class="d-flex gap-2">
        <a href="<?= Url::to(['/finance/register/view', 'key' => 'petty_cash', 'fund_id' => $fund->id]) ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-journal-check me-1" aria-hidden="true"></i>ทะเบียนคุม (พิมพ์/Excel)
        </a>
        <?php if ($canOperate): ?>
            <a href="<?= Url::to(['update', 'id' => $fund->id]) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil me-1" aria-hidden="true"></i>แก้ไขกอง
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach ([
        ['label' => 'วงเงิน (imprest)', 'value' => $fund->float_amount, 'class' => 'text-body'],
        ['label' => 'เงินคงเหลือในมือ', 'value' => $onHand, 'class' => $onHand < 0 ? 'text-danger' : 'text-success-emphasis'],
        ['label' => 'รอเบิกชดเชย', 'value' => $unrei, 'class' => $unrei > 0 ? 'text-warning-emphasis' : 'text-body'],
    ] as $c): ?>
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm"><div class="card-body py-3">
                <div class="text-body-secondary small"><?= Html::encode($c['label']) ?></div>
                <div class="fs-3 fw-semibold <?= $c['class'] ?>"><?= $money($c['value']) ?> <span class="fs-6 fw-normal">บาท</span></div>
            </div></div>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($canOperate): ?>
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-body"><h6 class="mb-0"><i class="bi bi-plus-circle me-1"></i>บันทึกรายการ</h6></div>
        <div class="card-body">
            <?= Html::beginForm(['add-txn', 'id' => $fund->id], 'post') ?>
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">ประเภท</label>
                    <?= Html::dropDownList('FinancePettyCashTxn[txn_type]', FinancePettyCashTxn::TYPE_DISBURSE, FinancePettyCashTxn::TYPES, ['class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">วันที่</label>
                    <?= DatepickerThai::widget(['name' => 'FinancePettyCashTxn[doc_date]', 'value' => date('Y-m-d'), 'options' => ['class' => 'form-control form-control-sm', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.']]) ?>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">เลขที่เอกสาร</label>
                    <?= Html::textInput('FinancePettyCashTxn[doc_no]', '', ['class' => 'form-control form-control-sm', 'maxlength' => 64]) ?>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">จำนวนเงิน</label>
                    <?= Html::textInput('FinancePettyCashTxn[amount]', '', ['class' => 'form-control form-control-sm text-end', 'inputmode' => 'decimal']) ?>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small mb-1">รายการ</label>
                    <?= Html::textInput('FinancePettyCashTxn[description]', '', ['class' => 'form-control form-control-sm', 'maxlength' => 500]) ?>
                </div>
                <div class="col-8 col-md-4">
                    <label class="form-label small mb-1">จ่ายให้ (ถ้ามี)</label>
                    <?= Html::textInput('FinancePettyCashTxn[payee]', '', ['class' => 'form-control form-control-sm', 'maxlength' => 255]) ?>
                </div>
                <div class="col-4 col-md-2">
                    <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-save me-1"></i>บันทึก</button>
                </div>
            </div>
            <?= Html::endForm() ?>
            <div class="form-text mt-2">
                <i class="bi bi-info-circle me-1"></i>ตั้งวงเงิน/เบิกชดเชย = เงินเข้ามือ · จ่าย/ส่งคืน = เงินออกจากมือ
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-body d-flex justify-content-between align-items-center">
        <h6 class="mb-0">สมุดเงินสดย่อย</h6>
        <span class="text-body-secondary small"><?= count($txns) ?> รายการ</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-sm mb-0 align-middle text-nowrap">
            <thead class="table-light text-center">
                <tr>
                    <th style="width:7rem">วันที่</th>
                    <th style="width:7rem">ประเภท</th>
                    <th style="width:8rem">เลขที่</th>
                    <th>รายการ</th>
                    <th style="width:8rem">รับ</th>
                    <th style="width:8rem">จ่าย</th>
                    <th style="width:9rem">คงเหลือ</th>
                    <?php if ($canOperate): ?><th style="width:3rem"></th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $bal = 0.0; ?>
                <?php if (!$txns): ?>
                    <tr><td colspan="<?= $canOperate ? 8 : 7 ?>" class="text-center text-body-secondary py-4">ยังไม่มีรายการ</td></tr>
                <?php endif; ?>
                <?php foreach ($txns as $t): ?>
                    <?php
                    $in = $t->isInflow() ? (float) $t->amount : 0.0;
                    $out = $t->isInflow() ? 0.0 : (float) $t->amount;
                    $bal += $in - $out;
                    $desc = $t->description ?: '';
                    if ($t->payee) {
                        $desc .= ($desc ? ' — ' : '') . 'จ่ายให้ ' . $t->payee;
                    }
                    ?>
                    <tr>
                        <td class="text-center"><?= Html::encode(AppHelper::convertToThai($t->doc_date)) ?></td>
                        <td class="text-center">
                            <span class="badge <?= $t->isInflow() ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis' ?>"><?= Html::encode($t->typeLabel()) ?></span>
                        </td>
                        <td><?= Html::encode($t->doc_no ?: '-') ?></td>
                        <td><?= Html::encode($desc ?: '-') ?></td>
                        <td class="text-end"><?= $in ? $money($in) : '' ?></td>
                        <td class="text-end"><?= $out ? $money($out) : '' ?></td>
                        <td class="text-end fw-semibold"><?= $money($bal) ?></td>
                        <?php if ($canOperate): ?>
                            <td class="text-center">
                                <?= Html::beginForm(['delete-txn', 'id' => $t->id], 'post', ['onsubmit' => "return confirm('ลบรายการนี้?')"]) ?>
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="ลบ"><i class="bi bi-trash"></i></button>
                                <?= Html::endForm() ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <?php if ($txns): ?>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="4" class="text-end">คงเหลือในมือ</td>
                        <td colspan="<?= $canOperate ? 4 : 3 ?>" class="text-end"><?= $money($bal) ?></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
