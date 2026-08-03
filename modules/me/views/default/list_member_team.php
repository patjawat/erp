<?php
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use yii\helpers\Html;
use yii\helpers\Url;

$deptIds = array_filter([$me->department]);
if ($me->department && ($org = Organization::findOne($me->department))) {
    $deptIds = array_merge($deptIds, $org->children()->select('id')->column());
}

$listsMemberTeam = Employees::find()
->where(['department' => $deptIds, 'status' => 1])
->andWhere(['<>', 'id', (int) $me->id])->all();
?>
        <div class="row g-3 align-items-center justify-content-between mb-4 mt-4">
            <div class="col-12 col-sm-auto">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-4 d-flex align-items-center justify-content-center"
                         style="width: 48px; height: 48px;">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                    <div>
                        <h3 class="mb-0" style="font-size: 1.1rem;"><?= $me->departmentName() ?></h3>
                        <p class="text-muted mb-0" style="font-size: 0.8rem;">
                            ทีมงานทั้งหมด <span class="fw-bold text-primary"><?= count($listsMemberTeam) ?></span> คน
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-auto">
                <button class="btn btn-light btn-sm rounded-pill px-3 shadow-sm border text-muted">ดูทั้งหมด</button>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <div class="overflow-auto pe-2" style="max-height: 450px; scrollbar-width: thin;">
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($listsMemberTeam as $item): ?>
                        <div class="card mb-1 flex-row align-items-center justify-content-between p-3 rounded-4 shadow-sm transition-all">
                            <div class="d-flex align-items-center flex-grow-1 min-w-0">
                                <?= $item->getAvatar(false) ?>
                            </div>
                            <div class="dropdown flex-shrink-0 ms-2">
                                <button class="btn btn-light border-0 rounded-pill py-1 px-2 shadow-sm dropdown-toggle" type="button" id="dropdown-member-<?= $item->id ?>" data-bs-toggle="dropdown" aria-expanded="false" title="เลือกเมนูการทำงาน">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-2" aria-labelledby="dropdown-member-<?= $item->id ?>">
                                    <li><h6 class="dropdown-header text-truncate" style="max-width:260px"><?= Html::encode($item->fullname) ?></h6></li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= Url::to(['/hr/employees/view', 'id' => $item->id, 'view' => 'manager']) ?>">
                                            <i class="bi bi-folder2-open text-primary team-member-menu__icon"></i>
                                            แฟ้มประวัติพนักงาน
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= Url::to(['/hr/employees/view', 'id' => $item->id, 'name' => 'performance_appraisal', 'view' => 'manager']) ?>">
                                            <i class="bi bi-clipboard-check text-success team-member-menu__icon"></i>
                                            ประเมินทดลองงาน
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

<style>
.transition-all { transition: all 0.2s ease-in-out; }
.team-member-menu__icon { width: 1.15rem; flex: 0 0 1.15rem; text-align: center; }
.overflow-auto::-webkit-scrollbar { width: 4px; }
.overflow-auto::-webkit-scrollbar-thumb { background: var(--bs-border-color); border-radius: 10px; }
</style>
