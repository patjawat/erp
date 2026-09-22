<?php

use app\components\AppHelper;
use app\modules\finance\models\FinanceBudgetTxn;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $fy */
/** @var FinanceBudgetTxn $model */
/** @var app\modules\finance\models\FinanceBudgetAllotment[] $allotments */
/** @var FinanceBudgetTxn[] $list */
/** @var int[] $fiscalYears */

$this->title = 'รับ-จ่ายเงินงบประมาณ';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'เงินงบประมาณ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$canOperate = Yii::$app->user->can('financeOperate');
$money = fn ($v) => number_format((float) $v, 2);

$allotOptions = ArrayHelper::map($allotments, 'id', fn ($a) => ($a->period_no ? 'งวด ' . $a->period_no . ' ' : '') . $a->categoryLabel() . ' (' . number_format((float) $a->amount) . ')');

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-arrow-left-right fs-4"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'budget']);
$this->endBlock();
?>

<?= $this->render('_menu', ['active' => 'transactions', 'fy' => $fy]) ?>

<div class="d-flex justify-content-end mb-3">
    <a href="<?= Url::to(['/finance/register/view', 'key' => 'budget_cashbook', 'fiscal_year' => $fy]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-journal-check me-1"></i>ทะเบียนคุม</a>
</div>

<?php if ($canOperate): ?>
<div class="card shadow-sm mb-3">
    <div class="card-header bg-body"><h6 class="mb-0"><i class="bi bi-plus-circle me-1"></i>บันทึกรายการ</h6></div>
    <div class="card-body">
        <?= Html::beginForm(['transactions', 'fiscal_year' => $fy], 'post') ?>
        <?= Html::hiddenInput('FinanceBudgetTxn[fiscal_year]', $fy) ?>
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-2"><label class="form-label small mb-1">ประเภท</label><?= Html::dropDownList('FinanceBudgetTxn[txn_type]', 'disburse', FinanceBudgetTxn::typeOptions(), ['class' => 'form-select form-select-sm']) ?></div>
            <div class="col-6 col-md-2"><label class="form-label small mb-1">งบรายจ่าย</label><?= Html::dropDownList('FinanceBudgetTxn[budget_category]', 'operation', FinanceBudgetTxn::CATEGORIES, ['class' => 'form-select form-select-sm']) ?></div>
            <div class="col-6 col-md-2"><label class="form-label small mb-1">วันที่</label><?= DatepickerThai::widget(['name' => 'FinanceBudgetTxn[doc_date]', 'value' => date('Y-m-d'), 'options' => ['class' => 'form-control form-control-sm', 'autocomplete' => 'off']]) ?></div>
            <div class="col-6 col-md-2"><label class="form-label small mb-1">เลขที่เอกสาร</label><?= Html::textInput('FinanceBudgetTxn[doc_no]', '', ['class' => 'form-control form-control-sm']) ?></div>
            <div class="col-6 col-md-2"><label class="form-label small mb-1">จำนวนเงิน</label><?= Html::textInput('FinanceBudgetTxn[amount]', '', ['class' => 'form-control form-control-sm text-end', 'inputmode' => 'decimal']) ?></div>
            <div class="col-6 col-md-2"><label class="form-label small mb-1">เงินประจำงวด</label><?= Html::dropDownList('FinanceBudgetTxn[allotment_id]', '', $allotOptions, ['class' => 'form-select form-select-sm', 'prompt' => '— ไม่ระบุ —']) ?></div>
            <div class="col-12 col-md-8"><label class="form-label small mb-1">รายการ</label><?= Html::textInput('FinanceBudgetTxn[description]', '', ['class' => 'form-control form-control-sm']) ?></div>
            <div class="col-8 col-md-2"><label class="form-label small mb-1">จ่ายให้</label><?= Html::textInput('FinanceBudgetTxn[payee]', '', ['class' => 'form-control form-control-sm']) ?></div>
            <div class="col-4 col-md-2 d-grid"><button class="btn btn-success btn-sm"><i class="bi bi-save me-1"></i>บันทึก</button></div>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-body"><span class="text-body-secondary small">แสดงสูงสุด 500 · <?= count($list) ?> รายการ</span></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle text-nowrap">
            <thead class="table-light text-center"><tr><th>วันที่</th><th class="text-start">ประเภท</th><th class="text-start">งบรายจ่าย</th><th class="text-start">รายการ</th><th class="text-end">รับ</th><th class="text-end">จ่าย</th><?php if ($canOperate): ?><th></th><?php endif; ?></tr></thead>
            <tbody>
                <?php if (!$list): ?><tr><td colspan="<?= $canOperate ? 7 : 6 ?>" class="text-center text-body-secondary py-4">ยังไม่มีรายการ</td></tr><?php endif; ?>
                <?php foreach ($list as $t): ?>
                    <?php $isIn = $t->txn_type === FinanceBudgetTxn::TYPE_RECEIVE; ?>
                    <tr>
                        <td class="text-center"><?= Html::encode(AppHelper::convertToThai($t->doc_date)) ?></td>
                        <td><span class="badge <?= $isIn ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis' ?>"><?= Html::encode($t->typeLabel()) ?></span></td>
                        <td><?= Html::encode($t->categoryLabel()) ?></td>
                        <td><?= Html::encode(($t->description ?: '-') . ($t->payee ? ' — ' . $t->payee : '')) ?></td>
                        <td class="text-end"><?= $isIn ? $money($t->amount) : '' ?></td>
                        <td class="text-end"><?= $isIn ? '' : $money($t->amount) ?></td>
                        <?php if ($canOperate): ?><td class="text-center"><?= Html::beginForm(['delete-transaction', 'id' => $t->id], 'post', ['onsubmit' => "return confirm('ลบ?')"]) ?><button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button><?= Html::endForm() ?></td><?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
