<?php

use app\components\AppHelper;
use app\modules\finance\models\FinanceCashVoucher;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var string $active */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $fiscalYear */
/** @var string $date */
/** @var string $q */
/** @var float $sum */
/** @var array $tree */
/** @var array $accounts */
/** @var array $vendors */

$this->title = 'ทะเบียนรายจ่าย (ใบสำคัญจ่าย)';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รับ–จ่ายเงิน', 'url' => ['/finance/cash']];
$this->params['breadcrumbs'][] = 'รายจ่าย';

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-receipt text-warning" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ใบสำคัญจ่ายเงินบำรุง (1 ใบมีหลายบรรทัด + VAT/WHT + จ่ายจากบัญชี)<?php $this->endBlock();
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

<?php if (empty($accounts)): ?>
    <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><i class="bi bi-exclamation-triangle me-1"></i> ยังไม่มีบัญชีเงินสำหรับ “จ่ายจากบัญชี” — ป้อนชุดเริ่มต้นก่อน</span>
        <?= Html::beginForm(['seed-accounts'], 'post') ?>
        <button type="submit" class="btn btn-sm btn-warning">ป้อนบัญชีเงินเริ่มต้น</button>
        <?= Html::endForm() ?>
    </div>
<?php endif; ?>

<div class="card border">
    <div class="card-header bg-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0">รายการบันทึก “รายจ่าย”</h5>
        <button type="button" class="btn btn-warning" data-cash-add data-year="<?= (int) $fiscalYear ?>" <?= empty($accounts) ? 'disabled' : '' ?>>
            <i class="bi bi-plus-circle me-1" aria-hidden="true"></i> บันทึกรายจ่าย
        </button>
    </div>
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end mb-3">
            <div class="col-6 col-md-2">
                <label class="form-label" for="f-year">ปีงบประมาณ</label>
                <input type="number" class="form-control" id="f-year" name="fiscal_year" value="<?= Html::encode((string) $fiscalYear) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" for="f-date">วันที่จ่าย</label>
                <?= DatepickerThai::widget(['name' => 'date', 'value' => $date, 'options' => ['id' => 'f-date', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.']]) ?>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="f-q">ค้นหา (เลขใบสำคัญ / เลขเช็ค / ผู้รับ)</label>
                <input type="text" class="form-control" id="f-q" name="q" value="<?= Html::encode($q) ?>">
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>ค้นหา</button>
                <a href="<?= Url::to(['expense']) ?>" class="btn btn-outline-secondary">เคลียร์</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:110px">วันที่จ่าย</th>
                        <th style="width:120px">เลขใบสำคัญ</th>
                        <th style="width:110px">วิธีจ่าย</th>
                        <th>บัญชีที่จ่าย</th>
                        <th>จ่ายให้</th>
                        <th class="text-end" style="width:130px">จ่ายจริง</th>
                        <th style="width:90px" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php /** @var FinanceCashVoucher $row */ ?>
                    <?php foreach ($dataProvider->getModels() as $row): ?>
                        <tr>
                            <td><?= Html::encode(AppHelper::convertToThai($row->pay_date)) ?></td>
                            <td><?= Html::encode($row->doc_no ?: '-') ?></td>
                            <td><span class="badge bg-light text-dark border"><?= Html::encode($row->payMethodLabel()) ?></span></td>
                            <td><small><?= Html::encode($row->account ? $row->account->label() : '-') ?></small></td>
                            <td><small class="text-body-secondary"><?= Html::encode($row->payee_name ?: '') ?></small></td>
                            <td class="text-end fw-semibold"><?= number_format((float) $row->net_amount, 2) ?></td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">จัดการ</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><button type="button" class="dropdown-item" data-cash-edit data-id="<?= $row->id ?>"><i class="bi bi-pencil me-2"></i>แก้ไข</button></li>
                                        <li>
                                            <?= Html::beginForm(['voucher-delete', 'id' => $row->id], 'post') ?>
                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('ยืนยันลบใบสำคัญนี้?')"><i class="bi bi-trash me-2"></i>ลบ</button>
                                            <?= Html::endForm() ?>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dataProvider->getModels())): ?>
                        <tr><td colspan="7" class="text-center text-body-secondary py-5">ยังไม่มีใบสำคัญจ่ายในเงื่อนไขนี้</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="5" class="text-end fw-semibold">รวมจ่ายจริงตามเงื่อนไข</td>
                        <td class="text-end fw-bold"><?= number_format($sum, 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <small class="text-body-secondary">ทั้งหมด <?= number_format($dataProvider->getTotalCount()) ?> ใบสำคัญ</small>
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

<?= $this->render('voucher_form', ['tree' => $tree, 'accounts' => $accounts, 'vendors' => $vendors]) ?>
