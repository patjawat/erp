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
                                                <i class="bi bi-check2-circle text-primary me-1"></i><?php // isset($model->data_json['comment']) ? $model->data_json['comment'] : ''?>
                                            </p>
                                     
                                             
                                        </div>
                                    </div>
                                    <div class="card-footer py-1">
                                        <i class="fa-regular fa-clock"></i>
                                        <span class="fw-lighter"><?php // $model->viewCommentDate()?></span>
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