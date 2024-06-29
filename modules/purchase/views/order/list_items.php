<?php
use app\modules\purchase\models\Order;
use yii\helpers\Html;
use yii\widgets\Pjax;

$listItems = Order::find()->where(['category_id' => $model->id, 'name' => 'order_item'])->all();
?>
<?php Pjax::begin(['id' => 'order_item']); ?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>รูป</th>
                        <th style="width:500px">รายการ</th>
                        <th class="text-center" style="width:80px">หน่วย</th>
                        <th class="text-end">ราคาต่อหน่วย</th>
                        <th class="text-center" style="width:80px">จำนวน</th>
                        <th class="text-end">จำนวนเงิน</th>
                        <th style="width:180px">
                            <div class="d-flex justify-content-center">
                                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่มรายการ', ['/purchase/order/product-list', 'order_id' => $model->id, 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เพิ่มวัสดุใหม่'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-xl']]) ?>

                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listItems as $item): ?>
                    <tr class="">
                        <td class="align-middle">
                            <?php
                            try {
                                echo Html::img($item->product->ShowImg(), ['class' => '  ', 'style' => 'max-width:50px;;height:280px;max-height: 50px;']);
                            } catch (\Throwable $th) {
                                // throw $th;
                            }
                            ?>
                        </td>
                        <td class="align-middle">
                            <?php
                            try {
                                echo $item->product->title;
                            } catch (\Throwable $th) {
                            }
                            // throw $th;
                            ?></td>
                        <td class="align-middle text-center">
                            
                        <?php
                        try {
                            echo $item->product->data_json['unit'];
                        } catch (\Throwable $th) {
                        }
                        // throw $th;
                        ?>
                        </td>
                        <td class="align-middle text-end fw-semibold">
                            <?php
                            try {
                                echo number_format($item->price, 2);
                            } catch (\Throwable $th) {
                                // throw $th;
                            }
                            ?>
                        </td>
                        <td class="align-middle text-center">
                            <?= $item->qty ?>
                        </td>
                        <td class="align-middle text-end">
                            <div class="d-flex justify-content-end fw-semibold">
                                <?php
                                try {
                                    echo number_format(($item->qty * $item->price), 2);
                                } catch (\Throwable $th) {
                                    // throw $th;
                                }
                                ?>
                            </div>
                        </td>

                        <td class="align-middle gap-2">
                            <div class="d-flex justify-content-center gap-2">
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/purchase/order/update-item', 'id' => $item->id], ['class' => 'btn btn-sm btn-warning rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['/purchase/order/delete-item', 'id' => $item->id], ['class' => 'btn btn-sm btn-danger rounded-pill delete-item']) ?>
                            </div>

                        </td>
                    </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
            <div class="row">
                <div class="col-6"></div>
                <div class="col-6">
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr class="">
                                    <td>ทั้งหมด</td>
                                    <td><span class="fw-semibold"><?= number_format($model->SumPo(), 2) ?></span>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                    <div class="d-grid gap-2">
                        <?php // if ($model->status == '' && count($listItems) > 0): ?>
                        <?php if ($model->status == '' && count($listItems) > 0): ?>
                        <?= Html::a('<i class="fa-solid fa-circle-exclamation"></i> ส่งคำขอซื้อ', [
                            '/purchase/pr-order/pr-confirm',
                            'id' => $model->id,
                            'status' => 2,
                        ], ['class' => 'btn btn-primary rounded shadow pr-confirm']) ?>
                        <?php endif; ?>

                        <?php if ($model->approve == 'Y'): ?>

                            <?php if ($model->status == 2): ?>
                        <?= Html::a('ลงทะเบียนคุม', ['/purchase/pq-order/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-circle-check"></i> ลงทะเบียนคุม'], ['class' => 'btn btn-primary rounded shadow open-modal shadow', 'data' => ['size' => 'modal-lg']]) ?>
                       <?php endif; ?>


                       <?php if ($model->status == 3): ?>
                        <?= Html::a('sss', ['/purchase/po-order/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-circle-check"></i> ลงทะเบียนคุม'], ['class' => 'btn btn-primary rounded shadow open-modal-x shadow', 'data' => ['size' => 'modal-lg']]) ?>
                       <?php endif; ?>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php Pjax::end() ?>