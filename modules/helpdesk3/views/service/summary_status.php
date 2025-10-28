<div class="row">
    <div class="col-md-3">
        <div class="card dashboard-card" style="border-left-color: #0d6efd;">
            <div class="card-body">
                <h2><?= $model->SummaryStatus('pending')['count_status']?> </h2>
                <h5 class="card-title">ร้องขอ/รอดำเนินการ</h5>
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
                <h5 class="card-title">รับเรื่อง</h5>
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
                <h5 class="card-title">กำลังดำเนินการ</h5>
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
                <h5 class="card-title">เสร็จสิ้น</h5>
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-success"
                        style="width: <?= $model->SummaryStatus('success')['progress_bar']?>%"></div>
                </div>
            </div>
        </div>
    </div>

</div>