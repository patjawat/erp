<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Approve;
use app\components\NotificationHelper;
$notifications = NotificationHelper::Info()['leave']['datas'];
?>
<div class="card">
    <div class="card-body">
<h6>ขออนุมัติการลา</h6>
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
       