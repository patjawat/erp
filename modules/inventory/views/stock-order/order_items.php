<?php
use yii\helpers\Html;

?>
<table class="table table-striped mt-3">
    <thead class="table-primary">
        <tr>
            <th>รายการ</th>
            <th class="text-center">หน่วย</th>
            <th class="text-start">ล็อตผลิต</th>
            <th class="text-end">มูลค่า</th>
            <th class="text-center">คงเหลือ</th>
            <th class="text-center">ขอเบิก</th>
            <th class="text-center">จ่าย</th>
            <th class="text-center" scope="col" style="width:120px;">ดำเนินการ</th>
        </tr>
    </thead>

    <tbody class="table-group-divider align-middle">
        <?php foreach ($model->getItems() as $item):?>
        <tr
            class="<?=$item->qty > $item->SumlotQty() ? 'bg-warning' : null?> <?php echo $item->order_status == 'await' ? 'bg-warning-subtle' : ''; ?>">
            <td class="align-middle">
                <?php
                                        try {
                                            echo $item->product->Avatar();
                                        } catch (Throwable $th) {
                                        }
                            ?>
            </td>
            <td class="align-middle text-center">
                <?php echo isset($item->product->data_json['unit']) ? $item->product->data_json['unit'] : '-'; ?>
            </td>
            <td class="align-middle text-start"><?php echo $item->lot_number; ?>(<code><?php echo $item->SumLotQty(); ?></code>)</td>
            <td class="align-middle text-end"><?php echo number_format($item->unit_price,2); ?></td>
            <td class="text-center"><?php echo $item->SumStockQty(); ?></td>
            <td class="align-middle text-center"><?php echo isset($item->data_json['req_qty']) ? $item->data_json['req_qty'] : '-'; ?></td>
            <td class="text-center">

                <?php if ($model->OrderApprove() && Yii::$app->user->can('warehouse') &  $item->SumLotQty() > 0 && isset($office) && $model->order_status != 'success'): ?>
                <div class="d-flex">
                    <span type="button" class="minus btn btn-sm btn-light" id="min" data-lot_qty="<?php echo $item->SumLotQty(); ?>" data-id="<?php echo $item->id; ?>"><i class="fa-regular fa-square-minus fs-3"></i></span>
                    <input name="qty" id="<?=$item->id?>" type="text" min="0" max="2" value="<?php echo $item->qty; ?>" class="qty" data-maxlot="<?=$item->SumLotQty()?>" style="width: 55px;font-weight: 600;font-size: large;">
                    <span type="button" class="plus btn btn-sm btn-light" id="plus" data-lot_qty="<?php echo $item->SumLotQty(); ?>" data-id="<?php echo $item->id; ?>"> <i class="fa-regular fa-square-plus fs-3"></i>
                    </span>
                </div>
                <?php else:?>
                <?php  echo ($model->OrderApprove() && $model->order_status == 'success') ? $item->qty : '-' ?>
                <?php endif;?>
            </td>
            <td class="text-center">

                <?php if ($model->OrderApprove() && isset($office) &&  $item->SumStockQty() > 1): ?>
                <?php echo $model->order_status == 'success' ? '' : Html::a('<i class="fa-solid fa-copy"></i>', ['/inventory/stock-order/copy-item', 'id' => $model->id, 'lot_number' => $item->lot_number], ['class' => 'btn btn-sm btn-primary copy-item']); ?>
                <?php endif;?>

                <?php //if (($item->data_json['req_qty'] > $item->SumLotQty()) && $item->CountItem($model->id) < 2) { ?>

                <?php if(!$model->OrderApprove()):?>
                <?php echo $model->order_status == 'success' ? '' : Html::a('<i class="fa-solid fa-trash"></i>', ['/inventory/stock-order/delete', 'id' => $item->id], ['class' => 'btn btn-sm btn-danger delete-item']); ?>
                <?php endif;?>


            </td>
        </tr>

        <?php endforeach; ?>

    </tbody>
</table>