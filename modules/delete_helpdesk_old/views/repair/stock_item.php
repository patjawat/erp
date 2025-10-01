<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
$cart = \Yii::$app->cart;
?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h4 class="card-title text-center"><i class="fa-regular fa-clipboard"></i> รายการเบิกอะไหล่</h4>
            <?php if(isset($model->stockEvent->order_status)):?>
            <?= $model->stockEvent->order_status !=='success' ? Html::a('<i class="fa-solid fa-cube"></i> สต๊อก/เบิกอะไหล่', ['/helpdesk/stock/index', 'title' => '<i class="fa-solid fa-cube"></i> สต๊อก/เบิกอะไหล่'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-xl']]) : '' ?>
            <?php else:?>
            <?= Html::a('<i class="fa-solid fa-cube"></i> สต๊อก/เบิกอะไหล่', ['/helpdesk/stock/index', 'title' => '<i class="fa-solid fa-cube"></i> สต๊อก/เบิกอะไหล่'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-xl']])?>
            <?php endif;?>
        </div>

        <?php Pjax::begin(['id' => 'helpdesk-cart-container', 'enablePushState' => true, 'timeout' => 88888888]); ?>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">ชื่อรายการ</th>
                    <th class="text-center">จำนวนสต็อก</th>
                    <th class="text-center">หน่วย</th>
                    <th class="text-end">มูลค่า</th>
                    <th class="text-center" style="width:300px">จำนวนเบิก</th>
                    <th class="text-end">รวม</th>
                    <th scope="col" class="text-center align-center" style="width:32px;">#</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php if(isset($model->stockEvent->id) && $model->stockEvent->getItems() !== null):?>
                <?php foreach($model->stockEvent->getItems() as $stockItem):?>
                <tr class="">
                    <td scope="row"><?= $stockItem->product->Avatar(); ?></td>
                    <td><?php // $stockItem->product->unit_name?></td>
                    <td><?=$stockItem->product->unit_name?></td>
                    <td class="text-end"><?php echo number_format($stockItem->unit_price,2); ?></td>
                    <td class="text-center"><?=$stockItem->qty?></td>
                    <td>
                        <?= $model->stockEvent->order_status !=='success' ?  Html::a('<i class="fa-solid fa-trash"></i>', ['/helpdesk/stock/delete-item', 'id' => $stockItem->id], ['class' => 'delete-item-cart btn btn-sm btn-danger shadow ']) : '-' ?>
                    </td>
                </tr>
                <?php endforeach?>
                <?php endif;?>
                <?php  $balanced = 0;
                foreach ($cart->getItems() as $item): ?>
                <?php
            if($item->getQuantity() > $item->SumQty()){
                $balanced +=1;
            }
   
                ?>
                <tr class="">
                    <td scope="row"><?= $item->product->Avatar(); ?></td>
                    <td class="text-center">
                        <span class="badge rounded-pill badge-soft-primary text-primary fs-13"> <?= $item->SumQty() ?>
                        </span>
                    </td>
                    <td class="text-center"><?= $item->product->unit_name ?></td>
                    <td class="text-end"><span class="fw-semibold"><?= number_format($item->unit_price, 2) ?></span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center flex-row">
                            <?= Html::a('<i class="fa-solid fa-chevron-left"></i>', ['/helpdesk/stock/update-cart', 'id' => $item->id, 'quantity' => ($item->getQuantity() - 1)], ['class' => 'btn update-cart']) ?>
                            <input type="text" value="<?= $item->getQuantity() ?>" class="form-control update-qty"
                                id="<?= $item->id ?>" style="width:100px;font-weight: 600;" />
                            <?= Html::a('<i class="fa-solid fa-chevron-right"></i>', ['/helpdesk/stock/update-cart', 'id' => $item->id, 'quantity' => ($item->getQuantity() + 1)], ['class' => 'btn update-cart']) ?>
                        </div>
                    </td>
                    <td class="text-end"><span
                            class="fw-semibold"><?= number_format(($item->unit_price* $item->getQuantity()), 2) ?></span>
                    <td>
                        <?= Html::a('<i class="fa-solid fa-trash"></i>', ['/helpdesk/stock/delete-item', 'id' => $item->id], ['class' => 'delete-item-cart btn btn-sm btn-danger shadow ']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>

            </tbody>
        </table>

        <?php if($cart->getCount() > 0):?>
        <div class="row d-flex justify-content-center">
            <div class="col-4">
                <div class="d-grid gap-2 p-3">

                    <?php if($balanced >0):?>
                    <button class="btn btn-primary" disabled data-totalcount="<?= $cart->getCount() ?>">
                        <span>เบิกวัสดุ (<?= $cart->getCount() ?> รายการ)</span>
                        <br>
                        <span class="fw-semibold"><i
                                class="bi bi-currency-dollar"></i><?= number_format($cart->getCost(), 2); ?></span>
                    </button>
                    <?php else:?>

                    <button class="btn btn-primary" id="checkout" data-totalcount="<?= $cart->getCount() ?>"
                        data-url="<?= Url::to(['/helpdesk/stock/check-out']) ?>" data-id="<?=$model->id?>">
                        <span>เบิกวัสดุ (<?= $cart->getCount() ?> รายการ)</span>
                        <br>
                        <span class="fw-semibold"><i
                                class="bi bi-currency-dollar"></i><?= number_format($cart->getCost(), 2); ?></span>
                    </button>
                    <?php endif;?>

                </div>

                <?php endif;?>
                <?php Pjax::end(); ?>

            </div>
        </div>
    </div>
</div>