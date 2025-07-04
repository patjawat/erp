

<?php
use yii\helpers\Html;
use yii\db\Expression;
use yii\widgets\DetailView;
use app\modules\inventory\models\StockEvent;
$warehouse = Yii::$app->session->get('warehouse');
$stockEvents = StockEvent::find()
    ->select([
        'stock_events.*',
        new Expression('SUM(qty * unit_price) AS total')
    ])
    ->where([
        'asset_item' => $asset_item,
        'warehouse_id' => $warehouse->id,
        'order_status' => 'success'
    ])
    ->groupBy('id')
    ->orderBy(['movement_date' => SORT_ASC]);

// Debug raw SQL
// echo $stockEvents->createCommand()->getRawSql();

$stockEvents = $stockEvents->all();

$balance = 0;
$balanceQty = 0;
?>

<table class="table">
            <thead>
                <tr>
                    <th class="fw-semibold" scope="col" style="width:130px">ความเคลื่อนไหว</th>
                    <th class="fw-semibold" scope="col" style="width:120px">ปีงบประมาณ</th>
                    <th class="fw-semibold" scope="col" style="width:180px">วันที่รับ/จ่าย</th>
                    <th class="fw-semibold" scope="col" style="width:130px">หมายเลขล็อต</th>
                    <!-- <th class="fw-semibold" scope="col">เลขที่เอกสาร</th> -->
                    <th class="text-end fw-semibold" scope="col">ราคาต่อหน่วย</th>
                    <th class="fw-semibold" scope="col" class="text-center">จำนวนเข้า</th>
                    <th class="fw-semibold" scope="col" class="text-center">จำนวนออก</th>
                    <th class="fw-semibold" scope="col" class="text-center">คงเหลือ</th>
                    <th class="fw-semibold text-end" scope="col">มูลค่าวัสดุคงเหลือ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach($stockEvents as $item2):?>
                <?php
                        if ($item2->transaction_type == 'IN') {
                            $balance += $item2['total'];
                            $balanceQty += $item2->qty;
                        } elseif ($item2->transaction_type == 'OUT') {
                            $balance -= $item2['total'];
                            $balanceQty -= $item2->qty;
                        }
                    ?>
                <tr>
                    <td>
                        <?php if ($item2->transaction_type == 'IN'): ?>
                        <div class="badge rounded-pill badge-soft-primary text-primary fs-13"><i
                                class="fa-solid fa-circle-plus"></i> รับ</div>
                        <?php else: ?>
                        <div class="badge rounded-pill badge-soft-danger text-danger fs-13"><i
                                class="fa-solid fa-circle-minus"></i> จ่าย</div>
                        <?php endif ?>
                    </td>
                    <td><?=$item2->thai_year?></td>
                    <td><?= $item2?->ViewReceiveDate();?></td>
                    <td><?=$item2->lot_number?></td>


                    </td>
                    <td class="fw-semibold text-end"><?= $item2->unit_price !== null ? number_format($item2->unit_price, 2) : '-' ?></td>
                    <td class="fw-semibold text-center"><?=$item2->transaction_type == 'IN' ? $item2->qty : ''?></td>
                    <td class="fw-semibold text-center"><?=$item2->transaction_type == 'OUT' ? -ABS($item2->qty) : ''?>
                    </td>
                    <td class="fw-semibold text-center"><?= $balanceQty?></td>
                    <td class="fw-semibold text-end">
                        <?php
            // echo $item2['total'];
            echo number_format($balance, 2)?></td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
