<?php
use yii\web\View;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\db\Expression;
use yii\widgets\DetailView;
use app\components\UserHelper;
use app\modules\inventory\models\Warehouse;
//ตรวจสอบว่าเป็นผู้ดูแลคลัง
$userid = \Yii::$app->user->id;
$office = Warehouse::find()->andWhere(['id' => $model->warehouse_id])->andWhere(new Expression("JSON_CONTAINS(data_json->'$.officer','\"$userid\"')"))->one();
$emp = UserHelper::GetEmployee();
?>
<table class="table table-striped mt-3">
    <thead class="table-primary">
        <tr>
            <th>รายการ</th>
            <th class="text-end">มูลค่า</th>
            <th class="text-start">ล็อตผลิต</th>
            <th class="text-end">คงเหลือ</th>
            <th class="text-center">หน่วย</th>
            <th class="text-center">ขอเบิก</th>
            <!-- <th class="text-center">คงเหลือ</th> -->
            <th class="text-center">อนุมัติจ่าย</th>
            <th class="text-center" scope="col" style="width:120px;">ดำเนินการ</th>
        </tr>
    </thead>

    <tbody class="table-group-divider align-middle">
        <?php $balanced=0;foreach ($model->getItems() as $item):?>
        <?php
                        if($item->qty > $item->SumlotQty()){
                            $balanced +=1;
                        }
                                ?>
        <tr class="<?=$item->qty > $item->SumlotQty() ? 'bg-warning' : null?>">
            <td class="align-middle"><?php echo $item->product?->Avatar();?>
  
        </td>

            <td class="align-middle text-end"><?php echo number_format($item->unit_price,2); ?></td>
            <td class="align-middle text-start"><?php echo $item->lot_number; ?></td>
            <td class="text-center fw-semibold"><?=$item->SumlotQty();?></td>
            <td class="align-middle text-center">
                <?php echo isset($item->product->data_json['unit']) ? $item->product->data_json['unit'] : '-'; ?>
            </td>
            <td class="align-middle text-center fw-semibold">
                <?php echo isset($item->data_json['req_qty']) ? $item->data_json['req_qty'] : '-'; ?></td>
            <td class="text-center">
                <?php // if ($model->OrderApprove() && Yii::$app->user->can('warehouse') && $item->SumLotQty() > 0 && $office ?? false && !in_array($model->order_status, ['cancel'])): ?>
                <?php if ($model->OrderApprove() && Yii::$app->user->can('warehouse') &&($item->SumLotQty() > 0) && ($office ?? false) && !in_array($model->order_status, ['success','cancel'])): ?>
                <div class="d-flex justify-content-center align-items-center gap-1">
                    <span type="button" class="minus btn btn-sm btn-light" id="min"
                        data-lot_qty="<?php echo $item->SumLotQty(); ?>" data-id="<?php echo $item->id;?>"
                        data-total="<?php echo $item->SumStockQty();?>">
                        <!-- <i class="fa-regular fa-square-minus fs-3"></i> -->
                        <i class="fa-solid fa-minus"></i>
                    </span>
                    <input name="qty" id="<?=$item->id?>" type="text" min="0" max="2" value="<?php echo $item->qty; ?>"
                        class="form-control qty" data-maxlot="<?=$item->SumLotQty()?>"
                        style="width: 55px;font-weight: 500;">

                    <span type="button" class="plus btn btn-sm btn-light" id="plus"
                        data-lot_qty="<?php echo $item->SumLotQty();?>" data-id="<?php echo $item->id;?>"
                        data-total="<?php echo $item->SumStockQty();?>">
                        <i class="fa-solid fa-plus"></i>
                        <!-- <i class="fa-regular fa-square-plus fs-3"></i> -->
                    </span>
                </div>
                <?php else:?>
                <?php  // echo  $model->order_status == 'success' ? $item->qty : '-' ?>

                <div class="d-flex justify-content-center align-items-center gap-1">
                    <span type="button" class="minus btn btn-sm btn-light" id="min"
                        data-lot_qty="<?php echo $item->SumLotQty(); ?>" data-id="<?php echo $item->id;?>"
                        data-total="<?php echo $item->SumStockQty();?>">
                        <!-- <i class="fa-regular fa-square-minus fs-3"></i> -->
                        <i class="fa-solid fa-minus"></i>
                    </span>
                    <input name="qty" id="<?=$item->id?>" type="text" min="0" max="2" value="<?php echo $item->qty; ?>"
                        class="form-control qty" data-maxlot="<?=$item->SumLotQty()?>"
                        style="width: 55px;font-weight: 500;">

                    <span type="button" class="plus btn btn-sm btn-light" id="plus"
                        data-lot_qty="<?php echo $item->SumLotQty();?>" data-id="<?php echo $item->id;?>"
                        data-total="<?php echo $item->SumStockQty();?>">
                        <i class="fa-solid fa-plus"></i>
                        <!-- <i class="fa-regular fa-square-plus fs-3"></i> -->
                    </span>
                </div>
                
                <?php endif;?>
            </td>

            <td class="text-center">
                <div class="btn-group">
                    <?= Html::a('<i class="bi bi-ui-checks"></i>', ['//inventory/stock-order/show-stock','asset_item' => $item->asset_item,'lot_number' => $item->lot_number], ['class' => 'btn btn-light w-100 open-modal','data' => ['size' => 'modal-md']]) ?>
                    <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                        data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                        <i class="bi bi-caret-down-fill"></i>
                    </button>

                    <ul class="dropdown-menu">
                        <?php if ($model->OrderApprove() && isset($office) &&  $item->SumStockQty() > 1): ?>
                        <li>
                            <?php echo $item->qty > $item->SumlotQty() ? in_array($model->order_status, ['success','cancel']) ? '' : Html::a('<i class="fa-solid fa-copy me-1"></i> เพิ่มล๊อตจ่าย', ['/inventory/stock-order/copy-item', 'id' => $item->id], ['class' => 'dropdown-item copy-item']) : ''; ?>
                        </li>
                        <?php endif;?>
                        <?php if(!in_array($model->order_status, ['success','cancel']) && $userid == $item->created_by):?>
                        <li>
                            <?php echo $model->order_status == 'success' ? '' : Html::a('<i class="fa-solid fa-trash me-1"></i> ลบรายการ', ['/inventory/stock-order/delete', 'id' => $item->id], ['class' => 'dropdown-item delete-item']); ?>
                        </li>
                        <?php endif?>
                        </ui>
                </div>



                <?php //if (($item->data_json['req_qty'] > $item->SumLotQty()) && $item->CountItem($model->id) < 2) { ?>

                <?php if(!$model->OrderApprove()):?>
                <?php //echo $model->order_status == 'success' ? '' : Html::a('<i class="fa-solid fa-trash"></i>', ['/inventory/stock-order/delete', 'id' => $item->id], ['class' => 'btn btn-sm btn-danger delete-item']); ?>
                <?php endif;?>


            </td>
        </tr>

        <?php endforeach; ?>

    </tbody>
</table>