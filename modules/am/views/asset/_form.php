<?php
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\modules\am\models\Asset;
use unclead\multipleinput\MultipleInput;

$title = Yii::$app->request->get('title');
$group = Yii::$app->request->get('group');
/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */
/** @var yii\widgets\ActiveForm $form */

?>
<?php $this->beginBlock('page-action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock();?>
<style>
.modal-footer {
    display: none !important;
}
</style>
<?php $form = ActiveForm::begin([
    'id' => 'form-asset',
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/am/asset/validator'],
]);?>

<?=$form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false)?>
<?=$form->field($model, 'asset_group')->hiddenInput(['maxlength' => true])->label(false)?>


<div class="row">
    <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="dropdown edit-field-half-left ml-2">
                    <div class="btn-icon btn-icon-sm btn-icon-soft-primary dropdown-toggle me-0 edit-field-icon"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-ellipsis"></i>
                    </div>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="#" class="dropdown-item select-photo">
                            <i class="fa-solid fa-file-image me-2 fs-5"></i>
                            <span>อัพโหลดภาพ</span>
                        </a>

                    </div>
                </div>

                <input type="file" id="my_file" style="display: none;" />
                <a href="#" class="select-photo">
                    <?=Html::img($model->showImg(), ['class' => 'avatar-profile object-fit-cover rounded', 'style' => 'max-width:100%;'])?>
                </a>
            </div>
        </div>

    </div>
    <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12">
        <?=$this->render('_form_detail' . $model->asset_group . '.php', ['model' => $model, 'form' => $form])?>


    </div>

</div>


<ul class="nav nav-tabs justify-content-start" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="option-tab" data-bs-toggle="tab" href="#option" role="tab" aria-controls="option"
            aria-selected="true">
            รายละเอียดครุภัณฑ์
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="uploadFile-tab" data-bs-toggle="tab" href="#uploadFile" role="tab"
            aria-controls="uploadFile" aria-selected="false">
            อัพโหลดต่างๆ
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab" aria-controls="profile"
            aria-selected="false">
            ครุภัณฑ์ภายใน
        </a>
    </li>
</ul>
<div class="tab-content mt-3" id="myTabContent">
    <div class="tab-pane fade bg-white p-3 show active" id="option" role="tabpanel" aria-labelledby="option-tab">
        <div class="alert alert-primary" role="alert">
            <strong>*</strong> รายละเอียดครุภัณฑ์
        </div>
        <div class="row">
            <div class="col-3">
            <?=$form->field($model, 'car_type')->widget(Select2::classname(), [
                    'data' => [
                        'general' => 'รถใช้งานทั่วไป',
                        'ambulance' => 'รถพยาบาล'
                    ],
                    'options' => ['placeholder' => 'เลือกรถยนต์ ...'],
                   
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->label('ประการใช้งานรถยนต์');
                ?>
            </div>
            <div class="col-2">
            <?=$form->field($model, 'license_plate')->textInput()->label('หมายเลขทะเบียน');?>
        </div>
            <div class="col-2">
                <?=$form->field($model, 'data_json[color]')->textInput()->label('สี');?>
            </div>
            <div class="col-2">
                <?=$form->field($model, 'data_json[fuel_type]')->textInput()->label('ชนิดของเชื้อเพลิง');?>
            </div>
            <div class="col-2">
                    <?=$form->field($model, 'data_json[seat_size]')->textInput()->label('จำนวนที่นั่ง');?>
            </div>
        </div>
        <?=$form->field($model, 'data_json[asset_option]')->textArea(['rows' => 5])->label(false);?>
    </div>
    <div class="tab-pane fade bg-white p-3" id="uploadFile" role="tabpanel" aria-labelledby="uploadFile-tab">
        <?=$model->Upload($model->ref, 'asset_pic')?>
    </div>
    <div class="tab-pane fade bg-white p-3" id="profile" role="tabpanel" aria-labelledby="profile-tab">
        <h1 class="text-center">เพิ่มรายการครุภัณฑ์ภายใน</h1>
        <?php
$itemsOption = ArrayHelper::map(Asset::find()->where(['asset_group' => 3])->all(), 'code', function ($model) {
    try {
        return $model->data_json['asset_name'] . ' | ' . $model->code;
    } catch (\Throwable $th) {
        return '-';
    }
});

echo $form->field($model, 'device_items')->widget(MultipleInput::className(), [
    'max'               => 6,
    'min'               => 1, // should be at least 2 rows
    'allowEmptyList'    => false,
    'enableGuessTitle'  => true,
    'addButtonPosition' => MultipleInput::POS_HEADER,
        'addButtonOptions' => [
        'class' => 'btn btn-sm btn-primary',
        'label' => '<i class="fa-solid fa-circle-plus"></i>', // also you can use html code
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
])
->label(false);
?>
    </div>

</div>
</div>

<div class="form-group mt-4 d-flex justify-content-center">
    <?=AppHelper::BtnSave();?>
</div>
<?php ActiveForm::end();?>


<?php
$js = <<< JS
$('#form-asset').on('beforeSubmit', function (e) {
    var form = $(this);
    Swal.fire({
        title: "ยืนยัน?",
        text: "บันทึกขอมูลทรัพย์สิน!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก!",
        confirmButtonText: "ใช่, ยืนยัน!"
        }).then((result) => {
        if (result.isConfirmed) {
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (res) {
            form.yiiActiveForm('updateMessages', res, true);
            if (form.find('.invalid-feedback').length) {
                // validation failed
            } else {
                // validation succeeded
            }
            if(res.status == 'success') {
                // alert(data.status)
                console.log(res.container);
                // $('#main-modal').modal('toggle');
                success()
                 $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});
            }
        }
            });

        }
        });
        return false;
    });

// เลือก upload รูปภาพ


$(".select-photo").click(function() {
    $("input[id='my_file']").click();
});

$("input[id='my_file']").on("change", function() {
    var fileInput = $(this)[0];
    if (fileInput.files && fileInput.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
        $(".avatar-profile").attr("src", e.target.result);
        };
        reader.readAsDataURL(fileInput.files[0]);
        uploadImg()
    }

});

function uploadImg()
{
    formdata = new FormData();
    if($("input[id='my_file']").prop('files').length > 0)
    {
		file = $("input[id='my_file']").prop('files')[0];
        formdata.append("asset", file);
        formdata.append("id", 1);
        formdata.append("ref", '$model->ref');
        formdata.append("name", 'asset');

        console.log(file);
		$.ajax({
			url: '/filemanager/uploads/single',
			type: "POST",
			data: formdata,
			processData: false,
			contentType: false,
			success: function (res) {
                success('แก้ไขภาพสำเร็จ')
                console.log(res)
			}
		});
    }
}

$("button[id='summit']").on('click', function() {
    formdata = new FormData();
    if($("input[id='my_file']").prop('files').length > 0)
    {
		file = $("input[id='my_file']").prop('files')[0];
        formdata.append("avatar", file);
        formdata.append("id", 1);
        formdata.append("ref", '$model->ref');
        formdata.append("name", 'avatar');

        console.log(file);
		$.ajax({
			url: '/filemanager/uploads/single',
			type: "POST",
			data: formdata,
			processData: false,
			contentType: false,
			success: function (res) {
                // success('แก้ไขภาพ')
                console.log(res)
			}
		});
    }
})


var thaiYear = function (ct) {
        var leap=3;
        var dayWeek=["พฤ.", "ศ.", "ส.", "อา.","จ.", "อ.", "พ."];
        if(ct){
            var yearL=new Date(ct).getFullYear()-543;
            leap=(((yearL % 4 == 0) && (yearL % 100 != 0)) || (yearL % 400 == 0))?2:3;
            if(leap==2){
                dayWeek=["ศ.", "ส.", "อา.", "จ.","อ.", "พ.", "พฤ."];
            }
        }
        this.setOptions({
            i18n:{ th:{dayOfWeek:dayWeek}},dayOfWeekStart:leap,
        })
    };

    $("#asset-receive_date").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,
        onShow:thaiYear,
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });

JS;
$this->registerJs($js);
?>