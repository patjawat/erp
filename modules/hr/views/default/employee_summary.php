<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\EmployeeHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\TeamGroup;
use app\modules\hr\models\Organization;

$sqlPositionName = "SELECT format(COUNT(e.id) * 100 / (SELECT COUNT(id) FROM employees WHERE status = 1 AND id <> 1),2) FROM employees e
INNER JOIN categorise c ON c.code = e.position_name
WHERE c.name = 'position_name'";

$queryPositionName = Yii::$app->db->createCommand($sqlPositionName)->queryScalar();
?>


<div class="row">

    <div class="col-lg-3 col-md-3 col-sm-12 col-sx-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <a href="<?=Url::to(['/hr/employees'])?>">
                            <span class="text-muted text-uppercase fs-6">บุคลากรทั้งหมด</span>
                        </a>
                        <h2 class="mb-0 mt-1"><?=$dataProvider->getTotalCount()?></h2>
                    </div>
                    <div class="text-center" style="position: relative;">
                        <div id="t-rev" style="min-height: 45px;">
                            <div id="apexchartsdlqwjkgl"
                                class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                                style="width: 90px; height: 45px;">
                                <i class="bi bi-person-badge fs-1"></i>
                                <div class="apexcharts-legend"></div>

                            </div>
                        </div>

                        <div class="resize-triggers">
                            <div class="expand-trigger">
                                <div style="width: 91px; height: 70px;"></div>
                            </div>
                            <div class="contract-trigger"></div>
                        </div>
                    </div>
                </div>


                <!-- ความสมบรูณ์ของข้อมูล -->
                <div class="progress progress-animate progress-sm" role="progressbar"
                    aria-valuenow="<?=$queryPositionName;?>" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-striped bg-danger" style="width: <?=$queryPositionName;?>%">
                    </div>
                </div>
                <!-- ความสมบรูณ์ของข้อมูล -->



            </div>
        </div>
    </div>
    <!-- End-col -->

    <div class="col-lg-3 col-md-12 col-sm-12 col-sx-12">
        <div class="card" style="height: 103px;">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <a href="<?=Url::to(['/hr/organization/diagram'])?>">
                            <span class="text-muted text-uppercase fs-6">ผังองค์กร/กลุ่มงาน</span>
                        </a>
                        <h2 class="mb-0 mt-1"><?=Organization::find()->where(['tb_name' => 'diagram'])->count('id')?>
                        </h2>
                    </div>

                    <div class="text-center" style="position: relative;">
                        <div id="t-rev" style="min-height: 45px;">
                            <div id="apexchartsdlqwjkgl"
                                class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                                style="width: 90px; height: 45px;">
                                <i class="bi bi-diagram-3 fs-1"></i>
                                <div class="apexcharts-legend"></div>

                            </div>
                        </div>
                        <!-- <span class="text-success fw-bold fs-13">
                            <i class="bx bx-up-arrow-alt"></i> 10.21%
                        </span> -->
                        <div class="resize-triggers">
                            <div class="expand-trigger">
                                <div style="width: 91px; height: 70px;"></div>
                            </div>
                            <div class="contract-trigger"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End-col -->

    <!-- End-col -->
    <div class="col-lg-3 col-md-12 col-sm-12 col-sx-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <?=Html::a(' <span class="text-muted text-uppercase fs-6">ตำแหน่ง</span>',['/hr/categorise','name' => 'position_name','title' => 'ตำแหน่ง'],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?>

                        <h2 class="mb-0 mt-1"><?=Organization::find()->where(['tb_name' => 'position'])->count('id')?>
                        </h2>
                    </div>
                    <div class="text-center" style="position: relative;">
                        <div id="t-rev" style="min-height: 45px;">
                            <div id="apexchartsdlqwjkgl"
                                class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                                style="width: 90px; height: 45px;">
                                <i class="fa-solid fa-user-tag fs-1"></i>
                                <div class="apexcharts-legend"></div>

                            </div>
                        </div>
                        <div class="resize-triggers">
                            <div class="expand-trigger">
                                <div style="width: 91px; height: 70px;"></div>
                            </div>
                            <div class="contract-trigger"></div>
                        </div>
                    </div>
                </div>
                <!-- ความสมบรูณ์ของข้อมูล -->
                <div class="progress progress-animate progress-sm" role="progressbar"
                    aria-valuenow="<?=$queryPositionName;?>" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-striped bg-danger" style="width: <?=$queryPositionName;?>%">
                    </div>
                </div>
                <!-- ความสมบรูณ์ของข้อมูล -->


            </div>
        </div>
    </div>
    <!-- End-col -->


    <!-- End-col -->
    <div class="col-lg-3 col-md-12 col-sm-12 col-sx-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <?=Html::a(' <span class="text-muted text-uppercase fs-6">กลุ่ม/ทีมประสาน</span>',['/hr/team-group'])?>

                        <h2 class="mb-0 mt-1"><?=TeamGroup::find()->count('id')?>
                        </h2>
                    </div>
                    <div class="text-center" style="position: relative;">
                        <div id="t-rev" style="min-height: 45px;">
                                <i class="fa-solid fa-user-group fs-1"></i>
                            </div>
                        </div>
                        <div class="resize-triggers">
                            <div class="expand-trigger">
                                <div style="width: 91px; height: 70px;"></div>
                            </div>
                            <div class="contract-trigger"></div>
                        </div>
                    </div>
                </div>



            </div>
        </div>
    </div>
    <!-- End-col -->
     
</div>