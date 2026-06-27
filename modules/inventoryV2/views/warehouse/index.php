<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap5\LinkPager;

$this->title = 'ตั้งค่าระบบคลัง';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง V2', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-building"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventoryV2/views/default/_menu_main', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'pjax-warehouse']); ?>
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h6 class="text-white mt-2"><i class="bi bi-search me-2"></i>การค้นหา</h6>
    </div>
    <div class="card-body">
        <?= $this->render('_search', ['model' => $searchModel]) ?>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center">
        <h6 class="text-white mt-2 mb-0">
            <i class="bi bi-ui-checks me-2"></i>จำนวนคลัง
            <span class="badge text-bg-light text-dark ms-2"><?= number_format($dataProvider->getTotalCount(), 0) ?> รายการ</span>
        </h6>
        <?= Html::a('<i class="bi bi-plus-circle me-1"></i> สร้างใหม่', ['/inventory-v2/warehouse/create', 'title' => 'สร้างคลังใหม่'], ['id' => 'addWarehouse', 'class' => 'btn btn-light btn-sm open-modal', 'data' => ['size' => 'modal-xl']]) ?>
    </div>
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col" style="width:50px">รหัส</th>
                    <th scope="col">ชื่อรายการ</th>
                    <th scope="col">แผนก/ฝ่าย</th>
                    <th scope="col">ประเภทคลัง</th>
                    <th scope="col">ผู้รับผิดชอบคลัง</th>
                    <th scope="col" style="width:180px">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr>
                        <td scope="row"><?= $model->id ?></td>
                        <td><?= Html::encode($model->warehouse_name) ?></td>
                        <td><?= Html::encode($model->departmentName()) ?></td>
                        <td><?= $model->viewWarehouseType() ?></td>
                        <td><?= $model->avatarStack() ?></td>
                        <td class="d-flex justify-content-center gap-2">
                            <?= Html::a('<i class="bi bi-pencil"></i>', ['/inventory-v2/warehouse/update', 'id' => $model->id, 'title' => 'แก้ไข'], ['class' => 'btn btn-warning btn-sm open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                            <?= Html::a('<i class="bi bi-sliders2"></i>', ['/inventory-v2/warehouse/stock-min-max', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm', 'title' => 'ตั้ง min/max วัสดุ', 'data-bs-toggle' => 'tooltip']) ?>
                            <?= Html::a('<i class="bi bi-trash"></i>', ['/inventory-v2/warehouse/delete', 'id' => $model->id], ['class' => 'btn btn-danger btn-sm delete-item']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($dataProvider->pagination && $dataProvider->pagination->pageCount > 1): ?>
            <div class="d-flex justify-content-center mt-3">
                <?= LinkPager::widget([
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
