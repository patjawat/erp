<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use kartik\form\ActiveField;
// use kartik\widgets\DateTimePicker;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use kartik\datecontrol\DateControl;
use app\modules\hr\models\Employees;
use softark\duallistbox\DualListbox;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\Repair $model */
/** @var yii\widgets\ActiveForm $form */
$emp = Employees::findOne(['user_id' => Yii::$app->user->id]);
$this->title = 'ศูนย์คอมพิวเตอร์';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-computer fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('menu') ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('menu',['active' => 'index']) ?>
<?php $this->endBlock(); ?>



<?php $form = ActiveForm::begin([
        'id' => 'form-repair',
    ]); ?>
<!-- เอาเก็บข้อมูล auto -->
<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput()->label(false) ?>

<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-body">

                <div class="d-flex bg-danger justify-content-between bg-opacity-10 py-2 px-3 rounded mb-3">
                    <div class="d-flex gap-3">
                        <?php
                          try {
                              echo Html::img($model->asset->showImg(), ['class' => 'avatar avatar-xl object-fit-cover rounded m-auto mb-3 border border-2 border-secondary-subtle', 'style' => 'max-width:100%;min-width: 120px;']);
                            } catch (\Throwable $th) {
                                // throw $th;
                            }
                            ?>

                        <div class="d-flex flex-column">


                            <div>
                                <i class="fa-solid fa-triangle-exclamation text-danger"></i>
                                <?php if($model->code !== ''):?>
                                <span class="text-primary">แจ้งซ่อมครุภัณฑ์</span>
                                <?php echo $model->viewCreateDateTime()?>

                                <?php else:?>
                                <span class="text-primary">แจ้งซ่อมทั่วไป</span>
                                <?php echo $model->viewCreateDateTime()?>
                                <?php endif;?>
                            </div>
                            <span class="fw-semibold"><?php echo $model->title?></span>
                        </div>
                    </div>

                    <div>
                        <?php echo $model->viewCreateUser()?>
                    </div>
                </div>

                <div class="d-flex bg-primary justify-content-between bg-opacity-10 py-2 px-3 rounded mb-3">
                    <div><i class="bi bi-check2-circle fs-5"></i> <span
                            class="text-primary">การประเมินงานซ่อมและมอบหมายงาน</span>
                    </div>
                    <div>ขั้นตอนที่ <span class="badge rounded-pill bg-primary text-white">1</span> </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="border-1 border-primary p-3 rounded">
                            <?=$form->field($model, 'date_start')->textInput()->label('วันที่เริ่มดำเนินการซ่อม');?>
                            <?=$form->field($model, 'date_end')->textInput()->label('ซ่อมเสร็จวันที่');?>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border-1 border-primary p-3 rounded" style="height: 127px;">
                            <?= $form->field($model, 'data_json[price]')->textInput(['placeholder' => 'ระบุมูลค่าการซ่อมถ้ามี', 'type' => 'number'])->label('มูลค่าการซ่อม') ?>
                            <?=
                $form
                    ->field($model, 'data_json[repair_type]')
                    ->radioList(['ซ่อมภายใน' => 'ซ่อมภายใน', 'ซ่อมภายนอก' => 'ซ่อมภายนอก'], ['inline' => true, 'custom' => true])
                    ->label(false)
                // ->label('ประเภทารซ่อม')
                ?>



                        </div>
                    </div>

                </div>


                <div class="d-flex bg-primary justify-content-between bg-opacity-10 px-3 py-2 rounded mb-3">
                    <div><i class="bi bi-check2-circle fs-5"></i> <span
                            class="text-primary">สรุปผลดำเนินงานและวิธีแก้ไข</span>
                    </div>
                    <div>ขั้นตอนที่ <span class="badge rounded-pill bg-primary text-white">2</span> </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <?= $form->field($model, 'data_json[repair_note]')->textArea(['style' => 'height: 127px;', 'placeholder' => 'ระบุวิธีการแก้ไข/แนวทางแก้ไข/อื่นๆ...'])->label(false) ?>
                    </div>
                    <div class="col-4">
                        <?= $form->field($model, 'status')->widget(Select2::classname(), [
                                    'data' => $model->ListStatus(),
                                    'options' => ['placeholder' => 'เลือกสถานะ...'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-method_get_text').val(data.text)
                                         }",
                                    ]
                                ])->label('สถานะ') ?>
                    </div>
                </div>

            </div>
        </div>
        <div class="form-group mt-3 d-flex justify-content-center">
            <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
        </div>
    </div>
    <div class="col-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-person-circle"></i> ผู้ร่วมดำเนินงาน</h6>

                </div>
                <?php echo $model->StackTeam()?>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <?= Html::a('รายการ', [
                            '/helpdesk/team','category_id' => $model->id,'title' => '<i class="bi bi-person-circle"></i> ผู้ร่วมดำเนินงาน'
                        ], ['class' => 'open-modal','data' => ['size' => 'modal-lg']]) ?>
                <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> เพิ่ม', ['/helpdesk/team/create', 'id' => $model->id, 'name' => 'repair_team', 'title' => '<i class="fa-regular fa-pen-to-square"></i> ผู้ร่วมดำเนินงาน'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="fa-solid fa-store"></i> เบิกอะไหล่</h6>

                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <?= Html::a('รายการ', ['/helpdesk/spare-part/index','title' => '<i class="fa-regular fa-pen-to-square"></i> เบิกอะไหล่'], ['class' => 'open-modal','data' => ['size' => 'modal-lg']]) ?>
                <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> รายการเบิกอะไหล่', ['/helpdesk/spare-part/index','title' => '<i class="fa-regular fa-pen-to-square"></i> เบิกอะไหล่'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
            </div>
        </div>
    </div>
</div>





<?php ActiveForm::end(); ?>

<?php
$urlDateNow = Url::to(['/helpdesk/default/datetime-now']);
$js = <<< JS

thaiDatepicker('#helpdesk-date_start,#helpdesk-date_end')
            
$('#form-repair').on('beforeSubmit', function (e) {
    e.preventDefault(); // ป้องกันการส่งฟอร์มอัตโนมัติ
    var form = $(this);

    Swal.fire({
        title: "ยืนยันการส่งข้อมูล?",
        text: "คุณแน่ใจหรือไม่ว่าต้องการบันทึกฟอร์มนี้?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "ใช่, ส่งเลย!",
        cancelButtonText: "ยกเลิก"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: async function (response) {
                    form.yiiActiveForm('updateMessages', response, true);
                    if (response.status == 'success') {
                        $("#main-modal").modal("toggle");
                        success();
                        await $.pjax.reload({ container: '#purchase-container', history: false, timeout: false });
                        Swal.fire("สำเร็จ!", "ข้อมูลถูกบันทึกเรียบร้อย", "success");
                    }
                }
            });
        }
    });

    return false;
});


    
JS;
$this->registerJS($js)
?>