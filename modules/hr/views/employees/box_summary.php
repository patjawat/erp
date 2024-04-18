<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\components\AppHelper;
use app\components\AgeProcessHelper;
?>


<div class="row">
            <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12">
                <div class="card card-body">
                    <div class="d-flex">
                        <div class="avatar-md rounded-circle bg-light">
                            <i class="bi bi-calendar2-plus fs-md avatar-title text-primary"></i>
                        </div>
                        <div class="flex-grow-1 text-end">
                            <h5 class="text-dark"><span data-plugin="counterup">Job Description</span></h5>
                            <p class="text-muted mb-0 text-truncate">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12">
                <div class="card card-body">
                    <div class="d-flex">
                        <div class="avatar-md rounded-circle bg-light">
                            <i class="bi bi-clock fs-md avatar-title text-primary"></i>
                        </div>
                        <div class="flex-grow-1 text-end">
                            <h5 class="text-dark"><span data-plugin="counterup">KPI</span></h5>
                            <p class="text-muted mb-0 text-truncate">0</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12">
                <div class="card card-body">
                    <div class="d-flex">
                        <div class="avatar-md rounded-circle bg-light">
                            <i class="bi bi-hourglass-split avatar-title text-primary"></i>
                        </div>
                        <div class="flex-grow-1 text-end">
                            <h5 class="text-dark"><span data-plugin="counterup">เงินเดือน</span></h5>
                            <p class="text-muted mb-0 text-truncate"><?=$model->salary ? number_format($model->salary,2) : '' ?> บาท</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        

<?php
$js = <<< JS

JS;
$this->registerJS($js)

?>