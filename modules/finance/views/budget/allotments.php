<?php

use app\components\AppHelper;
use app\modules\finance\models\FinanceBudgetTxn;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $fy */
/** @var app\modules\finance\models\FinanceBudgetAllotment $model */
/** @var app\modules\finance\models\FinanceBudgetAllotment[] $list */
/** @var int[] $fiscalYears */

$this->title = 'เงินประจำงวด';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'เงินงบประมาณ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$canOperate = Yii::$app->user->can('financeOperate');
$money = fn ($v) => number_format((float) $v, 2);

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-inboxes fs-4"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'budget']);
$this->endBlock();
?>

<?= $this->render('_menu', ['active' => 'allotments', 'fy' => $fy]) ?>

<div class="d-flex justify-content-end align-items-center mb-3 gap-2">
    <a href="<?= Url::to(['/finance/register/view', 'key' => 'budget_allotment', 'fiscal_year' => $fy]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-journal-check me-1"></i>ทะเบียนคุม</a>
</div>

<?php if ($canOperate): ?>
<div class="card shadow-sm mb-3">
    <div class="card-header bg-body"><h6 class="mb-0"><i class="bi bi-plus-circle me-1"></i>บันทึกเงินประจำงวด</h6></div>
    <div class="card-body">
        <?= Html::beginForm(['allotments', 'fiscal_year' => $fy], 'post') ?>
        <?= Html::hiddenInput('FinanceBudgetAllotment[fiscal_year]', $fy) ?>
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-1"><label class="form-label small mb-1">งวดที่</label><?= Html::textInput('FinanceBudgetAllotment[period_no]', '', ['class' => 'form-control form-control-sm', 'type' => 'number']) ?></div>
            <div class="col-6 col-md-3"><label class="form-label small mb-1">งบรายจ่าย</label><?= Html::dropDownList('FinanceBudgetAllotment[budget_category]', 'operation', FinanceBudgetTxn::CATEGORIES, ['class' => 'form-select form-select-sm']) ?></div>
            <div class="col-6 col-md-2"><label class="form-label small mb-1">เลขที่หนังสือ</label><?= Html::textInput('FinanceBudgetAllotment[allotment_no]', '', ['class' => 'form-control form-control-sm']) ?></div>
            <div class="col-6 col-md-2"><label class="form-label small mb-1">วันที่จัดสรร</label><?= DatepickerThai::widget(['name' => 'FinanceBudgetAllotment[allotment_date]', 'value' => '', 'options' => ['class' => 'form-control form-control-sm', 'autocomplete' => 'off']]) ?></div>
            <div class="col-6 col-md-2"><label class="form-label small mb-1">ยอดจัดสรร</label><?= Html::textInput('FinanceBudgetAllotment[amount]', '', ['class' => 'form-control form-control-sm text-end', 'inputmode' => 'decimal']) ?></div>
            <div class="col-12 col-md-2 d-grid"><button class="btn btn-success btn-sm"><i class="bi bi-save me-1"></i>บันทึก</button></div>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle text-nowrap">
            <thead class="table-light text-center"><tr><th>งวด</th><th class="text-start">งบรายจ่าย</th><th class="text-start">เลขที่หนังสือ</th><th>วันที่</th><th class="text-end">จัดสรร</th><th class="text-end">เบิกแล้ว</th><th class="text-end">คงเหลือ</th><?php if ($canOperate): ?><th></th><?php endif; ?></tr></thead>
            <tbody>
                <?php if (!$list): ?><tr><td colspan="<?= $canOperate ? 8 : 7 ?>" class="text-center text-body-secondary py-4">ยังไม่มีข้อมูล</td></tr><?php endif; ?>
                <?php foreach ($list as $a): ?>
                    <tr>
                        <td class="text-center"><?= $a->period_no ?: '-' ?></td>
                        <td><?= Html::encode($a->categoryLabel()) ?></td>
                        <td><?= Html::encode($a->allotment_no ?: '-') ?></td>
                        <td class="text-center"><?= $a->allotment_date ? Html::encode(AppHelper::convertToThai($a->allotment_date)) : '-' ?></td>
                        <td class="text-end"><?= $money($a->amount) ?></td>
                        <td class="text-end"><?= $money($a->getDisbursed()) ?></td>
                        <td class="text-end fw-semibold <?= $a->getRemaining() < 0 ? 'text-danger' : '' ?>"><?= $money($a->getRemaining()) ?></td>
                        <?php if ($canOperate): ?><td class="text-center"><?= Html::beginForm(['delete-allotment', 'id' => $a->id], 'post', ['onsubmit' => "return confirm('ลบ?')"]) ?><button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button><?= Html::endForm() ?></td><?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
