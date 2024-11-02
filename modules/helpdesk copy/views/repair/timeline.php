<?php
use yii\helpers\Html;
use kartik\widgets\StarRating;
$this->registerCssFile('@web/css/timeline.css');
?>
<style>
    .modal-body{
        background: #f1f2f8;
    }
</style>
<section class="bsb-timeline-1">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-10 col-md-8 col-xl-8">
                <ul class="timeline">

                <?php if($model->rating != ''):?>
                    <li class="timeline-item">
                        <div class="timeline-body">
                            <div class="timeline-content">
                                <div class="card border-0 shadow-none">
                                    <div class="card-body p-2">
                                  <div class="d-flex justify-content-between">
                                      <h6 class="card-subtitle text-dark py-2">ข้อเสนอแนะและการให้คะแนน</h6>
                                        
                                  </div>
                                        <div class="col-12 text-truncate px-2">
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-check2-circle text-primary me-1"></i><?=isset($model->data_json['comment']) ? $model->data_json['comment'] : ''?>
                                            </p>
                                     
                                                <?php
                                                echo kartik\widgets\StarRating::widget([
                                                    'name' => 'rating',
                                                    'value' => $model->rating,
                                                    'disabled' => true,
                                                    'pluginOptions' => [
                                                        'step' => 1,
                                                        'size' => 'sm',
                                                        'starCaptions' => $model->listRating(),
                                                        'starCaptionClasses' => [
                                                            1 => 'text-danger',
                                                            2 => 'text-warning',
                                                            3 => 'text-info',
                                                            4 => 'text-success',
                                                            5 => 'text-success',
                                                        ],
                                                    ],
                                                ]);
                                                ?>
                                        </div>
                                    </div>
                                    <div class="card-footer py-1">
                                        <i class="fa-regular fa-clock"></i>
                                        <span class="fw-lighter"><?=$model->viewCommentDate()?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php endif;?>

                <?php if(isset($model->data_json['end_job']) && $model->data_json['end_job'] !=0):?>
                    <li class="timeline-item">
                        <div class="timeline-body">
                            <div class="timeline-content">
                                <div class="card border-0 shadow-none">
                                    <div class="card-body p-2">
                                  <div class="d-flex justify-content-between">
                                      <h6 class="card-subtitle text-dark py-2">เสร็จสิ้น</h6>
                                      <?=$model->rating == '' ? Html::a('<i class="fa-solid fa-star text-warning"></i> ให้คะแนน', ['/helpdesk/repair/rating', 'id' => $model->id,'title' => '<i class="fa-solid fa-star text-warning"></i> ให้คะแนนการทำงาน'], ['class' => 'btn btn-success open-modal','data' => ['size' => 'modal-lg']]) : '' ?>
                                  </div>
                                        <div class="col-12 text-truncate px-2">
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-check2-circle text-primary me-1"></i><?=isset($model->data_json['repair_type']) ? $model->data_json['repair_type'] : ''?>
                                            </p>
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-check2-circle text-primary me-1"></i>วิธีแก้ไข <?=$model->data_json['repair_note']?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="card-footer py-1">
                                        <i class="fa-regular fa-clock"></i>
                                        <span class="fw-lighter"><?=$model->viewEndJob()?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php endif;?>
                    
                    <?php if(isset($model->data_json['start_job']) && $model->data_json['start_job'] !=0):?>
                    <li class="timeline-item">
                        <div class="timeline-body">
                            <div class="timeline-content">
                                <div class="card border-0 shadow-none">
                                    <div class="card-body p-2">
                                  
                                        <h6 class="card-subtitle text-dark py-2">ดำเนินการ</h6>
                                        <div class="col-12 text-truncate px-2">
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-check2-circle text-primary me-1"></i><?=isset($model->data_json['repair_type']) ? $model->data_json['repair_type'] : ''?>
                                            </p>
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-check2-circle text-primary me-1"></i>มอบหมาย <?=$model->avatarStack()?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="card-footer py-1">
                                        <i class="fa-regular fa-clock"></i>
                                        <span class="fw-lighter"><?=$model->viewStartJob()?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php endif;?>

                    <?php if(isset($model->data_json['technician_name']) && $model->data_json['technician_name'] !==""):?>
                    <li class="timeline-item">
                        <div class="timeline-body">
                            <div class="timeline-content">
                                <div class="card border-0 shadow-none">
                              
                                    <div class="card-body p-2">
                                        <h6 class="card-subtitle text-dark py-2">รับเรื่อง</h6>

                                        <div class="col-12 text-truncate px-2">
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-check2-circle text-primary me-1"></i><?=$model->data_json['technician_name']?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="card-footer py-1">
                                        <i class="fa-regular fa-clock"></i>
                                        <span class="fw-lighter"><?=$model->viewAccetpTime()?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php endif;?>
                  
                    <li class="timeline-item">
                        <div class="timeline-body">
                            <div class="timeline-content">
                                <div class="card border-0 shadow-none">
                                  
                                    <div class="card-body pt-2">
                                        <h6 class="card-subtitle text-dark py-2">ร้องขอ</h6>
                                        <div class="col-12 text-truncate px-2">
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-check2-circle text-primary me-1"></i><?=$model->data_json['title']?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="card-footer py-1">
                                        <i class="fa-regular fa-clock"></i>
                                        <span class="fw-lighter"><?=$model->viewCreateDateTime()?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>

            </div>
        </div>
    </div>
</section>