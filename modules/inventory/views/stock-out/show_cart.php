<?php
use yii\widgets\Pjax;
?>
<?php // Pjax::begin(['id' => 'inventory']); ?>
<?php
use yii\helpers\Html;
use yii\web\View;

    $cart = \Yii::$app->cart2;
    $products = $cart->getItems();
    $warehouseSelect = Yii::$app->session->get('select-warehouse');
    // echo "<pre>";
    // print_r($cart);
    // echo "</pre>";
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ขอเบิกวัสดุ <span class="badge rounded-pill text-bg-primary"><?=$cart->getCount()?> </span> รายการ</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-primary">
                <thead>
                    <tr>
                        <th scope="col">ชื่อรายการ</th>
                        <th scope="col" style="width:60px">จำนวน</th>
                        <th scope="col" class="text-center align-center" style="width:32px;">#</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $item):?>
                    <tr class="">
                        <td scope="row">
                        <?php
                            // echo "<pre>";
                            // print_r($item);
                            // echo "</pre>";
                        ?>
                            <?php
                            echo $item->product->Avatar();
                            ?></td>
                        <td>
                            <div class="d-flex d-flex flex-row">
                                <?=Html::a('<i class="fa-solid fa-chevron-left"></i>',['/inventory/stock-out/update-qty','id' => $item->id,'qty' => ($item->getQuantity()-1)],['class' => 'btn update-cart'])?>
                                <input type="text" value="<?=$item->getQuantity()?>" class="form-control update-qty" id="<?=$item->id?>"
                                    style="width:50px;font-weight: 600;" />
                                <?=Html::a('<i class="fa-solid fa-chevron-right"></i>',['/inventory/stock-out/update-qty','id' => $item->id,'qty' => ($item->getQuantity()+1)],['class' => 'btn update-cart'])?>
                            </div>
                        </td>
                        <td>
                            <?=Html::a('<i class="fa-solid fa-trash"></i>',['/inventory/store/delete-item','id' => $item->id],['class' => 'delete-item-cart btn btn-sm btn-danger shadow '])?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>

        <div class="d-grid gap-2">
<?php if($cart->getCount() == 0):?>
        <button type="button" class="btn btn-primary" disabled><i class="fa-solid fa-cart-shopping"></i> เบิก</button>
<?php else:?>
        <?= Html::a('<i class="fa-solid fa-cart-shopping"></i> บันทึกเบิก', ['/inventory/stock-out/checkout','name' => 'order','type' => 'OUT','title' => 'เบิก'], ['class' => 'btn btn-primary rounded-pill shadow position-relative checkout']) ?>
        <?php endif?>
    </div>

    </div>
</div>


<?php // Pjax::end(); ?>