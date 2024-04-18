<?php
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use app\models\Province;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\depdrop\DepDrop;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use yii\bootstrap5\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\MaskedInput;


$formatJs = <<< 'JS'
var formatRepo = function (repo) {
    if (repo.loading) {
        return repo.text;
        console.log('loadding');
    }
    var markup =
        '<div class="row">' +
            '<div class="col-sm-3">' +
                '(<code>' + repo.id + '</code>)' +
            '</div>' +
            '<div class="col-sm-5" style="padding-right: 0px;">' + repo.name + '</div>' +
            '<div class="col-sm-2">' + repo.province + '</div>' +
        '</div>';
    return '<div style="overflow:hidden;">' + markup + '</div>';

};


var formatRepo2 = function (repo) {
    if (repo.loading) {
        return repo.text;
        console.log('loadding');
    }
    var markup =repo.text;
    return '<div style="overflow:hidden;">' + markup + '</div>';

};


var formatRepoSelection = function (repo) {
    return repo.id || repo.text;
}
JS;

$this->registerJs($formatJs, View::POS_HEAD);

$resultsJs = <<< JS
function (data, params) {
    params.page = params.page || 1;
    return {
        // results: data.items,
        results: data.items,
        pagination: {
            more: (params.page * 30) < data.total_count
        }
    };
}
JS;

?>

<style>

</style>
<?php $form = ActiveForm::begin([
    'id' => 'form-profile',
    'enableAjaxValidation'      => true, //เปิดการ validate ด้วย AJAX
    'enableClientValidation'    => false, // validate ฝั่ง client เมื่อ submit หรือ เปลี่ยนค่า
    'validateOnChange'          => true,// validate เมื่อมีการเปลี่ยนค่า
    'validateOnSubmit'          => true,// validate เมื่อ submit ข้อมูล
    'validateOnBlur'            => false,// validate เมื่อเปลี่ยนตำแหน่ง cursor ไป input อื่น
    'options' => [
        'enctype' => 'multipart/form-data'
    ]
]); ?>

                                            <div class="row">
                                      
                                                <div class="col-md-8">
        
                                                       <?= $form->field($model, 'data_json[farther_name]')->textInput(['maxlength' => true])->label('ชื่อบิดา') ?>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <b>เพศ</b>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="text" class="form-control" >
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <b>วันเกิด</b>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" >
                                                 
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <b>อายุ</b>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" >
                                                 
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <b>สถานภาพ</b>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control" >
                                                 
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <b>สัญชาติ</b>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control" >
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <b>ศาสนา</b>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control" >
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <b>เชื้อชาติ</b>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control" >
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <b>หมู่โลหิต</b>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="text" class="form-control" >
                                                </div>
                                            </div>

			
									<div class="submit-section">
										<?=SiteHelper::BtnSave();?>
									</div>

<?php ActiveForm::end(); ?>


<?php
$urlUpload = Url::to('/profile/upload');
$js  = <<< JS

// edit avatar
 $('.edit-avatar').change(function (e) { 
	e.preventDefault();
	formdata = new FormData();
    if($(this).prop('files').length > 0)
    {
		file =$(this).prop('files')[0];
        formdata.append("avatar", file);
        formdata.append("id", 1);
		$.ajax({
			url: '$urlUpload',
			type: "POST",
			data: formdata,
			processData: false,
			contentType: false,
			success: function (result) {
				$('.view-avatar').attr('src', result)
                success('แก้ไขภาพ')
			}
		});
    }
 });

//  Form submit Ajax
$('#form-profile').on('beforeSubmit', function (e) {
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (data) {
            form.yiiActiveForm('updateMessages', data, true);
            if (form.find('.has-error').length) {
                // validation failed
            } else {
                // validation succeeded
            }

            if(data.status == 'success') {
                closeModal()
				success()
                await  $.pjax.reload({ container:'#general-container', history:false,replace: false});                                
            }
        }
    });
    return false;
});

// คเนหาที่อยู่จาก ไปรษณี
$('#employees-zipcode').keyup(function (e) { 
    var key = e.target.value.length;
    // console.log($('#employees-zipcode').val());
    console.log($('#employees-zipcode').val().length == 4);
    if(''){
        // console.log('Search');
        $.ajax({
            type: "get",
            url: "depdrop/get-address",
            data: {
                'zipcode': $('#employees-zipcode').val(),
            },
            dataType: "json",
            success: async function (response) {
                console.log(response);
                await $('#employees-province').val(response.province_id).trigger('change');
                await $('#employees-amphure').val(response.id).trigger('change');
            }
        });
    }
    
});


JS;
$this->registerJS($js,\yii\web\View::POS_END);
?>
