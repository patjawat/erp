<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\am\models\AssetItem;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetItemSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */


$this->title = 'บริหารทรัพย์สิน | กำหนดรหัสทรัพย์สิน';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title');?>
<i class="bi bi-folder-check fs-1"></i> กำหนดรหัสทรัพย์สิน
<?php $this->endBlock();?>


<?php Pjax::begin(['enablePushState' => false]);?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-item-center">
            <?php  echo $this->render('_search_list_item', ['model' => $searchModel]); ?>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <h5 class="card-title"><i class="bi bi-ui-checks text-primary"></i> จำนวน <span class="badge rounded-pill text-bg-primary"><?=number_format($dataProvider->getTotalCount(),0)?></span> รายการ</h5>
        <table class="table">
            <thead>
                <tr>
                    <th class="text-center" scope="col" style="width: 5%">#</th>
                    <th scope="col" style="width: 12%">รหัส FSN</th>
                    <th scope="col" style="width: 40%">ชื่อทรัพย์สิน</th>
                      <th scope="col">ประเภท</th>
                    <th scope="col">หมวดหมู่</th>
                    <th scope="col" style="width:5%" class="text-end">ราคากลาง</th>
                    <th class="text-center" scope="col" style="width: 8%">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider align-middle">
                <?php foreach($dataProvider->getModels() as $key => $item):?>
                <tr>
                    <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                    <td class="fw-semibold text-primary"><?=$item->fsn?></td>
                    <td><?=$item->title?></td>
                      <td><?=$item->assetType->title ?? '-'?></td>
                    <td><?=$item->category->title ?? '-'?></td>
                    <td class="fw-semibold text-end"><?= $item->price?></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-primary select-item" 
                        data-group="<?=$item->asset_group_id?>" 
                        data-type="<?=$item->asset_type_id?>" 
                        data-category="<?=$item->asset_category_id?>" 
                        data-code="<?=$item->id?>" 
                        data-fsn="<?=$item->fsn?>" 
                        data-title="<?=$item->title?>"
                        data-price="<?=$item->price?>"
                        data-fsn-next="<?php // $item->nextCode()?>"
                        >เลือก</button>
                    </td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
        <div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
        </div>
    </div>
</div>

<?php Pjax::end(); ?>

<?php
$js = <<< JS

$("body").on("click", ".select-item", function (e) {
    var group = $(this).data('group');
    var type = $(this).data('type');
    var category = $(this).data('category');
    var code = $(this).data('code');
    var fsn = $(this).data('fsn');
    var fsnNext = $(this).data('fsn-next');
    var title = $(this).data('title');
    var price = $(this).data('price');
    var totalPrice = (price * 1);

    Swal.fire({
        title: 'ยืนยันการเลือก?',
        text: "คุณต้องการเลือกทรัพย์สิน: " + title + " (" + code + ")",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'เลือก',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {


            let rowIndex = $("#item-table tbody tr").length;

                let row = `<tr>
                    <td><input type="text" name="items[\${rowIndex}][item_name]" value="\${title}" class="form-control"></td>
                    <td><input type="number" name="items[\${rowIndex}][qty]" value="1" class="form-control qty"></td>
                    <td><input type="number" step="0.01" name="items[\${rowIndex}][unit_price]" value="\${price}" class="form-control price"></td>
                    <td class="total text-end fw-semibold">\${totalPrice}</td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">ลบ</button></td>
                </tr>`;
                $("#item-table tbody").append(row);
                rowIndex++;

            $("#main-modal").modal("toggle");
        }
    });
});

    
JS;
$this->registerJs($js,View::POS_END);
?>


