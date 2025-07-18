<?php
use yii\web\View;
use yii\helpers\Html;
?>
<?php  yii\widgets\Pjax::begin(['id' => 'inventory']); ?>
<?php

$cart = \Yii::$app->cartMain;
$products = $cart->getItems();
$warehouseSelect = Yii::$app->session->get('selectMainWarehouse');
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">ชื่อรายการ</th>
                        <th class="text-center">จำนวนสต็อก</th>
                        <th class="text-center">หน่วย</th>
                        <th class="text-end">มูลค่า</th>
                        <th class="text-center" style="width:300px">จำนวนเบิก</th>
                        <th scope="col" class="text-center align-center" style="width:32px;">#</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                    <?php $sumQty = 0; $getQuantity=0; foreach($products as $item):?>
                        <?php
                              $sumQty += (float)$item->SumQty();
                              $getQuantity += (float)$item->getQuantity();
                            ?>
                    <tr class="">
                        <td scope="row"><?=$item->product->Avatar();?></td>
                        <td class="text-center">
                        <span class="badge rounded-pill badge-soft-primary text-primary fs-13"> <?=$item->SumQty()?> </span>
                        </td>
                        <td class="text-center"><?=$item->product->unit_name?></td>
                        <td class="text-end"><span class="fw-semibold"><?=number_format($item->unit_price,2)?></span></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center flex-row">
                                <?=Html::a('<i class="fa-solid fa-chevron-left"></i>',['/inventory/main-stock/update-cart','id' => $item->id,'quantity' => ($item->getQuantity()-1)],['class' => 'btn update-cart'])?>
                                <input type="text" value="<?=$item->getQuantity()?>" class="form-control update-qty" id="<?=$item->id?>" style="width:100px;font-weight: 600;" />
                                <?=Html::a('<i class="fa-solid fa-chevron-right"></i>',['/inventory/main-stock/update-cart','id' => $item->id,'quantity' => ($item->getQuantity()+1)],['class' => 'btn update-cart'])?>
                            </div>
                        </td>
                        <td>
                            <?=Html::a('<i class="fa-solid fa-trash"></i>',['/inventory/main-stock/delete-item','id' => $item->id],['class' => 'delete-item-cart btn btn-sm btn-danger shadow '])?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                    <tr>
                        <td colspan="3" class="text-center">รวมทั้งสิ้น</td>
                        <td class="text-end"><span class="fw-semibold"><?=number_format($cart->getCost(),2);?></span></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

<div class="text-center">
        <?php if($cart->getCount() == 0):?>
            <button type="button" class="btn btn-primary" disabled><i class="fa-solid fa-cart-shopping"></i> เบิก</button>
        <?php else:?>
            <?php
            try {
                echo Html::a('<i class="fa-solid fa-cart-shopping"></i> บันทึกเบิก', ['/inventory/main-stock/create','name' => 'order','type' => 'OUT','title' => 'เบิก'.$warehouseSelect['warehouse_name']], ['class' => 'btn btn-primary rounded-pill shadow position-relative open-modal','data' => ['size' => 'modal-ld']]);
                //code...
            } catch (\Throwable $th) {
                //throw $th;
            }
            ?>
                
        <?php endif?>
    </div>


<?php  yii\widgets\Pjax::end(); ?>