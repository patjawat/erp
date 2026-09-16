<?php

use app\components\AppHelper;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinanceReceiptBook $book */
/** @var array $usedNums number => FinanceCashTxn[] */

$this->title = 'เล่มใบเสร็จ ' . $book->book_no;
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนคุมใบเสร็จ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $book->book_no;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-receipt-cutoff" aria-hidden="true"></i>เล่มใบเสร็จ <?= Html::encode($book->book_no) ?></h4>
<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();

$u = $book->usage();
ksort($usedNums);
$dupes = array_flip($u['duplicates']);
$oor = array_flip($u['outOfRange']);
?>

<?= $this->render('@app/modules/finance/views/cash/_menu', ['active' => 'receipt']) ?>

<a href="<?= \yii\helpers\Url::to(['index']) ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left me-1"></i>กลับทะเบียน</a>

<div class="row g-3 mb-3">
    <div class="col-lg-7"><div class="card border h-100"><div class="card-body">
        <h6 class="mb-3">ข้อมูลเล่ม</h6>
        <div class="row g-2 small">
            <div class="col-6"><span class="text-body-secondary">ช่วงเลข:</span> <?= (int) $book->number_from ?>–<?= (int) $book->number_to ?> (<?= $u['total'] ?> ฉบับ)</div>
            <div class="col-6"><span class="text-body-secondary">ประเภท:</span> <?= Html::encode($book->receipt_type ?: '-') ?></div>
            <div class="col-6"><span class="text-body-secondary">เบิกให้:</span> <?= Html::encode($book->issuedTo ? $book->issuedTo->fullname() : '-') ?></div>
            <div class="col-6"><span class="text-body-secondary">วันที่เบิก:</span> <?= $book->issued_date ? Html::encode(AppHelper::convertToThai($book->issued_date)) : '-' ?></div>
            <div class="col-6"><span class="text-body-secondary">สถานะ:</span> <?= Html::encode($book->statusLabel()) ?></div>
            <div class="col-6"><span class="text-body-secondary">รับเข้า:</span> <?= $book->received_date ? Html::encode(AppHelper::convertToThai($book->received_date)) : '-' ?></div>
        </div>
    </div></div></div>
    <div class="col-lg-5"><div class="card border h-100"><div class="card-body text-center">
        <div class="row">
            <div class="col-4"><div class="fs-4 fw-bold text-primary"><?= $u['used'] ?></div><small class="text-body-secondary">ใช้แล้ว</small></div>
            <div class="col-4"><div class="fs-4 fw-bold text-success"><?= $u['remaining'] ?></div><small class="text-body-secondary">คงเหลือ</small></div>
            <div class="col-4"><div class="fs-4 fw-bold"><?= $u['total'] ?></div><small class="text-body-secondary">ทั้งหมด</small></div>
        </div>
        <div class="progress mt-3" style="height:8px"><div class="progress-bar" style="width:<?= $u['total'] ? round($u['used'] / $u['total'] * 100) : 0 ?>%"></div></div>
        <?php if ($u['duplicates']): ?><div class="alert alert-danger mt-3 mb-0 py-2 small">⚠️ เลขซ้ำ: <?= Html::encode(implode(', ', $u['duplicates'])) ?></div><?php endif; ?>
        <?php if ($u['outOfRange']): ?><div class="alert alert-warning mt-2 mb-0 py-2 small">เลขนอกช่วงเล่ม: <?= Html::encode(implode(', ', $u['outOfRange'])) ?></div><?php endif; ?>
    </div></div></div>
</div>

<div class="card border"><div class="card-header bg-body fw-semibold">เลขที่ใช้แล้วในเล่ม (<?= count($usedNums) ?>)</div>
    <div class="table-responsive"><table class="table table-sm table-hover mb-0">
        <thead class="table-light"><tr><th style="width:120px">เลขที่ใบเสร็จ</th><th>วันที่รับ</th><th class="text-end">จำนวนเงิน</th><th>สถานะ</th></tr></thead>
        <tbody>
            <?php foreach ($usedNums as $num => $txns): foreach ($txns as $t): ?>
                <tr class="<?= isset($dupes[$num]) ? 'table-danger' : (isset($oor[$num]) ? 'table-warning' : '') ?>">
                    <td class="fw-semibold"><?= Html::encode($book->book_no . '/' . $num) ?></td>
                    <td><?= Html::encode(AppHelper::convertToThai($t->doc_date)) ?></td>
                    <td class="text-end"><?= number_format((float) $t->amount, 2) ?></td>
                    <td><?php if (isset($dupes[$num])): ?><span class="badge bg-danger">ซ้ำ</span><?php endif; ?>
                        <?php if (isset($oor[$num])): ?><span class="badge bg-warning text-dark">นอกช่วง</span><?php endif; ?></td>
                </tr>
            <?php endforeach; endforeach; ?>
            <?php if (!$usedNums): ?><tr><td colspan="4" class="text-center text-body-secondary py-4">ยังไม่มีการใช้เลขในเล่มนี้</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>
