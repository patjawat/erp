<?php
use yii\helpers\Html;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\LinkPager;
use yii\widgets\Pjax;
use app\components\AppHelper;
$this->title = 'ทะเบียนประวัติ';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col" style="width:100px">ชื่อ-นามสกุล</th>
                        <th scope="col" class="text-center" style="width: 280px;">สถานะ | เริ่มงาน</th>
                        <th scope="col">แผนกฝ่าย | อายุราชการ</th>
                        <th scope="col">เหลืออีก | สิ้นสุดสัญญาจ้าง | เกษียรอายุ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($dataProvider->getModels() as $model):?>
                    <tr class="">
                        <td class="text-truncate"><?=$model->getAvatar()?></td>
                        <td class="align-middle">
                            <div class="d-flex flex-column">
                                <span>
                                    <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1"><i
                                            class="bi bi-clipboard-check"></i> <?=$model->statusName()?></label>
                                    | <i class="bi bi-calendar-check-fill"></i>
                                    <?=Yii::$app->thaiFormatter->asDate($model->join_date,'medium')?>
                                </span>

                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex flex-column">

                                <span>
                                    <?=$model->departmentName()?>
                                </span>
                                <span>
                                    <!-- <i class="bi bi-clock text-primary"></i> 1 ปี 2 เดือน 3 วัน -->
                                    <i class="bi bi-clock text-primary"></i> <?=$model->age_join_date?>
                                </span>
                            </div>

                        </td>
                        <td class="align-middle">
                            <!-- กำหนดวันหมดอายุ -->
                            <div class="d-flex justify-content-between">
                                <div>
                                    <?=AppHelper::CountDown($model->Retire()['date'])?>
                                </div>
                                <div>
                                    <i class="bi bi-calendar2-event"></i> <?=$model->Retire()['date'];?>
                                </div>

                            </div>
                            <div class="progress progress-sm mt-3 w-100">
                                <div class="progress-bar bg-<?=$model->Retire()['color']?>" role="progressbar"
                                    <?= "style='width:". $model->Retire()['progress'] ."%;'" ?> aria-valuenow="65"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <!-- จบการกำหนดวันหมดอายุ -->
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>




    </div>
</div>