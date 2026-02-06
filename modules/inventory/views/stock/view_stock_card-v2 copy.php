<?php

use yii\helpers\Html;
use yii\db\Expression;
use yii\widgets\DetailView;
use app\components\AppHelper;
use app\modules\inventory\models\StockEvent;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Stock $model */

$warehouse = Yii::$app->session->get('warehouse');
$this->title = $warehouse['warehouse_name'];
$this->params['breadcrumbs'][] = ['label' => 'Stocks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php // Pjax::begin(['id' => 'viewStock'])?>
<?php
$asset_item = $model->asset_item;
$sql = "SELECT 
i.id,
e.code,
e.data_json->>'$.note' as note,
e.po_number,
e.movement_date,i.data_json->>'$.exp_date' as exp_date,i.qty,i.unit_price,e.thai_year,i.lot_number,e.transaction_type,sum(i.qty*i.unit_price) as total FROM stock_events i 
LEFT JOIN stock_events e ON e.id = i.category_id
WHERE i.asset_item = :asset_item
AND e.warehouse_id = :warehouse_id
AND e.order_status = 'success'
GROUP BY i.id
ORDER BY e.movement_date ASC";
$stockEvents = Yii::$app->db->createCommand($sql)
    ->bindValue(':asset_item', $asset_item)
    ->bindValue(':warehouse_id', $warehouse->id)
    ->queryAll();


$balance = 0;
$balanceQty = 0;
?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="d-flex gap-2">
                    <?= Html::img($model->product->ShowImg(), ['class' => 'object-fit-cover rounded-3', 'width' => '50']) ?>
                    <div>
                        <h5 class="fw-bold mb-1"> <?= $model->product->title ?></h5>
                        <p class="text-muted small mb-0">
                            หมวดหมู่:<span class="mx-2 badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1"><?= $model->product->ViewTypeName()['title'] ?></span> | 
                            รหัส: <?= $model->product->code ?> | มูลค่าคงเหลือ: <?= $model->SumPriceByItem() ?> </p>
                    </div>
                </div>

            </div>
            <div class="col-md-4 text-md-end">
                <small class="text-muted">ยอดคงเหลือ</small>
                <h3 class="fw-bold text-primary"><?= $model->SumQty() ?> <small class="fs-6 text-muted"><?= $model->product->data_json['unit'] ?? '-' ?></small></h3>
            </div>
        </div>

        <h6 class="fw-bold mb-3"><i class="bi bi-ui-checks"></i> ประวัติ <span class="badge rounded-pill text-bg-primary">
                <?= count($dataProvider->getModels()) ?> </span> รายการ</h6>
        <div class="table-responsive">
            <table class="table table-bordered align-middle stock-card-table table-striped">
                <thead>
                    <tr class="text-center">
                        <th style="width: 120px;">วันที่</th>
                        <th>เลขที่เอกสาร / รายการ</th>
                        <th style="width: 100px;">หมายเลขล็อต</th>
                        <th style="width: 100px;">วันหมดอายุ</th>
                        <th style="width: 100px;">รับเข้า</th>
                        <th style="width: 100px;">จ่ายออก</th>
                        <th style="width: 100px;">ราคาต่อหน่วย</th>
                        <th style="width: 120px;">คงเหลือ</th>
                        <th style="width: 80px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                    <?php foreach ($stockEvents as $item2): ?>
                        <?php
                        if ($item2['transaction_type'] == 'IN') {
                            $balance += $item2['total'];
                            $balanceQty += $item2['qty'];
                        } elseif ($item2['transaction_type'] == 'OUT') {
                            $balance -= $item2['total'];
                            $balanceQty -= $item2['qty'];
                        }
                        ?>
                        <tr>
                            <td class="text-center"><?= Yii::$app->thaiDate->toThaiDate($item2['movement_date'], false, 'short'); ?></td>
                            
                            <td>
                                <?php if ($item2['transaction_type'] == 'IN'): ?>
                                    รับเข้า #<span class="fw-bold"><?= $item2['code'] ?></span>

                                <?php else: ?>
                                    ใบเบิก #<span class="fw-bold"><?= $item2['code'] ?></span> (<?= $item2['note'] ?>)
                                <?php endif; ?>

                            </td>
                            
                            <td class="text-center <?= $item2['transaction_type'] == 'IN' ? "text-success" : "text-danger" ?> fw-bold"><?= $item2['lot_number'] ?></td>
                            <td class="text-center fw-bold"> <?= (isset($this->data_json['exp_date']) && $item2['exp_date'] !== '') ? AppHelper::convertToThai($item2['exp_date']) : '-' ?></td>
                            <td class="text-center text-success fw-bold"><?= $item2['transaction_type'] == 'IN' ? '+' . (float)$item2['qty'] : '' ?></td>
                            <td class="text-center text-danger fw-bold"><?= $item2['transaction_type'] == 'OUT' ? -ABS((float)$item2['qty']) : '' ?></td>
                            <td class="fw-semibold text-end"><?= $item2['unit_price'] !== null ? number_format($item2['unit_price'], 2) : '-' ?></td>
                            <td class="text-end px-3">
                                <?= number_format($balance, 2) ?>
                            </td>
                            <td class="text-center">
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>',['/inventory/stock/update-stock-card','id' => $item2['id'],'title' => 'แก้ไขราการที่# '.$item2['code']],
                                        ['class' => 'open-modal',
                                        'data' => ['size'=> 'modal-md']]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php // Pjax::end()?>