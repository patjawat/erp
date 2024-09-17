<?php

use app\modules\lm\models\Leave;
use app\modules\lm\models\LeaveType;
use yii\helpers\Html;
$leaveType = LeaveType::find()->where(['name' => 'leave_type'])->limit(10)->all();
?>
<div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6>รายชื่อบุคลกรขออนุมัติการลา</h6>
                                    <?=Html::a('<i class="fa-solid fa-circle-plus me-2"></i> ยื่นใบลา',['/lm/leave/create','title' => '<i class="fa-solid fa-circle-plus"></i> ยื่นใบลา'],['class' => 'btn btn-primary shadow rounded-pill open-modal','data' => ['size' => 'modal-lg']])?>
                      
                </div>
               
                <div class="table-container">
                    <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>สถานะ</th>
                            <th>ปีงบ</th>
                            <th>ประเภทการลา</th>
                            <th>เนื่องจาก</th>
                            <th>เริ่มลา</th>
                            <th>ถึงวันที่</th>
                            <th>จำนวนวัน</th>
                            <th class="text-center">ดำเนินการ</th>
                        <tr>
                    </thead>
                    <tbody>
                        <?php foreach(Leave::find()->all() as $leaveItem):?>
                        <tr>
                            <td>1</td>
                            <td>1</td>
                            <td><?=$leaveItem->thai_year?></td>
                            <td>1</td>
                            <td>1</td>
                            <td>1</td>
                            <td>1</td>
                            <td>1</td>
                            <td class="text-center">
                            <?=Html::a('<i class="fa-regular fa-pen-to-square"></i>',['/lm/leave/update','id' => $leaveItem->id,'title' => '<i class="fa-solid fa-circle-plus"></i> แก้ไขใบลา'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                            </td>
                        </tr>
                        <?php endforeach?>
                    </tbody>
                    </table>
                </div>
                <a href="#" class="btn btn-primary">Go somewhere</a>
            </div>
        </div>