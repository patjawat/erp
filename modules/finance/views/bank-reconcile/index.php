<?php

use app\modules\finance\models\FinanceBankReconcile;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var FinanceBankReconcile[] $list */
/** @var int $fy */
/** @var int[] $fiscalYears */

$this->title = 'งบพิสูจน์ยอดเงินฝากธนาคาร';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$canOperate = Yii::$app->user->can('financeOperate');
$money = fn ($v) => number_format((float) $v, 2);
$months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-bank fs-4"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'bankrec']);
$this->endBlock();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="get" class="d-flex align-items-end gap-2">
        <div>
            <label class="form-label small mb-1">ปีงบประมาณ</label>
            <select name="fiscal_year" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php foreach ($fiscalYears as $y): ?><option value="<?= $y ?>" <?= $y === $fy ? 'selected' : '' ?>><?= $y ?></option><?php endforeach; ?>
            </select>
        </div>
    </form>
    <?php if ($canOperate): ?>
        <a href="<?= Url::to(['create']) ?>" class="btn btn-sm btn-success"><i class="bi bi-plus-circle me-1"></i>สร้างงบพิสูจน์ยอด</a>
    <?php endif; ?>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0 align-middle text-nowrap">
            <thead class="table-light text-center">
                <tr>
                    <th class="text-start">บัญชี</th><th>เดือน</th><th class="text-end">ยอด statement</th><th class="text-end">ยอดบัญชี รพ.</th>
                    <th class="text-end">ผลต่าง</th><th>กระทบยอด</th><th>สถานะ</th><th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$list): ?>
                    <tr><td colspan="8" class="text-center text-body-secondary py-4">ยังไม่มีงบพิสูจน์ยอดในปีงบนี้</td></tr>
                <?php endif; ?>
                <?php foreach ($list as $r): ?>
                    <tr>
                        <td><?= Html::encode($r->account ? $r->account->label() : '-') ?></td>
                        <td class="text-center"><?= $r->period_month ? $months[$r->period_month] : '-' ?></td>
                        <td class="text-end"><?= $money($r->statement_balance) ?></td>
                        <td class="text-end"><?= $money($r->book_balance) ?></td>
                        <td class="text-end <?= $r->isMatched() ? '' : 'text-danger fw-semibold' ?>"><?= $money($r->difference()) ?></td>
                        <td class="text-center">
                            <?php if ($r->isMatched()): ?>
                                <span class="badge bg-success-subtle text-success-emphasis"><i class="bi bi-check2"></i> ตรงกัน</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger-emphasis"><i class="bi bi-exclamation-triangle"></i> ไม่ตรง</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><span class="badge bg-secondary-subtle text-secondary-emphasis"><?= Html::encode(FinanceBankReconcile::statusOptions()[$r->status] ?? $r->status) ?></span></td>
                        <td class="text-center"><a href="<?= Url::to(['view', 'id' => $r->id]) ?>" class="btn btn-sm btn-outline-primary py-0 px-2">เปิด</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
