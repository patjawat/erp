<?php

use yii\helpers\Url;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use Yiisoft\Arrays\ArrayHelper;
use app\modules\helpdesk2\models\Helpdesk;

$helpdesk = Helpdesk::find()
    ->where(['IS NOT', 'title', null])
    ->andWhere(['id' => 125])
    ->orderBy(['id' => SORT_DESC]) // id จากมาก → น้อย
    ->one();

    // echo "<pre>";
    // print_r($helpdesk->viewTechRevice());
    // echo "</pre>";

?>

<?php $form = ActiveForm::begin(['id' => 'form']); ?>
<div class="row">
    <div class="col-6">
        <iframe id="preview-frame" src="<?= \yii\helpers\Url::to(['service/print', 'id' => $helpdesk->id]) ?>" width="100%"
            height="800px">
        </iframe>
    </div>
    <div class="col-6">


        <div class="position-relative">
            <div class="file-upload-btnxx btn btn-primary shadow rounded-pill">
                <i class="fa-solid fa-upload"></i>
                <span>คลิกอัปโหลดไฟล์ที่นี่</span>
            </div>
            <input type="file" class="file-upload-input" id="my_file" accept="pdf/*">
        </div>

        <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>

        <div class="row">
            <div class="col-6">


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
                    <?= $form->field($model, 'data_json[department_x]')->textInput()->label('ฝ่ายงนาที่ส่งซ่อม-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[department_y]')->textInput()->label('ฝ่ายงนาที่ส่งซ่อม-แนวตั้ง') ?>
                </div>

                <div class="d-flex gap-2">
                    <?= $form->field($model, 'data_json[location_x]')->textInput()->label('สถานที่-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[location_y]')->textInput()->label('สถานที่-แนวตั้ง') ?>
                </div>

                <div class="d-flex gap-2">
                    <?= $form->field($model, 'data_json[createdby_x]')->textInput()->label('ผู่ส่งซ่อม-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[createdby_y]')->textInput()->label('ผู่ส่งซ่อม-แนวตั้ง') ?>
                </div>
                <div class="d-flex gap-2">
                    <?= $form->field($model, 'data_json[createtime_x]')->textInput()->label('เวลาซ่อม-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[createtime_y]')->textInput()->label('เวลาซ่อม-แนวตั้ง') ?>
                </div>

                <div class="d-flex gap-2">
                    <?= $form->field($model, 'data_json[tech_receive_x]')->textInput()->label('ช่างผู้รับงาน-แนวนอน') ?>
                    <?= $form->field($model, 'data_json[tech_receive_y]')->textInput()->label('ช่างผู้รับงาน-แนวตั้ง') ?>
                </div>

            </div>
        </div>
    </div>

</div>




<?php ActiveForm::end(); ?>


<?php

$ref = $model->ref;

$urlUpload = Url::to('/filemanager/uploads/upload-pdf');
$formName = 'vehicle_layout_form'; // ชื่อแบบฟอร์มที่ใช้สำหรับการจัดเก็บ layout


$js = <<< JS

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
            formData.append("id", 1);
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
                        location.reload();
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