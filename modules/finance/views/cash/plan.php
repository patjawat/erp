<?php

use app\modules\finance\models\FinanceCashCategory;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $year */
/** @var int[] $actualYears */
/** @var int[] $planYears */
/** @var array $types */
/** @var array $netA */
/** @var array $netP */

$this->title = 'แผนรายรับ-รายจ่ายประจำปี';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รับ–จ่ายเงิน', 'url' => ['/finance/cash']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-calendar3" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ตั้งยอดแผนรับ-จ่ายล่วงหน้า 3 ปี เทียบกับผลจริงย้อนหลัง 3 ปี (รองรับทั้งแผนรายรับและรายจ่าย)<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();

$nCols = count($actualYears) + count($planYears) + 1;
?>

<?= $this->render('_menu', ['active' => 'plan']) ?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card border mb-3"><div class="card-body d-flex flex-wrap justify-content-between align-items-end gap-2">
    <a href="<?= Url::to(['plan-excel', 'year' => $year]) ?>" class="btn btn-sm btn-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
    <form method="get" class="d-flex gap-2 align-items-end">
        <div><label class="form-label mb-0 small">ปีงบเริ่มแผน (พ.ศ.)</label>
            <input type="number" class="form-control form-control-sm" name="year" value="<?= $year ?>" style="width:120px"></div>
        <button type="submit" class="btn btn-sm btn-primary">ดู</button>
    </form>
</div></div>

<?= Html::beginForm(['plan-save'], 'post') ?>
<?= Html::hiddenInput('year', $year) ?>

<div class="card border"><div class="table-responsive">
    <table class="table table-bordered table-sm align-middle mb-0">
        <thead class="table-light text-center">
            <tr>
                <th rowspan="2" style="min-width:260px">รายการ</th>
                <th colspan="<?= count($actualYears) ?>">ผลจริงย้อนหลัง</th>
                <th colspan="<?= count($planYears) ?>">แผน</th>
            </tr>
            <tr>
                <?php foreach ($actualYears as $ay): ?><th style="width:120px"><?= $ay ?></th><?php endforeach; ?>
                <?php foreach ($planYears as $py): ?><th class="text-primary" style="width:140px"><?= $py ?></th><?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ([FinanceCashCategory::TYPE_IN => 'รายรับ', FinanceCashCategory::TYPE_OUT => 'รายจ่าย'] as $type => $label): ?>
                <tr class="table-secondary"><td colspan="<?= $nCols ?>" class="fw-bold"><?= $label ?></td></tr>
                <?php foreach ($types[$type]['groups'] as $g): ?>
                    <tr class="table-light">
                        <td class="fw-semibold"><?= Html::encode($g['name']) ?></td>
                        <?php foreach ($actualYears as $ay): ?><td class="text-end fw-semibold"><?= number_format($g['subA'][$ay], 2) ?></td><?php endforeach; ?>
                        <?php foreach ($planYears as $py): ?><td class="text-end fw-semibold text-warning-emphasis"><?= number_format($g['subP'][$py], 2) ?></td><?php endforeach; ?>
                    </tr>
                    <?php foreach ($g['rows'] as $row): ?>
                        <tr>
                            <td class="ps-4"><?= Html::encode($row['name']) ?></td>
                            <?php foreach ($actualYears as $ay): ?>
                                <td class="text-end text-body-secondary"><?= number_format($row['actual'][$ay], 2) ?></td>
                            <?php endforeach; ?>
                            <?php foreach ($planYears as $py): ?>
                                <td class="p-1"><input type="text" inputmode="decimal" class="form-control form-control-sm text-end plan-input"
                                    name="plan[<?= $row['id'] ?>][<?= $py ?>]"
                                    value="<?= $row['plan'][$py] > 0 ? number_format($row['plan'][$py], 2) : '' ?>" placeholder="0.00"></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <tr class="table-primary fw-bold">
                    <td class="text-end">รวม<?= $label ?></td>
                    <?php foreach ($actualYears as $ay): ?><td class="text-end"><?= number_format($types[$type]['totA'][$ay], 2) ?></td><?php endforeach; ?>
                    <?php foreach ($planYears as $py): ?><td class="text-end"><?= number_format($types[$type]['totP'][$py], 2) ?></td><?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            <tr class="table-primary fw-bold border-top border-3 border-primary-subtle">
                <td class="text-end">รายรับสูง (ต่ำกว่า) รายจ่ายสุทธิ</td>
                <?php foreach ($actualYears as $ay): ?><td class="text-end <?= $netA[$ay] < 0 ? 'text-danger' : '' ?>"><?= number_format($netA[$ay], 2) ?></td><?php endforeach; ?>
                <?php foreach ($planYears as $py): ?><td class="text-end <?= $netP[$py] < 0 ? 'text-danger' : '' ?>"><?= number_format($netP[$py], 2) ?></td><?php endforeach; ?>
            </tr>
        </tbody>
    </table>
</div></div>

<div class="d-flex justify-content-end mt-3">
    <?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึกแผน', ['class' => 'btn btn-primary']) ?>
</div>
<?= Html::endForm() ?>
