<?php
use yii\helpers\Url;
use app\models\Approve;
use app\components\NotificationHelper;
$notifications = NotificationHelper::Info()['leave']['datas'];
?>
 <?php foreach ($notifications as $item): ?>
    <?php 
    $msg = 'ขอ'.$item->data_json['topic'].$item->leave->leaveType->title;
    ?>
                <tr class="">
                    <td scope="row">
                    <a href="<?php echo Url::to(['/hr/leave/view', 'id' => $item->from_id, 'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> แจ้งซ่อม']); ?>">
                        <?php // echo $item->getAvatar($msg)['avatar']?>
                        <?php echo $item->leave->getAvatar($msg)['avatar']?>
 </a>
                    </td>
                    <td><?php echo $item->emp_id?></td>
                </tr>
                <?php endforeach ?>