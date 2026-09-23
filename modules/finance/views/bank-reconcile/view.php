<?php

use app\modules\finance\models\FinanceBankReconcile;
use app\modules\finance\models\FinanceBankReconcileItem;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var FinanceBankReconcile $model */
/** @var FinanceBankReconcileItem[] $items */
/** @var FinanceBankReconcileItem $newItem */

$this->title = 'งบพิสูจน์ยอด: ' . ($model->account ? $model->account->label() : '');
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'งบพิสูจน์ยอด', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'รายละเอียด';

$canOperate = Yii::$app->user->can('financeOperate');
$money = fn ($v) => number_format((float) $v, 2);
$months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

$bySide = [FinanceBankReconcileItem::SIDE_BANK => [], FinanceBankReconcileItem::SIDE_BOOK => []];
foreach ($items as $it) {
    $bySide[$it->side][] = $it;
}
$matched = $model->isMatched();

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-bank fs-4"></i><h4 class="mb-0">งบพิสูจน์ยอดเงินฝาก</h4></div>';
$this->endBlock();
$this->beginBlock('sub-title');
echo Html::encode(($model->account ? $model->account->label() : '') . ' · ' . ($model->period_month ? $months[$model->period_month] . ' ' : '') . 'ปีงบ ' . $model->fiscal_year);
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();

$sidePanel = function (string $side, string $title, array $rows, float $base, float $adjusted) use ($money, $canOperate) {
    ob_start(); ?>
    <div class="card shadow-sm h-100">
        <div class="card-header bg-body"><h6 class="mb-0"><?= Html::encode($title) ?></h6></div>
        <div class="card-body">
            <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                <span class="text-body-secondary"><?= $side === 'bank' ? 'ยอดตาม statement' : 'ยอดตามบัญชี รพ.' ?></span>
                <strong><?= $money($base) ?></strong>
            </div>
            <?php if (!$rows): ?>
                <div class="text-body-secondary small py-2">ไม่มีรายการกระทบยอด</div>
            <?php endif; ?>
            <?php foreach ($rows as $it): ?>
                <div class="d-flex justify-content-between align-items-center py-1">
                    <span class="min-w-0">
                        <span class="badge <?= $it->direction === 'add' ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis' ?>"><?= $it->signedLabel() ?></span>
                        <?= Html::encode($it->typeLabel()) ?><?= $it->description ? ' — ' . Html::encode($it->description) : '' ?>
                        <?= $it->ref ? '<span class="text-body-secondary small">(' . Html::encode($it->ref) . ')</span>' : '' ?>
                    </span>
                    <span class="text-nowrap">
                        <span class="<?= $it->direction === 'add' ? 'text-success-emphasis' : 'text-danger' ?>"><?= $it->signedLabel() ?><?= $money($it->amount) ?></span>
                        <?php if ($canOperate): ?>
                            <?= Html::beginForm(['delete-item', 'id' => $it->id], 'post', ['class' => 'd-inline', 'onsubmit' => "return confirm('ลบรายการนี้?')"]) ?>
                            <button class="btn btn-sm btn-link text-danger p-0 ms-1" title="ลบ"><i class="bi bi-x-lg"></i></button>
                            <?= Html::endForm() ?>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
            <div class="d-flex justify-content-between border-top pt-2 mt-2 fw-bold">
                <span>ยอดคงเหลือที่ถูกต้อง</span>
                <span><?= $money($adjusted) ?></span>
            </div>
        </div>
    </div>
    <?php return ob_get_clean();
};
?>

<?= $this->render('@app/modules/finance/views/cash/_menu', ['active' => 'bankrec']) ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="<?= Url::to(['index', 'fiscal_year' => $model->fiscal_year]) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>รายการทั้งหมด</a>
    <div class="d-flex gap-2">
        <a href="<?= Url::to(['print', 'id' => $model->id]) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-printer me-1"></i>พิมพ์</a>
        <?php if ($canOperate): ?>
            <a href="<?= Url::to(['update', 'id' => $model->id]) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil me-1"></i>แก้ไขหัวงบ</a>
            <?= Html::beginForm(['toggle-status', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?>
            <button class="btn btn-sm <?= $model->status === 'done' ? 'btn-outline-warning' : 'btn-success' ?>">
                <i class="bi bi-<?= $model->status === 'done' ? 'unlock' : 'check2-circle' ?> me-1"></i><?= $model->status === 'done' ? 'กลับเป็นร่าง' : 'ยืนยัน' ?>
            </button>
            <?= Html::endForm() ?>
        <?php endif; ?>
    </div>
</div>

<div class="alert <?= $matched ? 'alert-success' : 'alert-danger' ?> d-flex align-items-center gap-2">
    <i class="bi bi-<?= $matched ? 'check2-circle' : 'exclamation-triangle' ?> fs-5"></i>
    <div>
        <?php if ($matched): ?>
            <strong>ยอดกระทบตรงกัน</strong> — ยอดคงเหลือที่ถูกต้องทั้งสองด้าน = <?= $money($model->adjustedBank()) ?> บาท
        <?php else: ?>
            <strong>ยอดยังไม่ตรงกัน</strong> — ผลต่าง <?= $money($model->difference()) ?> บาท (ธนาคาร <?= $money($model->adjustedBank()) ?> · บัญชี รพ. <?= $money($model->adjustedBook()) ?>)
        <?php endif; ?>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6"><?= $sidePanel('bank', 'ด้านธนาคาร (statement)', $bySide['bank'], (float) $model->statement_balance, $model->adjustedBank()) ?></div>
    <div class="col-lg-6"><?= $sidePanel('book', 'ด้านบัญชี รพ. (book)', $bySide['book'], (float) $model->book_balance, $model->adjustedBook()) ?></div>
</div>

<?php if ($canOperate): ?>
<div class="card shadow-sm">
    <div class="card-header bg-body"><h6 class="mb-0"><i class="bi bi-plus-circle me-1"></i>เพิ่มรายการกระทบยอด</h6></div>
    <div class="card-body">
        <?= Html::beginForm(['add-item', 'id' => $model->id], 'post') ?>
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">ฝั่ง</label>
                <?= Html::dropDownList('FinanceBankReconcileItem[side]', 'bank', FinanceBankReconcileItem::sideOptions(), ['class' => 'form-select form-select-sm']) ?>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">ทิศทาง</label>
                <?= Html::dropDownList('FinanceBankReconcileItem[direction]', 'add', FinanceBankReconcileItem::dirOptions(), ['class' => 'form-select form-select-sm']) ?>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">ประเภท</label>
                <?= Html::dropDownList('FinanceBankReconcileItem[item_type]', 'outstanding_cheque', FinanceBankReconcileItem::typeOptions(), ['class' => 'form-select form-select-sm']) ?>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">จำนวนเงิน</label>
                <?= Html::textInput('FinanceBankReconcileItem[amount]', '', ['class' => 'form-control form-control-sm text-end', 'inputmode' => 'decimal']) ?>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small mb-1">อ้างอิง</label>
                <?= Html::textInput('FinanceBankReconcileItem[ref]', '', ['class' => 'form-control form-control-sm']) ?>
            </div>
            <div class="col-12 col-md-10">
                <label class="form-label small mb-1">รายละเอียด</label>
                <?= Html::textInput('FinanceBankReconcileItem[description]', '', ['class' => 'form-control form-control-sm']) ?>
            </div>
            <div class="col-12 col-md-2 d-grid">
                <button class="btn btn-success btn-sm"><i class="bi bi-plus-lg me-1"></i>เพิ่ม</button>
            </div>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>
<?php endif; ?>
