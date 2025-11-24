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
                    <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
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

    Swal.fire({
        title: 'ยืนยันการเลือก?',
        text: "คุณต้องการเลือกทรัพย์สิน: " + title + " (" + code + ")",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'เลือก',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {


            // ตรวจสอบว่า plugin ผูกอยู่กับ element จริงไหม
console.log($('#asset_type_id').data('depdrop')); // ต้องไม่ undefined

if ($('#asset_type_id').data('depdrop')) {
    $('#asset_type_id').val(type).depdrop('change');
} else {
    console.error('DepDrop plugin ยังไม่ถูก initialize');
}

       // STEP 1: ผูก event depdrop:afterChange ที่ dropdown หมวดหมู่ (ตัวลูก) ก่อน
        $('#asset_category_id').one('depdrop:afterChange', function () {
            console.log('depdrop:afterChange fired');
            // เมื่อหมวดหมู่โหลดเสร็จแล้ว ค่อย set ค่า category และ trigger change ถ้าจำเป็น
            $('#asset_category_id').val(category).trigger('change');
        });

        // STEP 2: ตั้งค่า asset_type_id และสั่ง DepDrop โหลดข้อมูลหมวดหมู่
        setTimeout(() => {
            $('#asset_type_id').val(type).depdrop('change');
        }, 200);


            // ✅ STEP 3: อื่นๆ
            $('#asset-asset_name').val(title);
            if (fsn) {
                $('#asset-fsn_number').val(fsn);
            }

            $("#main-modal").modal("toggle");
        }
    });
});

    
JS;
$this->registerJs($js,View::POS_END);
?>


