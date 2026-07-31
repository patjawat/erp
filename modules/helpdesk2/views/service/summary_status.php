<?php
use yii\helpers\Html;
use app\modules\helpdesk2\models\Helpdesk;
$statusMeta = Helpdesk::repairStatusMeta();
?>
<div class="row">
    <div class="col-md-3">
        <div class="card dashboard-card" style="border-left-color: #0d6efd;">
            <div class="card-body">
                <h2><?= $model->SummaryStatus('pending')['count_status']?> </h2>
                <h5 class="card-title"><i class="<?= Html::encode($statusMeta['pending']['icon']) ?> me-1" aria-hidden="true"></i><?= Html::encode($statusMeta['pending']['label']) ?></h5>
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-primary"
                        style="width:<?= $model->SummaryStatus('pending')['progress_bar']?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card" style="border-left-color: #dc3545;">
            <div class="card-body">
                <h2><?= $model->SummaryStatus('receive')['count_status']?> </h2>
                <h5 class="card-title"><i class="<?= Html::encode($statusMeta['receive']['icon']) ?> me-1" aria-hidden="true"></i><?= Html::encode($statusMeta['receive']['label']) ?></h5>
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-info"
                        style="width: <?= $model->SummaryStatus('receive')['progress_bar']?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card" style="border-left-color: #ffc107;">
            <div class="card-body">
                <h2><?= $model->SummaryStatus('in_progress')['count_status']?> </h2>
                <h5 class="card-title"><i class="<?= Html::encode($statusMeta['in_progress']['icon']) ?> me-1" aria-hidden="true"></i><?= Html::encode($statusMeta['in_progress']['label']) ?></h5>
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-warning"
                        style="width: <?= $model->SummaryStatus('in_progress')['progress_bar']?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card" style="border-left-color: #20c997;">
            <div class="card-body">
                <h2><?= $model->SummaryStatus('success')['count_status']?> </h2>
                <h5 class="card-title"><i class="<?= Html::encode($statusMeta['success']['icon']) ?> me-1" aria-hidden="true"></i><?= Html::encode($statusMeta['success']['label']) ?></h5>
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-success"
                        style="width: <?= $model->SummaryStatus('success')['progress_bar']?>%"></div>
                </div>
            </div>
        </div>
    </div>

</div>
