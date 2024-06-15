<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
use unclead\multipleinput\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;
use app\modules\am\models\Asset;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */

$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>

<?php $form = ActiveForm::begin(); ?>


                                <?php // Html::submitButton('<i class="bi bi-check2-circle"></i> ดำเนินการต่อ', ['class' => 'btn btn-success waves-effect waves-light']) ?>







                                <?= $form->field($model, 'name')->hiddenInput(['value'=>'purchase_order'])->label(false) ?>


<div class="row">
    <div class="col-lg-8 col-xl-9">
        <div class="card">
            <div class="card-header py-3 bg-default rounded-top">
                <h5 class=" mb-0">ข้อมูลจัดซื้อจัดจ้าง</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">

                <?php

echo $form->field($model,'schedule')->widget(MultipleInput::class,[
    'allowEmptyList'    => false,
    'enableGuessTitle'  => true,
    'addButtonPosition' => MultipleInput::POS_HEADER,
    'addButtonOptions' => [
        'class' => ' btn-sm btn btn-success',
        'label' => '<i class="bi bi-plus fw-bold" ></i>',
    ],
    'removeButtonOptions' => [
        'class' => 'btn-sm btn btn-danger',
        'label' => '<i class="bi bi-x fs-5 fw-bold"></i>' // also you can use html code
    ],
    'columns' => [
        [
            'name' => 'id',
            'title' => 'ID',
            'enableError' => true,
            'type' => MultipleInputColumn::TYPE_HIDDEN_INPUT,
            
        ],
        [
            'name'  => 'product_id',
            'type' => Select2::class,
            'title' => 'รายการ',
            'headerOptions' => [
                'class' => 'table-light',// กำหนดสไตล์ให้กับพื้นหลังของ label
            ],
            'options' => [
                'data' => ArrayHelper::map(array_map(function ($asset) {
                    $data = json_decode($asset['data_json'], true);
                    // return ['id' => $asset['id'], 'name' => $data['name']];
                }, Asset::find()->asArray()->all()), 'id', 'name'),
                'pluginEvents' => [
                    'change' => 'function() { 
                        var id = $(this).val();
                        fetch("deteil?id=" + id)
                            .then(res => res.json())
                            .then(json => {
                                console.log($(this).closest("tr").find("input[name*=\'amount\']").val());
                                //$("#Inventory-schedule-" + id + "-detail").text(json.description);
                                //$("#Inventory-schedule-" + id + "-price").text(json.price);
                                $(this).closest("tr").find("input[name*=\'price\']").val(json.price);
                                $(this).closest("tr").find("input[name*=\'detail\']").val(json.data_json.detail);
                                $(this).closest("tr").find("input[name*=\'total\']").val($(this).closest("tr").find("input[name*=\'amount\']").val() * json.price);
                            });
                    }',
                ],
                'pluginOptions' => [
                    'allowClear' => true, 
                    'placeholder' => 'เลือกสินค้า......',
                    'style' => 'width: 150px;'
                ],
            ]
 /*            'options' => [
                'prompt' => 'เลือกสินค้า',
                'style' => 'width:150px;',
                'onchange' => <<< JS
                var id = $(this).closest("select").val()
                fetch('deteil?id=' + id)
                .then(res=>res.json())
                .then(json=> {
                    console.log(json);
                    //$("#Inventory-schedule-" + id + "-detail").text(json.description);
                    //$("#Inventory-schedule-" + id + "-price").text(json.price);
                    $(this).closest("tr").find("input[name*=\'price\']").val(json.price)
                   $(this).closest("tr").find("input[name*=\'detail\']").val(json.data_json.detail)
                    $(this).closest("tr").find("input[name*=\'total\']").val($(this).closest("tr").find("input[name*=\'qty\']").val() * json.price);



                    
                })
                JS, 
            ] */
        ],
        [
            'name'  => 'detail',
            'options' => [
                'readonly' => true,
                'style' => 'background: none; border: none; width:400px;',
                'disabled' => 'disabled' // กำหนดให้ input field เป็น readonly
            ],
            'headerOptions' => [
                'class' => 'table-light', // กำหนดสไตล์ให้กับพื้นหลังของ label
            ],
            'title' => 'รายละเอียดเพิ่มเติม',
        ],
        [	
            'name'  => 'price',
            'title' => 'ราคา',
            'options' => [
                'readonly' => true,
                'style' => 'background: none; border: none; width: 100px;', // กำหนดให้ input field เป็น readonly
            ],
            'headerOptions' => [
                'class' => 'table-light', // กำหนดสไตล์ให้กับพื้นหลังของ label
            ],
        ],
        [
            'name'  => 'amount',
            'title' => 'จำนวน',
            'defaultValue' => 1,
            'options' => [
                'type' => 'number',
                'class' => 'input-priority ',
                'onchange' => <<< JS
                    $(this).closest("tr").find("input[name*=\'total\']").val($(this).closest("tr").find("input[name*=\'amount\']").val() * $(this).closest("tr").find("input[name*=\'price\']").val());
                JS,
            ],
            'headerOptions' => [
                'class' => 'table-light',
                'style' => 'width: 150px;', // กำหนดสไตล์ให้กับพื้นหลังของ label
            ],
            'inputTemplate' => '<div class="input-group"><button class="btn btn-primary decrement-btn" type="button">-</button>{input}<button class="btn btn-primary increment-btn" type="button">+</button></div>',
        ],
        [
            'name'  => 'total',
            'title' => 'Total',
            'options' => [
                'readonly' => true, 
                'style' => 'background: none; border: none;',
                'disabled' => 'disabled'// กำหนดให้ input field เป็น readonly
            ],
            'headerOptions' => [
                'class' => 'table-light', // กำหนดสไตล์ให้กับพื้นหลังของ label
            ],
            'enableError' => true,
        ],
        
    ]

])->label(false);

?>
                </div>
                <div class="row mt-4">
                    <div class="col-sm-6">
                        <a href="ecommerce-product.html" class="btn btn-secondary waves-effect waves-light"
                            data-effect="wave">
                            <i class="fa-solid fa-trash-can"></i> ยกเลิกใบสั้งซื้อ </a>
                    </div> <!-- end col -->
                    <div class="col-sm-6">
                        <div class="text-sm-end mt-2 mt-sm-0">
                                <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ดำเนินการต่อ', ['class' => 'btn btn-success waves-effect waves-light']) ?>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row-->
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-xl-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">#เลขที่ขอ <?= $form->field($model, 'id')->textInput(['maxlength' => true]) ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table mb-0 table-borderless table-no-head">
                        <tbody>
                            <tr>
                                <td>Grand Total :</td>
                                <td>$1,857</td>
                            </tr>
                            <tr>
                                <td>Discount : </td>
                                <td class="text-danger">- $157</td>
                            </tr>
                            <tr>
                                <td>Shipping Charge :</td>
                                <td>$25</td>
                            </tr>
                            <tr>
                                <td>Estimated Tax : </td>
                                <td>$19.22</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Total :</td>
                                <td class="fw-bold">$1744.22</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-warning mt-3 fs-13" role="alert" id="dis-coupon">
                    สถานะ <strong>ขอเบิก</strong>
                </div>
                <div class="alert alert-info mt-3" role="alert" id="coupon-applied" style="display: none;">
                    Coupon Applied
                </div>
                
             <div class="d-grid gap-2">
                <button
                    type="button"
                    name=""
                    id=""
                    class="btn btn-primary"
                >
                <i class="fa-solid fa-print"></i> พิมพ์
                </button>
                <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-success waves-effect waves-light']) ?>

             </div>
             
                <!-- end table-responsive -->
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <div class="d-flex mb-2 mt-0">
                    <img class="avatar rounded-circle" src="/img/patjwat2.png" width="20" alt="">
                    <div class="flex-grow-1 overflow-hidden">
                        <h6 class="mb-0 text-truncate">
                            <a href="/hr/workgroup/view?id=4670" class="text-dark">ผู้ขอเบิก
                            </a>
                        </h6>
                        <p class="text-muted mb-0">นายปัจวัฒน์ ศรีบุญเรือง</p>
                    </div>
                </div>
             <?=Html::a('<i class="fa-solid fa-user-tag"></i>',['/hr/employees/select-emp'],['class' => 'open-modal','data' => ['size' => 'modal-md']])?>
            </div>
            <div class="card-body">
                <h6 class="fs-16 fw-bold mb-3">
                    <i class="bx bx-mobile-alt align-middle fs-sm text-primary"></i>
                    <span class="align-middle">โทร : 0909748044</span>
                </h6>
                <!-- <p class="mb-0"></p> -->
            </div>
        </div>
        <!-- end card -->
    </div>
</div>
<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
    $(document).on('click', '.increment-btn', function() {
        var input = $(this).closest('.input-group').find('input');
        var value = parseInt(input.val());
        input.val(value + 1);
        $(this).closest("tr").find("input[name*=\'total\']").val($(this).closest("tr").find("input[name*=\'amount\']").val() * $(this).closest("tr").find("input[name*=\'price\']").val());
        
    });

    $(document).on('click', '.decrement-btn', function() {
        var input = $(this).closest('.input-group').find('input');
        var value = parseInt(input.val());
        if (value > 0) {
            input.val(value - 1);
        }
        $(this).closest("tr").find("input[name*=\'total\']").val($(this).closest("tr").find("input[name*=\'amount\']").val() * $(this).closest("tr").find("input[name*=\'price\']").val());
    });
JS;
$this->registerJS($js)
?>