<?php
use app\modules\helpdesk\models\Helpdesk;
use app\modules\purchase\models\Order;
use yii\helpers\Html;

$orders = Order::find()->where(['name' => 'order'])->all();
$sql = "SELECT * FROM `order` o 
INNER JOIN employees e ON e.id = cast(o.data_json->'\$.leader1' as UNSIGNED)
WHERE e.user_id = 10 AND o.status = 2 AND o.name = 'order'";
$querys = Yii::$app->db->createCommand($sql)->queryAll();

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
               
                <?php foreach ($querys as $model): ?>
                <tr class="">
                    <td scope="row">ขอซื้อขอจ้าง</td>
                    <td>
                    <?= isset($model['data_json']['comment']) ? Html::a($model['data_json']['comment'], ['/sm/order/view', 'id' => $model['id']]) : '' ?>
                    </td>
                    <td>อนุมัติ</td>
                    <td>
                        <div class="d-flex flex-row">
                            <?php //  Html::a('<i class="fa-solid fa-eye"></i>', ['/sm/order/view', 'id' => $order->id],['class' => 'btn btn-sm btn-primary']) ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
 