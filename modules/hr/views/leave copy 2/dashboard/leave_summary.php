<?php

use yii\helpers\Html;
use app\modules\lm\models\Leave;
use app\modules\lm\models\LeaveType;
?>
<div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-ui-checks"></i> สรุปประเภทการลา</h6>
                </div>
                <div class="table-container">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="text-start fw-semibold">ประเภทการลา</th>
                            <th class="text-center fw-semibold">ต.ค.</th>
                            <th class="text-center fw-semibold">พ.ย.</th>
                            <th class="text-center fw-semibold">ธ.ค.</th>
                            <th class="text-center fw-semibold">ม.ค.</th>
                            <th class="text-center fw-semibold">ก.พ.</th>
                            <th class="text-center fw-semibold">มี.ค.</th>
                            <th class="text-center fw-semibold">เม.ย.</th>
                            <th class="text-center fw-semibold">พ.ค.</th>
                            <th class="text-center fw-semibold">มิ.ย.</th>
                            <th class="text-center fw-semibold">ก.ค.</th>
                            <th class="text-center fw-semibold">ส.ค.</th>
                            <th class="text-center fw-semibold">ก.ย.</th>
                        <tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <?php foreach($dataProvider->getModels() as $model):?>
                        <tr>
                            <td class="text-start"><?php echo $model->title?></td>
                            <td class="text-center fw-semibold"><?php echo $model->m10?></td>
                            <td class="text-center fw-semibold"><?php echo $model->m11?></td>
                            <td class="text-center fw-semibold"><?php echo $model->m12?></td>
                            <td class="text-center fw-semibold"><?php echo $model->m1?></td>
                            <td class="text-center fw-semibold"><?php echo $model->m2?></td>
                            <td class="text-center fw-semibold"><?php echo $model->m3?></td>
                            <td class="text-center fw-semibold"><?php echo $model->m4?></td>
                            <td class="text-center fw-semibold"><?php echo $model->m5?></td>
                            <td class="text-center fw-semibold"><?php echo $model->m6?></td>
                            <td class="text-center fw-semibold"><?php echo $model->m7?></td>
                            <td class="text-center fw-semibold"><?php echo $model->m8?></td>
                            <td class="text-center fw-semibold"><?php echo $model->m9?></td>

                        </tr>
                        <?php endforeach?>
                    </tbody>
                    </table>
                </div>
            </div>
        </div>