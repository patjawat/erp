<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
$title = '<i class="fa-regular fa-address-card"></i> ข้อมูลพื้นฐาน';
?>
<div class="card border-0 rounded-3">
    <div class="card-body">
        <div class="d-flex justify-content-between">

            <h5 class="card-title"><?=$title;?></h5>
        </div>

        <table class="table border-0 table-striped-columns mt-3">
            <tbody>
                <tr>
                    <td>ชื่อ-สกุล : </td>
                    <td><span class="text-pink fw-semibold"><?=$model->fullname?></span></td>

                    <td>เลขบัตรประชาชน : </td>
                    <td><span class="text-pink fw-semibold"><?=$model->cid?></span></td>
                </tr>
                <tr>
                    <td>วันเกิด : </td>
                    <td><?=Yii::$app->thaiFormatter->asDate(AppHelper::DateToDb($model->birthday),'medium')?></td>

                    <td>อายุปัจจุบัน : </td>
                    <td><?=$model->age?></td>
                </tr>

                <tr>
                    <td>อายุครบหกสิบปี : </td>
                    <td><?=$model->year60()?></td>

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
                    <td><span class="text-pink fw-semibold"><?=$model->positionName()?></span></td>
                    <td>ประเภท</td>
                    <td><span class="text-pink fw-semibold"><?=$model->positionTypeName()?></span></td>
                   

                </tr>
                <tr>
               
                    <td scope="row">ตำแหน่งเลขที่</td>
                    <td><span class="text-pink fw-semibold"><?=$model->position_number?></span></td>
                    <td>ระดับตำแหน่ง</td>
                    <td><span class="text-pink fw-semibold"><?=$model->positionLevelName()?></span></td>
                </tr>
                <tr>
                    <td>ตำแหน่งบริหาร</td>
                    <td><span class="text-pink fw-semibold"><?=$model->positionManageName();?></span></td>
                    <td>ความเชี่ยวชาญ</td>
                    <td><span class="text-pink fw-semibold"><?=$model->expertiseName();?></span></td>
                </tr>
                <tr>
                    <td>ระดับการศึกษา</td>
                    <!-- <td colspan="3"> -->
                        <td>
                       <?=$model->educationName()?>
                        </div>
                    </td>

                    <td>ประเภท/กลุ่มงาน</td>
                    <td><span class="text-pink fw-semibold"><?=$model->positionGroupName();?></span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
