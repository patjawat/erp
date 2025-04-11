
<?php
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\modules\am\models\Asset;
use unclead\multipleinput\MultipleInput;
?>
<ul class="nav nav-tabs justify-content-start" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="option-tab" data-bs-toggle="tab" href="#option" role="tab" aria-controls="option"
            aria-selected="true">รายละเอียดครุภัณฑ์</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="uploadFile-tab" data-bs-toggle="tab" href="#uploadFile" role="tab"
            aria-controls="uploadFile" aria-selected="false">อัพโหลดต่างๆ</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab" aria-controls="profile"
            aria-selected="false">ครุภัณฑ์ภายใน</a>
    </li>
</ul>

<div class="tab-content mt-3" id="myTabContent">
    <div class="tab-pane fade bg-white p-3 show active" id="option" role="tabpanel" aria-labelledby="option-tab">
        <div class="alert alert-primary" role="alert">
            <strong>*</strong> รายละเอียดครุภัณฑ์
        </div>
        <div class="row">
            <div class="col-3">
                <?= $form->field($model, 'car_type')->widget(Select2::classname(), [
                    'data' => [
                        'general' => 'รถใช้งานทั่วไป',
                        'ambulance' => 'รถพยาบาล'
                    ],
                    'options' => ['placeholder' => 'เลือกรถยนต์ ...'],
                    'pluginOptions' => ['allowClear' => true],
                ])->label('ประการใช้งานรถยนต์'); ?>
                <?= $form->field($model, 'data_json[engine_size]')->textInput()->label('ขนาดของเครื่องยนต์'); ?>
            </div>
            <div class="col-3">
                <?= $form->field($model, 'license_plate')->textInput()->label('หมายเลขทะเบียน'); ?>
                <?= $form->field($model, 'data_json[fuel_type]')->textInput()->label('ชนิดของเชื้อเพลิง'); ?>
            </div>
            <div class="col-3">
                <?= $form->field($model, 'data_json[brand]')->textInput()->label('ยี่ห้อ'); ?>
                <?= $form->field($model, 'data_json[color]')->textInput()->label('สี'); ?>
            </div>
            <div class="col-3">
                <?= $form->field($model, 'data_json[model]')->textInput()->label('รุ่น'); ?>
                <?= $form->field($model, 'data_json[seat_size]')->textInput()->label('จำนวนที่นั่ง'); ?>
            </div>
        </div>
        <?= $form->field($model, 'data_json[asset_option]')->textArea(['rows' => 5])->label(false); ?>
    </div>
    <div class="tab-pane fade bg-white p-3" id="uploadFile" role="tabpanel" aria-labelledby="uploadFile-tab">
        <?= $model->Upload($model->ref, 'asset_pic') ?>
    </div>
    <div class="tab-pane fade bg-white p-3" id="profile" role="tabpanel" aria-labelledby="profile-tab">
        <h6 class="text-start">เพิ่มรายการครุภัณฑ์ภายใน</h6>
        <?php
        $itemsOption = ArrayHelper::map(Asset::find()->where(['asset_group' => 3])->all(), 'code', function ($model) {
            try {
                return $model->data_json['asset_name'] . ' | ' . $model->code;
            } catch (\Throwable $th) {
                return '-';
            }
        });

        echo $form->field($model, 'device_items')->widget(MultipleInput::className(), [
            'max' => 6,
            'min' => 1,
            'allowEmptyList' => false,
            'enableGuessTitle' => true,
            'addButtonPosition' => MultipleInput::POS_HEADER,
            'addButtonOptions' => [
                'class' => 'btn btn-sm btn-primary',
                'label' => '<i class="fa-solid fa-circle-plus"></i>',
            ],
            'removeButtonOptions' => [
                'class' => 'btn btn-sm btn-danger',
                'label' => '<i class="fa-solid fa-trash"></i>',
            ],
            'columns' => [
                [
                    'name' => 'device_items',
                    'type' => Select2::class,
                    'headerOptions' => [
                        'class' => 'table-light',
                        'style' => 'width: 100%;',
                    ],
                    'title' => 'รายการครุภัณฑ์ภายใน',
                    'options' => [
                        'pluginOptions' => [
                            'allowClear' => true,
                            'placeholder' => 'เลือกรายการ ...',
                        ],
                        'pluginEvents' => [
                            'change' => 'function() {
                                var id = $(this).val();
                                var name = $(this).find("option:selected").text();
                                console.log(name)
                                $(this).closest("tr").find("input[name*=\'code\']").val(id);
                                $(this).closest("tr").find("input[name*=\'name\']").val(name);
                            }',
                        ],
                        'data' => $itemsOption,
                    ],
                ],
            ],
        ])->label(false);
        ?>
    </div>
</div>