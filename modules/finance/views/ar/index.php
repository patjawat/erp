<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $fy */
/** @var array $rows */
/** @var array $tot */
/** @var int[] $fiscalYears */

$this->title = 'ลูกหนี้ค่ารักษา (AR)';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$money = fn ($v) => number_format((float) $v, 2);

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-clipboard2-pulse fs-4" aria-hidden="true"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('sub-title');
echo 'ลูกหนี้ค่ารักษาแยกสิทธิ + รายได้ค้างรับ — แหล่งข้อมูลนำเข้าจาก HIS/ระบบเคลม';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'ar']);
$this->endBlock();

$canOperate = Yii::$app->user->can('financeOperate');
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <form method="get" class="d-flex align-items-end gap-2">
        <div>
            <label class="form-label small mb-1">ปีงบประมาณ</label>
            <select name="fiscal_year" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php foreach ($fiscalYears as $y): ?>
                    <option value="<?= $y ?>" <?= $y === $fy ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= Url::to(['invoices', 'fiscal_year' => $fy]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-list-ul me-1"></i>ทะเบียนลูกหนี้</a>
        <a href="<?= Url::to(['deposits', 'fiscal_year' => $fy]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-piggy-bank me-1"></i>เงินมัดจำ</a>
        <?php if ($canOperate): ?>
            <a href="<?= Url::to(['import']) ?>" class="btn btn-sm btn-success"><i class="bi bi-upload me-1"></i>นำเข้าจาก HIS</a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach ([
        ['label' => 'ตั้งเบิกทั้งหมด', 'value' => $tot['billed'], 'class' => 'text-body', 'icon' => 'bi-receipt'],
        ['label' => 'รับ/ตัดแล้ว', 'value' => $tot['settled'], 'class' => 'text-success-emphasis', 'icon' => 'bi-check2-circle'],
        ['label' => 'คงค้างเรียกเก็บ', 'value' => $tot['outstanding'], 'class' => 'text-warning-emphasis', 'icon' => 'bi-hourglass-split'],
    ] as $c): ?>
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm"><div class="card-body py-3 d-flex align-items-center gap-3">
                <i class="bi <?= $c['icon'] ?> fs-2 <?= $c['class'] ?>"></i>
                <div>
                    <div class="fs-4 fw-semibold <?= $c['class'] ?>"><?= $money($c['value']) ?></div>
                    <div class="text-body-secondary small"><?= Html::encode($c['label']) ?></div>
                </div>
            </div></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-body d-flex justify-content-between align-items-center">
        <h6 class="mb-0">สรุปลูกหนี้แยกสิทธิ ปีงบ <?= $fy ?></h6>
        <span class="text-body-secondary small"><?= number_format($tot['count']) ?> รายการ</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0 align-middle">
            <thead class="table-light text-center">
                <tr><th class="text-start">สิทธิ</th><th>จำนวนใบ</th><th class="text-end">ตั้งเบิก</th><th class="text-end">รับ/ตัดแล้ว</th><th class="text-end">คงค้าง</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="6" class="text-center text-body-secondary py-4">ยังไม่มีข้อมูลลูกหนี้ในปีงบนี้</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= Html::encode($r['fund']->name) ?> <span class="text-body-secondary small">(<?= Html::encode($r['fund']->code) ?>)</span></td>
                        <td class="text-center"><?= number_format($r['count']) ?></td>
                        <td class="text-end"><?= $money($r['billed']) ?></td>
                        <td class="text-end text-success-emphasis"><?= $money($r['settled']) ?></td>
                        <td class="text-end fw-semibold <?= $r['outstanding'] > 0 ? 'text-warning-emphasis' : '' ?>"><?= $money($r['outstanding']) ?></td>
                        <td class="text-center">
                            <a href="<?= Url::to(['/finance/register/view', 'key' => 'ar_by_fund', 'fiscal_year' => $fy, 'fund_id' => $r['fund']->id]) ?>" class="btn btn-sm btn-link p-0" title="ทะเบียนคุม"><i class="bi bi-journal-check"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <?php if ($rows): ?>
                <tfoot><tr class="table-light fw-bold">
                    <td>รวม</td><td class="text-center"><?= number_format($tot['count']) ?></td>
                    <td class="text-end"><?= $money($tot['billed']) ?></td>
                    <td class="text-end"><?= $money($tot['settled']) ?></td>
                    <td class="text-end"><?= $money($tot['outstanding']) ?></td><td></td>
                </tr></tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<p class="text-body-secondary small mt-3">
    <i class="bi bi-info-circle me-1"></i>ทะเบียนคุมทางการ (พิมพ์/Excel) — ลูกหนี้แยกสิทธิ · รายได้ค้างรับ · เงินมัดจำ — ดูที่
    <a href="<?= Url::to(['/finance/register']) ?>">ทะเบียนคุม</a>
</p>
