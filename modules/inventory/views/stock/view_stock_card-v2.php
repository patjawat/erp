<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2;
use app\components\AppHelper;
use app\modules\inventory\models\Warehouse;

/** @var yii\web\View $this */
/** @var \app\modules\inventory\models\Stock $model */
/** @var array $card */
/** @var string $dateFrom */
/** @var string $dateTo */
/** @var int    $whId */

$selectedWh = $whId ? Warehouse::findOne($whId) : null;
$this->title = $selectedWh ? $selectedWh->warehouse_name : 'Stock Card';
$this->params['breadcrumbs'][] = ['label' => 'Stocks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$warehouseOptions = \yii\helpers\ArrayHelper::map(
    Warehouse::find()->orderBy(['warehouse_name' => SORT_ASC])->all(),
    'id', 'warehouse_name'
);

$fmtQty = static function ($v) { return number_format((float)($v ?? 0)); };
$fmtVal = static function ($v) { return number_format((float)($v ?? 0), 2); };

$balanceQty = (float) $card['opening']['qty'];
$balanceVal = (float) $card['opening']['value'];
?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <!-- HEADER -->
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="d-flex gap-2">
                    <?= Html::img($model->product->ShowImg(), ['class' => 'object-fit-cover rounded-3', 'width' => '50']) ?>
                    <div>
                        <h5 class="fw-bold mb-1"><?= Html::encode($card['item_info']['title']) ?></h5>
                        <p class="text-muted small mb-0">
                            หมวดหมู่:
                            <span class="mx-2 badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                                <?= Html::encode($model->product->ViewTypeName()['title']) ?>
                            </span> |
                            รหัส: <?= Html::encode($card['item_info']['code']) ?> |
                            หน่วย: <?= Html::encode($card['item_info']['unit'] ?: '-') ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <small class="text-muted">คงเหลือสิ้นช่วง</small>
                <h3 class="fw-bold text-primary"><?= $fmtQty($card['closing']['qty']) ?>
                    <small class="fs-6 text-muted"><?= Html::encode($card['item_info']['unit'] ?? '') ?></small>
                </h3>
                <small class="text-muted">มูลค่า: <?= $fmtVal($card['closing']['value']) ?> บาท</small>
            </div>
        </div>

        <!-- FILTER FORM -->
        <form method="get" class="row g-2 align-items-end bg-light p-3 rounded mb-3">
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1">ตั้งแต่วันที่</label>
                <input type="date" class="form-control form-control-sm" name="date_from" value="<?= Html::encode($dateFrom) ?>">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1">ถึงวันที่</label>
                <input type="date" class="form-control form-control-sm" name="date_to" value="<?= Html::encode($dateTo) ?>">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1">คลัง</label>
                <?= Select2::widget([
                    'name' => 'warehouse_id',
                    'value' => $whId,
                    'data' => $warehouseOptions,
                    'options' => ['placeholder' => 'เลือกคลัง'],
                    'size' => Select2::SIZE_SMALL,
                ]) ?>
            </div>
            <div class="col-12 col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                        <i class="fa-solid fa-magnifying-glass"></i> ค้นหา
                    </button>
                    <?= Html::a('<i class="fa-solid fa-file-excel"></i>',
                        array_merge(['/inventory/stock/stock-card-excel', 'id' => $model->id],
                            ['date_from' => $dateFrom, 'date_to' => $dateTo, 'warehouse_id' => $whId]),
                        ['class' => 'btn btn-sm btn-success', 'target' => '_blank', 'title' => 'Excel']) ?>
                    <?= Html::a('<i class="fa-solid fa-print"></i>',
                        array_merge(['/inventory/stock/stock-card-print', 'id' => $model->id],
                            ['date_from' => $dateFrom, 'date_to' => $dateTo, 'warehouse_id' => $whId]),
                        ['class' => 'btn btn-sm btn-dark', 'target' => '_blank', 'title' => 'พิมพ์ A4']) ?>
                </div>
            </div>
        </form>

        <h6 class="fw-bold mb-2"><i class="bi bi-ui-checks"></i>
            ประวัติ <span class="badge rounded-pill text-bg-primary"><?= count($card['movements']) ?></span> รายการ
            <?php if (!empty($card['adjustments'])): ?>
                + <span class="badge rounded-pill text-bg-warning"><?= count($card['adjustments']) ?></span> การปรับยอด
            <?php endif; ?>
        </h6>

        <div class="table-responsive">
            <table class="table table-bordered align-middle stock-card-table table-striped">
                <thead class="table-light">
                    <tr class="text-center">
                        <th style="width: 105px;">วันที่</th>
                        <th>เลขที่เอกสาร / รายการ</th>
                        <th style="width: 90px;">ล็อต</th>
                        <th style="width: 90px;">วันหมดอายุ</th>
                        <th style="width: 80px;">รับเข้า</th>
                        <th style="width: 90px;">จ่าย รพ.</th>
                        <th style="width: 90px;">จ่าย รพ.สต.</th>
                        <th style="width: 90px;">ราคา/หน่วย</th>
                        <th style="width: 100px;" class="table-warning">คงเหลือ qty</th>
                        <th style="width: 110px;" class="table-warning">คงเหลือ มูลค่า</th>
                        <th style="width: 60px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    <!-- OPENING ROW -->
                    <tr class="table-info">
                        <td class="text-center small"><?= AppHelper::convertToThai($dateFrom) ?></td>
                        <td colspan="7">
                            <strong>ยอดยกมา</strong>
                            <small class="text-muted ms-2">
                                (<?= $card['opening']['source'] === 'monthly_close'
                                    ? 'จากปิดเดือนก่อน (stock_monthly_report)'
                                    : ($card['opening']['source'] === 'bootstrap'
                                        ? 'คำนวณจากประวัติ stock_events'
                                        : 'ไม่มีข้อมูล') ?>)
                            </small>
                        </td>
                        <td class="text-end fw-bold table-warning"><?= $fmtQty($card['opening']['qty']) ?></td>
                        <td class="text-end fw-bold table-warning"><?= $fmtVal($card['opening']['value']) ?></td>
                        <td></td>
                    </tr>

                    <!-- MOVEMENTS -->
                    <?php foreach ($card['movements'] as $m): ?>
                        <?php
                        $qty = (float) $m['qty'];
                        $val = (float) $m['value'];
                        if ($m['kind'] === 'IN') {
                            $balanceQty += $qty; $balanceVal += $val;
                        } elseif (in_array($m['kind'], ['OUT', 'OUT_HOSP', 'OUT_BRANCH'])) {
                            $balanceQty -= $qty; $balanceVal -= $val;
                        }
                        ?>
                        <tr>
                            <td class="text-center small"><?= Yii::$app->thaiDate->toThaiDate($m['movement_date'], false, 'short') ?></td>
                            <td>
                                <?php if ($m['kind'] === 'IN'): ?>
                                    <span class="text-success">รับเข้า</span> #<span class="fw-bold"><?= Html::encode($m['code']) ?></span>
                                    <?php if ($m['po_number']): ?>
                                        <small class="text-muted">PO: <?= Html::encode($m['po_number']) ?></small>
                                    <?php endif; ?>
                                <?php elseif ($m['kind'] === 'OUT_HOSP'): ?>
                                    <span class="text-danger">จ่ายให้ รพ.</span> #<span class="fw-bold"><?= Html::encode($m['code']) ?></span>
                                    <?php if ($m['from_warehouse']): ?><small class="text-muted">(<?= Html::encode($m['from_warehouse']) ?>)</small><?php endif; ?>
                                <?php elseif ($m['kind'] === 'OUT_BRANCH'): ?>
                                    <span class="text-warning">จ่ายให้ รพ.สต.</span> #<span class="fw-bold"><?= Html::encode($m['code']) ?></span>
                                    <?php if ($m['from_warehouse']): ?><small class="text-muted">(<?= Html::encode($m['from_warehouse']) ?>)</small><?php endif; ?>
                                <?php else: ?>
                                    <?= Html::encode($m['transaction_type']) ?> #<?= Html::encode($m['code']) ?>
                                <?php endif; ?>
                                <?php if (!empty($m['note'])): ?>
                                    <span class="text-muted small">— <?= Html::encode($m['note']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center small"><?= Html::encode($m['lot_number'] ?: '-') ?></td>
                            <td class="text-center small">
                                <?= !empty($m['exp_date']) ? AppHelper::convertToThai($m['exp_date']) : '-' ?>
                            </td>
                            <td class="text-center text-success fw-bold">
                                <?= $m['kind'] === 'IN' ? '+' . $fmtQty($qty) : '' ?>
                            </td>
                            <td class="text-center text-danger fw-bold">
                                <?= $m['kind'] === 'OUT_HOSP' ? '-' . $fmtQty($qty) : '' ?>
                            </td>
                            <td class="text-center text-warning fw-bold">
                                <?= $m['kind'] === 'OUT_BRANCH' ? '-' . $fmtQty($qty) : '' ?>
                            </td>
                            <td class="text-end small"><?= $fmtVal($m['unit_price']) ?></td>
                            <td class="text-end fw-bold table-warning"><?= $fmtQty($balanceQty) ?></td>
                            <td class="text-end fw-bold table-warning"><?= $fmtVal($balanceVal) ?></td>
                            <td class="text-center">
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>',
                                    ['/inventory/stock/update-stock-card', 'id' => $m['id'], 'title' => 'แก้ไขรายการที่# ' . $m['code']],
                                    ['class' => 'open-modal text-primary', 'data' => ['size' => 'modal-md']]
                                ) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- ADJUSTMENT ROWS -->
                    <?php foreach ($card['adjustments'] as $a): ?>
                        <?php
                        $balanceQty += $a['delta_qty'];
                        $balanceVal += $a['delta_value'];
                        ?>
                        <tr class="table-danger">
                            <td class="text-center small"><?= AppHelper::convertToThai($a['shown_date']) ?></td>
                            <td colspan="3">
                                <strong><i class="fa-solid fa-pen"></i> ปรับยอด</strong>
                                <small class="text-muted ms-2">(<?= Html::encode($a['note'] ?: '-') ?>)</small>
                            </td>
                            <td class="text-center text-success fw-bold">
                                <?= $a['delta_qty'] > 0 ? '+' . $fmtQty($a['delta_qty']) : '' ?>
                            </td>
                            <td colspan="2" class="text-center text-danger fw-bold">
                                <?= $a['delta_qty'] < 0 ? $fmtQty($a['delta_qty']) : '' ?>
                            </td>
                            <td></td>
                            <td class="text-end fw-bold table-warning"><?= $fmtQty($balanceQty) ?></td>
                            <td class="text-end fw-bold table-warning"><?= $fmtVal($balanceVal) ?></td>
                            <td></td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- CLOSING ROW -->
                    <tr class="table-success">
                        <td class="text-center small"><?= AppHelper::convertToThai($dateTo) ?></td>
                        <td colspan="7"><strong>ยอดยกไป (คงเหลือสิ้นช่วง)</strong></td>
                        <td class="text-end fw-bold table-warning"><?= $fmtQty($card['closing']['qty']) ?></td>
                        <td class="text-end fw-bold table-warning"><?= $fmtVal($card['closing']['value']) ?></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
