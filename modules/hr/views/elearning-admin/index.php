<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'จัดการระบบ E-learning';
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนบุคลากร', 'url' => ['/hr/employees']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="elearning-admin-index">
    <?php $this->beginBlock('page-title'); ?>
    <?= Html::encode($this->title) ?>
    <?php $this->endBlock(); ?>

    <div class="d-flex justify-content-end gap-2 mb-4">
        <?= Html::a('<i class="fa-solid fa-chart-line me-1"></i> รายงานสถิติภาพรวม', ['dashboard'], ['class' => 'btn btn-outline-primary rounded-pill px-3']) ?>
        <?= Html::a('<i class="fa-solid fa-plus me-1"></i> เพิ่มหลักสูตรใหม่', ['create'], ['class' => 'btn btn-primary rounded-pill px-3']) ?>
    </div>

    <?php $this->beginBlock('navbar_menu'); ?>
    <?= $this->render('@app/modules/hr/views/employees/menu', ['active' => 'employees']) ?>
    <?php $this->endBlock(); ?>

    <div class="row">
        <?php foreach ($dataProvider->getModels() as $model): ?>
            <?php
            $targetDeps = json_decode($model->target_departments, true) ?: [];
            $depNames = [];
            $allDeps = \app\modules\hr\models\Employees::ListDepartment();
            foreach ($targetDeps as $depId) {
                if (isset($allDeps[$depId])) {
                    $depNames[] = $allDeps[$depId];
                }
            }
            ?>
            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge <?= $model->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> px-2.5 py-1.5 rounded-pill fs-8">
                                <?= $model->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' ?>
                            </span>
                            <small class="text-muted"><i class="fa-regular fa-clock me-1"></i> <?= Yii::$app->formatter->asDate($model->created_at, 'medium') ?></small>
                        </div>
                        
                        <h5 class="card-title text-primary fw-bold mb-2">
                            <?= Html::encode($model->title) ?>
                        </h5>
                        
                        <p class="card-text text-secondary mb-4 fs-7 flex-grow-1">
                            <?= Html::encode(mb_strimwidth($model->description, 0, 120, "...")) ?>
                        </p>

                        <div class="mb-3">
                            <span class="fs-8 text-muted d-block mb-1">กลุ่มเป้าหมาย:</span>
                            <?php if (empty($targetDeps)): ?>
                                <span class="badge bg-light text-dark fs-8">ทั่วไป</span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark fs-8" title="<?= implode(', ', $depNames) ?>">
                                    <?= count($depNames) ?> แผนก
                                </span>
                            <?php endif; ?>
                            <span class="badge bg-info-subtle text-info fs-8 ms-1">เกณฑ์ผ่าน <?= $model->passing_score_percent ?>%</span>
                        </div>

                        <hr class="my-3 opacity-10">

                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <div class="d-flex gap-2">
                                <span class="text-muted fs-8"><i class="fa-solid fa-book-open text-primary me-1"></i> <?= count($model->materials) ?> สื่อ</span>
                                <span class="text-muted fs-8 ms-2"><i class="fa-solid fa-circle-question text-warning me-1"></i> <?= count($model->questions) ?> ข้อ</span>
                            </div>
                            <div>
                                <?= Html::a('จัดการหลักสูตร <i class="fa-solid fa-arrow-right fs-8 ms-1"></i>', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary rounded-pill px-3']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ($dataProvider->getCount() == 0): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm text-center py-5">
                    <div class="card-body">
                        <div class="fs-1 text-muted mb-3"><i class="fa-regular fa-folder-open"></i></div>
                        <h5 class="text-secondary fw-bold">ไม่พบหลักสูตร E-learning</h5>
                        <p class="text-muted">คุณสามารถเริ่มสร้างหลักสูตรแรกได้โดยการกดปุ่ม "เพิ่มหลักสูตรใหม่"</p>
                        <?= Html::a('<i class="fa-solid fa-plus me-1"></i> เพิ่มหลักสูตรแรก', ['create'], ['class' => 'btn btn-primary rounded-pill mt-2']) ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="d-flex justify-content-center mt-3">
        <?= LinkPager::widget([
            'pagination' => $dataProvider->getPagination(),
            'options' => ['class' => 'pagination pagination-rounded'],
        ]) ?>
    </div>
</div>
