<?php

/** @var yii\web\View $this */
/** @var app\models\Development $model */

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Uploads;
use kartik\form\ActiveForm;
use app\components\ThaiDateHelper;
use app\modules\filemanager\components\FileManagerHelper;

$this->title = 'Layout Designer';
$pdfFile = Uploads::findOne(['name' => 'form_development_pdf', 'ref' => $model->ref]);


// กำหนดค่าเริ่มต้นเป็น null
$existingPdfUrl = null;

// ตรวจสอบว่ามี Object และไฟล์ในเครื่องจริงหรือไม่
if ($pdfFile) {
    // สมมติว่าไฟล์เก็บที่ @webroot/uploads/... หรือผ่าน Action show-pdf
    // ตรวจสอบเบื้องต้น (ถ้า FileManagerHelper มีฟังก์ชันเช็คไฟล์ให้ใช้ตัวนั้น)
    $existingPdfUrl = Url::to(['/filemanager/uploads/show-pdf', 'id' => $pdfFile->id]);
}

?>

<style>
    .main-header {
        background: #ffffff;
        border-bottom: 1px solid #dee2e6;
        padding: 10px 20px;
    }

    /* sidebar-panel สไตล์ Yii2 Admin */
    .sidebar-panel {
        background: #ffffff;
        border-left: 1px solid #dee2e6;
        height: calc(100vh - 60px);
        overflow-y: auto;
        padding: 20px;
    }

    /* ปรับให้รองรับไฟล์ที่ยาวกว่าหน้าจอ */
    .canvas-area {
        min-height: calc(100vh - 60px);
        overflow: auto;
        padding: 20px;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        /* เริ่มจากด้านบนเสมอ */
        background: #525659;
    }

    #pdf-render-container {
        position: relative;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        background: white;
        /* ให้ Container กว้างยาวตาม Canvas อัตโนมัติ */
        display: inline-block;
    }

    #labels-layer {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        /* เพื่อไม่ให้ขวางการคลิกอย่างอื่น แต่จะถูก override โดยลูก */
    }

    .draggable-label {
        pointer-events: auto;
        /* ให้ลากตัว label ได้ */
        position: absolute;
        /* ปรับแต่งความสวยงาม */
        padding: 1px 4px;
        background: rgba(255, 235, 59, 0.85);
        border: 1px solid #fbc02d;
        font-family: 'Sarabun', sans-serif;
        font-size: 12px;
    }

    .draggable-label.active {
        background: rgba(13, 110, 253, 0.3);
        border: 1px solid #0d6efd;
        box-shadow: 0 0 5px rgba(13, 110, 253, 0.5);
    }

    /* ส่วน Upload */
    .upload-zone {
        border: 2px dashed #0d6efd;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        background: #f8f9fa;
        margin-bottom: 20px;
        transition: 0.3s;
    }

    .upload-zone:hover {
        background: #e9ecef;
    }

    .field-card {
        border-radius: 8px;
        margin-bottom: 12px;
        transition: 0.2s;
    }

    .field-card:hover {
        border-color: #0d6efd;
    }
</style>



<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <strong class="text-primary me-3">Layout Designer</strong>
                        <span class="badge bg-secondary" id="file-name-display">ยังไม่ได้เลือกไฟล์</span>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary btn-sm me-2" onclick="location.reload()">ล้างข้อมูล</button>
                        <button class="btn btn-primary btn-sm px-4" id="btn-save-all">บันทึกตำแหน่งลงฐานข้อมูล</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ฝั่งซ้าย: PDF Preview Area -->
    <div class="col-md-9 canvas-area">
        <div id="pdf-render-container">
            <canvas id="pdf-canvas"></canvas>
            <!-- Labels จะถูกสร้างขึ้นที่นี่ด้วย JS -->
            <div id="labels-layer"></div>
        </div>

    </div>

    <!-- ฝั่งขวา: Config sidebar-panel -->
    <div class="col-md-3 sidebar-panel">
        <!-- 1. ส่วนอัปโหลด -->
        <div class="upload-zone">
            <h6>1. อัปโหลดเทมเพลต PDF</h6>
            <input type="file" id="pdf-upload" class="form-control form-control-sm" accept="application/pdf">
            <p class="small text-muted mt-2 mb-0">เลือกไฟล์ PDF หรือไฟล์ที่บันทึกไว้จะแสดงอัตโนมัติ</p>
        </div>

        <!-- 2. รายการฟิลด์ -->
        <h6>2. กำหนดตำแหน่งฟิลด์</h6>
        <div id="fields-list">
            <?php $form = ActiveForm::begin(['id' => 'form']); ?>
            <?= $form->field($model, 'name')->textInput()->label(false) ?>

            <!-- Field Item: ส่วนราชการ -->
            <div class="card field-card shadow-sm" data-field="company_name" data-title="ชื่อส่วนราชการ">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">ส่วนราชการ</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[company_name_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[company_name_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <!-- Field Item: ที่ -->
            <div class="card field-card shadow-sm" data-field="doc_number" data-title="เลขที่หนังสือ (ที่)">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">ที่(1234/1)</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[doc_number_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[doc_number_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <!-- Field Item: วันที่ -->
            <div class="card field-card shadow-sm" data-field="doc_date" data-title="วันที่หนังสือ">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">วันที่</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[doc_date_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[doc_date_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card field-card shadow-sm" data-field="fullname" data-title="นายทดสอบ ระบบพิมพ์">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">ด้วยข้าพเจ้า</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[fullname_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[fullname_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card field-card shadow-sm" data-field="fullname_signature" data-title="นายทดสอบ ระบบพิมพ์">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">ผู้ขออนุญาติ</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[fullname_signature_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[fullname_signature_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>
            <div class="card field-card shadow-sm" data-field="position_signature" data-title="นักจัดการงานทั่วไป">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">ตำแหน่งผู้ขอ</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[position_signature_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[position_signature_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card field-card shadow-sm" data-field="position" data-title="นักจัดการงานทั่วไป">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">ด้วยข้าพเจ้า</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[position_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[position_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>



            <div class="card field-card shadow-sm" data-field="topic" data-title="เพื่อไปเป็นวิทยากรสอนระบบ ERP">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">วัดถุประสงค์</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[topic_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[topic_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card field-card shadow-sm" data-field="location" data-title="มูลนิธิรามาธิบดี">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">สถานที่ไป</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[location_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[location_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card field-card shadow-sm" data-field="date_start" data-title="<?= ThaiDateHelper::formatThaiDate(date('Y-m-d'), 'medium') ?>">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">วันที่ไป</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[date_start_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[date_start_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>
            <?php
            $tomorrow_timestamp = strtotime('+1 day');
            $endDate =  date('Y-m-d', $tomorrow_timestamp);
            ?>
            <div class="card field-card shadow-sm" data-field="date_end" data-title="<?= ThaiDateHelper::formatThaiDate($endDate, 'medium') ?>">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">ถึงวันที่</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[date_end_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[date_end_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card field-card shadow-sm" data-field="vehicle_date_start" data-title="<?= ThaiDateHelper::formatThaiDate($endDate, 'medium') ?>(วันออกเดินทาง)">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">วันออกเดินทาง</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[vehicle_date_start_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[vehicle_date_start_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card field-card shadow-sm" data-field="vehicle_time_start" data-title="80:00">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">เวลาออกเดินทาง</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[vehicle_time_start_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[vehicle_time_start_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>


            <div class="card field-card shadow-sm" data-field="vehicle_date_end" data-title="<?= ThaiDateHelper::formatThaiDate($endDate, 'medium') ?>(วันกลับ)">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">วันกลับ</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[vehicle_date_end_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[vehicle_date_end_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card field-card shadow-sm" data-field="vehicle_time_end" data-title="16:00">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">เวลากลับ</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[vehicle_time_end_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[vehicle_time_end_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card field-card shadow-sm" data-field="claim_type_name" data-title="เบิกจากผู้จัด">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">การเบิกเงิน</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[claim_type_name_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[claim_type_name_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>


            <div class="card field-card shadow-sm" data-field="total_days" data-title="1">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">การเบิกเงิน</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[total_days_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[total_days_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>



            <div class="card field-card shadow-sm" data-field="vehicle_type" data-title="รถยนต์ส่วนตัว">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">พาหนะเดินทาง</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[vehicle_type_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[vehicle_type_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card field-card shadow-sm" data-field="assigned_to" data-title="นายสมชาย ผู้ปฏิบัติหน้าที่แทน">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">ผู้ปฏิบัติหน้าที่แทน</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[assigned_to_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[assigned_to_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>
            <div class="card field-card shadow-sm" data-field="assigned_to_position" data-title="นักวิชาการคอมพิวเตอร์">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">ตำแหน่งผู้ปฏิบัติหน้าที่แทน</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[assigned_to_position_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[assigned_to_position_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>


            <div class="card field-card shadow-sm" data-field="assigned_to_signature" data-title="นายสมชาย ผู้ปฏิบัติหน้าที่แทน">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">ลงชื่อผู้ปฏิบัติหน้าที่แทน</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[assigned_to_signature_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[assigned_to_signature_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>
            <div class="card field-card shadow-sm" data-field="assigned_to_position_signature" data-title="นักวิชาการคอมพิวเตอร์">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">ลงชื่อตำแหน่งผู้ปฏิบัติหน้าที่แทน</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[assigned_to_position_signature_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[assigned_to_position_signature_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card field-card shadow-sm" data-field="member_fullname_start" data-title="นายทดสอบ ใจดี">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">ชื่อคณะเดินทาง</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[member_fullname_start_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[member_fullname_start_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card field-card shadow-sm" data-field="member_position_start" data-title="เจ้าพนักงานพัสดุ">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">ตำแหน่งคณะเดินทาง</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[member_position_start_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[member_position_startt_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card field-card shadow-sm" data-field="approve_date" data-title="<?= ThaiDateHelper::formatThaiDate($endDate, 'medium') ?>">
                <div class="card-body p-2">
                    <div class="small fw-bold mb-2 text-primary">วันอนุมัติ</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[approve_date_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[approve_date_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control form-control-sm coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>




            <?php ActiveForm::end(); ?>
        </div>

        <div class="alert alert-info mt-3 small">
            <i class="bi bi-info-circle"></i> <b>คำแนะนำ:</b> ลาก Label สีเหลืองในหน้ากระดาษเพื่อปรับตำแหน่ง พิกัดจะอัปเดตอัตโนมัติ
        </div>
    </div>
</div>

<?php
// ลงทะเบียน JavaScript สำหรับ PDF.js และ jQuery UI
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js');
$this->registerJsFile('https://code.jquery.com/ui/1.13.2/jquery-ui.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$existingPdf = $existingPdfUrl;

$js = <<<JS
    const pdfjsLib = window['pdfjs-dist/build/pdf'];
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

   let pdfDoc = null;
let renderScale = 1.5; // Scale สำหรับแสดงผลบนจอ
const canvas = document.getElementById('pdf-canvas');
const ctx = canvas.getContext('2d');

async function loadPdfFromUrl(url) {
    try {
        const loadingTask = pdfjsLib.getDocument(url);
        const pdf = await loadingTask.promise;
        $('#file-name-display').text('ไฟล์ปัจจุบัน: ' + url.split('/').pop());
        renderPDFFromDoc(pdf);
    } catch (error) {
        console.error('Error loading PDF:', error);
    }
}

    // เมื่อเริ่มโหลดหน้าเว็บ ถ้ามีไฟล์เดิมให้ดึงมาแสดง
    const existingUrl = "{$existingPdf}";
    if (existingUrl) {
        loadPdfFromUrl(existingUrl);
    }

    $('#pdf-upload').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            $('#file-name-display').text(file.name);
            const reader = new FileReader();
            reader.onload = function() {
                const typedarray = new Uint8Array(this.result);
                renderPDF(typedarray);
            };
            reader.readAsArrayBuffer(file);
        }
    });

    // Render จาก ArrayBuffer (อัปโหลดใหม่)
    async function renderPDF(data) {
        pdfDoc = await pdfjsLib.getDocument(data).promise;
        renderPDFFromDoc(pdfDoc);
    }

  // แก้ไขในส่วน renderPDFFromDoc
async function renderPDFFromDoc(doc) {
    pdfDoc = doc;
    const page = await pdfDoc.getPage(1);
    
    // สำคัญ: ต้องใช้ viewBox เพื่อหาขนาดที่แท้จริงของ PDF
    const viewport = page.getViewport({ scale: 1 });
    const pdfWidth = viewport.width;   // หน่วยจะเป็น Points (เช่น 595.28)
    const pdfHeight = viewport.height; // หน่วยจะเป็น Points (เช่น 841.89)

    // ปรับ Scale แสดงผลให้พอดีกับหน้าจอ แต่ยังคงสัดส่วนเดิม
    const displayScale = 1.5; 
    const displayViewport = page.getViewport({ scale: displayScale });
    
    canvas.width = displayViewport.width;
    canvas.height = displayViewport.height;

    // เก็บค่าหน่วย Point ไว้ใน Canvas เพื่อใช้คำนวณ
    $(canvas).data('pdf-width', pdfWidth);
    $(canvas).data('pdf-height', pdfHeight);
    $(canvas).data('render-scale', displayScale);

    await page.render({ canvasContext: ctx, viewport: displayViewport }).promise;
    initLabels();
}

function initLabels() {
    $('#labels-layer').empty();
    
    // อ่านขนาดจริงจาก Data ที่เก็บไว้
    const pdfWidth = $(canvas).data('pdf-width');
    const pdfHeight = $(canvas).data('pdf-height');

    $('.field-card').each(function() {
        const id = $(this).data('field');
        const title = $(this).data('title');
        
        // ค่า X, Y จากฐานข้อมูล (หน่วย Points)
        let pdfX = parseFloat($(this).find('.coord-x').val()) || 50;
        let pdfY = parseFloat($(this).find('.coord-y').val()) || 50;
        
        // แปลงจาก Points เป็น Pixels บนจอเพื่อแสดงผลตำแหน่ง Label
        let screenX = pdfX * renderScale;
        let screenY = pdfY * renderScale;
        
        const label = $('<div class="draggable-label"></div>')
            .text(title || id)
            .attr('id', 'lbl-' + id)
            .data('target', id)
            .css({ left: screenX + 'px', top: screenY + 'px' });
            
        $('#labels-layer').append(label);
    });
        
      $(".draggable-label").draggable({
        containment: "#pdf-render-container",
        start: function() { $(this).addClass('active'); },
        stop: function(event, ui) {
            $(this).removeClass('active');
            const id = $(this).data('target');
            const card = $('.field-card[data-field="' + id + '"]');
            const scale = $(canvas).data('render-scale');
            // --- หัวใจความแม่นยำ: แปลง Pixels กลับเป็น Points ---
            // สูตร: พิกัดบน PDF = พิกัดบนจอ / scale
            let finalX = Math.round(ui.position.left / renderScale);
            let finalY = Math.round(ui.position.top / renderScale);
            let pdfX = (ui.position.left / scale).toFixed(2);
            let pdfY = (ui.position.top / scale).toFixed(2);
            
            // card.find('.coord-x').val(finalX);
            // card.find('.coord-y').val(finalY);
            card.find('.coord-x').val(pdfX);
            card.find('.coord-y').val(pdfY);
        }
    });
    }

    $(document).on('input', '.coord-x, .coord-y', function() {
        const card = $(this).closest('.field-card');
        const id = card.data('field');
        const x = card.find('.coord-x').val();
        const y = card.find('.coord-y').val();
        $('#lbl-' + id).css({ left: x + 'px', top: y + 'px' });
    });

    $('#btn-save-all').on('click', function() {
        var form = $('#form')
        var data = form.serialize();
        var action = form.attr('action') || form.attr('href') || window.location.href;
        
        $.ajax({
            url: action,
            type: 'POST',
            data: data,
            success: function(res) {
                if(res.status === 'success') {
                    alert('บันทึกสำเร็จ');
                } else {
                    alert(res.message || 'เกิดข้อผิดพลาด');
                }
            },
            error: function() {
                alert('ไม่สามารถติดต่อ Server ได้');
            }
        });
    });
    
JS;
$this->registerJs($js);
?>