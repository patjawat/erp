<?php
use yii\widgets\Pjax;
?>
<?php  Pjax::begin(['id' => 'inventory']); ?>
<?php
use yii\helpers\Html;
use yii\web\View;

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
                        <th class="text-center" style="width:190px">จำนวนเบิก</th>
                        <th scope="col" class="text-center align-center" style="width:32px;">#</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $item):?>
                    <tr class="">
                        <td scope="row"><?=$item->product->Avatar();?></td>
                        <td class="text-center"><?=$item->qty?></td>
                        <td class="text-center"><?=$item->product->unit_name?></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center flex-row">
                                <?=Html::a('<i class="fa-solid fa-chevron-left"></i>',['/inventory/main-stock/update-cart','id' => $item->id,'quantity' => ($item->getQuantity()-1)],['class' => 'btn update-cart'])?>
                                <input type="text" value="<?=$item->getQuantity()?>" class="form-control update-qty" id="<?=$item->id?>" style="width:50px;font-weight: 600;" />
                                <?=Html::a('<i class="fa-solid fa-chevron-right"></i>',['/inventory/main-stock/update-cart','id' => $item->id,'quantity' => ($item->getQuantity()+1)],['class' => 'btn update-cart'])?>
                            </div>
                        </td>
                        <td>
                            <?php // Html::a('<i class="fa-solid fa-trash"></i>',['/inventory/main-stock/delete-item','id' => $item->id],['class' => 'delete-item-cart btn btn-sm btn-danger shadow '])?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>

        <div class="d-grid gap-2">
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


<?php  Pjax::end(); ?>