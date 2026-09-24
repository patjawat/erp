<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\finance\models\FinanceChequeBook;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinanceChequeBook[] $books */
/** @var array $accounts */
/** @var string|null $accountId */

$this->title = 'ทะเบียนเล่มเช็ค';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'พิมพ์เช็ค', 'url' => ['/finance/cheque']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo 'คุมเล่มเช็คที่รับเข้า — ช่วงเลข / ใช้ไป / คงเหลือ ต่อบัญชีจ่าย';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'cheque']);
$this->endBlock();

$badge = [
    FinanceChequeBook::STATUS_ACTIVE => 'bg-success-subtle text-success-emphasis',
    FinanceChequeBook::STATUS_USED_UP => 'bg-secondary-subtle text-secondary-emphasis',
    FinanceChequeBook::STATUS_CANCELLED => 'bg-danger-subtle text-danger-emphasis',
];
?>

<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
    <?= Html::beginForm(['book-index'], 'get', ['class' => 'd-flex gap-2 align-items-center']) ?>
        <select name="account_id" class="form-select form-select-sm" style="min-width:260px" onchange="this.form.submit()">
            <option value="">— ทุกบัญชีจ่าย —</option>
            <?php foreach ($accounts as $aid => $al): ?>
                <option value="<?= $aid ?>" <?= (string) $accountId === (string) $aid ? 'selected' : '' ?>><?= Html::encode($al) ?></option>
            <?php endforeach; ?>
        </select>
    <?= Html::endForm() ?>
    <div class="d-flex gap-2">
        <a href="<?= Url::to(['book-create']) ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>รับเล่มเช็คเข้า</a>
        <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>ทะเบียนคุมเช็ค</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>บัญชีจ่าย</th><th>เล่ม</th><th>ช่วงเลข</th>
                    <th class="text-center">ทั้งหมด</th><th class="text-center">ใช้แล้ว</th><th class="text-center">คงเหลือ</th>
                    <th class="text-center">สถานะ</th><th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$books): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">ยังไม่มีเล่มเช็ค — กด "รับเล่มเช็คเข้า"</td></tr>
            <?php else: foreach ($books as $b): $rem = $b->remaining(); $low = $rem > 0 && $rem <= 5; ?>
                <tr>
                    <td><?= $b->cashAccount ? Html::encode($b->cashAccount->label()) : '#' . $b->cash_account_id ?></td>
                    <td><?= Html::encode($b->book_no ?: '–') ?></td>
                    <td><?= Html::encode($b->formatNo((int) $b->start_no) . ' – ' . $b->formatNo((int) $b->end_no)) ?></td>
                    <td class="text-center"><?= $b->totalLeaves() ?></td>
                    <td class="text-center"><?= $b->usedCount() ?></td>
                    <td class="text-center <?= $low ? 'text-danger fw-bold' : '' ?>"><?= $rem ?><?= $low ? ' <i class="bi bi-exclamation-triangle" title="ใกล้หมดเล่ม"></i>' : '' ?></td>
                    <td class="text-center"><span class="badge <?= $badge[$b->status] ?? 'bg-light' ?>"><?= Html::encode(FinanceChequeBook::statusOptions()[$b->status] ?? $b->status) ?></span></td>
                    <td class="text-end">
                        <?php if ($b->status === FinanceChequeBook::STATUS_ACTIVE): ?>
                            <?= Html::beginForm(['book-close', 'id' => $b->id], 'post', ['class' => 'd-inline', 'onsubmit' => 'return confirm("ปิดเล่มนี้ (ใช้หมดเล่ม)?")']) ?>
                                <?= Html::hiddenInput('status', FinanceChequeBook::STATUS_USED_UP) ?>
                                <button class="btn btn-sm btn-outline-secondary">ปิดเล่ม</button>
                            <?= Html::endForm() ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
