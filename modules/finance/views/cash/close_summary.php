<?php

use app\components\AppHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $fy */
/** @var app\modules\finance\models\FinanceCashClose[] $batches */

$this->title = 'สรุปการปิดบัญชี';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รับ–จ่ายเงิน', 'url' => ['/finance/cash']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-list-check" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ประวัติการปิดบัญชีประจำวัน ปีงบประมาณ <?= $fy ?><?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();

$totIn = 0;
$totOut = 0;
?>

<?= $this->render('_menu', ['active' => 'close']) ?>
<?= $this->render('_close_menu', ['active' => 'summary']) ?>

<div class="card border mb-3"><div class="card-body">
    <form method="get" class="row g-2 align-items-end">
        <div class="col-6 col-md-2">
            <label class="form-label" for="f-year">ปีงบประมาณ</label>
            <input type="number" class="form-control" id="f-year" name="year" value="<?= $fy ?>">
        </div>
        <div class="col-6 col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>ดู</button>
            <a href="<?= Url::to(['close-excel', 'report' => 'summary', 'year' => $fy]) ?>" class="btn btn-success"><i class="bi bi-file-earmark-excel me-1"></i>ส่งออก Excel</a>
        </div>
    </form>
</div></div>

<div class="card border"><div class="card-body table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light"><tr>
            <th>วันที่ปิด</th><th class="text-end">จำนวนรับ</th><th class="text-end">ยอดรับ</th>
            <th class="text-end">จำนวนจ่าย</th><th class="text-end">ยอดจ่าย</th><th class="text-end">คงเหลือ</th><th>ปิดเมื่อ</th><th class="text-center">รายงาน</th>
        </tr></thead>
        <tbody>
            <?php foreach ($batches as $b): $totIn += (float) $b->total_in; $totOut += (float) $b->total_out; ?>
                <tr>
                    <td class="fw-semibold"><?= Html::encode(AppHelper::convertToThai($b->close_date)) ?></td>
                    <td class="text-end text-body-secondary"><?= (int) $b->in_count ?></td>
                    <td class="text-end text-success"><?= number_format((float) $b->total_in, 2) ?></td>
                    <td class="text-end text-body-secondary"><?= (int) $b->out_count ?></td>
                    <td class="text-end text-warning"><?= number_format((float) $b->total_out, 2) ?></td>
                    <td class="text-end fw-semibold"><?= number_format((float) $b->total_in - (float) $b->total_out, 2) ?></td>
                    <td><small class="text-body-secondary"><?= $b->created_at ? Html::encode(AppHelper::convertToThai(substr($b->created_at, 0, 10))) : '' ?></small></td>
                    <td class="text-center"><?php $dparam = AppHelper::convertToThai($b->close_date); ?>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="bi bi-file-earmark-excel"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= Url::to(['close-excel', 'report' => 'register', 'date' => $dparam]) ?>">ทะเบียนปิดบัญชี</a></li>
                                <li><a class="dropdown-item" href="<?= Url::to(['close-excel', 'report' => 'balance407', 'date' => $dparam, 'year' => $fy]) ?>">เงินคงเหลือประจำวัน (407)</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$batches): ?><tr><td colspan="8" class="text-center text-body-secondary py-5">ยังไม่มีการปิดบัญชีในปีงบนี้</td></tr><?php endif; ?>
        </tbody>
        <?php if ($batches): ?>
        <tfoot><tr class="table-light fw-bold">
            <td>รวม</td><td></td><td class="text-end"><?= number_format($totIn, 2) ?></td>
            <td></td><td class="text-end"><?= number_format($totOut, 2) ?></td>
            <td class="text-end"><?= number_format($totIn - $totOut, 2) ?></td><td></td><td></td>
        </tr></tfoot>
        <?php endif; ?>
    </table>
</div></div>
