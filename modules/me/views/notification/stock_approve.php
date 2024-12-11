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