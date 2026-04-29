<?php

use app\components\AppHelper;
use app\modules\am\models\AssetDisposal;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var AssetDisposal $model */

$this->title = $model->disposal_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบขอจำหน่ายครุภัณฑ์', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="asset-disposal-view">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h3 class="mb-1"><?= Html::encode($model->disposal_no) ?></h3>
            <div class="text-muted">
                ปีงบประมาณ <?= Html::encode($model->fiscal_year) ?>
                <?php if ($model->disposal_date): ?>
                    | วันที่ <?= Html::encode(AppHelper::convertToThai($model->disposal_date)) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('ลบ', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-outline-danger',
                'data' => [
                    'confirm' => 'ต้องการลบใบขอจำหน่ายนี้หรือไม่?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-3">
                    <div class="text-muted small">เลขที่</div>
                    <div class="fw-semibold"><?= Html::encode($model->disposal_no) ?></div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="text-muted small">ปีงบประมาณ</div>
                    <div class="fw-semibold"><?= Html::encode($model->fiscal_year) ?></div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="text-muted small">หน่วยงาน</div>
                    <div class="fw-semibold"><?= Html::encode($model->departmentRef->name ?? '-') ?></div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="text-muted small">วันที่</div>
                    <div class="fw-semibold"><?= Html::encode($model->disposal_date ? AppHelper::convertToThai($model->disposal_date) : '-') ?></div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="text-muted small">วิธีจำหน่าย</div>
                    <div class="fw-semibold"><?= Html::encode($model->disposal_method ?: '-') ?></div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="text-muted small">ผู้รับผิดชอบ</div>
                    <div class="fw-semibold"><?= Html::encode($model->responsibleLabel) ?></div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="text-muted small">สถานะ</div>
                    <div><span class="badge bg-primary"><?= Html::encode($model->getStatusLabel()) ?></span></div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">หมายเหตุ</div>
                    <div><?= nl2br(Html::encode($model->summary_note ?: '-')) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">รายการพัสดุที่ขอจำหน่าย</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>รหัส</th>
                        <th>ชื่อ</th>
                        <th>สภาพ</th>
                        <th>เหตุผล</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->disposalItems as $item): ?>
                        <tr>
                            <td><?= Html::encode($item->asset_code) ?></td>
                            <td><?= Html::encode($item->asset_name) ?></td>
                            <td><?= Html::encode($item->condition->name ?? $item->asset_condition ?? '-') ?></td>
                            <td><?= nl2br(Html::encode($item->reason ?: '-')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($model->disposalItems)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">ไม่มีรายการ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
