<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetConditionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'สภาพครุภัณฑ์';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="receipt-text" class="me-2"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'work']) ?>
<?php $this->endBlock(); ?>

<div class="asset-condition-index">
    <?php Pjax::begin(['id' => 'pjax-asset-condition']); ?>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="text-white mb-0"><?= Html::encode($this->title) ?></h5>
                <small class="text-white-50">
                    <?= number_format($dataProvider->getTotalCount(), 0) ?> รายการ
                </small>
            </div>
            <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> สร้างใหม่', ['create', 'title' => 'Create Asset Condition'], [
                'class' => 'btn btn-light btn-sm open-modal',
                'data' => ['size' => 'modal-md'],
            ]) ?>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 80px">#</th>
                            <th scope="col">รหัส</th>
                            <th scope="col">ชื่อรายการ</th>
                            <th scope="col" style="width: 120px">Sort</th>
                            <th scope="col" style="width: 120px">สถานะ</th>
                            <th scope="col" style="width: 160px">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <?php foreach ($dataProvider->getModels() as $key => $model): ?>
                            <tr>
                                <td><?= (($dataProvider->pagination?->offset ?? 0) + $key + 1) ?></td>
                                <td class="fw-semibold text-primary"><?= Html::encode($model->id) ?></td>
                                <td><?= Html::encode($model->name) ?></td>
                                <td><?= Html::encode($model->sort_order) ?></td>
                                <td>
                                    <?php if ((int) $model->is_active === 1): ?>
                                        <span class="badge text-bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <?= Html::a('<i class="fa-regular fa-eye"></i>', ['view', 'id' => $model->id, 'title' => 'รายละเอียด Asset Condition'], [
                                            'class' => 'btn btn-outline-primary btn-sm open-modal',
                                            'data' => ['size' => 'modal-md'],
                                            'title' => 'ดูรายละเอียด',
                                        ]) ?>
                                        <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $model->id, 'title' => 'แก้ไข Asset Condition'], [
                                            'class' => 'btn btn-warning btn-sm open-modal',
                                            'data' => ['size' => 'modal-md'],
                                            'title' => 'แก้ไข',
                                        ]) ?>
                                        <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['delete', 'id' => $model->id], [
                                            'class' => 'btn btn-danger btn-sm delete-item',
                                            'data-method' => 'post',
                                            'data' => ['confirm' => 'ยืนยันลบรายการนี้ใช่หรือไม่?'],
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
                <div class="d-flex justify-content-center mt-3">
                    <?= \yii\bootstrap5\LinkPager::widget([
                        'pagination' => $dataProvider->pagination,
                        'firstPageLabel' => 'หน้าแรก',
                        'lastPageLabel' => 'หน้าสุดท้าย',
                        'options' => ['class' => 'pagination pagination-sm'],
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php Pjax::end(); ?>
</div>