<?php

use app\modules\finance\services\FinanceRegisterService;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $register */
/** @var array $categories */
/** @var array|null $data */
/** @var array $filters */
/** @var int[] $fiscalYears */

$catLabel = $categories[$register['cat']]['label'] ?? '';
$this->title = 'ทะเบียนคุม' . $register['label'];
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนคุม', 'url' => ['/finance/register']];
$this->params['breadcrumbs'][] = $register['label'];

$months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
    'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
$fyOrder = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9]; // เรียงตามปีงบ

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-journal-check fs-4" aria-hidden="true"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('sub-title');
echo Html::encode($catLabel . ' · เล่มที่ ' . $register['no']);
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'register']);
$this->endBlock();

$money = fn ($v) => $v === null || $v === '' ? '' : number_format((float) $v, 2);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 d-print-none">
    <a href="<?= Url::to(['/finance/register']) ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>กลับหน้ารวมทะเบียนคุม
    </a>
    <?php if ($data !== null): ?>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="bi bi-printer me-1" aria-hidden="true"></i>พิมพ์
            </button>
            <a href="<?= Url::to(array_filter(array_merge(
                ['/finance/register/export', 'key' => $register['key']],
                $filters,
                ['fiscal_year' => $data['period']['fiscal_year'] ?? null]
            ), fn ($v) => $v !== null && $v !== '')) ?>"
               class="btn btn-sm btn-success">
                <i class="bi bi-file-earmark-excel me-1" aria-hidden="true"></i>Excel
            </a>
        </div>
    <?php endif; ?>
</div>

<?php if ($data === null): ?>
    <div class="alert alert-warning d-flex align-items-start gap-2" role="alert">
        <i class="bi bi-hourglass-split fs-5" aria-hidden="true"></i>
        <div>
            <strong>อยู่ระหว่างพัฒนา (เฟส <?= (int) $register['phase'] ?>)</strong><br>
            ทะเบียนนี้ยังไม่ได้ต่อ Register Layer — ต้นทางข้อมูล: <?= Html::encode($register['source']) ?>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-body"><h6 class="mb-0">โครงตารางทะเบียนคุม (รูปแบบมาตรฐาน)</h6></div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0 align-middle text-nowrap">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>ลำดับ</th><th>วันที่</th><th>เลขที่เอกสาร</th><th>รายการ</th>
                        <th>รับ</th><th>จ่าย</th><th>คงเหลือ</th><th>อ้างอิง</th><th>หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="9" class="text-center text-body-secondary py-5">
                        <i class="bi bi-inbox fs-3 d-block mb-2" aria-hidden="true"></i>รอต่อ Register Layer
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <?php $cols = $data['columns']; $colCount = count($cols); ?>

    <form method="get" class="card card-body shadow-sm mb-3 d-print-none">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">ปีงบประมาณ</label>
                <select name="fiscal_year" class="form-select form-select-sm">
                    <?php foreach ($fiscalYears as $fy): ?>
                        <option value="<?= $fy ?>" <?= (int) $data['period']['fiscal_year'] === $fy ? 'selected' : '' ?>><?= $fy ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">เดือน</label>
                <select name="month" class="form-select form-select-sm">
                    <option value="">ทั้งปี</option>
                    <?php foreach ($fyOrder as $m): ?>
                        <option value="<?= $m ?>" <?= (int) ($data['period']['month'] ?? 0) === $m ? 'selected' : '' ?>><?= $months[$m] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (!empty($data['filterSelect'])): ?>
                <?php $fs = $data['filterSelect']; ?>
                <div class="col-12 col-md-4">
                    <label class="form-label small mb-1"><?= Html::encode($fs['label']) ?></label>
                    <select name="<?= Html::encode($fs['param']) ?>" class="form-select form-select-sm">
                        <option value=""><?= Html::encode($fs['allLabel']) ?></option>
                        <?php foreach ($fs['options'] as $optVal => $optLabel): ?>
                            <option value="<?= Html::encode((string) $optVal) ?>" <?= (string) ($fs['selected'] ?? '') === (string) $optVal ? 'selected' : '' ?>>
                                <?= Html::encode($optLabel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <div class="col-12 col-md-auto">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-funnel me-1" aria-hidden="true"></i>แสดง
                </button>
            </div>
        </div>
    </form>

    <div class="text-center mb-2 d-none d-print-block">
        <div class="fw-bold">ทะเบียนคุม<?= Html::encode($register['label']) ?></div>
        <div><?= Html::encode(FinanceRegisterService::periodLabel($data['period'])) ?></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-body d-flex justify-content-between align-items-center d-print-none">
            <h6 class="mb-0"><?= Html::encode(FinanceRegisterService::periodLabel($data['period'])) ?></h6>
            <span class="text-body-secondary small"><?= count($data['rows']) ?> รายการ</span>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0 align-middle text-nowrap">
                <thead class="table-light">
                    <tr class="text-center">
                        <?php foreach ($cols as $c): ?>
                            <th <?= !empty($c['w']) ? 'style="width:' . $c['w'] . '"' : '' ?>><?= Html::encode($c['label']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($data['opening'])): ?>
                        <tr class="table-light fw-semibold">
                            <?php foreach ($cols as $c): ?>
                                <?php if ($c['key'] === ($data['totalLabelKey'] ?? '')): ?>
                                    <td>ยอดยกมา</td>
                                <?php elseif ($c['key'] === ($data['runningKey'] ?? 'balance')): ?>
                                    <td class="text-end"><?= $money($data['opening']) ?></td>
                                <?php else: ?>
                                    <td></td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endif; ?>

                    <?php if (!$data['rows']): ?>
                        <tr><td colspan="<?= $colCount ?>" class="text-center text-body-secondary py-4">ไม่มีรายการในช่วงที่เลือก</td></tr>
                    <?php endif; ?>

                    <?php foreach ($data['rows'] as $row): ?>
                        <tr>
                            <?php foreach ($cols as $c): ?>
                                <?php
                                $val = $row[$c['key']] ?? null;
                                $align = ($c['align'] ?? '') === 'end' ? 'text-end' : (($c['align'] ?? '') === 'center' ? 'text-center' : '');
                                ?>
                                <td class="<?= $align ?>"><?= !empty($c['money']) ? $money($val) : Html::encode((string) ($val ?? '')) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <?php foreach ($cols as $c): ?>
                            <?php if (array_key_exists($c['key'], $data['totals'])): ?>
                                <td class="text-end"><?= $money($data['totals'][$c['key']]) ?></td>
                            <?php elseif ($c['key'] === ($data['totalLabelKey'] ?? '')): ?>
                                <td class="text-end">รวม</td>
                            <?php else: ?>
                                <td></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php endif; ?>
