<?php

use app\components\AppHelper;
use app\modules\finance\models\FinanceCashTxn;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinanceCashProject $model */
/** @var FinanceCashTxn[] $assigned */
/** @var FinanceCashTxn[] $unassigned */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'โครงการเงินบำรุง', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->name;

$canOperate = Yii::$app->user->can('financeOperate');
$money = fn ($v) => number_format((float) $v, 2);
$inc = $model->incomeTotal();
$exp = $model->expenseTotal();

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-diagram-3 fs-4"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('sub-title');
echo Html::encode(($model->code ? $model->code . ' · ' : '') . $model->fundSourceLabel() . ' · ปีงบ ' . ($model->fiscal_year ?: '-'));
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'budget']);
$this->endBlock();

$txnRow = function (FinanceCashTxn $t) use ($money) {
    $isIn = $t->txn_type === FinanceCashTxn::TYPE_IN;
    return '<td class="text-center">' . Html::encode(AppHelper::convertToThai($t->doc_date)) . '</td>'
        . '<td>' . Html::encode($t->doc_no ?: '-') . '</td>'
        . '<td>' . Html::encode(($t->category->name ?? '-') . ($t->party_name ? ' — ' . $t->party_name : '')) . '</td>'
        . '<td class="text-end">' . ($isIn ? $money($t->amount) : '') . '</td>'
        . '<td class="text-end">' . ($isIn ? '' : $money($t->amount)) . '</td>';
};
?>

<?= $this->render('@app/modules/finance/views/budget/_menu', ['active' => 'project']) ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>ทุกโครงการ</a>
    <div class="d-flex gap-2">
        <a href="<?= Url::to(['/finance/register/view', 'key' => 'fund_by_project', 'fiscal_year' => $model->fiscal_year, 'project_id' => $model->id]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-journal-check me-1"></i>ทะเบียนคุม (พิมพ์/Excel)</a>
        <?php if ($canOperate): ?><a href="<?= Url::to(['update', 'id' => $model->id]) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil me-1"></i>แก้ไข</a><?php endif; ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach ([['รับเข้าโครงการ', $inc, 'text-success-emphasis'], ['จ่ายในโครงการ', $exp, 'text-danger'], ['คงเหลือ', $inc - $exp, 'text-body']] as $c): ?>
        <div class="col-6 col-md-4"><div class="card h-100 shadow-sm"><div class="card-body py-3">
            <div class="text-body-secondary small"><?= $c[0] ?></div><div class="fs-4 fw-semibold <?= $c[2] ?>"><?= $money($c[1]) ?></div>
        </div></div></div>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header bg-body d-flex justify-content-between align-items-center"><h6 class="mb-0">รายการในโครงการ</h6><span class="text-body-secondary small"><?= count($assigned) ?> รายการ</span></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle text-nowrap">
            <thead class="table-light text-center"><tr><th>วันที่</th><th class="text-start">เลขที่</th><th class="text-start">รายการ</th><th class="text-end">รับ</th><th class="text-end">จ่าย</th><?php if ($canOperate): ?><th></th><?php endif; ?></tr></thead>
            <tbody>
                <?php if (!$assigned): ?><tr><td colspan="<?= $canOperate ? 6 : 5 ?>" class="text-center text-body-secondary py-3">ยังไม่มีรายการในโครงการ — เลือกผูกด้านล่าง</td></tr><?php endif; ?>
                <?php foreach ($assigned as $t): ?>
                    <tr><?= $txnRow($t) ?>
                        <?php if ($canOperate): ?><td class="text-center"><?= Html::beginForm(['unassign', 'id' => $t->id], 'post', ['onsubmit' => "return confirm('ปลดรายการนี้ออกจากโครงการ?')"]) ?><button class="btn btn-sm btn-link text-danger p-0" title="ปลดออก"><i class="bi bi-x-lg"></i></button><?= Html::endForm() ?></td><?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($canOperate): ?>
<div class="card shadow-sm">
    <div class="card-header bg-body"><h6 class="mb-0"><i class="bi bi-link-45deg me-1"></i>ผูกรายการเข้าโครงการ <span class="text-body-secondary small">(รายการเงินบำรุงปีงบ <?= $model->fiscal_year ?: '-' ?> ที่ยังไม่ผูกโครงการ · สูงสุด 300)</span></h6></div>
    <?= Html::beginForm(['assign', 'id' => $model->id], 'post') ?>
    <div class="table-responsive" style="max-height:28rem;overflow:auto;">
        <table class="table table-sm table-hover mb-0 align-middle text-nowrap">
            <thead class="table-light text-center" style="position:sticky;top:0;z-index:1;">
                <tr><th style="width:2rem"><input type="checkbox" id="chk-all"></th><th>วันที่</th><th class="text-start">เลขที่</th><th class="text-start">รายการ</th><th class="text-end">รับ</th><th class="text-end">จ่าย</th></tr>
            </thead>
            <tbody>
                <?php if (!$unassigned): ?><tr><td colspan="6" class="text-center text-body-secondary py-3">ไม่มีรายการที่ยังไม่ผูกในปีงบนี้</td></tr><?php endif; ?>
                <?php foreach ($unassigned as $t): ?>
                    <tr><td class="text-center"><input type="checkbox" class="chk-txn" name="txn_ids[]" value="<?= $t->id ?>"></td><?= $txnRow($t) ?></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($unassigned): ?>
    <div class="card-footer bg-body text-end">
        <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-link me-1"></i>ผูกรายการที่เลือกเข้าโครงการ</button>
    </div>
    <?php endif; ?>
    <?= Html::endForm() ?>
</div>
<?php
$this->registerJs(<<<'JS'
document.getElementById('chk-all')?.addEventListener('change', function(){
    document.querySelectorAll('.chk-txn').forEach(c => c.checked = this.checked);
});
JS);
?>
<?php endif; ?>
