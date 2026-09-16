<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var int $year */
/** @var array $groups [['label'=>,'rows'=>[['account'=>,'balance'=>]],'sum'=>]] */

$this->title = 'เงินคงเหลือสะสม';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รับ–จ่ายเงิน', 'url' => ['/finance/cash']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-cash-stack" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ยอดคงเหลือทุกบัญชี ปีงบประมาณ <?= $year ?><?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();

$grand = 0;
foreach ($groups as $g) {
    $grand += $g['sum'];
}
?>

<?= $this->render('@app/modules/finance/views/cash/_menu', ['active' => 'account']) ?>
<?= $this->render('_account_menu', ['active' => 'summary']) ?>

<div class="card border mb-3"><div class="card-body">
    <form method="get" class="d-flex gap-2 align-items-end">
        <div><label class="form-label mb-0 small">ปีงบประมาณ</label>
            <input type="number" class="form-control form-control-sm" name="year" value="<?= $year ?>" style="width:120px"></div>
        <button type="submit" class="btn btn-sm btn-primary">ดู</button>
    </form>
</div></div>

<div class="card border-primary mb-3"><div class="card-body d-flex justify-content-between align-items-center">
    <span class="fs-5">รวมเงินคงเหลือทุกบัญชี</span>
    <span class="fs-3 fw-bold text-primary"><?= number_format($grand, 2) ?> บาท</span>
</div></div>

<?php foreach ($groups as $g): ?>
    <div class="card border mb-3">
        <div class="card-header bg-body d-flex justify-content-between">
            <span class="fw-semibold"><?= Html::encode($g['label']) ?></span>
            <span class="fw-bold"><?= number_format($g['sum'], 2) ?></span>
        </div>
        <div class="table-responsive"><table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>เลขที่/ชื่อบัญชี</th><th>ธนาคาร</th><th class="text-end">คงเหลือ</th></tr></thead>
            <tbody>
                <?php foreach ($g['rows'] as $r): /** @var app\modules\finance\models\FinanceCashAccount $a */ $a = $r['account']; ?>
                    <tr>
                        <td><?= Html::encode(trim(($a->code ? $a->code . ' ' : '') . $a->name)) ?></td>
                        <td><small class="text-body-secondary"><?= Html::encode($a->bank_name ?: '-') ?></small></td>
                        <td class="text-end fw-semibold"><?= number_format((float) $r['balance'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$g['rows']): ?><tr><td colspan="3" class="text-center text-body-secondary py-3">ไม่มีบัญชีในกลุ่มนี้</td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </div>
<?php endforeach; ?>
