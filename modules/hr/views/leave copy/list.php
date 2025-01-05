<?php

use yii\bootstrap5\Html;
?>
<table class="table table-striped mt-3">
            <thead>
                <tr class="table-secondary">
                    <th scope="col">ผู้ขออนุมัติการลา</th>
                    <th class="text-center" scope="col">เป็นเวลา/วัน</th>
                    <th scope="col">ปีงบประมาณ</th>
                    <th scope="col">จากวันที่</th>
                    <th scope="col">ถึงวันที่</th>
                    <th scope="col">มอบหมาย</th>
                    <th scope="col">ผู้ตรวจสอบและอนุมัติ</th>
                    <th class="text-start">ความคืบหน้า</th>
                    <th class="text-center">สถานะ</th>
                    <th class="text-center">#</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $model):?>
                <tr class="">
                    <td  class="text-truncate" style="max-width: 250px;"><?=$model->getAvatar(false)['avatar']?></td>
                    <td class="text-center fw-semibold "><?php echo $model->total_days?></td>
                    <td><?php echo $model->thai_year?></td>
                    <td><?=Yii::$app->thaiFormatter->asDate($model->date_start, 'medium')?></td>
                    <td><?=Yii::$app->thaiFormatter->asDate($model->date_end, 'medium')?></td>
                    <td><?php echo $model->leaveWorkSend()->getAvatar(false, $msg)?></td>
                    <td><?php echo $model->stackChecker()?></td>
                    <td class="fw-light align-middle text-start">
                        <?php
                       // echo $model->statusProcess();
                       echo $model->showStatus();
                        ?>

                </td>
                    <td class="fw-center align-middle text-center">
                        <?php
                        try {
                            echo $model->leaveStatus->title;
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                        ?>
                    </td>

                    <td class="text-center">
                        <?=Html::a(' <i class="fa-solid fa-eye me-1"></i>',['/hr/leave/view','id' => $model->id,'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'],['class' => 'btn btn-sm btn-primary open-modalx','data' => ['size' => 'modal-lg']]) ?>
                        <?=Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>',['/hr/leave/update','id' => $model->id,'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'],['class' => 'btn btn-sm btn-warning open-modal','data' => ['size' => 'modal-lg']]) ?>
                        
                    </td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
        <div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
        </div>
