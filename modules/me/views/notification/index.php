<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\lm\models\Leave;
use app\components\NotificationHelper;
$notifications = NotificationHelper::Info();


$this->title = 'Notification';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-bell noti-animate"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>


<div class="row d-flex justify-content-start">
    <div class="col-lg-8 col-md-8 col-sm-12">


        <?php echo $this->render('leave')?>



        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-primary">
                        <thead>
                            <tr>
                                <th scope="col">รายการ</th>
                                <th scope="col">ระยะเวลา</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php  echo $this->render('helpdesk')?>
                            <?php  echo $this->render('stock_approve')?>
                            <?php  echo $this->render('orders')?>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>