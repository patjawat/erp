<?php

use app\components\AppHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinancePatientDeposit[] $deposits */
/** @var int $fy */
/** @var int[] $fiscalYears */

$this->title = 'เงินมัดจำ / รับฝากผู้ป่วย';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ลูกหนี้ค่ารักษา', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'เงินมัดจำ';

$canOperate = Yii::$app->user->can('financeOperate');
$money = fn ($v) => number_format((float) $v, 2);

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-piggy-bank fs-4"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'ar']);
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
    <div class="d-flex gap-2">
        <a href="<?= Url::to(['/finance/register/view', 'key' => 'patient_deposit', 'fiscal_year' => $fy]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-journal-check me-1"></i>ทะเบียนคุม</a>
        <?php if ($canOperate): ?><a href="<?= Url::to(['deposit-form']) ?>" class="btn btn-sm btn-success"><i class="bi bi-plus-circle me-1"></i>รับเงินมัดจำ</a><?php endif; ?>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-body"><span class="text-body-secondary small"><?= count($deposits) ?> รายการ</span></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle text-nowrap">
            <thead class="table-light text-center">
                <tr>
                    <th class="text-start">วันที่</th><th class="text-start">เลขที่ใบรับ</th><th class="text-start">ผู้ป่วย/HN</th>
                    <th class="text-end">รับฝาก</th><th class="text-end">หักชำระ</th><th class="text-end">คืน</th><th class="text-end">คงเหลือ</th><th>สถานะ</th><?php if ($canOperate): ?><th></th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!$deposits): ?>
                    <tr><td colspan="<?= $canOperate ? 9 : 8 ?>" class="text-center text-body-secondary py-4">ยังไม่มีรายการเงินมัดจำ</td></tr>
                <?php endif; ?>
                <?php foreach ($deposits as $d): ?>
                    <tr>
                        <td><?= Html::encode(AppHelper::convertToThai($d->deposit_date)) ?></td>
                        <td><?= Html::encode($d->receipt_no ?: '-') ?></td>
                        <td><?= Html::encode($d->patient_name ?: ($d->hn ? 'HN ' . $d->hn : '-')) ?></td>
                        <td class="text-end"><?= $money($d->amount) ?></td>
                        <td class="text-end"><?= $d->used_amount > 0 ? $money($d->used_amount) : '' ?></td>
                        <td class="text-end"><?= $d->refunded_amount > 0 ? $money($d->refunded_amount) : '' ?></td>
                        <td class="text-end fw-semibold"><?= $money($d->remaining()) ?></td>
                        <td class="text-center"><span class="badge bg-secondary-subtle text-secondary-emphasis"><?= Html::encode($d->statusLabel()) ?></span></td>
                        <?php if ($canOperate): ?>
                            <td class="text-center text-nowrap">
                                <a href="<?= Url::to(['deposit-form', 'id' => $d->id]) ?>" class="btn btn-sm btn-link py-0 px-1" title="แก้ไข"><i class="bi bi-pencil"></i></a>
                                <?= Html::beginForm(['delete-deposit', 'id' => $d->id], 'post', ['class' => 'd-inline', 'onsubmit' => "return confirm('ลบรายการนี้?')"]) ?>
                                <button type="submit" class="btn btn-sm btn-link text-danger py-0 px-1" title="ลบ"><i class="bi bi-trash"></i></button>
                                <?= Html::endForm() ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
