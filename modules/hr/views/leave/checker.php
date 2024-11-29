<?php
use yii\helpers\Html;
$this->registerCssFile('@web/css/timeline.css');

?>
<style>
.modal-body {
    background: #f1f2f8;
}
</style>

<section class="bsb-timeline-1">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-8 col-sm-12">
                <ul class="timeline">
                    <?php foreach ($model->listLeaveSteps() as $item): ?>
                    <li class="timeline-item">
                        <div class="timeline-body">
                            <div class="timeline-content">
                                <div class="card border-0 shadow-none">
                                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                                        <h6 class="mb-0"><span class="badge bg-primary rounded-pill text-white">1</span>
                                            <?php echo $item->title?></h6>
                                        <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#leader" aria-expanded="true" aria-controls="collapseCard">
                                            <i class="bi bi-chevron-down"></i>
                                        </button>
                                    </div>

                                    <div class="card-body card-body collapse show p-2">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="card-subtitle text-dark py-2"><?php // echo  $item['title']; ?></h6>
                                        </div>
                                        <div class="col-12 text-truncate px-2">
                                            <?php echo $item->getAvatar()['avatar']
                                            ?>
                                        </div>
                                    </div>
                                    <div class="card-footer py-1 d-flex justify-content-between">
                                        <div>

                                          
                                                <?php 
                                                if($item->status == 'Pending'){
                                                    echo '<i class="bi bi-hourglass-bottom  fw-semibold text-warning"></i> รอดำเนินการ';
                                                }else if($item->status == 'Approve'){
                                                    echo '<i class="bi bi-check-circle fw-semibold text-success"></i> ผ่าน';
                                                }else if($item->status == 'Reject'){
                                                    echo '<i class="bi bi-stop-circle  fw-semibold text-danger"></i> ไม่ผ่าน';
                                                }else if($item->status == 'Cancel'){

                                                }
                                                ?>
                                        </div>
                                        <?php echo Html::a('ดำเนินการ',['/hr/leave/approve','id' => $item->id,'title' => $item->title],['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal'])?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php endforeach?>
                </ul>

            </div>
        </div>
    </div>
</section>