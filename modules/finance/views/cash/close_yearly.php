<?php

use app\modules\finance\models\FinanceCashCategory;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $fy */
/** @var array $data ['IN'=>['groups'=>[],'total'=>], 'OUT'=>[...]] */

$this->title = 'รายงานรับ-จ่ายประจำปี';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รับ–จ่ายเงิน', 'url' => ['/finance/cash']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-bar-chart-line" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>สรุปยอดจริงรวมทั้งปีงบประมาณ <?= $fy ?> ตามผังบัญชี<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();

$totalIn = $data[FinanceCashCategory::TYPE_IN]['total'] ?? 0;
$totalOut = $data[FinanceCashCategory::TYPE_OUT]['total'] ?? 0;

$renderSide = function (array $side, string $color): string {
    $html = '';
    foreach ($side['groups'] as $g) {
        $html .= '<tr class="table-light fw-semibold"><td>' . Html::encode($g['name']) . '</td><td class="text-end">' . number_format($g['total'], 2) . '</td></tr>';
        foreach ($g['rows'] as $row) {
            $html .= '<tr><td class="ps-4"><small>' . Html::encode($row['name']) . '</small></td><td class="text-end">' . number_format($row['amount'], 2) . '</td></tr>';
        }
    }
    return $html;
};
?>

<?= $this->render('_menu', ['active' => 'close']) ?>
<?= $this->render('_close_menu', ['active' => 'yearly']) ?>

<div class="card border mb-3"><div class="card-body">
    <form method="get" class="row g-2 align-items-end">
        <div class="col-6 col-md-2">
            <label class="form-label" for="f-year">ปีงบประมาณ</label>
            <input type="number" class="form-control" id="f-year" name="year" value="<?= $fy ?>">
        </div>
        <div class="col-6 col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>ดู</button>
            <a href="<?= Url::to(['close-excel', 'report' => 'yearly', 'year' => $fy]) ?>" class="btn btn-success"><i class="bi bi-file-earmark-excel me-1"></i>ส่งออก Excel</a>
        </div>
    </form>
</div></div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card border-success"><div class="card-body text-center">
        <div class="text-success">รวมรายรับ</div><div class="fs-4 fw-bold text-success"><?= number_format($totalIn, 2) ?></div>
    </div></div></div>
    <div class="col-md-4"><div class="card border-warning"><div class="card-body text-center">
        <div class="text-warning">รวมรายจ่าย</div><div class="fs-4 fw-bold text-warning"><?= number_format($totalOut, 2) ?></div>
    </div></div></div>
    <div class="col-md-4"><div class="card border-primary"><div class="card-body text-center">
        <div class="text-primary">คงเหลือ (รับ − จ่าย)</div><div class="fs-4 fw-bold text-primary"><?= number_format($totalIn - $totalOut, 2) ?></div>
    </div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border"><div class="card-header bg-success-subtle fw-semibold text-success-emphasis">รายรับ</div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>หมวด</th><th class="text-end">จำนวนเงิน</th></tr></thead>
                <tbody><?= $renderSide($data[FinanceCashCategory::TYPE_IN], 'success') ?></tbody>
                <tfoot><tr class="fw-bold"><td>รวมรายรับ</td><td class="text-end"><?= number_format($totalIn, 2) ?></td></tr></tfoot>
            </table></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border"><div class="card-header bg-warning-subtle fw-semibold text-warning-emphasis">รายจ่าย</div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>หมวด</th><th class="text-end">จำนวนเงิน</th></tr></thead>
                <tbody><?= $renderSide($data[FinanceCashCategory::TYPE_OUT], 'warning') ?></tbody>
                <tfoot><tr class="fw-bold"><td>รวมรายจ่าย</td><td class="text-end"><?= number_format($totalOut, 2) ?></td></tr></tfoot>
            </table></div>
        </div>
    </div>
</div>
