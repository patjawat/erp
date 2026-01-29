<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$warehouse = Yii::$app->session->get('warehouse');
$this->title = $warehouse['warehouse_name'];
$warehouse = Yii::$app->session->get('warehouse');
$this->title = 'สต๊อก' . $warehouse['warehouse_name'];
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลังสินค้า', 'url' => ['/inventory']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs'][] = 'สต๊อก';

?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="layout-grid"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/inventory/menu', ['active' => 'stock']) ?>
<?php $this->endBlock(); ?>



<div class="row g-3">
    <div class="col-md-4 col-lg-3">
        <?php Pjax::begin() ?>
        <div class="card border-0 shadow-sm">
            <div class="p-3">
                <?= $this->render('_search-v2', ['model' => $searchModel]); ?>
            </div>

            <div style="max-height: 500px; overflow-y: auto;">
                <ol class="list-group list-group">
                    <?php foreach ($dataProvider->getModels() as $item): ?>
                        <a href="<?= Url::to(['/inventory/stock/view-stock-card', 'id' => $item->id]) ?>" class="list-group-item d-flex justify-content-between align-items-start view-stock">
                            <?php echo $item->product->Avatar() ?>
                            <span class="badge text-bg-primary rounded-pill"><?= $item->SumQty() ?></span>
                        </a>
                    <?php endforeach; ?>
                </ol>
            </div>

        </div>
        <?php Pjax::end(); ?>
    </div>

    <div class="col-md-8 col-lg-9">
        <?php // Pjax::begin(['id' => 'stock-pjax-container', 'timeout' => false, 'enablePushState' => false]); ?>
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
 <?php // Pjax::end(); ?>
    </div>
</div>

<?php
$js = <<< JS
// เก็บ URL ล่าสุดที่ผู้ใช้คลิกดูไว้ในตัวแปร global ของหน้าเว็บ
var lastStockUrl = '';

$(document).on('click', '.view-stock', function (e) { 
    e.preventDefault();

    // แสดง Loading ระหว่างรอข้อมูล
    $('#viewStock').html(`
        <div class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `);
    
    lastStockUrl = $(this).attr('href'); // บันทึก URL ไว้ที่นี่
    $.ajax({
        type: "get",
        url: lastStockUrl,
        dataType: "json",
        success: function (res) {
            $('#viewStock').html(res.content);
        }
    });
});

// ส่วนของ handleFormSubmit
handleFormSubmit('#form', null, async function(response) {
    if (response.status === 'success') {
        console.log('บันทึกสำเร็จ');
            $.ajax({
            type: "get",
            url: response.url,
            dataType: "json",
            success: function (res) {
                $('#viewStock').html(res.content);
            }
    });
        
        // ถ้ายูสเซอร์เคยคลิกดูสินค้าตัวไหนไว้ ให้ Pjax ไปโหลดตัวนั้นซ้ำ
        // if (lastStockUrl !== '') {
        //     $.pjax.reload({
        //         container: '#stock-pjax-container',
        //         url: lastStockUrl, // ระบุ URL ที่ต้องการให้โหลดใหม่
        //         push: false,
        //         replace: false,
        //         timeout: false
        //     });
        // } else {
        //     // ถ้ายังไม่เคยคลิกอะไรเลย แค่ reload ตามปกติ
        //     $.pjax.reload({container: '#stock-pjax-container'});
        // }
    }
});
JS;
$this->registerJS($js, View::POS_END);
?>