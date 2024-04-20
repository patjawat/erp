<?php
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use unclead\multipleinput\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;
?>

<?php $form = ActiveForm::begin(); ?>
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
            </div>

<?php ActiveForm::end(); ?>
