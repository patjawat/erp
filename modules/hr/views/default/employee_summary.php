<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\hr\models\TeamGroup;
use app\modules\hr\models\Organization;

$headcount = (int) $dataProvider->getTotalCount();
$male = isset($dataProviderGenderM) ? (int) $dataProviderGenderM->getTotalCount() : 0;
$female = isset($dataProviderGenderW) ? (int) $dataProviderGenderW->getTotalCount() : 0;
$genderRatio = $headcount > 0 ? round($male / $headcount * 100) . ' : ' . round($female / $headcount * 100) : '—';
?>

<div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <a href="<?= Url::to(['/hr/employees']) ?>" class="text-decoration-none">
                            <span class="text-muted text-uppercase small d-block text-truncate">จำนวนบุคลากร (ทั้งองค์กร)</span>
                        </a>
                        <h2 class="mb-0 mt-1 fw-bold"><?= $headcount ?></h2>
                        <span class="small text-muted">ชาย : หญิง = <?= $genderRatio ?></span>
                    </div>
                    <div class="flex-shrink-0 text-primary opacity-75">
                        <i class="bi bi-people fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <a href="<?= Url::to(['/hr/organization/diagram']) ?>" class="text-decoration-none">
                            <span class="text-muted text-uppercase small d-block text-truncate">ผังองค์กร / กลุ่มงาน</span>
                        </a>
                        <h2 class="mb-0 mt-1 fw-bold"><?= Organization::find()->where(['tb_name' => 'diagram'])->count('id') ?></h2>
                        <span class="small text-muted">หน่วยโครงสร้าง</span>
                    </div>
                    <div class="flex-shrink-0 text-success opacity-75">
                        <i class="bi bi-diagram-3 fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <?= Html::a('<span class="text-muted text-uppercase small d-block text-truncate">ตำแหน่ง (ระบุในระบบ)</span>', ['/hr/categorise', 'name' => 'position_name', 'title' => 'ตำแหน่ง'], ['class' => 'fw-bold open-modal text-decoration-none', 'data' => ['size' => 'modal-xl']]) ?>
                        <h2 class="mb-0 mt-1 fw-bold"><?= Organization::find()->where(['tb_name' => 'position'])->count('id') ?></h2>
                    </div>
                    <div class="flex-shrink-0 text-warning opacity-75">
                        <i class="bi bi-person-badge fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <?= Html::a('<span class="text-muted text-uppercase small d-block text-truncate">กลุ่ม / ทีมประสานงาน</span>', ['/hr/team-group'], ['class' => 'fw-bold text-decoration-none']) ?>
                        <h2 class="mb-0 mt-1 fw-bold"><?= TeamGroup::find()->count('id') ?></h2>
                        <span class="small text-muted">ทีมข้ามสายงาน</span>
                    </div>
                    <div class="flex-shrink-0 text-info opacity-75">
                        <i class="bi bi-person-workspace fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>