<?php

use yii\helpers\Html;
use app\modules\booking\models\Vehicle;

?>
<div class="card">
            <div class="card-header bg-primary-gradient text-white">
                <div class="d-flex justify-content-between">
                    <h6 class="text-white">
                        <i class="fa-solid fa-calendar-days"></i> การใช้รถวันนี้
                    </h6>
                </div>
            </div>
            <div class="card-body">
                <?php  $eventTodays = Vehicle::find()->where(['date_start' => date('Y-m-d')])->andWhere(['IN','status',['Pass','Approve']])->all(); ?>
                <table
                    class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">รายการ/สถานที่</th>
                            <th scope="col">เวลา</th>
                            <th scope="col">รถที่จัดสรรค์</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eventTodays as $_eventTodays): ?>
                            <tr>
                                <td>
                                    <p class="mb-0 fw-semibold"><?php echo $_eventTodays->locationOrg?->title ?? '-'?></p>
                                    <p class="mb-0 fs-11"><?php echo $_eventTodays->reason ?? '-'?></p>
                                   
                                </td>
                                <td>
                                    <a href="<?=Url::to(['view','id' => $_eventTodays->id])?>" class="open-modal" data-size="modal-lg">
                                    <p class="mb-0 fw-semibold"> <?= ($_eventTodays->viewTime()['full'] ?? '-'); ?></p>
                                </a>
                                </td>
                                <td class="">
                    <div class="d-flex">
                        <?php
                        try {
                            echo $_eventTodays->car ? Html::img($_eventTodays->car?->ShowImg()['image'],['class' => 'avatar rounded border-secondary']) : '';
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                        ?>
                        <div class="avatar-detail">
                            <div class="d-flex flex-column">
                                <p class="mb-0"><?=$_eventTodays->car?->data_json['brand'];?></p>
                                <p class="mb-0 fw-semibold text-primary"><?=$_eventTodays->license_plate?></p>
                            </div>
                        </div>
                    </div>
                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            </div>
        </div>