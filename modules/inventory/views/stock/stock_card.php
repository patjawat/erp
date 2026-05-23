<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $warehouseOptions */
/** @var int|null $currentWarehouseId */

$warehouseName = $currentWarehouseId
    ? ($warehouseOptions[$currentWarehouseId] ?? '')
    : '(ยังไม่ได้เลือกคลัง)';

$this->title = 'สต๊อกการ์ด — ' . $warehouseName;
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลังสินค้า', 'url' => ['/inventory']];
$this->params['breadcrumbs'][] = 'สต๊อกการ์ด';
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="id-card"></i>
        สต๊อกการ์ด
        <small class="text-muted fw-normal ms-2"><?= Html::encode($warehouseName) ?></small>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventory/menu_dashbroad', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<!-- WAREHOUSE PICKER -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-3">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-12 col-md-6">
                <label class="form-label small mb-1"><i class="fa-solid fa-warehouse"></i> เลือกคลัง</label>
                <?= Select2::widget([
                    'name' => 'warehouse_id',
                    'value' => $currentWarehouseId,
                    'data' => $warehouseOptions,
                    'options' => [
                        'placeholder' => '-- ดูทุกคลัง --',
                        'id' => 'wh-picker',
                    ],
                    'pluginOptions' => ['allowClear' => true],
                ]) ?>
            </div>
            <div class="col-12 col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-magnifying-glass"></i> โหลดรายการสินค้า
                </button>
            </div>
            <div class="col-12 col-md-3 text-muted small">
                <i class="fa-solid fa-circle-info"></i>
                เลือกคลังก่อน → ระบบจะแสดงรายการสินค้าให้คลิกดูสต๊อกการ์ด
            </div>
        </form>
    </div>
</div>

<?php if (!$currentWarehouseId): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i data-lucide="warehouse" style="width:64px; height:64px; opacity:0.3;"></i>
            <h5 class="text-muted fw-light mt-3">กรุณาเลือกคลังที่ต้องการดูสต๊อกการ์ด</h5>
            <p class="text-secondary small">เมื่อเลือกคลังแล้ว ระบบจะแสดงรายการสินค้าในคลังนั้นให้คลิกดูประวัติ</p>
        </div>
    </div>
<?php else: ?>
<div class="row g-3">
    <div class="col-md-4 col-lg-3">
        <?php Pjax::begin() ?>
        <div class="card border-0 shadow-sm">
            <div class="p-3">
                <?= $this->render('_search-v2', ['model' => $searchModel]); ?>
            </div>

            <div style="max-height: 600px; overflow-y: auto;">
                <ol class="list-group list-group-flush">
                    <?php foreach ($dataProvider->getModels() as $item): ?>
                        <a href="<?= Url::to(['/inventory/stock/view-stock-card',
                                'id' => $item->id,
                                'warehouse_id' => $currentWarehouseId]) ?>"
                           class="list-group-item d-flex justify-content-between align-items-start view-stock">
                            <?= $item->product->Avatar() ?>
                            <span class="badge text-bg-primary rounded-pill"><?= $item->SumQty() ?></span>
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($dataProvider->getModels())): ?>
                        <div class="text-center text-muted p-3 small">— ไม่พบรายการในคลังนี้ —</div>
                    <?php endif; ?>
                </ol>
            </div>
        </div>
        <?php Pjax::end(); ?>
    </div>

    <div class="col-md-8 col-lg-9">
        <div id="viewStock">
            <div class="card border-0 shadow-sm d-flex justify-content-center align-items-center text-center" style="min-height: 400px; background-color: #f8f9fa;">
                <div class="card-body">
                    <div class="mb-3 text-muted">
                        <i data-lucide="mouse-pointer-click" style="width: 64px; height: 64px; opacity: 0.3;"></i>
                    </div>
                    <h5 class="text-muted fw-light">กรุณาเลือกรายการสต๊อก</h5>
                    <p class="text-secondary small">คลิกเลือกรายการสินค้าทางด้านซ้ายเพื่อดูประวัติและรายละเอียดสต๊อกการ์ด</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$js = <<< JS
var lastStockUrl = '';

// auto-submit เมื่อเปลี่ยนคลัง
$('#wh-picker').on('change', function() {
    $(this).closest('form').trigger('submit');
});

$(document).on('click', '.view-stock', function (e) {
    e.preventDefault();
    $('#viewStock').html(`
        <div class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `);
    lastStockUrl = $(this).attr('href');
    $.ajax({
        type: "get",
        url: lastStockUrl,
        dataType: "json",
        success: function (res) {
            $('#viewStock').html(res.content);
        }
    });
});

handleFormSubmit('#form', null, async function(response) {
    if (response.status === 'success') {
        $.ajax({
            type: "get",
            url: response.url,
            dataType: "json",
            success: function (res) {
                $('#viewStock').html(res.content);
            }
        });
    }
});
JS;
$this->registerJS($js, View::POS_END);
?>
