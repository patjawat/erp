<?php
use app\components\EmployeeHelper;
$sqlPositionName = "SELECT format(COUNT(e.id) * 100 / (SELECT COUNT(id) FROM employees WHERE status = 1 AND id <> 1),2) FROM employees e
INNER JOIN categorise c ON c.code = e.position_name
WHERE c.name = 'position_name'";

$queryPositionName = Yii::$app->db->createCommand($sqlPositionName)->queryScalar();
?>
<div class="card">
    <div class="card-body">
   

<div class="d-flex align-items-center">
            <div class="p-3 d-flex justify-content-center bg-primary-subtle border-0 px-4 py-3 lh-1 rounded">
            <i class="fa-solid fa-person-hiking"></i>
            </div>
            <div class="p-2 flex-grow-1">
                <div class="d-flex align-items-center justify-content-between mb-2"> <span
                        class="d-block fw-semibold">ความสมบรูณ์ของตำแหน่ง</span> <span class="d-block text-secondary"><?=$queryPositionName;?>%</span>
                </div>
                <div class="progress progress-animate progress-sm" role="progressbar" aria-valuenow="<?=$queryPositionName;?>" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-striped bg-secondary" style="width: <?=$queryPositionName;?>%"></div>
                </div>
            </div>
        </div>


        </div>
</div>
