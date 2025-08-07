<?php
use yii\helpers\Html;
use app\components\UserHelper;
//ตรวจสอบว่าเป็นผู้ดูแลคลัง
$userid = \Yii::$app->user->id;
$emp = UserHelper::GetEmployee();
?>
<?php echo $model->viewChecker('ผู้เห็นชอบ')['avatar']; ?>
                    <?php if($model->checker == $emp->id && $model->order_status != 'success'):?>
                    <?php echo Html::a('<i class="fa-regular fa-pen-to-square"></i> ดำเนินการ', ['/me/approve/view-stock-out', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary shadow rounded-pill open-modal', 'data' => ['size' => 'modal-md']]); ?>
                    <?php endif;?>
