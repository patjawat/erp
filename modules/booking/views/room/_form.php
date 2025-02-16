<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use app\components\UserHelper;
use kartik\widgets\ActiveForm;


$me = UserHelper::GetEmployee();
$formatJs = <<< 'JS'
    var formatRepo = function (repo) {
        if (repo.loading) {
            return repo.avatar;
        }
        // console.log(repo);
        var markup =
    '<div class="row">' +
        '<div class="col-12">' +
            '<span>' + repo.avatar + '</span>' +
        '</div>' +
    '</div>';
        if (repo.description) {
          markup += '<p>' + repo.avatar + '</p>';
        }
        return '<div style="overflow:hidden;">' + markup + '</div>';
    };
    var formatRepoSelection = function (repo) {
        return repo.avatar || repo.avatar;
    }
    JS;

// Register the formatting script
$this->registerJs($formatJs, View::POS_HEAD);

// script to parse the results into the format expected by Select2
$resultsJs = <<< JS
    function (data, params) {
        params.page = params.page || 1;
        return {
            results: data.results,
            pagination: {
                more: (params.page * 30) < data.total_count
            }
        };
    }
    JS;
    
/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCar $model */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
.select2-container--krajee-bs5 .select2-selection--single .select2-selection__placeholder {
    font-weight: 300;
    font-size: medium;
}

.input-lg.select2-container--krajee-bs5 .select2-selection--single,
:not(.form-floating)>.input-group-lg .select2-container--krajee-bs5 .select2-selection--single {
    padding: .2rem 0.6rem !important;
}


.select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
    background-color: #d4e1f2;
    color: #111111;
}


.img-area {
	position: relative;
	width: 100%;
	height: 240px;
	background: var(--grey);
	margin-bottom: 30px;
	border-radius: 15px;
	overflow: hidden;
	display: flex;
	justify-content: center;
	align-items: center;
	flex-direction: column;
}
.img-area .icon {
	font-size: 100px;
}
.img-area h3 {
	font-size: 20px;
	font-weight: 500;
	margin-bottom: 6px;
}
.img-area p {
	color: #999;
}
.img-area p span {
	font-weight: 600;
}
.img-area img {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	object-fit: cover;
	object-position: center;
	z-index: 100;
}
.img-area::before {
	content: attr(data-img);
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: rgba(0, 0, 0, .5);
	color: #fff;
	font-weight: 500;
	text-align: center;
	display: flex;
	justify-content: center;
	align-items: center;
	pointer-events: none;
	opacity: 0;
	transition: all .3s ease;
	z-index: 200;
}
.img-area.active:hover::before {
	opacity: 1;
}


</style>

<div class="room-form">

    <?php $form = ActiveForm::begin(['id' => 'form']); ?>

    <div class="row">
        <div class="col-3">

            <?= $form->field($model, 'code')->textInput(['maxlength' => true,])->label('รหัสห้องประชุม') ?>

        </div>
        <div class="col-9">
            <?= $form->field($model, 'title')->textInput(['maxlength' => true,])->label('ชื่อห้องประชุม') ?>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <?= $form->field($model, 'data_json[location]')->textInput([])->label('สถานที่ตั้ง') ?>
            <?= $form->field($model, 'data_json[advance_booking]')->textInput(['type' => 'number',])->label('จองล่วงหน้า (วัน)') ?>
        </div>
        <div class="col-6">

            <?php
                        try {
                            //code...
                            if($model->isNewRecord){
                                $initEmployee =  Employees::find()->where(['id' => $model->data_json['owner']])->one()->getAvatar(false);    
                            }else{
                                $initEmployee =  Employees::find()->where(['id' => $model->data_json['owner']])->one()->getAvatar(false);    
                            }
                            // $initEmployee =  Employees::find()->where(['id' => $model->Approve()['leader']['id']])->one()->getAvatar(false);
                        } catch (\Throwable $th) {
                            $initEmployee = '';
                        }

                        echo $form->field($model, 'data_json[owner]')->widget(Select2::classname(), [
                            'initValueText' => $initEmployee,
                            'options' => ['placeholder' => 'เลือกรายการ...',],
                            'size' => Select2::LARGE,
                            'pluginEvents' => [
                                'select2:unselect' => 'function() {
                                    $("#order-data_json-board_fullname").val("")
                                }',
                                'select2:select' => 'function() {
                                    var fullname = $(this).select2("data")[0].fullname;
                                }',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'dropdownParent' => '#main-modal',
                                'minimumInputLength' => 1,
                                'ajax' => [
                                    'url' => Url::to(['/depdrop/employee-by-id']),
                                    'dataType' => 'json',
                                    'delay' => 250,
                                    'data' => new JsExpression('function(params) { return {q:params.term, page: params.page}; }'),
                                    'processResults' => new JsExpression($resultsJs),
                                    'cache' => true,
                                ],
                                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                'templateSelection' => new JsExpression('function (item) { return item.text; }'),
                                'templateResult' => new JsExpression('formatRepo'),
                            ],
                        ])->label('ผู้รับผิดชอบ');
                        ?>

            <?= $form->field($model, 'data_json[seat_capacity]')->textInput(['type' => 'number',])->label('ที่นั่ง') ?>
        </div>
    </div>

    <?= $form->field($model, 'data_json[room_accessory]')->widget(Select2::classname(), [
                        'data' => $model->ListAccessory(),
                        'options' => ['placeholder' => 'เลือกหน่วยงาน', 'multiple' => true],
                        'pluginOptions' => [
                            'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                            'allowClear' => true,
                            'dropdownParent' => '#main-modal',
                        ],
                        'pluginEvents' => [
                            'select2:select' => 'function(result) { 
                                            }',
                            'select2:unselecting' => 'function() {

                                            }',
                        ],
                
                    ])->label('รายการอุปกรณ์') ?>

    <?= $form->field($model, 'description')->textArea(['maxlength' => true])->label('หมายเหตุ'); ?>

    <?= $form->field($model, 'active')->checkbox(['custom' => true,'switch' => true, 'checked' => $model->active == 1 ? true : false])->label('เปิดใช้งาน') ?>


    <div class="d-flex justify-content-center flex-column align-items-center">
		<input type="file" id="file" accept="image/*" hidden>
		<div class="img-area" data-img="">
			<i class='bx bxs-cloud-upload icon'></i>
			<h3>Upload Image</h3>
			<p>Image size must be less than <span>2MB</span></p>
		</div>
		<button class="select-image btn btn-primary shadow rounded-pill w-50">Select Image</button>
	</div>

    
    <div class="d-flex justify-content-center">
            
            <input type="file" id="room_file"  style="display:none;"/>
            <a href="#" class="select-room">
                <?php if ($model->isNewRecord): ?>
                    <?= Html::img('@web/img/placeholder-img.jpg', ['class' => 'img-room object-fit-cover rounded shadow', 'style' => 'margin-top: 25px;max-width: 135px;max-height: 135px;    width: 100%;height: 100%;']) ?>
                    <?php else: ?>
                        
                        <?php echo Html::img($model->showImg(),['class' => 'img-room object-fit-cover rounded','style' =>'margin-top: 25px;max-width: 135px;max-height: 135px;    width: 100%;height: 100%;']) 
                ?>
            <?php endif ?>
        </a>
    </div>

    
    <?=$model->upload()?>

    <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
    <div class="form-group mt-3 d-flex justify-content-center gap-3">
        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
        <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"> <i
                class="fa-regular fa-circle-xmark"></i> ปิด</button>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php
$ref = $model->ref;
$urlUpload = Url::to('/filemanager/uploads/single');
$js = <<< JS


    const selectImage = document.querySelector('.select-image');
    const inputFile = document.querySelector('#file');
    const imgArea = document.querySelector('.img-area');

    selectImage.addEventListener('click', function () {
        inputFile.click();
    })

    inputFile.addEventListener('change', function () {
        const image = this.files[0]
        if(image.size < 2000000) {
            const reader = new FileReader();
            reader.onload = ()=> {
                const allImg = imgArea.querySelectorAll('img');
                allImg.forEach(item=> item.remove());
                const imgUrl = reader.result;
                const img = document.createElement('img');
                img.src = imgUrl;
                imgArea.appendChild(img);
                imgArea.classList.add('active');
                imgArea.dataset.img = image.name;
            }
            reader.readAsDataURL(image);
        } else {
            alert("Image size more than 2MB");
        }
    })

      $('#form').on('beforeSubmit', function (e) {
        var form = $(this);
        
        Swal.fire({
        title: "ยืนยัน?",
        text: "บันทึกข้อมูล!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก!",
        confirmButtonText: "ใช่, ยืนยัน!"
        }).then((result) => {
        if (result.isConfirmed) {
            
            \$.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: async function (response) {
                    console.log(response);
                    
                    if(response.status == 'success') {
                        closeModal()
                        success()
                        // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                    }
                }
            });

        }
        });
        return false;
    });


    \$(".select-room").click(function() {
                    \$("input[id='room_file']").click();
                    console.log('select-room');
                   
                    
                });


                \$('#room_file').change(function (e) { 
                    e.preventDefault();
                    console.log('room_file Click"');
                    formdata = new FormData();
                    if(\$(this).prop('files').length > 0)
                    {
                        file =\$(this).prop('files')[0];
                        formdata.append("meeting_room", file);
                        formdata.append("id", 1);
                        formdata.append("ref", '$ref');
                        formdata.append("name",'meeting_room');

                        console.log(file);
                        \$.ajax({
                            url: '$urlUpload',
                            type: "POST",
                            data: formdata,
                            processData: false,
                            contentType: false,
                            success: async function (res) {
                                console.log(res);
                                \$('.img-room').attr('src', res.img)
                                await  $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});      
                                // window.location.reload(true);
                                // success('แก้ไขภาพ')
                            }
                        });
                    }
                });
                
JS;
$this->registerJS($js, View::POS_END);
?>