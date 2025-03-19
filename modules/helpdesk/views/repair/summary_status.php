<div class="row">
    <div class="col-md-3">
        <div class="card dashboard-card" style="border-left-color: #0d6efd;">
            <div class="card-body">
                <h2><?= $model->SummaryStatus(1)['count_status']?> </h2>
                <h5 class="card-title">ร้องขอ/รอดำเนินการ</h5>
                <div class="progress">
                    <div class="progress-bar bg-primary"
                        style="width:<?= $model->SummaryStatus(1)['progress_bar']?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card" style="border-left-color: #dc3545;">
            <div class="card-body">
                <h2><?= $model->SummaryStatus(2)['count_status']?> </h2>
                <h5 class="card-title">รับเรื่อง</h5>
                <div class="progress">
                    <div class="progress-bar bg-info"
                        style="width: <?= $model->SummaryStatus(2)['progress_bar']?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card" style="border-left-color: #ffc107;">
            <div class="card-body">
                <h2><?= $model->SummaryStatus(3)['count_status']?> </h2>
                <h5 class="card-title">กำลังดำเนินการ</h5>
                <div class="progress">
                    <div class="progress-bar bg-warning"
                        style="width: <?= $model->SummaryStatus(3)['progress_bar']?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card" style="border-left-color: #20c997;">
            <div class="card-body">
                <h2><?= $model->SummaryStatus(4)['count_status']?> </h2>
                <h5 class="card-title">เสร็จสิ้น</h5>
                <div class="progress">
                    <div class="progress-bar bg-success"
                        style="width: <?= $model->SummaryStatus(4)['progress_bar']?>%"></div>
                </div>
            </div>
        </div>
    </div>

</div>