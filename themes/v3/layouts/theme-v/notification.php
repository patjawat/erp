<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\db\Expression;
use app\components\UserHelper;
use app\components\NotificationHelper;
use app\modules\purchase\models\Order;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\inventory\models\StockEvent;

$emp = UserHelper::GetEmployee();
$notify = NotificationHelper::Info();
$leave = $notify['leave'];
$purchase = $notify['purchase'];

?>


<?php if ($notify['total'] >= 1): ?>
<div class="d-inline-flex ms-0 ms-sm-2 dropdown">
    <button data-bs-toggle="dropdown" aria-haspopup="true" type="button" id="page-header-notification-dropdown"
        aria-expanded="false" class="btn header-item notify-icon position-relative">

        <i class="fa-solid fa-bell noti-animate"></i>
        <span
            class="badge bg-danger badge-pill notify-icon-badge bg-danger rounded-pill text-white"><?php echo $notify['total']?></span>
    </button>
    <div aria-labelledby="page-header-notification-dropdown"
        class="dropdown-menu-lg dropdown-menu-right p-0 dropdown-menu" style="width: 350px;">
        <div class="notify-title p-3">
            <h5 class="fs-14 fw-semibold mb-0">
                <span class="h5"><i class="fa-solid fa-bell noti-animate me-1"></i> Notification</span>
            </h5>
        </div>
        <div class="notify-scroll">
    <?php if($leave['total'] >=1):?>
            <div class="scroll-content" id="notify-scrollbar" data-scrollbar="true" tabindex="-1" style="overflow: hidden; outline: none;">
                <div class="scroll-content">
                    <div class="scroll-content container">
                        <a href="<?php echo Url::to(['/me/approve', 'name' => 'leave']); ?>">
                            <div class="d-flex justify-content-between">
                                        <span class="mb-1 h6">ขออนุมัติวันลา </span>
                                        <span class="badge bg-danger badge-pill bg-danger rounded-pill text-white"><?php echo $notify['total']?></span>
                                    </div>
                                    </a>
                    </div>
                </div>
               <?php endif;?>

               <?php if($purchase['total'] >=1):?>
            <div class="scroll-content" id="notify-scrollbar" data-scrollbar="true" tabindex="-1" style="overflow: hidden; outline: none;">
                <div class="scroll-content">
                    <div class="scroll-content container">
                        <a href="<?php echo Url::to(['/me/approve', 'name' => 'purchase']); ?>">
                            <div class="d-flex justify-content-between">
                                        <span class="mb-1 h6">ขออนุมัติจัดซื้อจัดจ้าง </span>
                                        <span class="badge bg-danger badge-pill bg-danger rounded-pill text-white"><?php echo $notify['total']?></span>
                                    </div>
                                    </a>
                    </div>
                </div>
               <?php endif;?>
              
              
            </div>
            <div class="notify-all">
                <a href="<?php echo Url::to(['/me/approve']); ?>" class="text-primary text-center p-3">
                    <small>View All</small>
                </a>
            </div>
        </div>


    </div>
</div>
<?php endif;?>