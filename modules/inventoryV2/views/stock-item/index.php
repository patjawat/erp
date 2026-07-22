<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\widgets\DataSummaryWidget;

/** @var yii\web\View $this */
/** @var app\modules\inventoryV2\models\StockItemSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int|null $warehouseId */
/** @var array $balanceMap item_code => balance_qty */
/** @var array $warehouses */
$this->title = 'วัสดุ';
$this->params['breadcrumbs'][] = $this->title;
$warehouseId = $warehouseId ?? null;
$balanceMap = $balanceMap ?? [];
//  sql นำเข้าวัสดุจาก categorise
$sql = "INSERT INTO stock_item (
    ref,
    category_id,
    item_code, 
    item_name, 
    is_asset, 
    is_active, 
    data_json, 
    created_at
)
SELECT 
    ref,
    category_id,
    code,
    title,
    0,
    1,
    JSON_OBJECT(
        'unit_name', JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.unit'))
    ) AS data_json,
    UNIX_TIMESTAMP()
FROM categorise 
WHERE name = 'asset_item' 
AND group_id = 'MATER';
";
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings-icon lucide-settings">
            <path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915"></path>
            <circle cx="12" cy="12" r="3"></circle>
        </svg>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventoryV2/views/default/_menu_main', ['active' => 'stock-item']) ?>
<?php $this->endBlock(); ?>


<?php Pjax::begin(['id' => 'sm-container']); ?>
<div class="container-fluid px-3 px-md-4 py-3 py-md-4">
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="text-white mb-0"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-gear fs-5"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?= Html::a('<i class="bi bi-grid-fill me-1"></i>  ประเภทวัสดุ', ['//inventory-v2/stock-item-type', 'title' => '<i class="bi bi-grid-fill"></i> ประเภทวัสดุ'], ['class' => 'dropdown-item open-modal-x', 'data' => ['size' => 'modal-md', 'pjax' => false]]) ?>
                            <?= Html::a('<i class="bi bi-grid-fill me-1"></i>  หน่วยนับ', ['//inventory-v2/stock-item-unit', 'title' => 'หน่วยนับ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <?php echo $this->render('_search', ['model' => $searchModel, 'warehouses' => $warehouses ?? []]);
                    ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="text-white mb-0">
                        <i class="bi bi-ui-checks"></i> รายการ<?= $this->title ?>
                        <?= number_format($dataProvider->getTotalCount()) ?> รายการ
                    </h6>
                    <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/inventory-v2/stock-item/create', 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เพิ่มวัสดุใหม่'], ['class' => 'btn btn-light open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                </div>

                <div class="card-body">
                    <div class="table-responsive" style="min-height: 300px;">
                        <table class="table table-striped table-hover align-middle custom-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:30px">ลำดับ</th>
                                    <th class="text-center" style="min-width: 110px;">รหัสพัสดุ</th>
                                    <th class="text-center" style="min-width: 100px;">รูปภาพ</th>
                                    <th style="min-width: 350px;">รายการวัสดุ</th>
                                    <th class="text-center" style="min-width: 150px;">ประเภทวัสดุ</th>
                                    <th style="min-width: 100px;">หน่วยนับ</th>
                                    <th style="min-width: 120px;">บัญชีนวัตกรรม</th>
                                    <th class="text-center" style="min-width: 100px;">สถานะ</th>

                                    <!--ใช้ min-width ป้องกันการบีบ -->
                                    <th class="text-center" style="min-width: 145px;">จัดการ</th>
                                </tr>
                            </thead>

                            <tbody class="table-group-divider">
                                <?php if ($dataProvider->getTotalCount() > 0): ?>
                                    <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                                        <tr>
                                            <td class="text-center">
                                                <?= (($dataProvider->pagination->offset + 1) + $key) ?>
                                            </td>

                                            <td class="text-center ps-4 fw-bold text-primary"> <?= $item->item_code ?></td>
                                            <td class="text-center">
                                                <?php echo Html::img($item->ShowImg(), ['class' => 'img-thumbnail', 'style' => 'width:100px']) ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?= $item->item_name; ?></div>
                                                <small class="text-muted">หมวดหมู่: <?= $item->categoryType->title ?? '-' ?></small>
                                            </td>
                                            <td class="text-center"><?= $item->data_json['metter_type'] ?? '-' ?></td>
                                            <td><?= $item->data_json['unit_name'] ?? $item->getUnitName() ?: '-' ?></td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-flex justify-content-center">
                                                    <input class="form-check-input set-active" type="checkbox" data-id="<?= $item->id ?>"
                                                        <?= $item->is_innovation == 1 ? 'checked' : '' ?>>
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                <div class="form-check form-switch d-flex justify-content-center">
                                                    <input class="form-check-input set-active" type="checkbox" data-id="<?= $item->id ?>"
                                                        <?= $item->is_active == 1 ? 'checked' : '' ?>>
                                                </div>
                                            </td>

                                            <td class="text-center text-nowrap">
                                                <?= Html::a('<i class="fa-solid fa-eye"></i>', ['//inventory-v2/stock-item/view', 'id' => $item->id], [
                                                    'class' => 'btn btn-sm btn-info open-modal',
                                                    'title' => 'แสดง',
                                                    'data' => ['size' => 'modal-lg'],
                                                ]) ?>
                                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['//inventory-v2/stock-item/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไขวัสดุ'], [
                                                    'class' => 'btn btn-sm btn-warning open-modal',
                                                    'title' => 'แก้ไข',
                                                    'data' => ['size' => 'modal-lg'],
                                                ]) ?>
                                                <?= Html::a('<i class="fa-solid fa-trash"></i>', ['//inventory-v2/stock-item/delete', 'id' => $item->id], [
                                                    'class' => 'btn btn-sm btn-danger delete-item',
                                                    'title' => 'ลบทิ้ง',
                                                ]) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center justify-content-center">
                                                <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                                                <h5 class="text-muted mb-2">ไม่พบข้อมูลพัสดุ</h5>
                                                <p class="text-muted mb-4">ไม่พบรายการที่ตรงกับการค้นหาของคุณ</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 pt-3 border-top">
                        <?= DataSummaryWidget::widget([
                            'dataProvider' => $dataProvider,
                            'pagerOptions' => [
                                'options' => ['class' => 'pagination pagination-sm mb-0 justify-content-end'],
                                'prevPageLabel' => '<i class="bi bi-chevron-left"></i>',
                                'nextPageLabel' => '<i class="bi bi-chevron-right"></i>',
                                'maxButtonCount' => 3,
                            ],
                        ]) ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php Pjax::end(); ?>

<div class="modal fade" id="stock-item-import-zip-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="stock-item-import-zip-form" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white mb-0">
                        <i class="fa-solid fa-file-zipper me-2"></i>นำเข้าข้อมูลวัสดุจาก ZIP
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="stock-item-import-zip-file">ไฟล์ ZIP</label>
                        <input type="file" class="form-control" id="stock-item-import-zip-file" name="zip_file" accept=".zip,application/zip" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-label fw-semibold">รูปแบบการนำเข้า</div>
                        <div class="vstack gap-2">
                            <label class="border rounded-2 p-3 mb-0">
                                <span class="d-flex gap-2 align-items-start">
                                    <input class="form-check-input mt-1" type="radio" name="mode" value="merge" checked>
                                    <span>
                                        <span class="d-block fw-semibold">นำเข้ารวมกับข้อมูลเดิม</span>
                                        <span class="text-muted small">ถ้ารหัสวัสดุซ้ำ จะอัปเดตข้อมูลและรูปภาพตามไฟล์ ZIP</span>
                                    </span>
                                </span>
                            </label>
                            <label class="border rounded-2 p-3 mb-0">
                                <span class="d-flex gap-2 align-items-start">
                                    <input class="form-check-input mt-1" type="radio" name="mode" value="replace">
                                    <span>
                                        <span class="d-block fw-semibold text-danger">ลบข้อมูลเดิมทั้งหมด แล้วนำเข้าจากไฟล์นี้</span>
                                        <span class="text-muted small">ลบ master วัสดุเดิมในขอบเขต Inventory V2 ก่อนนำเข้า</span>
                                    </span>
                                </span>
                            </label>
                            <label class="border rounded-2 p-3 mb-0">
                                <span class="d-flex gap-2 align-items-start">
                                    <input class="form-check-input mt-1" type="radio" name="mode" value="disable">
                                    <span>
                                        <span class="d-block fw-semibold">ปิดการใช้งานข้อมูลเดิม แล้วนำเข้าจากไฟล์นี้</span>
                                        <span class="text-muted small">ข้อมูลเดิมจะถูกตั้งเป็นไม่ active ส่วนข้อมูลใน ZIP จะถูกเพิ่มหรืออัปเดต</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div id="stock-item-import-zip-result" class="alert d-none mb-0"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-file-import me-1"></i> นำเข้า
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
$chageActiveUrl = Url::to(['/inventory-v2/stock-item/set-active']);
$importZipUrl = Url::to(['/inventory-v2/stock-item/import-zip']);
$js = <<< JS
        // ส่งออก Excel เปิดแท็บใหม่ (ใช้ window.open เพื่อเลี่ยง pjax ดัก click ในคอนเทนเนอร์)
        $("body").on("click", ".export-excel-btn", function (e) {
          e.preventDefault();
          window.open($(this).data('href'), '_blank');
        });

        $("body").on("click", ".export-zip-btn", function (e) {
          e.preventDefault();
          window.open($(this).data('href'), '_blank');
        });

        $("body").on("submit", "#stock-item-import-zip-form", function (e) {
          e.preventDefault();

          var form = this;
          var formData = new FormData(form);
          var submitBtn = $(form).find('button[type="submit"]');
          var originalBtnHtml = submitBtn.html();
          var resultBox = $("#stock-item-import-zip-result");

          submitBtn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span> กำลังนำเข้า...');
          resultBox.addClass("d-none").removeClass("alert-success alert-danger").empty();

          $.ajax({
            url: "$importZipUrl",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (res) {
              if (res.status === "success") {
                var s = res.summary || {};
                var message = "นำเข้าสำเร็จ: สร้างใหม่ " + (s.created || 0)
                  + " รายการ, อัปเดต " + (s.updated || 0)
                  + " รายการ, รูปภาพ " + (s.uploads || 0)
                  + " ไฟล์";
                resultBox.removeClass("d-none alert-danger").addClass("alert-success").text(message);
                if (typeof success === "function") {
                  success();
                }
                $.pjax.reload({container: res.container || "#sm-container", history: false});
              } else {
                resultBox.removeClass("d-none alert-success").addClass("alert-danger").text(res.message || "นำเข้าไม่สำเร็จ");
              }
            },
            error: function (xhr) {
              var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "นำเข้าไม่สำเร็จ";
              resultBox.removeClass("d-none alert-success").addClass("alert-danger").text(message);
            },
            complete: function () {
              submitBtn.prop("disabled", false).html(originalBtnHtml);
            }
          });
        });

        $("body").on("change", ".set-active", function (e) {

          var id = $(this).data('id');
          $.ajax({
            type: "post",
            url: "$chageActiveUrl",
            data:{
              id:id
            },
            dataType: "json",
            success: function (res) {
              if(res.status == 'success'){
              success()
                 $.pjax.reload({container:res.container, history:false});
              }
            }
          });
          
                        if ($(this).is(':checked')) {
                            // alert('Checkbox is checked!');
                        } else {
                            // alert('Checkbox is unchecked!');
                        }
                    });

              
JS;
$this->registerJS($js)
?>
