<?php

use yii\helpers\Html;
use app\modules\lm\models\Leave;
use app\modules\lm\models\LeaveType;
?>
<div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6>สรุปประเภทการลา</h6>
                </div>
                <div class="table-container">
                    <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="text-start">ประเภทการลา</th>
                            <th class="text-center">ต.ค.</th>
                            <th class="text-center">พ.ย.</th>
                            <th class="text-center">ธ.ค.</th>
                            <th class="text-center">ม.ค.</th>
                            <th class="text-center">ก.พ.</th>
                            <th class="text-center">มี.ค.</th>
                            <th class="text-center">เม.ย.</th>
                            <th class="text-center">พ.ค.</th>
                            <th class="text-center">มิ.ย.</th>
                            <th class="text-center">ก.ค.</th>
                            <th class="text-center">ส.ค.</th>
                            <th class="text-center">ก.ย.</th>
                        <tr>
                    </thead>
                    <tbody>
                        <?php foreach($dataProvider->getModels() as $model):?>
                        <tr>
                            <td class="text-start"><?php echo $model->title?></td>
                            <td class="text-center"><?php echo $model->m10?></td>
                            <td class="text-center"><?php echo $model->m11?></td>
                            <td class="text-center"><?php echo $model->m12?></td>
                            <td class="text-center"><?php echo $model->m1?></td>
                            <td class="text-center"><?php echo $model->m2?></td>
                            <td class="text-center"><?php echo $model->m3?></td>
                            <td class="text-center"><?php echo $model->m4?></td>
                            <td class="text-center"><?php echo $model->m5?></td>
                            <td class="text-center"><?php echo $model->m6?></td>
                            <td class="text-center"><?php echo $model->m7?></td>
                            <td class="text-center"><?php echo $model->m8?></td>
                            <td class="text-center"><?php echo $model->m9?></td>

                        </tr>
                        <?php endforeach?>
                    </tbody>
                    </table>
                </div>
            </div>
        </div>