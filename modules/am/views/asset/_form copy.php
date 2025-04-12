<?php
use yii\helpers\Url;
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
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<style>
.modal-footer {
    display: none !important;
}


.img-area {
    position: relative;
    width: 100%;
    height: 240px;
    background: var(--grey);
    margin-bottom: 30px;
    border-radius: 10px;
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

<?php $form = ActiveForm::begin([
    'id' => 'form-asset',
    'enableAjaxValidation' => true,
    'validationUrl' => ['/am/asset/validator'],
]); ?>

<?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'asset_group')->hiddenInput(['maxlength' => true])->label(false) ?>

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
                <div>
                    <input type="file" id="file" accept="image/*" hidden>
                    <div class="img-area" data-img="">
                        <i class='bx bxs-cloud-upload icon'></i>
                        <h3>Upload Image</h3>
                        <p>Image size must be less than <span>2MB</span></p>
                        <?php echo Html::img($model->showImg(), ['class' => 'card-img-top']) ?>
                    </div>

                    <span class="select-image btn btn-primary shadow rounded-pill w-50"><i
                            class="fa-solid fa-cloud-arrow-up"></i> เลือกรูปภาพ</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12">
        <?= $this->render('_form_detail' . $model->asset_group . '.php', ['model' => $model, 'form' => $form]) ?>
    </div>
</div>

<!-- ถ้าเป็นรถยนต์ -->
<?php if($model->assetItem?->category_id == 4):?>
<?php echo $model->assetItem->id?>
<?= $this->render('asset_item',['model' => $model, 'form' => $form]) ?>
<?php endif;?>

<div class="form-group mt-4 d-flex justify-content-center">
    <?= Html::button('<i class="fa-solid fa-floppy-disk"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>
<?php ActiveForm::end(); ?>


<?php
$ref = $model->ref;
$urlUpload = Url::to('/filemanager/uploads/single');

$js = <<< JS



    \$('.select-image').click(function (e) { 
            \$('#file').click();
            
        });
        \$('#file').on('change', function (e) {
        const image = this.files[0];

        if (image.size < 2000000) {
            const reader = new FileReader();
            reader.onload = function () {
                const imgArea = \$('.img-area');
                imgArea.find('img').remove();

                const imgUrl = reader.result;
                const img = \$('<img>').attr('src', imgUrl);
                imgArea.append(img).addClass('active').data('img', image.name);

                const file = \$('#file').prop('files')[0];
                const formData = new FormData();
                formData.append("asset", file);
                formData.append("id", 1);
                formData.append("ref", '$ref');
                formData.append("name", 'asset');

                console.log(file);

                \$.ajax({
                    url: '$urlUpload',
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        console.log(res);
                        \$('.img-room').attr('src', res.img);
                        // await \$.pjax.reload({ container: response.container, history: false, replace: false, timeout: false });
                    }
                });
            };
            reader.readAsDataURL(image);
        } else {
            alert("Image size more than 2MB");
        }
    });

    
    // เลือก upload รูปภาพ
    $(".select-photo").click(function() {
        \$("input[id='my_file']").click();
    });

    $("input[id='my_file']").on("change", function() {
        var fileInput = \$(this)[0];
        if (fileInput.files && fileInput.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
            \$(".avatar-profile").attr("src", e.target.result);
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
            turl: '/filemanager/uploads/single',
            ttype: "POST",
            tdata: formdata,
            tprocessData: false,
            tcontentType: false,
            tsuccess: function (res) {
                            success('แก้ไขภาพสำเร็จ')
                            console.log(res)
            }
            });
                }
            }

JS;
$this->registerJs($js);
?>