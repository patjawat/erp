<?php

use app\components\AppHelper;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $fy */
/** @var app\modules\finance\models\FinanceTreasuryRemit $model */
/** @var app\modules\finance\models\FinanceTreasuryRemit[] $list */
/** @var int[] $fiscalYears */

$this->title = 'รับและนำส่งเงิน (นส.02)';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'เงินงบประมาณ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$canOperate = Yii::$app->user->can('financeOperate');
$money = fn ($v) => number_format((float) $v, 2);

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-send fs-4"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'budget']);
$this->endBlock();
?>

<?= $this->render('_menu', ['active' => 'treasury', 'fy' => $fy]) ?>

<div class="d-flex justify-content-end mb-3">
    <a href="<?= Url::to(['/finance/register/view', 'key' => 'treasury_remit', 'fiscal_year' => $fy]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-journal-check me-1"></i>ทะเบียนคุม</a>
</div>

<?php if ($canOperate): ?>
<div class="card shadow-sm mb-3">
    <div class="card-header bg-body"><h6 class="mb-0"><i class="bi bi-plus-circle me-1"></i>บันทึกการจัดเก็บ/นำส่ง</h6></div>
    <div class="card-body">
        <?= Html::beginForm(['treasury', 'fiscal_year' => $fy], 'post') ?>
        <?= Html::hiddenInput('FinanceTreasuryRemit[fiscal_year]', $fy) ?>
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3"><label class="form-label small mb-1">ประเภทรายได้แผ่นดิน</label><?= Html::textInput('FinanceTreasuryRemit[revenue_type]', '', ['class' => 'form-control form-control-sm']) ?></div>
            <div class="col-6 col-md-2"><label class="form-label small mb-1">วันที่จัดเก็บ</label><?= DatepickerThai::widget(['name' => 'FinanceTreasuryRemit[collect_date]', 'value' => date('Y-m-d'), 'options' => ['class' => 'form-control form-control-sm', 'autocomplete' => 'off']]) ?></div>
            <div class="col-6 col-md-2"><label class="form-label small mb-1">ยอดจัดเก็บ</label><?= Html::textInput('FinanceTreasuryRemit[collected_amount]', '', ['class' => 'form-control form-control-sm text-end', 'inputmode' => 'decimal']) ?></div>
            <div class="col-6 col-md-2"><label class="form-label small mb-1">วันที่นำส่งคลัง</label><?= DatepickerThai::widget(['name' => 'FinanceTreasuryRemit[remit_date]', 'value' => '', 'options' => ['class' => 'form-control form-control-sm', 'autocomplete' => 'off']]) ?></div>
            <div class="col-6 col-md-2"><label class="form-label small mb-1">เลขที่ นส.02</label><?= Html::textInput('FinanceTreasuryRemit[remit_no]', '', ['class' => 'form-control form-control-sm']) ?></div>
            <div class="col-12 col-md-1 d-grid"><button class="btn btn-success btn-sm"><i class="bi bi-save"></i></button></div>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle text-nowrap">
            <thead class="table-light text-center"><tr><th>วันที่จัดเก็บ</th><th class="text-start">ประเภทรายได้</th><th class="text-end">ยอดจัดเก็บ</th><th>วันนำส่ง</th><th class="text-start">เลข นส.02</th><th>สถานะ</th><?php if ($canOperate): ?><th></th><?php endif; ?></tr></thead>
            <tbody>
                <?php if (!$list): ?><tr><td colspan="<?= $canOperate ? 7 : 6 ?>" class="text-center text-body-secondary py-4">ยังไม่มีรายการ</td></tr><?php endif; ?>
                <?php foreach ($list as $r): ?>
                    <tr>
                        <td class="text-center"><?= $r->collect_date ? Html::encode(AppHelper::convertToThai($r->collect_date)) : '-' ?></td>
                        <td><?= Html::encode($r->revenue_type ?: '-') ?></td>
                        <td class="text-end"><?= $money($r->collected_amount) ?></td>
                        <td class="text-center"><?= $r->remit_date ? Html::encode(AppHelper::convertToThai($r->remit_date)) : '-' ?></td>
                        <td><?= Html::encode($r->remit_no ?: '-') ?></td>
                        <td class="text-center"><span class="badge <?= $r->isRemitted() ? 'bg-success-subtle text-success-emphasis' : 'bg-warning-subtle text-warning-emphasis' ?>"><?= $r->isRemitted() ? 'นำส่งแล้ว' : 'ค้างนำส่ง' ?></span></td>
                        <?php if ($canOperate): ?><td class="text-center"><?= Html::beginForm(['delete-treasury', 'id' => $r->id], 'post', ['onsubmit' => "return confirm('ลบ?')"]) ?><button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button><?= Html::endForm() ?></td><?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
