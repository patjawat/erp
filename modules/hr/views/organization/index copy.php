<?php
use app\models\Categorise;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\modules\hr\models\Employees;

use yii\bootstrap5\LinkPager;

?>

<div class="card">
    <div class="card-body d-flex justify-content-between">
        <h4 class="card-title"> ผังองค์กร/กลุ่มงาน</h4>

        <!-- cta -->
        <div class="row">
            <div class="col-5">
                <form class="float-sm-end mt-3 mt-sm-0">
                    <div class="search-box">
                        <div class="position-relative">
                            <input type="text" placeholder="ค้นหา กลุ่ม/แผนก/ฝ่าย..." class="form-control">
                            <i class="bx bx-search icon"></i>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-7">
                <div class="float-sm-end">
                    <?=Html::a('<i class="bi bi-diagram-3"></i>  ผังองค์กร',['/hr/organization/diagram2'],['class' => 'btn btn-primary'])?>
                    <?=Html::a('<i class="fa-solid fa-gear"></i> ตั้งค่าผังองค์กร',['/hr/organization/setting'],['class' => 'btn btn-primary'])?>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-9">




    <div class="row">
    <?php foreach($dataProvider->getModels() as $model):?>
    <!-- Start col-4 -->
    <div class="col-xl-12 col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex">
                    <?=Html::img(( isset($model->getLeaderFormWorkGroup()->id) ? $model->getLeaderFormWorkGroup()->showAvatar() : ''),['class' => 'avatar rounded-circle','width' => 30])?>
                    <div class="flex-grow-1 overflow-hidden">
                        <h5 class="card-title mb-2 pr-4 text-truncate">
                            <a href="<?=Url::to(['view', 'id' => $model->id])?>" class="text-dark"><?=$model->title?>
                            </a>
                        </h5>
                        <p class="text-muted mb-3">
                            <?=isset($model->getLeaderFormWorkGroup()->fullname) ? $model->getLeaderFormWorkGroup()->fullname : '-'?>
                            <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1"><i
                                    class="fa-solid fa-flag-checkered"></i> ผู้นำทีม</label></p>


                        <div class="avatar-stack">
                            <?php
                            $total = (int)count($model->EmpOnWorkGroup($model->code));
                            ?>
                            <?php if($total >  11):?>
                            <a href="javascript: void(0);" class="condense-profile" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="" data-bs-title="ProfileUser">
                                <div class="avatar bg-light text-secondary op-6 fw-light avatar-sm me-0">
                                    +<?=$total-11?></div>
                            </a>

                            <?php endif;?>
                            <?php if($total == 0):?>
                            <a href="javascript: void(0);" class="condense-profile" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="" data-bs-title="">
                                <div class="avatar bg-light text-secondary op-6 fw-light avatar-sm me-0">
                                    0</div>
                            </a>

                            <?php endif;?>


                            <?php foreach($model->EmpOnWorkGroup($model->code) as  $key => $avatar):?>
                            <?php 
                                
                                    $emp = Employees::findOne(['id' => $avatar['id']]);
                                    ?>
                            <?php if($key < 11):?>
                            <a href="javascript: void(0);" class="me-1" data-bs-toggle="tooltip" data-bs-placement="top"
                                title="" data-bs-title="<?=$emp->fullname?>">
                                <?=Html::img($emp->ShowAvatar(),['class' => 'avatar-sm rounded-circle'])?>

                            </a>
                            <?php endif;?>
                            <?php endforeach;?>


                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body border-top">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto">
                        <ul class="list-inline mb-0">
                            <li class="list-inline-item">
                                <h5 class="fs-14 mb-0" data-bs-toggle="tooltip" data-bs-placement="top" title=""
                                    data-bs-title="Task">
                                    <i class="bi bi-people-fill  fs-sm text-secondary op-5 align-middle"></i>
                                    <span class="align-middle"> จำนวสมาชิก
                                        <?=count($model->EmpOnWorkGroup($model->code))?> คน</span>
                                </h5>
                            </li>
                        </ul>
                    </div>
                    <div class="col pl-2">
                        <span class="badge bg-primary text-white float-end">In Progress</span>
                    </div>
                </div>
            </div>
            <div class="dropdown edit-field-half-left">
                <div class="btn-icon btn-icon-sm btn-icon-soft-primary me-0 edit-field-icon" data-bs-toggle="dropdown">
                <i class="fa-solid fa-sliders"></i>
                </div>
                <div class="dropdown-menu dropdown-menu-right">
                    <?= Html::a('<i class="mdi mdi-pencil align-middle me-1 text-primary"></i>
                        <span><i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข</span>', ['view', 'id' => $model->id], ['class' => 'dropdown-item']) ?>
                    <a href="#" class="dropdown-item">
                        <i class="mdi mdi-delete align-middle me-1 text-danger"></i>
                        <span>Delete</span>
                    </a>
                </div>
            </div>
        </div>
        <!-- end card -->
    </div>
    <!-- end col-4 -->
    <?php endforeach;?>


</div>



    </div>


    <div class="col-3">
        <?php foreach(Categorise::find()->where(['name' => 'position_type'])->all() as $model):?>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <a href="/hr/employees">
                                <span class="text-muted text-uppercase fs-6"><?=$model->title;?></span>
                            </a>
                            <h2 class="mb-0 mt-1">264</h2>
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
        <?php endforeach;?>
    </div>
</div>