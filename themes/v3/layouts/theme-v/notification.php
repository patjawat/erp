<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\db\Expression;
use app\components\UserHelper;
use app\modules\purchase\models\Order;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\inventory\models\StockEvent;

$emp = UserHelper::GetEmployee();

// ดารแจ้งซ่อม
$helpdesks = Helpdesk::find()->where(['created_by' => Yii::$app->user->id])->andWhere(['in', 'status', [1, 2, 3]])->all();
// ขออนุมัติ
$approveStocks = isset($emp->id) ? StockEvent::find()->andFilterWhere(['name' => 'order', 'checker' => $emp->id])->andWhere(new Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.checker_confirm')) = ''"))->all() : 0;
if (Yii::$app->user->can('director')) {
$orders = Order::find()
    ->andwhere(['is not', 'pr_number', null])
    ->andwhere(['status' => 1])
    ->andFilterwhere(['name' => 'order'])
    ->andwhere(['=', new Expression("JSON_EXTRACT(data_json, '$.pr_director_confirm')"), ''])
    ->andFilterwhere(['=', new Expression("JSON_EXTRACT(data_json, '$.pr_officer_checker')"), 'Y'])
    ->andFilterwhere(['=', new Expression("JSON_EXTRACT(data_json, '$.pr_leader_confirm')"), 'Y'])
    ->all();
}else{
    $orders = Order::find()->andwhere(['is not', 'pr_number', null])
    ->andwhere(['status' => 1])
    ->andFilterwhere(['name' => 'order'])
    ->andFilterwhere(['=', new Expression("JSON_EXTRACT(data_json, '$.pr_leader_confirm')"), 'Y'])
    ->all();
}

// $countOrder = $orders->all(); // Execute the query
try {
    $summary = (count($approveStocks) + count($helpdesks)+count($orders));
} catch (\Throwable $th) {
    $summary = 0;
}

?>


<?php if ($summary > 0): ?>
<div class="d-inline-flex ms-0 ms-sm-2 dropdown" data-aos="zoom-in" data-aos-delay="300">
    <button data-bs-toggle="dropdown" aria-haspopup="true" type="button" id="page-header-notification-dropdown"
        aria-expanded="false" class="btn header-item notify-icon position-relative">

        <i class="fa-solid fa-bell noti-animate"></i>
        <span
            class="badge bg-danger badge-pill notify-icon-badge bg-danger rounded-pill text-white"><?php echo $summary; ?></span>
    </button>
    <div aria-labelledby="page-header-notification-dropdown"
        class="dropdown-menu-lg dropdown-menu-right p-0 dropdown-menu" style="width: 350px;">
        <div class="notify-title p-3">
            <h5 class="fs-14 fw-semibold mb-0">
                <span>Notification</span>
                <!-- <a class="text-primary" href="javascript: void(0);">
                                <small>Clear All</small>
                            </a> -->
            </h5>
        </div>
        <div class="notify-scroll">

            <div class="scroll-content" id="notify-scrollbar" data-scrollbar="true" tabindex="-1"
                style="overflow: hidden; outline: none;">
                <div class="scroll-content">
                    <div class="scroll-content">

                        <?php foreach ($orders as $order): ?>
                        <a href="<?php echo Url::to(['/purchase/order/view', 'id' => $order->id, 'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> แจ้งซ่อม']); ?>"
                            class="dropdown-item notification-item">
                            <div class="d-flex">
                            <?=Html::img($order->getUserReq()['employee']->showAvatar(), ['class' => 'avatar avatar-sm bg-primary text-white'])?>
                            <div class="avatar-detail">
                <h6 class="mb-1 fs-15" data-bs-toggle="tooltip" data-bs-placement="top"
                    data-bs-custom-class="custom-tooltip" data-bs-title="ดูเพิ่มเติม...">ขออนุมิติจัดซื้อจัดจ้าง
                </h6>

                <p class="text-muted mb-0 fs-13"> <?=$order->viewCreatedAt()?> | ผ่านมาแล้ว <?=$order->viewCreated()?></p>

            </div>
                            </div>
                        </a>
                        <?php endforeach?>

                        <?php foreach ($helpdesks as $helpdesk): ?>
                        <a href="<?php echo Url::to(['/helpdesk/repair/timeline', 'id' => $helpdesk->id, 'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> แจ้งซ่อม']); ?>"
                            class="dropdown-item notification-item open-modal">
                            <div class="d-flex">
                                <?php echo $helpdesk->viewCreateUser()['img']; ?>
                                <div class="avatar-detail text-truncate">
                                    <h6 class="mb-1 fs-13">แจ้งซ่อม</h6>
                                    <p class="media-body">
                                        <small
                                            class="text-muted"><?php echo Yii::$app->thaiFormatter->asDateTime($helpdesk->created_at, 'short'); ?>
                                            | <?php echo $helpdesk->data_json['title']; ?></small>
                                    </p>
                                </div>
                            </div>
                        </a>
                        <?php endforeach?>


                        <?php foreach ($approveStocks as $approveStock): ?>
                        <a href="<?php echo Url::to(['/inventory/stock-order/view', 'id' => $approveStock->id, 'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> แจ้งซ่อม']); ?>"
                            class="dropdown-item notification-item">
                            <div class="d-flex">
                                <?php
                                $msg = 'ขอเบิกวัสดุ';
                            echo $approveStock->CreateBy($msg)['img']; ?>
                                <div class="avatar-detail text-truncate">
                                    <h6 class="mb-1 fs-13">ขอเบิกวัสดุ</h6>
                                    <p class="text-muted mb-0 fs-13">
                                        <?=isset($approveStock->data_json['note']) ? $approveStock->data_json['note'].' | ' : ''?>  <i class="bi bi-clock"></i> <?php echo $approveStock->viewCreated(); ?></p>
                                </div>
                            </div>
                        </a>
                        <?php endforeach?>
                    </div>
                </div>
                <div class="scrollbar-track scrollbar-track-x" style="display: none;">
                    <div class="scrollbar-thumb scrollbar-thumb-x"></div>
                </div>
                <div class="scrollbar-track scrollbar-track-y" style="display: none;">
                    <div class="scrollbar-thumb scrollbar-thumb-y"></div>
                </div>
            </div>
            <div class="notify-all">
                <a href="<?php echo Url::to(['/me/notification']); ?>" class="text-primary text-center p-3">
                    <small>View All</small>
                </a>
            </div>
        </div>


    </div>
</div>
<?php endif;?>