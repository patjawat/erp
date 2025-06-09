<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\approve\models\Approve;
use app\components\ApproveHelper;
$notifications = ApproveHelper::Info()['leave']['datas'];
?>
<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-bell noti-animate"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/me/views//default/menu') ?>
<?php $this->endBlock(); ?>



<div class="card">
    <div class="card-body">
<h6>ขออนุมัติการลา</h6>
    <div
        class="table-responsive"
    >
        <table class="table">
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
       