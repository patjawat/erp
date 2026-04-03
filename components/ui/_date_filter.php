<?php

use kartik\widgets\Select2;
use app\components\DateFilterHelper;

echo $form->field($model, 'date_filter')->widget(Select2::classname(), [
    'data' => DateFilterHelper::getDropdownItems(),
    'options' => [
        'placeholder' => 'ช่วงเวลาทั้งหมด',
        'id' => 'dateFilter',
    ],
    'pluginOptions' => [
        'allowClear' => true,
    ],
    'pluginEvents' => [
        "select2:select" => "function(result) { 
                        $.ajax({
                            type: 'get',
                            url: '/depdrop/date-filter',
                            data: { date_filter: $(this).val() },
                            dataType: 'json',
                            success: function (res) {
                            $('#dateStart').val(res.date_start)
                            $('#dateEnd').val(res.date_end)
                            }
                        });
                    }",
        "select2:clear" => "function() {
                        $('#dateStart').val('');
                        $('#dateEnd').val('');
                    }",
    ]
])->label(false);
