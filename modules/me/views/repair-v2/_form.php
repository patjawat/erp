<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\form\ActiveField;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;


/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\Repair $model */
/** @var yii\widgets\ActiveForm $form */
$emp = Employees::findOne(['user_id' => Yii::$app->user->id]);

?>
<?php $form = ActiveForm::begin([
        'id' => 'form-repair',
        'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
        'validationUrl' => ['/helpdesk/repair/create-validator']
    ]); ?>

<div class="card">
                <div class="card-body">
                    <form id="maintenanceRequestForm">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="alert alert-primary">
                                    <i class="bi bi-info-circle me-2"></i>
                                    กรุณากรอกข้อมูลให้ครบถ้วนเพื่อความรวดเร็วในการดำเนินการ
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="equipmentType" class="form-label">ประเภทอุปกรณ์ <span class="text-danger">*</span></label>
                                <select class="form-select" id="equipmentType" required="">
                                    <option value="" selected="" disabled="">เลือกประเภทอุปกรณ์</option>
                                    <option value="computer">คอมพิวเตอร์/โน๊ตบุ๊ค</option>
                                    <option value="printer">เครื่องพิมพ์/สแกนเนอร์</option>
                                    <option value="network">อุปกรณ์เครือข่าย</option>
                                    <option value="ac">เครื่องปรับอากาศ</option>
                                    <option value="electrical">ระบบไฟฟ้า</option>
                                    <option value="plumbing">ระบบประปา</option>
                                    <option value="furniture">เฟอร์นิเจอร์</option>
                                    <option value="other">อื่นๆ</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="equipmentId" class="form-label">รหัสอุปกรณ์ (ถ้ามี)</label>
                                <input type="text" class="form-control" id="equipmentId" placeholder="เช่น PC-001, AC-102">
                            </div>

                            <div class="col-12">
                                <label for="issueDescription" class="form-label">รายละเอียดปัญหา <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="issueDescription" rows="4" placeholder="กรุณาอธิบายปัญหาที่พบโดยละเอียด" required=""></textarea>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="location" class="form-label">สถานที่ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="location" placeholder="เช่น ห้อง 301, แผนกบัญชี" required="">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="department" class="form-label">แผนก <span class="text-danger">*</span></label>
                                <select class="form-select" id="department" required="">
                                    <option value="" selected="" disabled="">เลือกแผนก</option>
                                    <option value="accounting">บัญชี</option>
                                    <option value="hr">ทรัพยากรบุคคล</option>
                                    <option value="it">ไอที</option>
                                    <option value="marketing">การตลาด</option>
                                    <option value="sales">ขาย</option>
                                    <option value="production">ฝ่ายผลิต</option>
                                    <option value="logistics">โลจิสติกส์</option>
                                    <option value="admin">ธุรการ</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="priority" class="form-label">ความเร่งด่วน <span class="text-danger">*</span></label>
                                <select class="form-select" id="priority" required="">
                                    <option value="" selected="" disabled="">เลือกความเร่งด่วน</option>
                                    <option value="low">ต่ำ - สามารถรอได้</option>
                                    <option value="medium">ปานกลาง - ควรซ่อมภายใน 3 วัน</option>
                                    <option value="high">สูง - ต้องซ่อมภายใน 24 ชั่วโมง</option>
                                    <option value="critical">วิกฤต - ต้องซ่อมทันที</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="requestDate" class="form-label">วันที่ต้องการให้ซ่อม</label>
                                <input type="date" class="form-control" id="requestDate">
                            </div>

                            <div class="col-12">
                                <label class="form-label">รูปภาพประกอบ (ถ้ามี)</label>
                                <div class="custom-file-upload" id="imageUpload">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                    <p class="mb-0">คลิกเพื่ออัพโหลดรูปภาพ หรือลากไฟล์มาวาง</p>
                                    <p class="text-secondary small mb-0">รองรับไฟล์ JPG, PNG ขนาดไม่เกิน 5MB</p>
                                </div>
                                <input type="file" id="fileUpload" class="d-none" accept="image/*" multiple="">
                            </div>

                            <div class="col-12" id="imagePreviewContainer" style="display: none;">
                                <div class="d-flex flex-wrap gap-2 mt-2" id="imagePreview"></div>
                            </div>

                            <div class="col-12">
                                <label for="additionalNotes" class="form-label">หมายเหตุเพิ่มเติม</label>
                                <textarea class="form-control" id="additionalNotes" rows="2" placeholder="ข้อมูลเพิ่มเติมที่อาจเป็นประโยชน์ต่อการซ่อม"></textarea>
                            </div>

                            <div class="col-12 d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-outline-secondary me-2">ยกเลิก</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-1"></i>
                                    บันทึก
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>


    <?php ActiveForm::end(); ?>