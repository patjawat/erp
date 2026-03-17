<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\Helpdesk $model */

$this->title = 'แจ้งซ่อม (ฟอร์มใหม่)';
$this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
$this->params['breadcrumbs'][] = $this->title;

$emp = Employees::findOne(['user_id' => Yii::$app->user->id]);
?>

<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="erp-icon-box bg-primary bg-opacity-10">
                            <i class="bi bi-tools"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-semibold">แบบฟอร์มแจ้งซ่อม (เวอร์ชันใหม่)</h4>
                            <div class="text-muted small">สร้างใบแจ้งซ่อมโดยไม่กระทบระบบเดิม</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <?= Html::a('<i class="bi bi-x-lg me-1"></i>ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <?php $form = ActiveForm::begin([
            'id' => 'form-create-v2',
            'enableAjaxValidation' => true,
            'validationUrl' => ['/helpdesk/service/create-v2-validator'],
            'options' => ['enctype' => 'multipart/form-data'],
        ]); ?>

        <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'name')->hiddenInput(['value' => 'repair'])->label(false) ?>

        <div class="row g-3">
            <div class="col-12 col-xl-4">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-bottom d-flex align-items-center gap-2">
                                <div class="erp-icon-box bg-primary bg-opacity-10">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <h6 class="text-uppercase text-secondary m-0">Reporter</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Requester name</label>
                                    <div class="form-control bg-body-tertiary"><?= Html::encode($emp->fullname ?? '-') ?></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Department</label>
                                    <div class="form-control bg-body-tertiary"><?= Html::encode($emp?->departmentName() ?? '-') ?></div>
                                </div>
                                <div class="mb-3">
                                    <?= $form->field($model, 'data_json[phone]')
                                        ->textInput(['placeholder' => 'เบอร์โทรศัพท์ติดต่อ'])
                                        ->label('Phone') ?>
                                </div>
                                <div class="mb-0">
                                    <?= $form->field($model, 'data_json[email]')
                                        ->textInput(['placeholder' => 'อีเมล (ถ้ามี)'])
                                        ->label('Email') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-bottom d-flex align-items-center gap-2">
                                <div class="erp-icon-box bg-primary bg-opacity-10">
                                    <i class="bi bi-send"></i>
                                </div>
                                <h6 class="text-uppercase text-secondary m-0">Submit</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="submit" name="save_mode" value="draft" class="btn btn-outline-secondary">
                                        <i class="bi bi-save2 me-1"></i> Save Draft
                                    </button>
                                    <button type="submit" name="save_mode" value="submit" class="btn btn-primary">
                                        <i class="bi bi-send me-1"></i> Submit Request
                                    </button>
                                    <?= Html::a('<i class="bi bi-x-lg me-1"></i>Cancel', ['index'], ['class' => 'btn btn-outline-danger']) ?>
                                </div>
                                <div class="small text-muted mt-3">
                                    กด Submit เพื่อส่งงานเข้าระบบ ช่างจะเห็นในทะเบียนงานซ่อมตามสถานะ
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-xl-8">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-bottom d-flex align-items-center gap-2">
                                <div class="erp-icon-box bg-primary bg-opacity-10">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                                <h6 class="text-uppercase text-secondary m-0">Request Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <?= $form->field($model, 'title')
                                            ->textInput(['placeholder' => 'เช่น เครื่องพิมพ์พิมพ์ไม่ออก / ไฟดับบางจุด'])
                                            ->label('Subject') ?>
                                    </div>
                                    <div class="col-12">
                                        <?= $form->field($model, 'data_json[repair_note]')
                                            ->textArea(['rows' => 4, 'placeholder' => 'อธิบายอาการ/บริบทเพิ่มเติม เช่น เกิดเมื่อไหร่, ทำอะไรแล้ว, ข้อความ error, ผลกระทบ'])
                                            ->label('Description') ?>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <?= $form->field($model, 'device_type_id')->widget(Select2::classname(), [
                                            'data' => $model->listDeviceType(),
                                            'options' => ['placeholder' => 'เลือกประเภทอุปกรณ์ ...'],
                                            'pluginOptions' => [
                                                'allowClear' => true,
                                            ],
                                        ])->label('Category') ?>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <?= $form->field($model, 'data_json[urgency]')->widget(Select2::classname(), [
                                            'data' => $model->listUrgency(),
                                            'options' => ['placeholder' => 'เลือกความเร่งด่วน ...'],
                                            'pluginOptions' => [
                                                'allowClear' => true,
                                            ],
                                        ])->label('Priority') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-bottom d-flex align-items-center gap-2">
                                <div class="erp-icon-box bg-primary bg-opacity-10">
                                    <i class="bi bi-pc-display"></i>
                                </div>
                                <h6 class="text-uppercase text-secondary m-0">Asset Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <?= $form->field($model, 'asset_number')->widget(Select2::classname(), [
                                            'data' => $model->listAsset(),
                                            'options' => ['placeholder' => 'เลือกครุภัณฑ์ ...'],
                                            'pluginOptions' => [
                                                'allowClear' => true,
                                            ],
                                        ])->label('Asset') ?>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <?= $form->field($model, 'data_json[location]')
                                            ->textInput(['placeholder' => 'เช่น ห้อง 301, แผนกบัญชี'])
                                            ->label('Location') ?>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <?= $form->field($model, 'repair_group')->widget(Select2::classname(), [
                                            'data' => $model->listRepairGroup(),
                                            'options' => ['placeholder' => 'เลือกแผนกช่าง ...'],
                                            'pluginOptions' => [
                                                'allowClear' => true,
                                            ],
                                        ])->label('Department') ?>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <?= $form->field($model, 'request_repair_date')->widget(\app\widgets\datepicker\DatepickerThai::class, [
                                            'options' => ['placeholder' => 'เลือกวันที่ต้องการให้ซ่อม'],
                                        ])->label('วันที่ต้องการให้ซ่อม') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-bottom d-flex align-items-center gap-2">
                                <div class="erp-icon-box bg-primary bg-opacity-10">
                                    <i class="bi bi-paperclip"></i>
                                </div>
                                <h6 class="text-uppercase text-secondary m-0">Attachments</h6>
                            </div>
                            <div class="card-body">
                                <?= $model->upload('repair_request') ?>
                                <div class="small text-muted mt-2">รองรับการแนบไฟล์/รูปภาพเพื่อช่วยให้ช่างวิเคราะห์อาการได้เร็วขึ้น</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

