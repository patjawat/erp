<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $fy */
/** @var array $rows */
/** @var array $tot */
/** @var int[] $fiscalYears */

$this->title = 'เงินงบประมาณ';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
$money = fn ($v) => number_format((float) $v, 2);

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-bank2 fs-4"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('sub-title');
echo 'เงินงบประมาณและการนำส่งคลัง (หมวด 1)';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'budget']);
$this->endBlock();
?>

<?= $this->render('_menu', ['active' => 'index', 'fy' => $fy]) ?>

<div class="d-flex justify-content-end mb-3">
    <form method="get" class="d-flex align-items-end gap-2">
        <div>
            <label class="form-label small mb-1">ปีงบประมาณ</label>
            <select name="fiscal_year" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php foreach ($fiscalYears as $y): ?><option value="<?= $y ?>" <?= $y === $fy ? 'selected' : '' ?>><?= $y ?></option><?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<div class="row g-3 mb-4">
    <?php foreach ([
        ['label' => 'ได้รับจัดสรร', 'value' => $tot['allot'], 'class' => 'text-body', 'icon' => 'bi-inboxes'],
        ['label' => 'เบิกจ่ายแล้ว', 'value' => $tot['disb'], 'class' => 'text-primary', 'icon' => 'bi-arrow-up-right'],
        ['label' => 'คงเหลือ', 'value' => $tot['allot'] - $tot['disb'], 'class' => 'text-success-emphasis', 'icon' => 'bi-wallet2'],
    ] as $c): ?>
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm"><div class="card-body py-3 d-flex align-items-center gap-3">
                <i class="bi <?= $c['icon'] ?> fs-2 <?= $c['class'] ?>"></i>
                <div><div class="fs-4 fw-semibold <?= $c['class'] ?>"><?= $money($c['value']) ?></div>
                <div class="text-body-secondary small"><?= Html::encode($c['label']) ?></div></div>
            </div></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-body"><h6 class="mb-0">สรุปตามงบรายจ่าย ปีงบ <?= $fy ?></h6></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle">
            <thead class="table-light text-center"><tr><th class="text-start">งบรายจ่าย</th><th class="text-end">จัดสรร</th><th class="text-end">เบิกจ่าย</th><th class="text-end">คงเหลือ</th></tr></thead>
            <tbody>
                <?php if (!$rows): ?><tr><td colspan="4" class="text-center text-body-secondary py-4">ยังไม่มีข้อมูลงบประมาณในปีงบนี้</td></tr><?php endif; ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= Html::encode($r['label']) ?></td>
                        <td class="text-end"><?= $money($r['allot']) ?></td>
                        <td class="text-end"><?= $money($r['disb']) ?></td>
                        <td class="text-end fw-semibold <?= $r['remain'] < 0 ? 'text-danger' : '' ?>"><?= $money($r['remain']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <?php if ($rows): ?>
                <tfoot><tr class="table-light fw-bold"><td>รวม</td><td class="text-end"><?= $money($tot['allot']) ?></td><td class="text-end"><?= $money($tot['disb']) ?></td><td class="text-end"><?= $money($tot['allot'] - $tot['disb']) ?></td></tr></tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<p class="text-body-secondary small mt-3"><i class="bi bi-info-circle me-1"></i>ทะเบียนคุมทางการ (พิมพ์/Excel): เงินประจำงวด · รับ-จ่ายงบประมาณ · นส.02 · เบิกเกินส่งคืน · งบกลาง — ดูที่ <a href="<?= Url::to(['/finance/register']) ?>">ทะเบียนคุม</a></p>
