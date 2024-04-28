<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use app\components\AppHelper;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use unclead\multipleinput\MultipleInput;
use iamsaint\datetimepicker\Datetimepicker;
use app\components\CategoriseHelper;
use app\modules\hr\models\Organization;
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
                     'enableAjaxValidation'      => true,//เปิดการใช้งาน AjaxValidation
                     'validationUrl' =>['/am/asset/validator']
                ]); ?>

<?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false)?>
<?= $form->field($model, 'asset_group')->hiddenInput(['maxlength' => true])->label(false)?>


<div class="row">
    <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="dropdown edit-field-half-left ml-2">
                    <div class="btn-icon btn-icon-sm btn-icon-soft-primary dropdown-toggle me-0 edit-field-icon"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-ellipsis"></i>
                    </div>
                    <div class="dropdown-menu dropdown-menu-right" style="">
                        <a href="#" class="dropdown-item select-photo">
                            <i class="fa-solid fa-file-image me-2 fs-5"></i>
                            <span>อัพโหลดภาพ</span>
                        </a>

                    </div>
                </div>

                <input type="file" id="my_file" style="display: none;" />
                <a href="#" class="select-photo">
                    <?= Html::img($model->showImg(),['class' => 'avatar-profile object-fit-cover rounded','style' =>'max-width:100%;']) ?>
                </a>
            </div>
        </div>

    </div>
    <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12">
        <?= $this->render('_form_detail'.$model->asset_group .'.php',['model' => $model,'form' => $form])?>


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
        <?= $form->field($model, 'data_json[asset_option]')->textArea(['rows' => 5])->label(false);?>
    </div>
    <div class="tab-pane fade bg-white p-3" id="uploadFile" role="tabpanel" aria-labelledby="uploadFile-tab">
        <?=$model->Upload($model->ref,'asset_pic')?>
    </div>
    <div class="tab-pane fade bg-white p-3" id="profile" role="tabpanel" aria-labelledby="profile-tab">
        <h1 class="text-center">เพิ่มรายการคุภัณฑ์ภายใน</h1>
        <?=Html::img('@web/images/demo_select_asset_list.png')?>
        <?php

echo $form->field($model,'ma')->widget(MultipleInput::class,[
    'allowEmptyList'    => false,
    'enableGuessTitle'  => true,
    'addButtonPosition' => MultipleInput::POS_HEADER,
    'addButtonOptions' => [
        'class' => 'btn btn-sm btn-primary',
        'label' => '<i class="fa-solid fa-circle-plus"></i>' // also you can use html code
    ],
    'removeButtonOptions' => [
        'class' => 'btn btn-sm btn-danger',
        'label' => '<i class="fa-solid fa-trash"></i>'
    ],
    'columns' => [
        [
            'name'  => 'item',
            'type' => Select2::class,
            'headerOptions' => [
                'class' => 'table-light', 
                'style' => 'width: 45%;',
            ],
            'title' => 'รายการที่ตรวจเช็ค',
            'options' => [
                // 'data' => $items
/*                array_map(function ($asset) {
                    return CategoriseHelper::Id($id_category)->one()->data_json["ma_items"][$asset]["item_name"];
                },range(0, count(CategoriseHelper::Id($id_category)->one()->data_json["ma_items"])-1)) */
                #CategoriseHelper::Id($id_category)->one()->data_json["ma_items"]
            ]
        ],
        [
            'name'  => 'ma_status',
            'type'  => 'dropDownList',
            'headerOptions' => [
                'class' => 'table-light',
                'style' => 'width: 10%;',
            ],
            'defaultValue' => 'ปกติ',
            'items' => [
                'สูง'=> 'สูง',
                'ปกติ' => 'ปกติ',
                'ต่ำ' => 'ต่ำ'
            ],
            'title' => 'สถานะ',
        ], 
        [
            'name'  => 'comment',
            'headerOptions' => [
                'class' => 'table-light', 
                'style' => 'width: 45%;',
            ],
            'title' => 'หมายเหตุ',
        ], 
        
 	
        
        
    ]

])->label(false);
?>
    </div>

</div>
</div>

<div class="form-group mt-4 d-flex justify-content-center">
    <?= AppHelper::BtnSave(); ?>
</div>
<?php ActiveForm::end(); ?>


<?php
$js = <<< JS
$('#form-asset').on('beforeSubmit', function (e) {
    var form = $(this);
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