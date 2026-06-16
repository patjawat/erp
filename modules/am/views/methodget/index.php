<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\am\models\ListMethodgetSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'วิธีได้มา';
$this->params['breadcrumbs'][] = ['label' => 'การตั้งค่า', 'url' => ['/am/setting']];
$this->params['breadcrumbs'][] = $this->title;

$total = $dataProvider->getTotalCount();
$models = $dataProvider->getModels();
$activeCount = 0;
foreach ($models as $m) {
    if ((int) $m->active === 1) { $activeCount++; }
}
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <span class="d-inline-flex align-items-center justify-content-center rounded-3"
              style="width:2.25rem;height:2.25rem;background:rgba(79,70,229,.12);color:#4338ca;">
            <i data-lucide="package-search"></i>
        </span>
        <?= Html::encode($this->title) ?>
    </h4>
    <small class="text-muted">รายการวิธีได้มาของทรัพย์สิน (ซื้อ, รับโอน, บริจาค ฯลฯ)</small>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?= $this->render('@app/modules/am/views/_partials/list_setting_styles', ['theme' => 'indigo']) ?>

<div class="list-methodget-index">
    <?php Pjax::begin(['id' => 'pjax-methodget', 'timeout' => false]); ?>

    <div class="card shadow-sm border-0 list-setting-card overflow-hidden">
        <div class="card-header text-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3 list-setting-header list-setting-header--indigo">
            <div class="d-flex align-items-center gap-3">
                <div class="list-setting-icon">
                    <i data-lucide="package-search" style="width:1.5rem;height:1.5rem;"></i>
                </div>
                <div>
                    <h5 class="text-white mb-0 fw-semibold"><?= Html::encode($this->title) ?></h5>
                    <small class="text-white-50">
                        <?= number_format($total, 0) ?> รายการ
                        <span class="opacity-75">·</span>
                        <?= number_format($activeCount, 0) ?> ใช้งาน
                    </small>
                </div>
            </div>
            <?= Html::a('<i class="fa-solid fa-plus me-1"></i> เพิ่มวิธีได้มา', ['create', 'title' => 'เพิ่มวิธีได้มา'], [
                'class' => 'btn btn-light btn-sm fw-medium open-modal',
                'data' => ['size' => 'modal-md'],
            ]) ?>
        </div>

        <div class="card-body p-0">
            <?php if ($total === 0): ?>
                <?= $this->render('@app/modules/am/views/_partials/list_setting_empty', [
                    'theme' => 'indigo',
                    'icon' => 'package-search',
                    'title' => 'ยังไม่มีวิธีได้มา',
                    'subtitle' => 'เริ่มต้นเพิ่มวิธีได้มา เช่น ซื้อ, จ้างก่อสร้าง, เช่า, บริจาค',
                    'createUrl' => ['create', 'title' => 'เพิ่มวิธีได้มา'],
                    'createLabel' => 'เพิ่มวิธีได้มารายการแรก',
                ]) ?>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 list-setting-table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 56px" class="text-center">#</th>
                                <th scope="col" style="width: 110px">รหัส</th>
                                <th scope="col">ชื่อวิธีได้มา</th>
                                <th scope="col" class="d-none d-md-table-cell">รายละเอียด</th>
                                <th scope="col" style="width: 90px" class="text-center">ลำดับ</th>
                                <th scope="col" style="width: 110px" class="text-center">สถานะ</th>
                                <th scope="col" style="width: 160px" class="text-end pe-3">ดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider">
                            <?php foreach ($models as $key => $model): ?>
                                <tr class="list-setting-row" style="--i:<?= $key ?>;">
                                    <td class="text-center text-muted"><?= (($dataProvider->pagination?->offset ?? 0) + $key + 1) ?></td>
                                    <td>
                                        <span class="badge bg-indigo-soft text-indigo fw-semibold font-monospace">
                                            <?= Html::encode($model->code) ?>
                                        </span>
                                    </td>
                                    <td class="fw-medium"><?= Html::encode($model->title) ?></td>
                                    <td class="d-none d-md-table-cell text-muted small">
                                        <?php
                                        $desc = (string) $model->description;
                                        if ($desc === '' || $desc === '0') {
                                            echo '<span class="opacity-50">—</span>';
                                        } else {
                                            echo Html::encode(mb_substr($desc, 0, 80)) . (mb_strlen($desc) > 80 ? '…' : '');
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center text-muted"><?= Html::encode($model->sort ?: '—') ?></td>
                                    <td class="text-center">
                                        <?php if ((int) $model->active === 1): ?>
                                            <span class="badge bg-indigo-soft text-indigo">
                                                <span class="status-dot status-dot--indigo me-1"></span>ใช้งาน
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                <span class="status-dot status-dot--muted me-1"></span>ปิดใช้
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <?= Html::a('<i class="fa-regular fa-eye"></i>', ['view', 'id' => $model->id, 'title' => 'รายละเอียดวิธีได้มา'], [
                                                'class' => 'btn btn-outline-secondary open-modal',
                                                'data' => ['size' => 'modal-md'],
                                                'title' => 'ดูรายละเอียด',
                                            ]) ?>
                                            <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $model->id, 'title' => 'แก้ไขวิธีได้มา'], [
                                                'class' => 'btn btn-outline-primary open-modal',
                                                'data' => ['size' => 'modal-md'],
                                                'title' => 'แก้ไข',
                                            ]) ?>
                                            <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['delete', 'id' => $model->id], [
                                                'class' => 'btn btn-outline-danger delete-item',
                                                'data-method' => 'post',
                                                'title' => 'ลบ',
                                            ]) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($dataProvider->pagination && $dataProvider->pagination->pageCount > 1): ?>
                    <div class="d-flex justify-content-center py-3 border-top">
                        <?= \yii\bootstrap5\LinkPager::widget([
                            'pagination' => $dataProvider->pagination,
                            'firstPageLabel' => 'หน้าแรก',
                            'lastPageLabel' => 'หน้าสุดท้าย',
                            'options' => ['class' => 'pagination pagination-sm mb-0'],
                        ]) ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php Pjax::end(); ?>
</div>
