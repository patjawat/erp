<?php

use yii\web\View;
use yii\helpers\Html;
use app\components\UserHelper;
use app\modules\hr\models\TeamGroupDetail;
use app\modules\dms\models\DocumentsDetail;
$me = UserHelper::GetEmployee();

?>

<div class="card" style="height:300px;">

        
        <div class="table-responsive">


            <table class="table">
                <thead>
                    <tr>
                        <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                        <th class="text-center fw-semibold" style="width:80px">พ.ศ.</th>
                        <th class="fw-semibold" scope="col">รายการกลุ่ม/ทีมประสาน</th>
                        <th class="fw-semibold" scope="col">ตำแหน่งที่ได้รับ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($me->teamGroup() as $key => $item):?>
                    <tr>
                        <td class="text-center fw-semibold">
                            <?php echo $key +1?>
                        </td>
                        <td class="text-center"><?=$item->thai_year?></td>
                        <td scope="row">
                            <?= $item->appointment?->teamGroup?->title;?>
                        </td>
                        <td><?=  $item->data_json['committee_name'] ?? '-'?></td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
    </div>
</div>
