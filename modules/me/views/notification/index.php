<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\lm\models\Leave;
use app\components\NotificationHelper;
$notifications = NotificationHelper::Info();
$totalLeave = $notifications['leave']['total'];
$totalHelpdesk = $notifications['helpdesk']['total'];
$totalStockApprove = $notifications['stock_approve']['total'];



$this->title = 'Notification';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-bell noti-animate"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>


<div class="row d-flex justify-content-center">
    <?php if($totalLeave >=1):?>
        <div class="col-lg-4 col-md-4 col-sm-12">
            <?php echo $this->render('leave')?>
        </div>
        <?php endif;?>
        
        <?php if($totalStockApprove >=1):?>
        <div class="col-lg-4 col-md-4 col-sm-12">
                    <?php  echo $this->render('stock_approve')?>
     
            
        </div>
        <?php endif;?>
    </div>

        <?php  echo $this->render('orders')?>
        
    