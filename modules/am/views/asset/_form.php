<?php
use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\widgets\MaskedInput;
use app\components\AppHelper;
use app\modules\am\models\Asset;
use app\modules\hr\models\Employees;
use unclead\multipleinput\MultipleInput;
use kartik\editors\Summernote;

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


</style>
<?php $form = ActiveForm::begin([
    'id' => 'form-asset',
    'enableAjaxValidation' => true,
    'validationUrl' => ['/am/asset/validator'],
     'fieldConfig' => ['options' => ['class' => 'form-group mb-1 mr-2 me-2']] // spacing form field groups
]); ?>

<?=$form->field($model, 'asset_item')->hiddenInput()->label(false);?>
<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-body">
                <!-- ข้อมูลทั่วไป -->
                <div class="form-section">
                    <h5 class="section-title">ข้อมูลทั่วไป</h5>
                    <div class="row g-3">
                        <div class="col-md-12">
                             <?php
                        echo $form->field($model, 'asset_name', [
                                    'addon' => [
                                        'append' => ['content'=>Html::a('<i class="fa-solid fa-magnifying-glass"></i>',['/am/asset-item/list-item','title' => '<i class="bi bi-ui-checks"></i> แสดงทะเบียนรหัสทรัพย์สิน'],['class' => 'btn btn-secondary open-modal','data' => ['size' => 'modal-xl']]), 'asButton'=>true]
                                    ]
                               ])->textInput([
                            'maxlength' => true, 
                            'placeholder' => 'ค้นหาชื่อครุภัณฑ์',
                            'readonly' => false,  // Make field readonly
                            'class' => 'form-control'  // Add background color
                        ])->label('ชื่อครุภัณฑ์');
                        ?>
                        </div>
                           <div class="col-md-6">
                            <?= $form->field($model, 'code')->textInput(['maxlength' => true])->label('หมายเลขครุภัณฑ์') ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                       
                        echo $form->field($model, 'fsn_number', [
                                    'addon' => [
                                        'append' => ['content'=>Html::a('<i class="fa-solid fa-magnifying-glass"></i>',['/am/fsn/list-fsn','title' => '<i class="bi bi-ui-checks"></i> แสดงทะเบียน FSN'],['class' => 'btn btn-secondary open-modal','data' => ['size' => 'modal-xl']]), 'asButton'=>true]
                                    ]
                               ])->textInput([
                            'maxlength' => true, 
                            'placeholder' => 'ค้นหาเลข FSN',
                            'readonly' => true,  // Make field readonly
                            'class' => 'form-control bg-primary text-white'  // Add background color
                        ])->label('FSN');
                        ?>
                        
                        </div>


                     

                        <div class="col-md-6">
                            <?= $form->field($model, 'data_json[fsn_old]')->textInput(['maxlength' => true])->label('เลขครุภัณฑ์เดิม') ?>
                        </div>

                        <div class="col-md-6">
                            <?php
                echo $form->field($model, 'data_json[brand]')->widget(Select2::classname(), [
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
                echo $form->field($model, 'data_json[asset_model]')->widget(Select2::classname(), [
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
                            <?=$form->field($model, 'department')->widget(\kartik\tree\TreeViewInput::className(), [
                                'name' => 'department',
                                'query' => app\modules\hr\models\Organization::find()->addOrderBy('root, lft'),
                                'value' => 1,
                                'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                                'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                                'fontAwesome' => true,
                                'asDropdown' => true,
                                'multiple' => false,
                                'options' => ['disabled' => false],
                            ])->label('หน่วยงานภายในตามโครงสร้าง');?>
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
                            <?=$form->field($model, 'data_json[asset_options]')->widget(Summernote::class, ['useKrajeePresets' => true])->label('คุณลักษณะเฉพาะ');?>
                        </div>
                    </div>
                </div>

                <!-- ข้อมูลสถานที่และวันที่ -->
                <div class="form-section">
                    <h5 class="section-title">ข้อมูลสถานที่และวันที่</h5>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <?=$form->field($model, 'data_json[location]')->textInput()->label('อาคาร/ห้อง');?>
                        </div>
                        <div class="col-6">
                            <?=$form->field($model, 'receive_date')->textInput()->label('วันที่รับเข้า');?>
                        </div>
                        <div class="col-6">
                            <?=$form->field($model, 'data_json[expire_date]')->textInput()->label('วันหมดประกัน');?>
                        </div>
                        <div class="col-md-6">
                            <?=$form->field($model, 'data_json[inspection_date]')->textInput()->label('วันที่ตรวจรับ');?>
                        </div>

                        <div class="col-md-6">
                            <?php
                                $url = \yii\helpers\Url::to(['/depdrop/employee']);
                                $owner = empty($model->owner) ? '' : Employees::findOne(['cid' => $model->owner])->fullname;
                                echo $form->field($model, 'owner')->widget(Select2::classname(), [
                                    // 'data' => $model->ListEmployees(),
                                    'initValueText'=>$owner,
                                    'options' => ['placeholder' => 'กรุณาเลือก'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'minimumInputLength' => 1,
                                        'language' => [
                                            'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                                        ],
                                        'ajax' => [
                                            'url' => $url,
                                            'dataType' => 'json',
                                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                        ],
                                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                        'templateResult' => new JsExpression('function(city) { return city.text; }'),
                                        'templateSelection' => new JsExpression('function (city) { return city.text; }'),
                                    ],
                                    'pluginEvents' => [
                                        // "select2:select" => "function(result) { 
                                        //     var data = $(this).select2('data')[0]
                                        //     $('#asset-data_json-method_get_text').val(data.text)
                                        //  }",
                                    ]
                                ])->label('ผู้รับผิดชอบ');
                        ?>
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
                            <?= Html::a('<i class="bi bi-arrow-left"></i> ย้อนกลับ', Yii::$app->request->referrer ?: ['/am/asset/view','id' => $model->id], ['class' => 'btn btn-secondary shadow']) ?>
                            <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary shadow', 'id' => 'summit']) ?>

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

                <label class="form-label mb-0">รูปภาพทรัพย์สิน</label>
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
        </div>
    </div>
</div>





<?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'asset_group')->hiddenInput(['maxlength' => true])->label(false) ?>


<?php //  $this->render('_form_detail3',['model' => $model, 'form' => $form]) ?>

<!-- ถ้าเป็นรถยนต์ -->
<?php if($model->assetItem?->category_id == 4):?>
<?= $this->render('asset_item',['model' => $model, 'form' => $form]) ?>
<?php endif;?>

<?php ActiveForm::end(); ?>


</div>
</div>



<?php
$ref = Json::encode($model->ref); // ปลอดภัยแม้มีอักขระพิเศษ
$urlUpload = Url::to('/filemanager/uploads/single');

$js = <<< JS
 //กำหนดให้ปฏิทินแสดงวันที่
 thaiDatepicker('#asset-receive_date,#asset-data_json-expire_date,#asset-data_json-inspection_date')

 isFile()
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
                 uploadImage('asset',$ref);
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

    
JS;
$this->registerJs($js);
?>