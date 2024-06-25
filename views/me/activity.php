<?php
use app\modules\helpdesk\models\Helpdesk;
use app\modules\purchase\models\Order;
use yii\helpers\Html;

$repairs = Helpdesk::find()->all();
$orders = Order::find()->where(['name' => 'order'])->all();

?>

        <table
            class="table table-primary"
        >
            <thead>
                <tr>
                    <th scope="col">ประเภท</th>
                    <th scope="col">รายการ</th>
                    <th scope="col">สถานะ</th>
                    <th scope="col">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($repairs as $repair): ?>
                <tr class="">
                    <td scope="row">
                       
                        <span> แจ้งซ่อม</span>
                        
                       
                    </td>
                    <td><span> <?= $repair->data_json['title'] ?></span></td>
                    <td>  <?= $repair->viewStatus() ?></td>
                    <td>
                    <?= Html::a('<i class="fa-solid fa-eye"></i>', ['/helpdesk/repair/timeline', 'id' => $repair->id, 'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> แจ้งซ่อม'], ['class' => 'btn btn-sm btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php foreach ($orders as $order): ?>
                <tr class="">
                    <td scope="row">ขอซื้อขอจ้าง</td>
                    <td>
                    <?= isset($model->data_json['product_type_name']) ? Html::a($model->data_json['comment'], ['/sm/order/view', 'id' => $model->id]) : '' ?>
                    </td>
                    <td>อนุมัติ</td>
                    <td>
                        <div class="d-flex flex-row">
                            <?= Html::a('<i class="fa-solid fa-eye"></i>', ['/sm/order/view', 'id' => $order->id], ['class' => 'btn btn-sm btn-primary']) ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
 