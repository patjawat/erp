<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap5\LinkPager;

/**
 * @var yii\web\View $this
 * @var app\modules\inventoryV2\models\WarehouseSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $departmentNames id => name
 * @var array $reqCounts warehouse_id => count
 * @var array $imgUrls ref => image URL
 * @var \app\modules\hr\models\Employees[] $employees user_id => model
 */
$departmentNames = $departmentNames ?? [];
$reqCounts = $reqCounts ?? [];
$imgUrls = $imgUrls ?? [];
$employees = $employees ?? [];
$defaultImg = \Yii::getAlias('@web') . '/images/store1.jpg';

$this->title = 'ตั้งค่าคลังสินค้า';
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
<?= $this->render('@app/modules/inventoryV2/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4" style="font-family: 'Sarabun', sans-serif;">

    <!-- ลิงก์ด่วน -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-2">
                <div class="col-auto">
                    <a href="<?= Url::to(['/inventory-v2/stock-item/index']) ?>" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-tags"></i> จัดการรายการพัสดุ
                    </a>
                </div>
                <div class="col-auto">
                    <a href="<?= Url::to(['/inventory-v2/default/index']) ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-map"></i> เมนูนำทางระบบคลัง
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- การค้นหา -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0"><i class="bi bi-search me-2"></i>การค้นหา</h6>
        </div>
        <div class="card-body">
            <?= $this->render('_search_warehouse', ['model' => $searchModel]) ?>
        </div>
    </div>

    <!-- รายการคลัง -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center py-2">
            <h6 class="mb-0">
                <i class="bi bi-ui-checks me-2"></i>รายการคลัง
                <span class="badge bg-light text-dark ms-2"><?= number_format($dataProvider->getTotalCount(), 0) ?> รายการ</span>
            </h6>
            <?= Html::a('<i class="bi bi-plus-circle me-1"></i> สร้างคลังใหม่', ['/inventory-v2/warehouse/create', 'title' => 'สร้างคลังใหม่'], [
                'class' => 'btn btn-light btn-sm open-modal',
                'data' => ['size' => 'modal-xl'],
            ]) ?>
        </div>
        <div class="card-body">
            <?php if ($dataProvider->getTotalCount() === 0): ?>
                <p class="text-muted mb-0">ไม่พบรายการคลังสินค้า</p>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                    <?php foreach ($dataProvider->getModels() as $model):
                        $imgUrl = $imgUrls[$model->ref] ?? $defaultImg;
                        $deptName = $departmentNames[$model->department] ?? 'ไม่ระบุ';
                        $reqCount = $reqCounts[$model->id] ?? 0;
                    ?>
                        <div class="col">
                            <div class="card h-100 border shadow-sm setting-warehouse-card">
                                <div class="position-relative">
                                    <?= Html::img($imgUrl, [
                                        'class' => 'card-img-top object-fit-cover',
                                        'style' => 'height: 160px; object-fit: cover;',
                                        'alt' => $model->warehouse_name,
                                    ]) ?>
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm rounded-circle shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <?= Html::a('<i class="bi bi-pencil me-2"></i>แก้ไข', ['/inventory-v2/warehouse/update', 'id' => $model->id, 'title' => 'แก้ไขคลัง'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                                                </li>
                                                <li>
                                                    <?= Html::a('<i class="bi bi-trash me-2 text-danger"></i>ลบ', ['/inventory-v2/warehouse/delete', 'id' => $model->id], ['class' => 'dropdown-item delete-item']) ?>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title fw-bold mb-1"><?= Html::encode($model->warehouse_name) ?></h6>
                                    <p class="text-muted small mb-2"><?= Html::encode($deptName) ?></p>
                                    <div class="mb-2"><?= $model->viewWarehouseType() ?></div>
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-2">
                                        <div class="avatar-stack d-flex flex-wrap gap-1">
                                            <?php
                                            if (!empty($model->data_json['officer']) && is_array($model->data_json['officer'])) {
                                                foreach ($model->data_json['officer'] as $uid) {
                                                    $emp = $employees[$uid] ?? null;
                                                    if ($emp) {
                                                        echo '<a href="javascript:void(0);" class="me-1" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="' . Html::encode($emp->fullname ?? '') . '">';
                                                        echo Html::img($emp->ShowAvatar(), ['class' => 'avatar-sm rounded-circle shadow']);
                                                        echo '</a>';
                                                    }
                                                }
                                            }
                                            ?>
                                        </div>
                                        <?php if ($reqCount > 0): ?>
                                            <span class="badge rounded-pill bg-primary"><?= (int) $reqCount ?> รอดำเนินการ</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-auto pt-2">
                                        <?= Html::a('<i class="bi bi-shop me-1"></i> เลือกคลัง', ['/inventory-v2/warehouse/view', 'id' => $model->id], [
                                            'class' => 'btn btn-primary btn-sm w-100 select-warehouse-btn',
                                            'data' => ['title' => $model->warehouse_name, 'img' => $imgUrl],
                                        ]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($dataProvider->pagination && $dataProvider->pagination->pageCount > 1): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <?= LinkPager::widget([
                            'pagination' => $dataProvider->pagination,
                            'firstPageLabel' => 'หน้าแรก',
                            'lastPageLabel' => 'หน้าสุดท้าย',
                            'options' => ['class' => 'pagination pagination-sm'],
                        ]) ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$js = <<<'JS'
$('.select-warehouse-btn').on('click', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');
    var title = $(this).data('title');
    var img = $(this).data('img');
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'ยืนยันการเลือกคลังสินค้า?',
            text: 'คุณต้องการเลือก ' + title + ' หรือไม่?',
            imageUrl: img || null,
            imageHeight: 200,
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก',
        }).then(function (result) {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    } else {
        window.location.href = url;
    }
});
JS;
$this->registerJs($js, View::POS_END);
?>

<style>
.setting-warehouse-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.setting-warehouse-card:hover { transform: translateY(-2px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important; }
.avatar-stack .avatar-sm { width: 28px; height: 28px; object-fit: cover; }
.avatar-stack a { text-decoration: none; }
.bg-primary-subtle { background-color: #cfe2ff !important; }
</style>
