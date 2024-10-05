<?php

use app\models\Categorise;
use kartik\depdrop\DepDrop;
use kartik\editable\Editable;
use kartik\popover\PopoverX;
use yii\bootstrap5\LinkPager;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

kartik\editable\EditableAsset::register($this);

/* @var yii\web\View $this */
/* @var app\modules\sm\models\ProductSearch $searchModel */
/* @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Products';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['enablePushState' => false]); ?>
<?php
// $this->registerCss('.popover-x {display:none !important;} .popover { display:none !important; } ');

?>

<?php echo $this->render('_search_product', ['searchModel' => $searchModel, 'model' => $model]); ?>


<?php if (count($dataProvider->getModels()) == 0) { ?>
<div class="row">
    <div class="row d-flex justify-content-center">
        <div class="col-5">
                <h4 class="text-center"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i> ไม่พบข้อมูล</h4>
        </div>
    </div>
</div>

<?php } else { ?>

<div class="table-responsive">
    <table class="table table-primary">
        <thead>
            <tr>
                <th scope="col">รายการ</th>
                <th scope="col" style="width:400px">หน่วย</th>
                <th scope="col"  style="width:90px">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody class="align-middle">
            <?php foreach ($dataProvider->getModels() as $item) { ?>
            <tr class="">
                <td scope="row"><?php echo $item->Avatar(); ?></td>
                <td>
                <?php
                    echo Editable::widget([
                        'model' => $item,
                        'asPopover' => false,
                        'attribute' => 'data_json[unit]', // แอตทริบิวต์ของโมเดลที่จะทำให้เป็น Editable
                        'inputType' => Editable::INPUT_SELECT2, // ใช้ Select2 เป็น input
                        'options' => [
                            'data' => ArrayHelper::map(Categorise::find()->where(['name' => 'unit'])->all(), 'title', 'title'), // ข้อมูลสำหรับ Select2 (อาจเป็นรายชื่อจากฐานข้อมูล)
                            'options' => ['placeholder' => 'Select an option...'],
                            'pluginOptions' => [
                                'dropdownParent' => '#main-modal',
                                'allowClear' => true, // เปิดใช้งานปุ่ม clear
                            ],
                        ],
                        'editableValueOptions' => [
                            'id' => 'unit-name-' . $item->id, // ระบุ id ของ Editable แต่ละอัน
                        ],
                        'formOptions' => ['action' => ['/sm/product/unit-update', 'id' => $item->id]], // การตั้งค่า action ที่จะเรียกใช้งานเมื่อบันทึก
                        'placement' => 'right', // ตำแหน่งของ popover
                        'valueIfNull' => 'Not set', // แสดงข้อความถ้าไม่มีค่า
                        'displayValueConfig' => [
                            '1' => 'Option 1',
                            '2' => 'Option 2',
                            '3' => 'Option 3',
                            null => 'No selection',
                        ],
                        // แสดงข้อความที่ใช้ในกรณีที่มีค่าเหล่านั้น
                    ]);

                // echo Editable::widget([
                //     'name'=>$item->title,
                //     'asPopover' => false,
                //     'value' => (isset($item->data_json['unit']) ? $item->data_json['unit'] : 'ไม่ระบุ'),
                //     'size' => PopoverX::SIZE_MEDIUM,
                // 'inputType' => Editable::INPUT_DEPDROP,
                // 'options' => [
                //     'type' => DepDrop::TYPE_SELECT2,
                //     'data' => ['aa' => 'ss','รีม' => 'รีม'],
                //     'options' => ['id'=>'subcat-id-p', 'placeholder' => 'Select subcat...'],
                //     'select2Options' => [
                //         'pluginOptions' => [
                //             'dropdownParent' => '#subcat-id-p-popover', // set this to "#<EDITABLE_ID>-popover" to ensure select2 renders properly within the popover
                //             'allowClear' => true,
                //         ]
                //     ],
                //     'pluginOptions'=>[
                //         'depends'=>['cat-id-p'],
                //         'url' => Url::to(['/site/subcat'])
                //     ]
                // ]
                // ]);
                ?>    
                <?php // (isset($item->data_json['unit']) ? '<span class="badge rounded-pill bg-success-subtle">'.$item->data_json['unit'].'</span>' : '<span class="badge rounded-pill bg-danger-subtle">ไม่ได้ตั้ง</span>')?></td>
                <td class="align-middle">
                    <?php echo Html::a('<i class="bi bi-bag-plus"></i> เลือก', ['/purchase/order/add-item', 'title' => $item->title, 'asset_item' => $item->id, 'code' => $model->code, 'order_id' => $model->id], ['class' => 'btn btn-sm btn-primary rounded-pill shadow text-center open-modal']); ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center">
                    <div class="text-muted">
                        <?php echo LinkPager::widget([
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

<?php } ?>
<?php
$js = <<< JS

    // \$('.product-add').click(function (e) { 
    //     e.preventDefault();
    //     var url = \$(this).attr('href')
    //     \$.ajax({
    //         type: "get",
    //         url: url,
    //         dataType: "json",
    //         success: function (res) {
                
    //         }
    //     });
    // });
    JS;
$this->registerJS($js);

?>
<?php Pjax::end(); ?>