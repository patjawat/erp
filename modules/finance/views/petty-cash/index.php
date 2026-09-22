<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinancePettyCash[] $funds */

$this->title = 'เงินสดย่อย / เงินทดรองจ่าย';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$canOperate = Yii::$app->user->can('financeOperate');
$money = fn ($v) => number_format((float) $v, 2);

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-wallet2 fs-4" aria-hidden="true"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('sub-title');
echo 'วงเงินหมุนเวียนรายจุด (imprest) แยกจากเงินยืมรายสัญญา';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'petty']);
$this->endBlock();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">กองเงินสดย่อย</h5>
    <?php if ($canOperate): ?>
        <a href="<?= Url::to(['create']) ?>" class="btn btn-success btn-sm">
            <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>สร้างกองใหม่
        </a>
    <?php endif; ?>
</div>

<?php if (!$funds): ?>
    <div class="card shadow-sm"><div class="card-body text-center text-body-secondary py-5">
        <i class="bi bi-wallet2 fs-1 d-block mb-2" aria-hidden="true"></i>
        ยังไม่มีกองเงินสดย่อย<?= $canOperate ? ' — กด "สร้างกองใหม่" เพื่อเริ่ม' : '' ?>
    </div></div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($funds as $f): ?>
            <?php $onHand = $f->balanceOnHand(); $unrei = $f->unreimbursed(); ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm <?= $f->is_active ? '' : 'opacity-75' ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="min-w-0">
                                <h6 class="mb-0 text-truncate">
                                    <?= $f->code ? '<span class="text-body-secondary">' . Html::encode($f->code) . '</span> ' : '' ?>
                                    <?= Html::encode($f->name) ?>
                                </h6>
                                <?php if ($f->custodian_name || $f->unit): ?>
                                    <small class="text-body-secondary">
                                        <?= Html::encode(trim(($f->unit ? $f->unit : '') . ($f->custodian_name ? ' · ' . $f->custodian_name : ''), ' ·')) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            <?php if (!$f->is_active): ?>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis">ปิดใช้</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-top">
                            <span class="text-body-secondary small">วงเงิน</span>
                            <strong><?= $money($f->float_amount) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-body-secondary small">เงินคงเหลือในมือ</span>
                            <strong class="<?= $onHand < 0 ? 'text-danger' : 'text-success-emphasis' ?>"><?= $money($onHand) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-body-secondary small">รอเบิกชดเชย</span>
                            <strong class="<?= $unrei > 0 ? 'text-warning-emphasis' : '' ?>"><?= $money($unrei) ?></strong>
                        </div>
                    </div>
                    <div class="card-footer bg-body d-flex gap-2">
                        <a href="<?= Url::to(['view', 'id' => $f->id]) ?>" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="bi bi-list-ul me-1" aria-hidden="true"></i>บันทึก/ดูรายการ
                        </a>
                        <a href="<?= Url::to(['/finance/register/view', 'key' => 'petty_cash', 'fund_id' => $f->id]) ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-journal-check" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
