<?php

use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\models\Categorise;
use app\widgets\datetimepicker\Datetimepicker;

if (is_string($model->data_json)) {
    $benefitData = json_decode($model->data_json, true) ?: [];
} else {
    $benefitData = $model->data_json ?? [];
}

if (!is_array($benefitData)) {
    $benefitData = [];
}

if (empty($benefitData['receive_date']) && !empty($benefitData['date_start'])) {
    $benefitData['receive_date'] = $benefitData['date_start'];
}

if (($benefitData['benefit_type'] ?? null) === 'house') {
    if (empty($benefitData['start_date']) && !empty($benefitData['date_start'])) {
        $benefitData['start_date'] = $benefitData['date_start'];
    }

    if (empty($benefitData['end_date']) && !empty($benefitData['date_end'])) {
        $benefitData['end_date'] = $benefitData['date_end'];
    }
}

foreach (['receive_date', 'start_date', 'end_date'] as $field) {
    if (!empty($benefitData[$field]) && preg_match('/^\d{4}-\d{2}-\d{2}/', $benefitData[$field])) {
        $benefitData[$field] = AppHelper::convertToThai($benefitData[$field]) ?: $benefitData[$field];
    }
}

$model->data_json = $benefitData;
?>

<div class="row">

    <!-- วันที่ / ประเภท -->
    <div class="col-lg-6 col-md-6 col-sm-12">
<?= $form->field($model, 'data_json[receive_date]')->widget(\app\widgets\datepicker\DatepickerThai::class, [
    'options' => ['id' => 'dateReceive', 'placeholder' => 'วันที่ได้รับสวัสดิการ'],
])->label('วันที่ได้รับสวัสดิการ'); ?>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-12">

        <?=
        $form->field($model, 'data_json[benefit_type]')
            ->dropDownList(
                ArrayHelper::map(
                    Categorise::find()
                        ->where(['name' => 'benefit'])
                        ->orderBy(['title' => SORT_ASC])
                        ->all(),
                    'code',
                    'title'
                ),
                [
                    'prompt' => 'เลือกประเภทสวัสดิการ',
                    'id' => 'benefit-type'
                ]
            )->label('ประเภทสวัสดิการ');
        ?>

    </div>

    <!-- จำนวนเงิน / สถานะ -->
    <div class="col-lg-6 col-md-6 col-sm-12">

        <?=
        $form->field($model, 'data_json[amount]')
            ->textInput([
                'type' => 'number',
            ])->label('จำนวนเงิน');
        ?>

    </div>

    <div class="col-lg-6 col-md-6 col-sm-12">

        <?=
        $form->field($model, 'data_json[status]')
            ->dropDownList([
                'active' => 'ใช้งาน',
                'expired' => 'สิ้นสุด',
                'cancel' => 'ยกเลิก',
            ], [
                'prompt' => 'เลือกสถานะ'
            ])->label('สถานะ');
        ?>

    </div>

    <!-- รายละเอียด -->
    <div class="col-12">

        <?=
        $form->field($model, 'data_json[detail]')
            ->textarea([
                'rows' => 3
            ])->label('รายละเอียด');
        ?>

    </div>

    <!-- เอกสาร / หมายเหตุ -->
    <div class="col-lg-6 col-md-6 col-sm-12">

        <?=
        $form->field($model, 'data_json[doc_ref]')
            ->textInput()
            ->label('เอกสารอ้างอิง');
        ?>

    </div>

    <div class="col-lg-6 col-md-6 col-sm-12">

        <?=
        $form->field($model, 'data_json[comment]')
            ->textInput()
            ->label('หมายเหตุ');
        ?>

    </div>

</div>

<!-- ส่วนบ้านพัก -->
<div id="house-section" style="display:none;">

    <div class="row mt-3">

        <div class="col-12">
            <h6 class="text-primary">
                ข้อมูลสวัสดิการบ้านพัก
            </h6>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12">

            <?=
            $form->field($model, 'data_json[house_name]')
                ->textInput()
                ->label('ชื่อบ้านพัก / อาคาร');
            ?>

        </div>

        <div class="col-lg-6 col-md-6 col-sm-12">

            <?=
            $form->field($model, 'data_json[house_no]')
                ->textInput()
                ->label('เลขห้อง / เลขบ้าน');
            ?>

        </div>

        <div class="col-lg-6 col-md-6 col-sm-12">
<?= $form->field($model, 'data_json[start_date]')->widget(\app\widgets\datepicker\DatepickerThai::class, [
    'options' => ['id' => 'dateStart', 'placeholder' => 'วันที่เริ่มเข้าพัก'],
])->label(false); ?>


        </div>

        <div class="col-lg-6 col-md-6 col-sm-12">
<?= $form->field($model, 'data_json[end_date]')->widget(\app\widgets\datepicker\DatepickerThai::class, [
    'options' => ['id' => 'dateEnd', 'placeholder' => 'วันที่ย้ายออก'],
])->label(false); ?>


        </div>

        <div class="col-lg-6 col-md-6 col-sm-12">

            <?=
            $form->field($model, 'data_json[house_status]')
                ->dropDownList([
                    'stay' => 'กำลังพัก',
                    'move_out' => 'ย้ายออก',
                ], [
                    'prompt' => 'เลือกสถานะบ้านพัก'
                ])->label('สถานะบ้านพัก');
            ?>

        </div>

    </div>

</div>

<?php

$this->registerJs(<<<JS

function toggleHouseSection() {

    let benefitType = $('#benefit-type').val();

    if (benefitType === 'house') {
        $('#house-section').show();
    } else {
        $('#house-section').hide();
    }
}

toggleHouseSection();

$('#benefit-type').on('change', function () {
    toggleHouseSection();
});

JS);

?>
