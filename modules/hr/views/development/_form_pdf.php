<?php
use yii\helpers\Url;
use kartik\widgets\ActiveForm;

?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
  <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">

       <?= $this->title; ?>
  </h4>
</div>
<?php $this->endBlock(); ?>



    <style>
  
        /* สไตล์หน้ากระดาษจำลอง */
        #pdf-container {
            width: 794px; /* ขนาด A4 ในหน่วย Pixel ที่ 96 DPI (โดยประมาณ) */
            height: 1123px;
            background-image: url('https://via.placeholder.com/794x1123/ffffff/808080?text=PDF+Background+Image'); 
            background-size: contain;
            background-repeat: no-repeat;
            position: relative;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            margin: 0 auto;
        }

        /* สไตล์ของ Label ที่ลากได้ */
        .draggable-label {
            position: absolute;
            padding: 4px 8px;
            background: rgba(13, 110, 253, 0.1);
            border: 1px solid #0d6efd;
            color: #0d6efd;
            cursor: move;
            font-size: 12px;
            white-space: nowrap;
            border-radius: 4px;
            font-weight: bold;
        }

        .draggable-label:hover {
            background: rgba(13, 110, 253, 0.2);
        }

        /* จัดระเบียบ Sidebar */
        .control-panel {
            height: 100vh;
            overflow-y: auto;
            background: white;
            border-left: 1px solid #dee2e6;
            padding: 20px;
        }
    </style>

    <div class="row">
        <div class="col-md-8 p-4 overflow-auto" style="height: 100vh;">
            <div class="d-flex justify-content-between mb-3 bg-white p-2 rounded shadow-sm">
                <h5 class="m-0">Preview: แบบฟอร์มขออนุญาต</h5>
                <div>
                    <button class="btn btn-outline-secondary btn-sm">Zoom Out</button>
                    <button class="btn btn-outline-secondary btn-sm">Zoom In</button>
                </div>
            </div>
            
            <div id="pdf-container">
                <div id="label_dept_h" class="draggable-label" data-target="dept_h" style="top: 150px; left: 100px;">ส่วนราชการ-แนวนอน</div>
                <div id="label_dept_v" class="draggable-label" data-target="dept_v" style="top: 200px; left: 100px;">ส่วนราชการ-แนวตั้ง</div>
                <div id="label_repair_no" class="draggable-label" data-target="repair_no" style="top: 250px; left: 100px;">เลขที่ส่งซ่อม</div>
            </div>
        </div>

        <div class="col-md-4 control-panel">
            <h4 class="mb-4">ตั้งค่าตำแหน่ง</h4>
            <form id="coord-form">
                
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title text-primary">ส่วนราชการ</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="small">แนวแกน X</label>
                                <input type="number" class="form-control coord-input" id="dept_h" value="100">
                            </div>
                            <div class="col-6">
                                <label class="small">แนวแกน Y</label>
                                <input type="number" class="form-control coord-input" id="dept_v" value="150">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title text-primary">เลขที่ส่งซ่อม</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="small">แกน X</label>
                                <input type="number" class="form-control coord-input" id="repair_no_x" value="100">
                            </div>
                            <div class="col-6">
                                <label class="small">แกน Y</label>
                                <input type="number" class="form-control coord-input" id="repair_no_y" value="250">
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <button type="button" class="btn btn-success w-100 py-2">บันทึกตำแหน่งทั้งหมด</button>
            </form>
        </div>
    </div>






<?php $form = ActiveForm::begin(['id' => 'form']); ?>
<div class="row">
    <div class="col-6">
        <iframe id="preview-frame" src="<?= \yii\helpers\Url::to(['/hr/development/preview-pdf']) ?>" width="100%"
            height="800px">
        </iframe>
    </div>
    <div class="col-6">
        <div class="position-relative">
            <div class="file-upload-btnxx btn btn-primary shadow rounded-pill">
                <i class="fa-solid fa-upload"></i>
                <span>คลิกอัปโหลดไฟล์แบบฟอร์มแจ้งซ่อมที่นี่</span>
            </div>
            <input type="file" class="file-upload-input" id="my_file" accept="pdf/*">
        </div>

        <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>

        <div class="row">
            <div class="col-6">


                <div class="d-flex gap-2">
                    <?= $form->field($model, 'data_json[company_name_x]')->textInput()->label('ส่วนราชการ-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[company_name_y]')->textInput()->label('ส่วนราชการ-แนวตั้ง') ?>
                </div>

                          <div class="d-flex gap-2">
                    <?= $form->field($model, 'data_json[tecdev_number_x]')->textInput()->label('แผนกช่าง-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[tecdev_number_y]')->textInput()->label('แผนกช่าง-แนวตั้ง') ?>
                </div>
                <div class="d-flex gap-2">
                    <?= $form->field($model, 'data_json[repair_number_x]')->textInput()->label('เลขที่ส่งซ่อม-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[repair_number_y]')->textInput()->label('เลขที่ส่งซ่อม-แนวตั้ง') ?>
                </div>

                <div class="d-flex gap-2">
                    <?= $form->field($model, 'data_json[urgency_x]')->textInput()->label('ความเร่งด่วน-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[urgency_y]')->textInput()->label('ความเร่งด่วน-แนวตั้ง') ?>
                </div>

                <div class="d-flex gap-2">
                    <?= $form->field($model, 'data_json[title_x]')->textInput()->label('สาเหตุที่ส่งซ่อม-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[title_y]')->textInput()->label('สาเหตุที่ส่งซ่อม-แนวตั้ง') ?>
                </div>

                <div class="d-flex gap-2">
                    <?= $form->field($model, 'data_json[device_x]')->textInput()->label('ประเภทอุปกรณ์-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[device_y]')->textInput()->label('ประเภทอุปกรณ์-แนวตั้ง') ?>
                </div>
                <div class="d-flex gap-2">
                    <?= $form->field($model, 'data_json[created_x]')->textInput()->label('วันที่ซ่อม-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[created_y]')->textInput()->label('วันที่ซ่อม-แนวตั้ง') ?>
                </div>


            </div>
            <div class="col-6">
                <div class="d-flex gap-2">
                    <?= $form->field($model, 'data_json[department_x]')->textInput()->label('ฝ่ายงานที่ส่งซ่อม-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[department_y]')->textInput()->label('ฝ่ายงานที่ส่งซ่อม-แนวตั้ง') ?>
                </div>

                <div class="d-flex gap-2">
                    <?= $form->field($model, 'data_json[location_x]')->textInput()->label('สถานที่-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[location_y]')->textInput()->label('สถานที่-แนวตั้ง') ?>
                </div>

                <div class="d-flex gap-2">
                    <?= $form->field($model, 'data_json[createdby_x]')->textInput()->label('ผู้ส่งซ่อม-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[createdby_y]')->textInput()->label('ผู้ส่งซ่อม-แนวตั้ง') ?>
                </div>
                <div class="d-flex gap-2">
                    <?= $form->field($model, 'data_json[createtime_x]')->textInput()->label('เวลาซ่อม-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[createtime_y]')->textInput()->label('เวลาซ่อม-แนวตั้ง') ?>
                </div>

                <div class="d-flex gap-2">
                    <?= $form->field($model, 'data_json[tech_receive_x]')->textInput()->label('ช่างผู้รับงาน-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[tech_receive_y]')->textInput()->label('ช่างผู้รับงาน-แนวตั้ง') ?>
                </div>
                <div class="d-flex gap-2">
                    <?= $form->field($model, 'data_json[note_x]')->textInput()->label('หมายเหตุ-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[note_y]')->textInput()->label('หมายเหตุ-แนวตั้ง') ?>
                </div>

            </div>
        </div>
    </div>

</div>




<?php ActiveForm::end(); ?>


<?php
$ref = $model->ref;


$urlUpload = Url::to('/filemanager/uploads/upload-pdf');
// $formName = 'development_pdf_layout'; // ชื่อแบบฟอร์มที่ใช้สำหรับการจัดเก็บ layout
$formName = 'form_development_pdf'; // ชื่อแบบฟอร์มที่ใช้สำหรับการจัดเก็บ layout


$js = <<< JS


$(function() {
    // เปิดการใช้งาน Draggable
    $(".draggable-label").draggable({
        containment: "#pdf-container",
        stop: function(event, ui) {
            // เมื่อวาง Label ให้เอาค่าพิกัดไปใส่ในช่อง Input ด้านขวา
            let targetId = $(this).data('target');
            // ในที่นี้สมมติโครงสร้าง ID ของ Input ไว้
            if(targetId === 'dept_h' || targetId === 'dept_v') {
                $('#dept_h').val(Math.round(ui.position.left));
                $('#dept_v').val(Math.round(ui.position.top));
            } else if(targetId === 'repair_no') {
                $('#repair_no_x').val(Math.round(ui.position.left));
                $('#repair_no_y').val(Math.round(ui.position.top));
            }
        }
    });

    // ถ้าพิมพ์ตัวเลขใน Input ให้ Label ขยับตาม (Two-way binding แบบง่าย)
    $('.coord-input').on('change keyup', function() {
        // โค้ดส่วนนี้สำหรับอัปเดตตำแหน่ง Label เมื่อพิมพ์ตัวเลข
        // (ต้องเขียน Logic แมตช์ ID เพิ่มเติม)
    });
});


   handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });

    $('#form input').on('keyup', async function(e) {
        await viewPdf()
    });
    
let timer;
async function viewPdf()
{
clearTimeout(timer); // เคลียร์ timeout เดิม
    timer = await setTimeout( async function() {
        var form = $('#form');
        await $.ajax({
            type: "post",
            url: form.attr('action'),
            data: form.serialize(),
            dataType: "json",
            success: function(response) {
                // โหลด iframe ใหม่หลังบันทึก
                const iframe = document.getElementById('preview-frame');
                if (iframe) {
                    iframe.src = iframe.src.split('?')[0] + '?id=1';
                }
            }
        });
    }, 800); // 0.8 วินาทีหลังพิมพ์หยุด
}

// อัปโหลด PDF
$('#my_file').on('change', function (e) {

    const file = this.files[0];
    if (!file) return;

    if (file.type !== 'application/pdf') {
        Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด',
            text: 'กรุณาเลือกไฟล์ PDF เท่านั้น'
        });
        $(this).val('');
        return;
    }

    Swal.fire({
        title: 'ยืนยันการอัปโหลด?',
        text: 'คุณต้องการอัปโหลดไฟล์ PDF นี้หรือไม่',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ใช่, อัปโหลด',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append("$formName", file);
            // formData.append("id", 1);
            formData.append("ref", '$ref');
            formData.append("name", '$formName');

            $.ajax({
                url: '$urlUpload',
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'อัปโหลดสำเร็จ',
                        showConfirmButton: false,
                        timer: 1200
                    }).then(() => {
                        // location.reload();
                    });
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถอัปโหลดไฟล์ได้'
                    });
                }
            });
        } else {
            $('#my_file').val('');
        }
    });
});

JS;
$this->registerJs($js);
?>