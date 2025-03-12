<?php
use yii\helpers\Html;
?>
<div class="d-flex flex-wrap">

    <?php foreach ($model->listLotNumber() as $model) { ?>
    <div class="p-2 col-4">
        <div class="card position-relative zoom-in">
            <p class="badge rounded-pill text-bg-primary position-absolute top-0 end-0">
                <?php echo $model->warehouse->warehouse_name; ?></p>
            <?php echo Html::img($model->product->ShowImg(), ['class' => 'card-top']); ?>
            <div class="card-body w-100">
                <div class="d-flex justify-content-center align-items-center">

                    <?php
                        try {
                            echo Html::a('<i class="fa-solid fa-cart-plus"></i> '.$model->getLotQty()['lot_number'].' <span class="badge text-bg-danger">'.$model->getLotQty()['qty'].'</span> เลือก', ['/inventory/main-stock/add-to-cart', 'id' => $model->getLotQty()['id']], ['class' => 'add-cart btn btn-sm btn-primary rounded-pill mt--45 zoom-in']);
                        } catch (Throwable $th) {
                            // throw $th;
                        }
        ?>
                </div>
                <p class="text-truncate mb-0"><?php echo $model->product->title; ?></p>

                <div class="d-flex justify-content-between">
                    <code class=""><?php echo $model->product->code; ?></code>
                    <div class="">
                        <span class="text-primary">ทั้งหมด</span>
                        <span class="fw-semibold text-danger"><?php echo $model->SumQty(); ?></span>
                        <span class="text-primary"><?php echo $model->product->unit_name; ?></span>
                    </div>

                    <!-- <span class="badge rounded-pill badge-soft-primary text-primary fs-13"> <?php echo $model->warehouse->warehouse_name; ?> </span> -->
                </div>
            </div>
        </div>

    </div>
    <?php }?>
</div>