<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
$title = '<i class="fa-regular fa-address-card"></i> ข้อมูลพื้นฐาน';
?>
<div class="card border-0 rounded-3">
    <div class="card-body">
        <div class="d-flex justify-content-between">

            <h5 class="card-title"><?=$title;?></h5>

            <div>
                <div class="dropdown">
                    <a href="javascript:void(0)" class="btn btn-primary  text-white rounded-pill dropdown-toggle me-0"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-sliders"></i> ดำเนินการ
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" style="">
                        <?=Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']])?>
                        <?=Html::a('<i
                                class="bi bi-database-fill-gear me-1"></i>ตั้งค่า', ['/hr/categorise', 'id' => $model->id,'title' => $title], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']])?>
                    </div>
                </div>
            </div>
        </div>

        <table class="table border-0 table-striped-columns mt-3">
            <tbody>
                <tr>
                    <td>ชื่อ-สกุล : </td>
                    <td><?=$model->fullname?></td>

                    <td>เลขบัตรประชาชน : </td>
                    <td><?=$model->cid?></td>
                </tr>
                <tr>
                    <td>วันเกิด : </td>
                    <td><?=$model->birthday?></td>

                    <td>อายุปัจจุบัน : </td>
                    <td>27 ปี 4 เดือน</td>
                </tr>

                <tr>
                    <td>อายุครบหกสิบปี : </td>
                    <td>12 กันยายน พ.ศ.2588</td>

                    <td>ภูมิลำเนาเดิม : </td>
                    <td><?=$model->born?></td>
                </tr>
                <tr>
                    <td>ที่อยู่ : </td>
                    <td colspan="4"><?=$model->fulladdress?></td>
                </tr>
                <tr>
                    <td>หมายเลขโทรศัพท์ : </td>
                    <td><?=$model->phone?></td>

                    <td>อีเมล : </td>
                    <td><?=$model->email?></td>
                </tr>


                <tr>
                    <td scope="row">ตำแหน่ง</td>
                    <td><?=$model->positionName()?></td>
                    <td>ประเภท</td>
                    <td><?=$model->positionTypeName()?></td>
                   

                </tr>
                <tr>
               
                    <td scope="row">ตำแหน่งเลขที่</td>
                    <td><?=$model->position_number?></td>
                    <td>ระดับตำแหน่ง</td>
                    <td><?=$model->positionLevelName()?></td>
                </tr>
                <tr>
                    <td>ตำแหน่งบริหาร</td>
                    <td>???</td>
                    <td>ความเชี่ยวชาญ</td>
                    <td>ชำนาญการพิเศษ</td>
                </tr>
                <tr>
                  
                    <td>สถานะ</td>
                    <td>ปฏิบัติงาน</td>
                    <td>รหัสประจำตัว</td>
                    <td>FT-0001</td>
                </tr>
                <tr>
                    <td>อัตราเงินเดือน</td>
                    <td colspan="3">
                       <?=isset($model->salary) ? number_format($model->salary,2) : '-'?> บาท
                        </div>
                    </td>
                </tr>

                
            </tbody>
        </table>
    </div>
</div>