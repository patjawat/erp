<?php
use yii\helpers\Html;

$this->registerCssFile('@web/css/timeline.css');

?>


<style>
.timeline {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    margin: 50px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 25%;
    left: 0;
    right: 0;
    height: 2px;
    background: #d3d3d3;
    z-index: 1;
}

.timeline-item {
    text-align: center;
    position: relative;
    z-index: 2;
}

.timeline-item .circle {
    /* width: 48px;
    height: 48px;
    background: #a0c4ff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    position: relative; */
}

.timeline-item .circle::before {
    content: '';
    width: 10px;
    height: 10px;
    background: #3b82f6;
    border-radius: 50%;
}

.timeline-item .year {
    font-weight: bold;
    margin-bottom: 5px;
    margin-top: 10px;
}

.timeline-item .description {
    color: #6b7280;
}
</style>

<div class="container">
    <div class="row text-center justify-content-center mb-5">
        <div class="col-xl-6 col-lg-8">
            <h5 class="font-weight-bold">การอนุมัติเห็นชอบ</h5>
            <!-- <p class="text-muted">We’re very proud of the path we’ve taken. Explore the history that made us the
                        company we are today.</p> -->
        </div>
    </div>

</div>


<div class="container">
    <div class="timeline">

        <?php foreach ($model->listApprove() as $item): ?>
        <div class="timeline-item">
            <div class="circle">
                <?php echo Html::img($item->getAvatar()['photo'],['class' => 'avatar avatar-sm','style' =>'margin-right: 6px;'])?>

            </div>
            <div class="year">
                <?php
                                                $approveDate = $item->viewApproveDate();
                                                if ($item->status == 'Pending') {
                                                    echo '<i class="bi bi-hourglass-bottom  fw-semibold text-warning"></i> รอ' . ($item->level == 3 ? $item->title : $item->data_json['topic']);
                                                } else if ($item->status == 'Approve') {
                                                    echo '<i class="bi bi-check-circle fw-semibold text-success"></i> ' . $item->data_json['topic'] . '  <i class="bi bi-clock-history"></i> ' . $approveDate;
                                                } else if ($item->status == 'Reject') {
                                                    echo '<i class="bi bi-stop-circle  fw-semibold text-danger"></i> ไม่' . $item->data_json['topic'] . ' <i class="bi bi-clock-history"></i> ' . $approveDate;
                                                } else if ($item->status == 'Cancel') {
                                                }
                                                ?>
            </div>
            <div class="description"><?php echo $item->getAvatar()['fullname']?></div>
            <?php if($model->status == 'ReqCancel' || $model->status == 'Cancel'):?>
            -
            <?php else:?>
            <?php echo Html::a('ดำเนินการ', ['/me/leave/approve', 'id' => $item->id, 'title' => $item->title], ['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal mt-2']) ?>
            <?php endif?>
        </div>
        <?php endforeach ?>

    </div>
</div>