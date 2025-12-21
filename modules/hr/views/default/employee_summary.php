<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\EmployeeHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\TeamGroup;
use app\modules\hr\models\Organization;

$sqlPositionName = "SELECT format(COUNT(e.id) * 100 / (SELECT COUNT(id) FROM employees WHERE status = 1 AND id <> 1),2) FROM employees e
INNER JOIN categorise c ON c.code = e.position_name
WHERE c.name = 'position_name'";

$queryPositionName = Yii::$app->db->createCommand($sqlPositionName)->queryScalar();
?>

<div class="row g-3"> <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 shadow-sm border-0"> <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="flex-grow-1 overflow-hidden">
                        <a href="<?=Url::to(['/hr/employees'])?>" class="text-decoration-none">
                            <span class="text-muted text-uppercase fs-13 fw-medium d-block text-truncate">บุคลากรทั้งหมด</span>
                        </a>
                        <h2 class="mb-0 mt-1 fw-bold"><?=$dataProvider->getTotalCount()?></h2>
                    </div>
                    <div class="flex-shrink-0 text-primary opacity-75">
                         <i class="bi bi-person-badge fs-1"></i>
                    </div>
                </div>
                <div class="progress progress-animate progress-sm mt-3" style="height: 5px;">
                    <div class="progress-bar progress-bar-striped bg-danger" style="width: <?=$queryPositionName;?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <a href="<?=Url::to(['/hr/organization/diagram'])?>" class="text-decoration-none">
                            <span class="text-muted text-uppercase fs-13 fw-medium d-block text-truncate">ผังองค์กร/กลุ่มงาน</span>
                        </a>
                        <h2 class="mb-0 mt-1 fw-bold"><?=Organization::find()->where(['tb_name' => 'diagram'])->count('id')?></h2>
                    </div>
                    <div class="flex-shrink-0 text-success opacity-75">
                        <i class="bi bi-diagram-3 fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="flex-grow-1 overflow-hidden">
                        <?=Html::a('<span class="text-muted text-uppercase fs-13 fw-medium d-block text-truncate">ตำแหน่ง</span>',['/hr/categorise','name' => 'position_name','title' => 'ตำแหน่ง'],['class' => 'open-modal text-decoration-none','data' => ['size' => 'modal-xl']])?>
                        <h2 class="mb-0 mt-1 fw-bold"><?=Organization::find()->where(['tb_name' => 'position'])->count('id')?></h2>
                    </div>
                    <div class="flex-shrink-0 text-warning opacity-75">
                        <i class="fa-solid fa-user-tag fs-1"></i>
                    </div>
                </div>
                <div class="progress progress-animate progress-sm mt-3" style="height: 5px;">
                    <div class="progress-bar progress-bar-striped bg-danger" style="width: <?=$queryPositionName;?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <?=Html::a('<span class="text-muted text-uppercase fs-13 fw-medium d-block text-truncate">กลุ่ม/ทีมประสาน</span>',['/hr/team-group'], ['class' => 'text-decoration-none'])?>
                        <h2 class="mb-0 mt-1 fw-bold"><?=TeamGroup::find()->count('id')?></h2>
                    </div>
                    <div class="flex-shrink-0 text-info opacity-75">
                        <i class="fa-solid fa-user-group fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
     
</div>