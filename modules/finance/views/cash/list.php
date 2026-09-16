<?php

use app\components\AppHelper;
use app\modules\finance\models\FinanceCashCategory;
use app\modules\finance\models\FinanceCashTxn;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var string $type */
/** @var string $active */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $fiscalYear */
/** @var string $date */
/** @var string $q */
/** @var float $sum */
/** @var array $tree */
/** @var app\modules\finance\models\FinanceReceiptBook[] $receiptBooks */

$typeLabel = FinanceCashCategory::typeLabel($type);
$isIn = $type === FinanceCashCategory::TYPE_IN;
$this->title = 'ทะเบียน' . $typeLabel;
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รับ–จ่ายเงิน', 'url' => ['/finance/cash']];
$this->params['breadcrumbs'][] = $typeLabel;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2">
    <i class="bi <?= $isIn ? 'bi-cash-coin text-success' : 'bi-receipt text-warning' ?>" aria-hidden="true"></i>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>บันทึกรายการ<?= Html::encode($typeLabel) ?>เงินบำรุง แยกละเอียดทีละใบ<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();
?>

<?= $this->render('_menu', ['active' => $active]) ?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card border">
    <div class="card-header bg-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0">รายการบันทึก “<?= Html::encode($typeLabel) ?>”</h5>
        <button type="button" class="btn <?= $isIn ? 'btn-success' : 'btn-warning' ?>" data-cash-add data-year="<?= (int) $fiscalYear ?>">
            <i class="bi bi-plus-circle me-1" aria-hidden="true"></i> บันทึก<?= Html::encode($typeLabel) ?>
        </button>
    </div>
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end mb-3">
            <div class="col-6 col-md-2">
                <label class="form-label" for="f-year">ปีงบประมาณ</label>
                <input type="number" class="form-control" id="f-year" name="fiscal_year" value="<?= Html::encode((string) $fiscalYear) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" for="f-date">วันที่</label>
                <?= DatepickerThai::widget([
                    'name' => 'date',
                    'value' => $date,
                    'options' => ['id' => 'f-date', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.'],
                ]) ?>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="f-q">ค้นหา (เลขที่ / ผู้รับ-จ่าย / หมายเหตุ)</label>
                <input type="text" class="form-control" id="f-q" name="q" value="<?= Html::encode($q) ?>">
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>ค้นหา</button>
                <a href="<?= Url::to([$active]) ?>" class="btn btn-outline-secondary">เคลียร์</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:110px">วันที่<?= $isIn ? 'รับ' : 'จ่าย' ?></th>
                        <th style="width:120px">เลขที่</th>
                        <th>หัวข้อบัญชี</th>
                        <th style="width:90px">วิธี</th>
                        <th>หมายเหตุ</th>
                        <th class="text-end" style="width:130px">จำนวนเงิน</th>
                        <th style="width:90px" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php /** @var FinanceCashTxn $row */ ?>
                    <?php foreach ($dataProvider->getModels() as $row): ?>
                        <tr>
                            <td><?= Html::encode(AppHelper::convertToThai($row->doc_date)) ?></td>
                            <td><?= Html::encode($row->doc_no ?: '-') ?></td>
                            <td><small><?= Html::encode($row->category ? $row->category->pathLabel() : '-') ?></small></td>
                            <td><span class="badge bg-light text-dark border"><?= Html::encode($row->payMethodLabel()) ?></span></td>
                            <td><small class="text-body-secondary"><?= Html::encode($row->note ?: ($row->party_name ?: '')) ?></small></td>
                            <td class="text-end fw-semibold"><?= number_format((float) $row->amount, 2) ?></td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">จัดการ</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><button type="button" class="dropdown-item" data-cash-edit data-id="<?= $row->id ?>"><i class="bi bi-pencil me-2"></i>แก้ไข</button></li>
                                        <li>
                                            <?= Html::beginForm(['delete', 'id' => $row->id], 'post') ?>
                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('ยืนยันลบรายการนี้?')"><i class="bi bi-trash me-2"></i>ลบ</button>
                                            <?= Html::endForm() ?>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dataProvider->getModels())): ?>
                        <tr><td colspan="7" class="text-center text-body-secondary py-5">ยังไม่มีรายการ<?= Html::encode($typeLabel) ?>ในเงื่อนไขนี้</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="5" class="text-end fw-semibold">รวมตามเงื่อนไข</td>
                        <td class="text-end fw-bold"><?= number_format($sum, 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <small class="text-body-secondary">ทั้งหมด <?= number_format($dataProvider->getTotalCount()) ?> รายการ</small>
            <?= LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'options' => ['class' => 'pagination mb-0'],
                'linkOptions' => ['class' => 'page-link'],
                'pageCssClass' => 'page-item',
                'activePageCssClass' => 'page-item active',
                'disabledPageCssClass' => 'page-item disabled',
            ]) ?>
        </div>
    </div>
</div>

<?= $this->render('form', ['type' => $type, 'tree' => $tree, 'receiptBooks' => $receiptBooks ?? []]) ?>
