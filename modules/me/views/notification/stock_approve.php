<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\db\Expression;
use app\components\UserHelper;
use app\components\NotificationHelper;
use app\modules\purchase\models\Order;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\inventory\models\StockEvent;

$notifications = NotificationHelper::Info()['stock_approve'];
$msg = $notifications['title'];
?>
<div class="card">
    <div class="card-body">
<h6>ขออนุมัติเบิกวัสดุ</h6>
    <div
        class="table-responsive"
    >
        <table class="table table-primary">
            <thead>
                <tr>
                    <th scope="col">รายการ</th>
                    <th class="text-center">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
 <?php foreach ($notifications['datas'] as $item): ?>
                <tr class="">
                    <td scope="row">
                    <a href="<?php echo Url::to(['/inventory/stock-order/view', 'id' => $item->id, 'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> แจ้งซ่อม']); ?>">
                        <?php echo $item->CreateBy($msg)['avatar']?>
 </a>
                    </td>
                    <td><?php echo $item->viewCreated(); ?></td>
                </tr>
                <?php endforeach ?>
                </table>
    </div>
    
    </div>
</div>
       