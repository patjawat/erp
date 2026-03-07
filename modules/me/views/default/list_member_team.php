<?php

use yii\helpers\Url;
use yii\helpers\Html;
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
            <?php foreach ($listsMemberTeam as $item): ?>
            <div class="d-flex align-items-center justify-content-between p-3 rounded-4 border border-light bg-white shadow-sm transition-all">
                <div class="d-flex align-items-center gap-3 flex-grow-1 min-w-0">
                    <?= $item->getAvatar(false) ?>
                </div>
                <div class="dropdown flex-shrink-0 ms-2">
                    <button class="btn btn-light border-0 rounded-pill py-1 px-2 shadow-sm dropdown-toggle" type="button" id="dropdown-member-<?= $item->id ?>" data-bs-toggle="dropdown" aria-expanded="false" title="เลือกเมนูการทำงาน">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-2" aria-labelledby="dropdown-member-<?= $item->id ?>">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= Url::to(['/hr/employees/view', 'id' => $item->id]) ?>">
                                <i class="bi bi-person-circle text-primary"></i>
                                ข้อมูลพนักงาน
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= Url::to(['/hr/employees/view', 'id' => $item->id]) ?>">
                                <i class="bi bi-graph-up text-info"></i>
                                KPI Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= Url::to(['/hr/employees/view', 'id' => $item->id, 'name' => 'health']) ?>">
                                <i class="bi bi-heart-pulse text-danger"></i>
                                ข้อมูลสุขภาพ
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.transition-all { transition: all 0.2s ease-in-out; }
.overflow-auto::-webkit-scrollbar { width: 4px; }
.overflow-auto::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
