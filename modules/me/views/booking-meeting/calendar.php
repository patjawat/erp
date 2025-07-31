<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;


$this->registerCssFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$this->title = 'ระบบจองห้องประชุม';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-handshake fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
ปฏิทินการใช้ห้องประชุม
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('menu', ['active' => 'calendar']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me//menu', ['active' => 'meeting']) ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-lg-8 col-md-8 col-sm-12">
        <?= $this->render('@app/modules/booking/views/meeting/calendar_item') ?>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-12">

        <div class="card">
            <div class="card-header bg-primary-gradient text-white">
                <div class="d-flex justify-content-between">
                    <h6 class="text-white">
                        <i class="fa-solid fa-calendar-days"></i> ปฏิทินวันนี้
                    </h6>
                </div>
            </div>
            <div class="card-body">
                    <table
                        class="table table-striped"
                    >
                        <thead>
                            <tr>
                                <th scope="col">เวลา</th>
                                <th scope="col">กิจกรรม</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($eventTodays as $_eventTodays):?>
                                <?php $iconData = $_eventTodays->showIconIfInTimeRange(); ?>
                            <tr>
                                <td scope="row"  class="<?=$iconData['active'] ? 'fw-semibold text-sucess' : ''?>">

                                <?php
                                
                                echo $iconData['icon'] . ' ' . ($_eventTodays->viewTime()['full'] ?? '-');
                                ?>
                                </td>
                                <!-- <td  class="<?=$iconData['active'] ? 'fw-semibold text-success' : ''?>"><?php // $_eventTodays->title?></td> -->
                                    <td>
                                    <p class="mb-0"><?=$_eventTodays->room->title?></p>
                                    <p class="mb-0 fs-12"><?=$_eventTodays->title?></p>
                                </td>
                            </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>

            </div>
        </div>

        <div class="card">
            <div class="card-header bg-primary-gradient text-white">
                <div class="d-flex justify-content-between">
                    <h6 class="text-white">
                        <i class="fa-solid fa-calendar-days"></i> ปฏิทินทั้งหมด
                    </h6>
                </div>
            </div>
            <div class="card-body">
                    <table
                        class="table table-striped"
                    >
                        <thead>
                            <tr>
                                <th scope="col">เวลา</th>
                                <th scope="col">กิจกรรม</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($eventTodays as $_eventTodays):?>
                                <?php $iconData = $_eventTodays->showIconIfInTimeRange(); ?>
                            <tr>
                                <td><?=$_eventTodays->viewTime()['full'] ?? '-'?></td>
                                 <td>
                                    <p class="mb-0"><?=$_eventTodays->room->title?></p>
                                    <p class="mb-0 fs-12"><?=$_eventTodays->title?></p>
                                </td>
                            </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>

            </div>
        </div>


    </div>
</div>