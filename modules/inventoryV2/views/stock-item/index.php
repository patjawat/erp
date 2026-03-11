<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap5\LinkPager;

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
<?= Html::a('<i class="bi bi-arrow-left me-1"></i> ย้อนกลับ', ['/inventory-v2/default/index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
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
                                    <th class="text-center" style="min-width: 172px;">จำนวนคงเหลือ<?= $warehouseId ? ' (คลังที่เลือก)' : '' ?></th>
                                    <th style="min-width: 120px;">บัญชีนวัตกรรม</th>
                                    <th class="text-center" style="min-width: 100px;">จำนวนสูงสุด</th>
                                    <th class="text-center" style="min-width: 100px;">จำนวนต่ำสุด</th>
                                    <th class="text-center" style="min-width: 100px;">สถานะ</th>

                                    <!--ใช้ min-width ป้องกันการบีบ -->
                                    <th class="text-center" style="min-width: 130px;">จัดการ</th>
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
                                            <td><?= $item->data_json['unit'] ?? $item->getUnitName() ?: '-' ?></td>
                                            <td class="text-center">
                                                <?php if ($warehouseId): ?>
                                                    <?php
                                                    $bal = (float)($balanceMap[$item->item_code] ?? 0);
                                                    $minQ = $item->min_qty !== null && $item->min_qty !== '' ? (float)$item->min_qty : null;
                                                    $maxQ = $item->max_qty !== null && $item->max_qty !== '' ? (float)$item->max_qty : null;
                                                    $hasMin = $minQ !== null && $minQ > 0;
                                                    $hasMax = $maxQ !== null && $maxQ > 0;
                                                    if ($hasMin && $bal < $minQ) {
                                                        $statusClass = 'text-danger';
                                                        $statusIcon = 'bi-arrow-down-circle-fill';
                                                        $statusTitle = 'ต่ำกว่ากำหนด (ยอด < Min)';
                                                    } elseif ($hasMax && $bal > $maxQ) {
                                                        $statusClass = 'text-warning';
                                                        $statusIcon = 'bi-arrow-up-circle-fill';
                                                        $statusTitle = 'มากกว่ากำหนด (ยอด > Max)';
                                                    } elseif ($hasMin || $hasMax) {
                                                        $statusClass = 'text-success';
                                                        $statusIcon = 'bi-check-circle-fill';
                                                        $statusTitle = 'พอดี';
                                                    } else {
                                                        $statusClass = 'text-muted';
                                                        $statusIcon = 'bi-dash-circle';
                                                        $statusTitle = 'ยังไม่ได้กำหนด Min/Max';
                                                    }
                                                    $numberClass = $bal > 0 ? 'text-dark' : $statusClass;
                                                    ?>
                                                    <span title="<?= htmlspecialchars($statusTitle) ?>">
                                                        <i class="bi <?= $statusIcon ?> me-1 <?= $statusClass ?>"></i>
                                                        <span class="fw-bold <?= $numberClass ?>"><?= number_format($bal, 2) ?></span>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-flex justify-content-center">
                                                    <input class="form-check-input set-active" type="checkbox" data-id="<?= $item->id ?>"
                                                        <?= $item->is_innovation == 1 ? 'checked' : '' ?>>
                                                </div>
                                            </td>

                                            <td class="text-center"><?= $item->max_qty ?></td>
                                            <td class="text-center"><?= $item->min_qty ?></td>

                                            <td class="text-center">
                                                <div class="form-check form-switch d-flex justify-content-center">
                                                    <input class="form-check-input set-active" type="checkbox" data-id="<?= $item->id ?>"
                                                        <?= $item->is_active == 1 ? 'checked' : '' ?>>
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                        type="button" data-bs-toggle="dropdown">
                                                        จัดการ
                                                    </button>

                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><?= Html::a('<i class="fa-solid fa-eye me-1"></i> แสดง', ['//inventory-v2/stock-item/view', 'id' => $item->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                                                        <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['//inventory-v2/stock-item/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไขวัสดุ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                                                        <li><?= Html::a('<i class="fa-solid fa-trash me-1"></i> ลบทิ้ง', ['//inventory-v2/stock-item/delete', 'id' => $item->id], ['class' => 'dropdown-item delete-item']) ?></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="12" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center justify-content-center">
                                                <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                                                <h5 class="text-muted mb-2">ไม่พบข้อมูลพัสดุ</h5>
                                                <p class="text-muted mb-4">ไม่พบรายการที่ตรงกับการค้นหาของคุณ</p>
                                                <?= Html::a(
                                                    '<i class="fa-solid fa-circle-plus me-2"></i> สร้างพัสดุใหม่',
                                                    ['/inventory-v2/stock-item/create', 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เพิ่มวัสดุใหม่'],
                                                    ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]
                                                ) ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($dataProvider->getTotalCount() > 0 && $dataProvider->pagination->pageCount > 1): ?>
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
        </div>
    </div>
</div>

<?php Pjax::end(); ?>

<?php
$chageActiveUrl = Url::to(['/inventory-v2/stock-item/set-active']);
$js = <<< JS
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
