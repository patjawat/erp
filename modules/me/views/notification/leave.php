<?php
use yii\helpers\Url;
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
        <table
            class="table table-primary"
        >
            <thead>
                <tr>
                    <th scope="col">รายการ</th>
                    <th scope="col">form_id</th>
                    <th scope="col">ระยะเวลา</th>
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
                    <a href="<?php echo Url::to(['/me/leave/view', 'id' => $item->from_id, 'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> แจ้งซ่อม']); ?>">
                        <?php // echo $item->getAvatar($msg)['avatar']?>
                        <?php echo $item->leave->getAvatar($msg)['avatar']?>
 </a>
                    </td>
                    <td><?php echo $item->from_id?></td>
                    <td><?php echo $item->emp_id?></td>
                </tr>
                <?php endforeach ?>
                </tbody>
        </table>
    </div>
    
    </div>
</div>
       