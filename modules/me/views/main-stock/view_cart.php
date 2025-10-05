<?php
use yii\web\View;
use yii\helpers\Html;
use app\modules\inventory\models\Stock;
?>
<?php  yii\widgets\Pjax::begin(['id' => 'inventory']); ?>
<?php

$cart = \Yii::$app->cartMain;
$products = $cart->getItems();
// $warehouseSelect = Yii::$app->session->get('selectMainWarehouse');
$mainWarehouse = \Yii::$app->session->get('main-warehouse');
// print_r($mainWarehouse->id);
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php $this->endBlock(); ?>

            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">ชื่อรายการ</th>
                        <th class="text-center">คงเหลือ</th>
                        <th class="text-center">หน่วย</th>
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
                        <td scope="row">
                            <?=$item->product->Avatar();?>
                         
                        </td>
                        <td class="text-center">
                           <?php
                            $checkStock = Stock::find()->where(['warehouse_id' => $mainWarehouse->id,'asset_item' => $item->asset_item])->sum('qty');
                            echo $checkStock;
                            ?>    
                            </td>
                        <td class="text-center"><?=$item->product->unit_name?></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center flex-row">
                                <?=Html::a('<i class="fa-solid fa-chevron-left"></i>',['/me/main-stock/update-cart','id' => $item->id,'quantity' => ($item->getQuantity()-1)],['class' => 'btn update-cart'])?>
                                <input type="text" value="<?=$item->getQuantity()?>" class="form-control update-qty" id="<?=$item->id?>" style="width:100px;font-weight: 600;" />
                                <?=Html::a('<i class="fa-solid fa-chevron-right"></i>',['/me/main-stock/update-cart','id' => $item->id,'quantity' => ($item->getQuantity()+1)],['class' => 'btn update-cart'])?>
                            </div>
                        </td>
                        <td>
                            <?=Html::a('<i class="fa-solid fa-trash"></i>',['/me/main-stock/delete-item','id' => $item->id],['class' => 'delete-item-cart btn btn-sm btn-danger shadow '])?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                   
                </tbody>
            </table>




<?php  yii\widgets\Pjax::end(); ?>