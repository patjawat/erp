<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\db\Expression;
use app\components\UserHelper;
use app\components\NotificationHelper;
use app\modules\purchase\models\Order;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\inventory\models\StockEvent;

$notifications = NotificationHelper::Info()['purchase']['datas'];
$data = NotificationHelper::Info()['purchase'];

?>
<div class="card">
    <div class="card-body">
<h6>อนุมัติขอซื้อขอจ้าง</h6>
    <div
        class="table-responsive"
    >
        <table class="table table-primary">
            <thead>
                <tr>
                    <th scope="col">รายการ</th>
                    <!-- <th class="text-center">ดำเนินการ</th> -->
                </tr>
            </thead>
            <tbody>
                
            <?php foreach ($notifications as $item): ?>
    <?php 
    // $msg = 'ขอ'.$item->data_json['topic'].$item->leave->leaveType->title;
    $msg = 'ขอ';
    ?>
                <tr class="">
                    <td scope="row">
                        <?php echo Html::a($item->leave->getAvatar($msg)['avatar'],['/me/leave/approve', 'id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']]);?>
                    </td>
                    
                   
                </tr>
                <?php endforeach ?>
                </tbody>
        </table>
    </div>
    
    </div>
</div>
       