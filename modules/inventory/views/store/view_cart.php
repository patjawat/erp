<?php
use yii\widgets\Pjax;
?>
<?php Pjax::begin(['id' => 'viewCart']); ?>
<?php
use yii\helpers\Html;
    $cart = \Yii::$app->cart;
    $products = $cart->getItems();

?>

<div class="card">
    <div class="card-body">

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
                        <td scope="row"><?=$item->Avatar()?></td>
                        <td>
                            <div class="d-flex d-flex flex-row">
                                <?=Html::a('<i class="fa-solid fa-chevron-left"></i>',['/inventory/store/update','id' => $item->id,'quantity' => ($item->getQuantity()-1)],['class' => 'btn update-cart'])?>
                                <input type="text" value="<?=$item->getQuantity()?>" class="form-control "
                                    style="width:50px;font-weight: 600;" />
                                <?=Html::a('<i class="fa-solid fa-chevron-right"></i>',['/inventory/store/update','id' => $item->id,'quantity' => ($item->getQuantity()+1)],['class' => 'btn update-cart'])?>
                            </div>
                        </td>
                        <td>
                            <?=Html::a('<i class="fa-solid fa-trash"></i>',['/inventory/store/delete','id' => $item->id],['class' => 'delete-item-cart btn btn-sm btn-danger shadow '])?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php Pjax::end(); ?>