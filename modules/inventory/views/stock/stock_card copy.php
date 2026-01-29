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
        <?php Pjax::begin()?>
        <div class="card border-0 shadow-sm">
            <div class="p-3">
                <?= $this->render('_search-v2', ['model' => $searchModel]); ?>
            </div>

            <ol class="list-group list-group">
                <?php foreach ($dataProvider->getModels() as $item): ?>

                        <a href="<?= Url::to(['/inventory/stock/view-stock-card','id' => $item->id])?>" class="list-group-item d-flex justify-content-between align-items-start view-stock">
                            <?php echo $item->product->Avatar() ?>
                            <span class="badge text-bg-primary rounded-pill"><?=$item->SumQty()?></span>
                        </a>

                <?php endforeach; ?>
            </ol>

        </div>
        <?php Pjax::end();?>
    </div>

    <div class="col-md-8 col-lg-9">

    <div id="viewStock"></div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h5 class="fw-bold mb-1">สีน้ำพลาสติก TOA (สีขาว)</h5>
                        <p class="text-muted small mb-0">หมวดหมู่: วัสดุก่อสร้าง | สถานที่เก็บ: A1-02</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <small class="text-muted">ยอดคงเหลือรวม</small>
                        <h3 class="fw-bold text-primary">120 <small class="fs-6 text-muted">ถัง</small></h3>
                    </div>
                </div>

                <h6 class="fw-bold mb-3">ประวัติ Stock Card</h6>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle stock-card-table">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 120px;">วันที่</th>
                                <th>เลขที่เอกสาร / รายการ</th>
                                <th style="width: 100px;">รับเข้า</th>
                                <th style="width: 100px;">จ่ายออก</th>
                                <th style="width: 120px;">คงเหลือ</th>
                                <th style="width: 80px;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">29/01/2026</td>
                                <td>เบิกจ่ายตามใบเบิก #REQ-102 (หน้างาน A)</td>
                                <td class="text-center text-muted">-</td>
                                <td class="text-center text-danger fw-bold">- 10</td>
                                <td class="text-end px-3">120</td>
                                <td class="text-center">
                                    <a href="#" class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal"><i class="bi bi-pencil-square"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center">25/01/2026</td>
                                <td>รับสินค้าจาก PO-2026-001</td>
                                <td class="text-center text-success fw-bold">+ 130</td>
                                <td class="text-center text-muted">-</td>
                                <td class="text-end px-3">130</td>
                                <td class="text-center">
                                    <a href="#" class="btn-edit"><i class="bi bi-pencil-square"></i></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">แก้ไขรายการ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small">รายการ/หมายเหตุ</label>
                        <input type="text" class="form-control" value="เบิกจ่ายตามใบเบิก #REQ-102">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">จำนวน</label>
                        <input type="number" class="form-control" value="10">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">ประเภท</label>
                        <select class="form-select">
                            <option value="in">รับเข้า</option>
                            <option value="out" selected>เบิกจ่าย</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary">บันทึก</button>
            </div>
        </div>
    </div>

<?php 
$js = <<< JS

$('.view-stock').click(function (e) { 
    e.preventDefault();
    var data = $(this);
    
    $.ajax({
        type: "get",
        url: data.href,
        dataType: "json",
        success: function (res) {
            $('#viewStock').html(res.content)
        }
    });
});
JS;
$this->registerJS($js,View::POS_END);
?>