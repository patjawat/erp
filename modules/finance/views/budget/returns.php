<?php

use app\components\AppHelper;
use app\modules\finance\models\FinanceBudgetTxn;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $fy */
/** @var app\modules\finance\models\FinanceBudgetReturn $model */
/** @var app\modules\finance\models\FinanceBudgetReturn[] $list */
/** @var int[] $fiscalYears */

$this->title = 'เบิกเกินส่งคืนคลัง';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'เงินงบประมาณ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$canOperate = Yii::$app->user->can('financeOperate');
$money = fn ($v) => number_format((float) $v, 2);

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-arrow-return-left fs-4"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'budget']);
$this->endBlock();
?>

<?= $this->render('_menu', ['active' => 'returns', 'fy' => $fy]) ?>

<div class="d-flex justify-content-end mb-3">
    <a href="<?= Url::to(['/finance/register/view', 'key' => 'budget_return', 'fiscal_year' => $fy]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-journal-check me-1"></i>ทะเบียนคุม</a>
</div>

<?php if ($canOperate): ?>
<div class="card shadow-sm mb-3">
    <div class="card-header bg-body"><h6 class="mb-0"><i class="bi bi-plus-circle me-1"></i>บันทึกการส่งคืน</h6></div>
    <div class="card-body">
        <?= Html::beginForm(['returns', 'fiscal_year' => $fy], 'post') ?>
        <?= Html::hiddenInput('FinanceBudgetReturn[fiscal_year]', $fy) ?>
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-2"><label class="form-label small mb-1">วันที่ส่งคืน</label><?= DatepickerThai::widget(['name' => 'FinanceBudgetReturn[return_date]', 'value' => date('Y-m-d'), 'options' => ['class' => 'form-control form-control-sm', 'autocomplete' => 'off']]) ?></div>
            <div class="col-6 col-md-2"><label class="form-label small mb-1">เลขที่เอกสาร</label><?= Html::textInput('FinanceBudgetReturn[return_no]', '', ['class' => 'form-control form-control-sm']) ?></div>
            <div class="col-6 col-md-3"><label class="form-label small mb-1">งบรายจ่าย</label><?= Html::dropDownList('FinanceBudgetReturn[budget_category]', '', FinanceBudgetTxn::CATEGORIES, ['class' => 'form-select form-select-sm', 'prompt' => '— ไม่ระบุ —']) ?></div>
            <div class="col-6 col-md-2"><label class="form-label small mb-1">ยอดส่งคืน</label><?= Html::textInput('FinanceBudgetReturn[amount]', '', ['class' => 'form-control form-control-sm text-end', 'inputmode' => 'decimal']) ?></div>
            <div class="col-12 col-md-2"><label class="form-label small mb-1">อ้างการเบิกเดิม</label><?= Html::textInput('FinanceBudgetReturn[source_ref]', '', ['class' => 'form-control form-control-sm']) ?></div>
            <div class="col-12 col-md-1 d-grid"><button class="btn btn-success btn-sm"><i class="bi bi-save"></i></button></div>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle text-nowrap">
            <thead class="table-light text-center"><tr><th>วันที่ส่งคืน</th><th class="text-start">เลขที่เอกสาร</th><th class="text-start">งบรายจ่าย</th><th class="text-start">อ้างการเบิกเดิม</th><th class="text-end">ยอดส่งคืน</th><?php if ($canOperate): ?><th></th><?php endif; ?></tr></thead>
            <tbody>
                <?php if (!$list): ?><tr><td colspan="<?= $canOperate ? 6 : 5 ?>" class="text-center text-body-secondary py-4">ยังไม่มีรายการ</td></tr><?php endif; ?>
                <?php foreach ($list as $r): ?>
                    <tr>
                        <td class="text-center"><?= $r->return_date ? Html::encode(AppHelper::convertToThai($r->return_date)) : '-' ?></td>
                        <td><?= Html::encode($r->return_no ?: '-') ?></td>
                        <td><?= Html::encode($r->categoryLabel()) ?></td>
                        <td><?= Html::encode($r->source_ref ?: '-') ?></td>
                        <td class="text-end"><?= $money($r->amount) ?></td>
                        <?php if ($canOperate): ?><td class="text-center"><?= Html::beginForm(['delete-return', 'id' => $r->id], 'post', ['onsubmit' => "return confirm('ลบ?')"]) ?><button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button><?= Html::endForm() ?></td><?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
