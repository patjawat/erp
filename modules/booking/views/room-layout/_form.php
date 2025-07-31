<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\RoomType $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php // echo Html::img($model->ShowAvatar(), ['class' => 'card-img-top']) ?>


<div class="room-type-form">

    <?php $form = ActiveForm::begin(['id' => 'form']); ?>

    <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-12">
       <div class="mb-3">
                    <div class="file-file-preview" id="editImagePreview" data-isfile="<?=$model->showImg()['isFile']?>" data-newfile="false">
                        <?= Html::img($model->showImg()['image'],['id' => 'editPreviewImg']) ?>
                        <div class="file-remove" id="editRemoveImage">
                            <i class="bi bi-x"></i>
                        </div>
                    </div>
                    <div class="file-upload">
                        <div class="file-upload-btn" id="editUploadBtn">
                            <i class="bi bi-cloud-arrow-up fs-3 mb-2"></i>
                            <span>คลิกหรือลากไฟล์มาวางที่นี่</span>
                            <small class="d-block text-muted mt-2">รองรับไฟล์ JPG, PNG ขนาดไม่เกิน 5MB</small>
                        </div>
                        <input type="file" class="file-upload-input" id="my_file" accept="image/*">
                    </div>
                </div>
        </div>
        <div class="col-lg-8 col-md-8 col-sm-12">
            <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'title')->textInput(['rows' => 6]) ?>

            <div class="form-group d-flex justify-content-center gap-3 mt-3">
                <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
                <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"> <i
                        class="fa-regular fa-circle-xmark"></i> ปิด</button>
            </div>
        </div>
    </div>




    <?php ActiveForm::end(); ?>

</div>

<?php
$ref = Json::encode($model->ref); // ปลอดภัยแม้มีอักขระพิเศษ
$urlUpload = Url::to('/filemanager/uploads/single');

$js = <<< JS

 isFile()
\$('#form').on('beforeSubmit', function (e) {
            var form = \$(this);
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
                 uploadImage('room_layout',$ref);
                \$.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success: async function (response) {
                        if(response.status == 'success') {
                            closeModal()
                            success()
                             window.location.reload(true);
                         
                        }
                    }
                });

            }
            });
            return false;
        });


    // \$('.select-image').click(function (e) { 
    //         \$('#file').click();
            
    //     });


    //     \$('#file').on('change', function (e) {
    //     const image = this.files[0];

    //     if (image.size < 2000000) {
    //         const reader = new FileReader();
    //         reader.onload = function () {
    //             const imgArea = \$('.img-area');
    //             imgArea.find('img').remove();

    //             const imgUrl = reader.result;
    //             const img = \$('<img>').attr('src', imgUrl);
    //             imgArea.append(img).addClass('active').data('img', image.name);

    //             const file = \$('#file').prop('files')[0];
    //             const formData = new FormData();
    //             formData.append("room_layout", file);
    //             formData.append("id", 1);
    //             formData.append("ref", '$ref');
    //             formData.append("name", 'room_layout');

    //             console.log(file);

    //             \$.ajax({
    //                 url: '$urlUpload',
    //                 type: "POST",
    //                 data: formData,
    //                 processData: false,
    //                 contentType: false,
    //                 success: function (res) {
    //                     console.log(res);
    //                     \$('.img-room').attr('src', res.img);
    //                     // await \$.pjax.reload({ container: response.container, history: false, replace: false, timeout: false });
    //                 }
    //             });
    //         };
    //         reader.readAsDataURL(image);
    //     } else {
    //         alert("Image size more than 2MB");
    //     }
    // });



JS;
$this->registerJS($js, View::POS_END);

?>