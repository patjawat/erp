<?php

use yii\helpers\Url;
use yii\bootstrap\Html;
use app\modules\hr\models\Employees;

$listsMemberTeam = Employees::find()
->where(['department' => $me->department,'status' => 1])
->andWhere(['<>','id',1])->all();
?>
<section class="mt-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 text-primary rounded-4 d-flex align-items-center justify-content-center"
                 style="width: 48px; height: 48px;">
                <i class="bi bi-people-fill fs-4"></i> </div>
            <div>
                <h3 class="mb-0" style="font-size: 1.1rem; color: #334155;"><?= $me->departmentName() ?></h3>
                <p class="text-muted mb-0" style="font-size: 0.8rem;">
                    ทีมงานทั้งหมด <span class="fw-bold text-primary"><?= count($listsMemberTeam) ?></span> คน
                </p>
            </div>
        </div>
        <button class="btn btn-light btn-sm rounded-pill px-3 shadow-sm border text-muted" style="font-size: 0.75rem;">ดูทั้งหมด</button>
    </div>

    <div class="overflow-auto pe-2" style="max-height: 450px; scrollbar-width: thin;">
        <div class="d-flex flex-column gap-2">
            <?php foreach($listsMemberTeam as $item): ?>
            <a href="<?=Url::to(['/hr/employees/view','id' => $item->id])?>">
                <div class="d-flex align-items-center justify-content-between p-3 rounded-4 border border-light bg-white shadow-sm hover-shadow-md transition-all cursor-pointer">
                    
                    <?= $item->getAvatar(false) ?>
<i class="bi bi-chevron-right"></i>
        
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
/* เพิ่มความนุ่มนวลเวลา Hover */
.transition-all { transition: all 0.2s ease-in-out; }
.cursor-pointer { cursor: pointer; }
.hover-shadow-md:hover { 
    transform: translateX(5px); 
    background-color: #f8fafc !important;
    border-color: #e2e8f0 !important;
}
/* ปรับแต่ง Scrollbar ให้เข้ากับ Bootstrap 5 */
.overflow-auto::-webkit-scrollbar { width: 4px; }
.overflow-auto::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
