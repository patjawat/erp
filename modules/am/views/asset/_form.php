<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\widgets\MaskedInput;
use app\components\AppHelper;
use app\modules\am\models\Asset;
use unclead\multipleinput\MultipleInput;

$title = Yii::$app->request->get('title');
$group = Yii::$app->request->get('group');

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



/* new  */
.image-preview {
    width: 100%;
    height: 200px;
    border: 2px dashed #ddd;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    overflow: hidden;
    position: relative;
}

.image-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.preview-placeholder {
    color: #6c757d;
    text-align: center;
}


.form-container {
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    padding: 30px;
    margin-bottom: 20px;
}

.form-section {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 20px;
    margin-bottom: 20px;
}

.form-section:last-child {
    border-bottom: none;
}

.section-title {
    border-left: 4px solid var(--bs-primary);
    padding: 10px;
    margin-bottom: 20px;
    color: #333;
    font-weight: 400;
    background-color:#d9e6f782
}
</style>
<?php $form = ActiveForm::begin([
    'id' => 'form-asset',
    'enableAjaxValidation' => true,
    'validationUrl' => ['/am/asset/validator'],
]); ?>

<?=$form->field($model, 'asset_item')->hiddenInput()->label(false);?>
<div class="row">
<div class="col-8">

<div class="card">
    <div class="card-body">

        <h5 class="card-title"><i class="bi bi-plus-circle"></i> ลงทะเบียนครุภัณฑ์</h5>
        <p class="card-text">กรุณากรอกข้อมูลครุภัณฑ์ให้ครบถ้วนและถูกต้อง</p>

            <!-- ข้อมูลทั่วไป -->
            <div class="form-section">
                <h5 class="section-title">ข้อมูลทั่วไป</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <?php
                       
                        echo $form->field($model, 'data_json[asset_item_name]', [
                                    'addon' => [
                                        'append' => ['content'=>Html::a('<i class="fa-solid fa-magnifying-glass"></i>',['/am/asset-items/list-item','title' => '<i class="bi bi-ui-checks"></i> แสดงทะเบียนรหัสทรัพย์สินย์'],['class' => 'btn btn-primary open-modal','data' => ['size' => 'modal-xl']]), 'asButton'=>true]
                                    ]
                               ])->textInput([
                            'maxlength' => true, 
                            'placeholder' => 'ค้นหาชื่อครุภัณฑ์จากปุ่มการค้นหา',
                            'readonly' => true,  // Make field readonly
                            'class' => 'form-control bg-secondary text-white'  // Add background color
                        ])->label('ชื่อครุภัณฑ์');
                        ?>
                       <?php
                        // $form->field($model, 'asset_item')->widget(Select2::classname(), [
                        //                 'data' => $model->ListAssetitem(),
                        //                 'options' => ['placeholder' => 'เลือกรายการครุภัณฑ์'],
                        //                 'pluginEvents' => [
                        //                     "select2:unselect" => "function() { 
                        //                         $('#asset-code').val('')
                        //                     }",
                        //                     "select2:select" => "function() {
                        //                         // console.log($(this).val());
                        //                         $.ajax({
                        //                             type: 'get',
                        //                             url: '".Url::to(['/depdrop/get-fsn'])."',
                        //                             data: {
                        //                                 asset_item: $(this).val(),
                        //                                 name:'asset_item'
                        //                             },
                        //                             dataType: 'json',
                        //                             success: function (res) {
                        //                                 console.log(res.code)
                        //                                 if(localStorage.getItem('fsn_auto') == 0){
                        //                                     $('#asset-code').val(res.fsn)
                        //                                     $('#asset-data_json-asset_name_text').val(res.title)
                        //                                 }
                        //                             }
                        //                         });
                        //                 }",],
                        //                 'pluginOptions' => [
                        //                 'allowClear' => true,
                        //                 ],
                        //             ])->label('ชื่อครุภัณฑ์');
                                    
                                    ?>
                    </div>
                    

                    <div class="col-md-6">
                         <?= $form->field($model, 'code')->textInput(['maxlength' => true])->label('หมายเลขครุภัณฑ์ FSN (Federal Stock Number)') ?>
                    </div>

                    <div class="col-md-6">
                        <?= $form->field($model, 'data_json[fsn_old]')->textInput(['maxlength' => true])->label('เลขครุภัณฑ์เดิม') ?>
                    </div>

                    <div class="col-md-6">
                    <?php
                echo $form->field($model, 'data_json[band]')->widget(Select2::classname(), [
                    'data' => $model->listBand(),
                    'options' => ['placeholder' => 'เลือกยี่ห้อ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                         'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้

                    ],
                ])->label("ยี่ห้อ")
                ?>
                    </div>

                    <div class="col-md-6">
                                      <?php
                echo $form->field($model, 'data_json[model]')->widget(Select2::classname(), [
                    'data' => $model->listModel(),
                    'options' => ['placeholder' => 'เลือกรุ่น/โมเดล...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                         'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้

                    ],
                ])->label("รุ่น/โมเดล")
                ?>
                       
                    </div>

                    <div class="col-md-3">
                        <?php
                echo $form->field($model, 'data_json[color_name]')->widget(Select2::classname(), [
                    'data' => $model->listColor(),
                    'options' => ['placeholder' => 'เลือกสี...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                         'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                    ],
                ])->label("สี")
                ?>

                    </div>

                    <div class="col-md-3">
                          <?php
                echo $form->field($model, 'data_json[unit]')->widget(Select2::classname(), [
                    'data' => $model->listUnit(),
                    'options' => ['placeholder' => 'ระบุ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                         'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                    ],
                ])->label("หน่วยนับ")
                ?>
                    </div>
                </div>
            </div>


            <!-- ข้อมูลการได้มา -->
            <div class="form-section">
                <h5 class="section-title">ข้อมูลการได้มา</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                       
                            <?php
                                echo $form->field($model, 'data_json[method_get]')->widget(Select2::classname(), [
                                    'data' => $model->ListMethodget(),
                                    'options' => ['placeholder' => 'กรุณาเลือก'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-method_get_text').val(data.text)
                                         }",
                                    ]
                                ])->label('วิธีได้มา');
                        ?>
                    </div>

                    <div class="col-md-6 purchase-method-field">
                         <?php
                                echo $form->field($model, 'purchase')->widget(Select2::classname(), [
                                    'data' => $model->ListPurchase(),
                                    'options' => ['placeholder' => 'กรุณาเลือก'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-purchase_text').val(data.text)
                                        }",
                                        ]
                                        ])->label('วิธีการได้มา');
                                        ?>
                    </div>

                    <div class="col-md-6">
                         <?php
                                echo $form->field($model, 'data_json[vendor_id]')->widget(Select2::classname(), [
                                    'data' => $model->ListVendor(),
                                    'options' => ['placeholder' => 'เลือกผู้จำหน่าย'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-vendor_text').val(data.text)
                                         }",
                                    ]
                                ])->label('ผู้ขาย/ผู้บริจาค');
                        ?>
                    </div>

                    <div class="col-md-6">
                        <label for="documentRef" class="form-label">เลขที่เอกสารอ้างอิง</label>
                        <input type="text" class="form-control" id="documentRef"
                            placeholder="เช่น เลขที่สัญญา, ใบสั่งซื้อ">
                    </div>
                </div>
            </div>

            <!-- ข้อมูลงบประมาณ -->
            <div class="form-section">
                <h5 class="section-title">ข้อมูลงบประมาณ</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                          <?php
                                echo $form->field($model, 'data_json[budget_type]')->widget(Select2::classname(), [
                                    'data' => $model->ListBudgetdetail(),
                                    'options' => ['placeholder' => 'กรุณาเลือก'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-budget_type_text').val(data.text)
                                         }",
                                    ]
                                ])->label('ประเภทเงิน');
                        ?>
                    </div>

                    <div class="col-md-4">
                           <?= $form->field($model, 'price')->textInput(['type' => 'number'])->label('ราคาแรกรับ') ?>
                    </div>

                    <div class="col-md-4">
                         <?= $form->field($model, 'on_year')->widget(MaskedInput::className(),['mask'=>'9999'])->label('ปีงบประมาณ') ?>
                    </div>

                    <div class="col-md-6">
                        <label for="budgetSource" class="form-label">แหล่งงบประมาณ</label>
                        <input type="text" class="form-control" id="budgetSource">
                    </div>

                    <div class="col-md-6">
                        <label for="department" class="form-label">หน่วยงานที่รับผิดชอบ</label>
                        <select class="form-select" id="department">
                            <option value="" selected="" disabled="">เลือกหน่วยงาน</option>
                            <option value="dept1">สำนักงานอธิการบดี</option>
                            <option value="dept2">คณะวิทยาศาสตร์</option>
                            <option value="dept3">คณะวิศวกรรมศาสตร์</option>
                            <option value="dept4">คณะมนุษยศาสตร์</option>
                            <option value="dept5">คณะศึกษาศาสตร์</option>
                            <option value="dept6">สำนักคอมพิวเตอร์</option>
                            <option value="dept7">สำนักหอสมุด</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ข้อมูลเฉพาะ -->
            <div class="form-section">
                <h5 class="section-title">ข้อมูลเฉพาะ</h5>
                <div class="row g-3">
                         <div class="col-6">
                            <?= $form->field($model, 'data_json[serial_number]')->textInput()->label('S/N') ?>
                        </div>
                        <div class="col-6">
                            <?= $form->field($model, 'data_json[license_plate]')->textInput()->label('เลขทะเบียน (รถยนต์)') ?>
                        </div>

    
                    
                    <div class="col-md-6">
                       <?php
                                echo $form->field($model, 'asset_status')->widget(Select2::classname(), [
                                    'data' => $model->ListAssetStatus(),
                                    'options' => ['placeholder' => 'กรุณาเลือก...'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-method_get_text').val(data.text)
                                         }",
                                    ]
                                ])->label('สถานะ');
                        ?>
                    </div>

                    <div class="col-md-12">
                        <label for="specifications" class="form-label">คุณลักษณะเฉพาะ</label>
                        <textarea class="form-control" id="specifications" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <!-- ข้อมูลสถานที่และวันที่ -->
            <div class="form-section">
                <h5 class="section-title">ข้อมูลสถานที่และวันที่</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="location" class="form-label">สถานที่ตั้ง</label>
                        <input type="text" class="form-control" id="location">
                    </div>

                    <div class="col-md-6">
                        <label for="building" class="form-label">อาคาร/ห้อง</label>
                        <input type="text" class="form-control" id="building">
                    </div>

                  
                        <div class="col-6">
                            <?=$form->field($model, 'receive_date')->textInput()->label('วันที่รับเข้า');
                        ?>
                        </div>
                        <div class="col-6">
                            <?=$form->field($model, 'data_json[expire_date]')->textInput()->label('วันหมดประกัน');
                        ?>
                        </div>

                    <div class="col-md-6">
                        <label for="inspectionDate" class="form-label">วันที่ตรวจรับ</label>
                        <input type="date" class="form-control" id="inspectionDate">
                    </div>

                    <div class="col-md-6">
                        <label for="responsiblePerson" class="form-label">ผู้รับผิดชอบ</label>
                        <input type="text" class="form-control" id="responsiblePerson">
                    </div>
                </div>
            </div>

            <!-- รูปภาพ -->
 

            <!-- หมายเหตุ -->


            <!-- ปุ่มดำเนินการ -->
            <div class="row mt-4">
                <div class="col-12 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" id="resetBtn">
                        <i class="bi bi-x-circle me-2"></i>ล้างข้อมูล
                    </button>
                    <div>
                        <button type="button" class="btn btn-outline-primary me-2" id="saveAsDraftBtn">
                            <i class="bi bi-save me-2"></i>บันทึกร่าง
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>บันทึกข้อมูล
                        </button>
                    </div>
                </div>
            </div>

    </div>
</div>

    
</div>
<div class="col-4">
    <div class="card">
        <div class="card-body">
           <!-- รูปภาพ -->
            <div class="form-section">
                <h5 class="section-title">รูปภาพครุภัณฑ์</h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="image-preview" id="imagePreview">
                            <div class="preview-placeholder">
                                <i class="bi bi-image" style="font-size: 3rem;"></i>
                                <p>ภาพตัวอย่างจะแสดงที่นี่</p>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <input type="file" class="form-control" id="equipmentImage" accept="image/*" multiple="">
                            <button class="btn btn-outline-secondary" type="button" id="clearImageBtn">ล้าง</button>
                        </div>
                        <div class="form-text">รองรับไฟล์ JPG, PNG หรือ GIF ขนาดไม่เกิน 5MB (สามารถอัพโหลดได้หลายรูป)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

                    <!-- หมายเหตุ -->
            <div class="form-section">
                <h5 class="section-title">หมายเหตุและเอกสารแนบ</h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="notes" class="form-label">หมายเหตุ</label>
                        <textarea class="form-control" id="notes" rows="3"></textarea>
                    </div>

                    <div class="col-md-12">
                        <label for="attachments" class="form-label">เอกสารแนบ</label>
                        <input type="file" class="form-control" id="attachments" multiple="">
                        <div class="form-text">รองรับไฟล์ PDF, DOC, DOCX, XLS, XLSX ขนาดไม่เกิน 10MB</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>





<?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'asset_group')->hiddenInput(['maxlength' => true])->label(false) ?>



        <!-- ถ้าเป็นรถยนต์ -->
        <?php if($model->assetItem?->category_id == 4):?>
        <?= $this->render('asset_item',['model' => $model, 'form' => $form]) ?>
        <?php endif;?>

        <div class="form-group mt-4 d-flex justify-content-center gap-3">
            <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
            <?php // Html::a('<i class="fa-solid fa-arrow-left"></i> ย้อนกลับ' , ['/am/asset/view','id' => $model->id], ['class' => 'btn btn-secondary rounded-pill shadow'])?>
            <?= Html::a('ย้อนกลับ', Yii::$app->request->referrer ?: ['/am/asset/view','id' => $model->id], ['class' => 'btn btn-secondary rounded-pill shadow']) ?>
        </div>
        <?php ActiveForm::end(); ?>


    </div>
</div>



<?php
$ref = $model->ref;
$urlUpload = Url::to('/filemanager/uploads/single');

$js = <<< JS
 //กำหนดให้ปฏิทินแสดงวันที่
 thaiDatepicker('#asset-receive_date,#asset-data_json-expire_date')



\$('#form-asset').on('beforeSubmit', function (e) {
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
                             window.location.reload(true);
                            // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                        }
                    }
                });

            }
            });
            return false;
        });

        
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