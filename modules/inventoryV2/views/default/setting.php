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
<div class="container-fluid px-3 px-md-4">
    <div class="row g-3">
        <div class="col-12">
            <?= $this->render('@app/modules/inventoryV2/views/default/_menu_setting', ['active' => 'setting']) ?>
        </div>
    </div>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4 px-3 px-md-4 setting-warehouse-page" style="font-family: 'Sarabun', sans-serif;">
    <!-- Inline toolbar: ค้นหา + สร้างคลัง -->
    <div class="setting-toolbar mb-3 d-flex flex-wrap align-items-end justify-content-between gap-2">
        <div class="flex-grow-1">
            <?= $this->render('_search_warehouse', ['model' => $searchModel]) ?>
        </div>
        <div>
            <?= Html::a('<i class="bi bi-plus-circle me-1"></i> สร้างคลังใหม่', ['/inventory-v2/warehouse/create', 'title' => 'สร้างคลังใหม่'], [
                'class' => 'btn btn-primary btn-sm open-modal',
                'data' => ['size' => 'modal-xl'],
            ]) ?>
        </div>
    </div>

    <!-- รายการคลัง -->
    <div class="row g-3">
        <div class="col-12">
            <div class="setting-list-header d-flex flex-wrap align-items-baseline justify-content-between gap-2 mb-2">
                <h6 class="mb-0 text-body fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-shop text-primary"></i>
                    รายการคลัง
                    <span class="badge rounded-pill text-bg-secondary fw-normal"><?= number_format($dataProvider->getTotalCount(), 0) ?></span>
                </h6>
            </div>
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
                            <div class="card h-100 border-0 shadow-sm setting-warehouse-card">
                                <div class="position-relative">
                                    <?= Html::img($imgUrl, [
                                        'class' => 'card-img-top object-fit-cover',
                                        'style' => 'height: 160px; object-fit: cover;',
                                        'alt' => $model->warehouse_name,
                                    ]) ?>
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm rounded-circle shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="เมนูคลัง">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <?= Html::a('<i class="bi bi-pencil me-2"></i>แก้ไขข้อมูลคลัง', ['/inventory-v2/warehouse/update', 'id' => $model->id, 'title' => 'แก้ไขคลัง'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <?= Html::a('<i class="bi bi-trash me-2 text-danger"></i>ลบคลัง', ['/inventory-v2/warehouse/delete', 'id' => $model->id], ['class' => 'dropdown-item delete-item']) ?>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <?php if ($reqCount > 0): ?>
                                        <span class="badge rounded-pill text-bg-primary position-absolute top-0 start-0 m-2 shadow-sm">
                                            <i class="bi bi-bell me-1"></i><?= (int) $reqCount ?> รอดำเนินการ
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title fw-bold mb-1"><?= Html::encode($model->warehouse_name) ?></h6>
                                    <p class="text-muted small mb-2"><?= Html::encode($deptName) ?></p>
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                        <?= $model->viewWarehouseType() ?>
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
                                    </div>
                                    <div class="mt-auto pt-2 d-grid gap-2">
                                        <?= Html::a('<i class="bi bi-sliders2 me-1"></i> ตั้ง Min/Max วัสดุ', ['/inventory-v2/warehouse/stock-min-max', 'id' => $model->id], [
                                            'class' => 'btn btn-primary btn-sm fw-semibold',
                                            'title' => 'กำหนดจุดสั่งซื้อขั้นต่ำ/สูงของวัสดุในคลังนี้',
                                        ]) ?>
                                        <?= Html::a('เข้าใช้คลัง <i class="bi bi-arrow-right ms-1"></i>', ['/inventory-v2/warehouse/view', 'id' => $model->id], [
                                            'class' => 'btn btn-link btn-sm text-decoration-none text-secondary select-warehouse-btn p-1',
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
.setting-warehouse-page .setting-warehouse-card {
    transition: transform 180ms ease-out, box-shadow 180ms ease-out;
    border-radius: 12px;
    overflow: hidden;
}
.setting-warehouse-page .setting-warehouse-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.6rem 1.25rem rgba(0,0,0,0.08) !important;
}
.setting-warehouse-page .setting-warehouse-card .btn-primary { min-height: 38px; }
.setting-warehouse-page .avatar-stack .avatar-sm { width: 26px; height: 26px; object-fit: cover; }
.setting-warehouse-page .avatar-stack a { text-decoration: none; }
.setting-toolbar .warehouse-search .form-control,
.setting-toolbar .warehouse-search .form-select { min-height: 34px; }
@media (prefers-reduced-motion: reduce) {
    .setting-warehouse-page .setting-warehouse-card,
    .setting-warehouse-page .setting-warehouse-card:hover {
        transition: none;
        transform: none;
    }
}
</style>
