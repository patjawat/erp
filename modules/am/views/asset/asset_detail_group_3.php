<?php
use app\components\AppHelper;
use yii\helpers\Html;


?>


<div class="card mb-3">
    <div class="row g-0">
        <div class="col-md-4">
            <div class="position-relative p-2 d-flex">
                <?php // Html::img('@web/images/imac.png',['class' => 'img-fluid rounded-start p-5']);?>
                <div class="dropdown edit-field-half-left">
                    <div class="btn-icon btn-icon-sm btn-icon-soft-primary dropdown-toggle me-0 edit-field-icon"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-ellipsis"></i>
                    </div>
                    <div class="dropdown-menu dropdown-menu-right" style="">
                        <a href="#" class="dropdown-item select-photo">
                            <i class="fa-solid fa-file-image me-2 fs-5"></i>
                            <span>อัพโหลดภาพ</span>
                        </a>
                    </div>
                </div>

                <?=Html::img($model->showImg(), ['class' => 'avatar-profile object-fit-cover rounded m-auto', 'style' => 'max-width:100%;min-width: 320px;'])?>
                <input type="file" id="my_file" style="display: none;" />
                <a href="#" class="select-photo">

                </a>
            </div>
        </div>
        <div class="col-md-8">

            <div class="card border-0 shadow-none h-75">
                <div class="card-body">


                    <div class="d-flex justify-content-between align-item-middle">
                        <div>
                            <h5 class="card-title mb-0 position-relative" style="margin-left: 26px;">
                            <i class="fa-solid fa-circle-info" style="
      position: absolute;
    font-size: 47px;
    margin-left: -32px;
    margin-top: -4px;
    color: #2196F3;
"></i>
                                <?=Html::a('&nbsp;'.(isset($model->data_json['asset_name']) ? $model->data_json['asset_name'] : '-'),['/sm/asset-item/view','id' => $model->assetItem->id],['class' => 'btn btn-primary open-modal','data' => ['size' => 'modal-lg']])?>
                            </h5>
                            

                        </div>
                        <div>

                            <?=Html::a('<i class="fa-solid fa-qrcode"></i> QR-Code', ['qrcode', 'id' => $model->id], ['class' => 'open-modal btn btn-success rounded-pill shadow', 'data' => ['size' => 'modal-md']])?>
                            <?=Html::a('<i class="fa-solid fa-chart-line"></i> ค่าเสื่อม', ['depreciation', 'id' => $model->id], ['class' => 'open-modal btn btn-primary rounded-pill shadow', 'data' => ['size' => 'modal-lg']])?>
                            <?=Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-warning rounded-pill shadow'])?>

                            <!-- <div class="btn-group">
                                <span class="btn btn-light rounded-star">
                                    <i class="fa-solid fa-gear"></i>
                                    ตั้งค่า
                                </span>
                                <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><?=Html::a('<i class="fa-solid fa-weight-scale me-1"></i> รายการสอบเทียบ', ['/am/asset-detail', 'name' => 'calibration_items'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']])?>
                                    </li>
                                </ul>
                            </div> -->

                        </div>

                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
                            <ul class="list-inline">
                            <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">รหัส </span>
                                        <span class="text-danger"><?=$model->code?><span>
</li>
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">ประเภท </span>
                                    <?=isset($model->data_json['asset_type_text']) ? $model->data_json['asset_type_text'] : '-'?>
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">มูลค่า</span>
                                    <span class="text-white bg-primary badge rounded-pill fs-6">
                                        <?=number_format($model->price, 2)?>
                                    </span>
                                    บาท
                                </li>
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">วันเดือนปีทีซื้อ</span> :
                                    <?=Yii::$app->thaiFormatter->asDate($model->receive_date, 'medium')?></li>

                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">การจัดซื้อ</span> <?=$model->purchase_text?></li>

                                <?php if (isset($model->data_json['fsn_old']) && $model->data_json['fsn_old'] != ''): ?>
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">รหัสเดิม</span> <span
                                        class="text-danger"><?=$model->data_json['fsn_old']?><span></li>
                                <?php endif;?>

                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">ประจำหน่วยงาน</span>
                                    <?php if (isset($model->data_json['department_name']) && $model->data_json['department_name'] == ''): ?>
                                    <?=isset($model->data_json['department_name_old']) ? $model->data_json['department_name_old'] : ''?>
                                    <?php else: ?>
                                    <?=isset($model->data_json['department_name']) ? $model->data_json['department_name'] : ''?>
                                    <?php endif;?>
                                </li>

                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">S/N</span> <?=$model->serial_number?></li>


                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">วิธีได้มา</span> <?=$model->method_get?>
                                </li>
                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">ประเภทเงิน</span> :
                                    <?=$model->budget_type?>
                                </li>
                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">สถานะ</span> :
                                    <?=$model->statusName()?>
                                </li>

                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">ผู้ขาย/ผู้จำหน่าย/ผู้บริจาค</span> :
                                    <?=$model->vendor_name?></li>
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">ลักษณะ/คุณสมบัติ</span> :
                                    <p>
                                        <?=isset($model->data_json['asset_option']) ? $model->data_json['asset_option'] : ''?>
                                    </p>
                                </li>


                            </ul>

                        </div>
                        <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">

                            <!-- ถ้าเป็นรถ -->
                            <?=$model->isCar() ? $this->render('./is_cars/tax', ['model' => $model]) : ''?>
                            <?=$model->isComputer() ? $this->render('./is_computer/spec', ['model' => $model]) : ''?>

                            <div class="alert alert-primary bprder-0 d-flex justify-content-between" role="alert">
                                <span><i class="fa-solid fa-hourglass-end"></i> อัตราค่าเสื่อม
                                    <?=isset($model->data_json['depreciation']) ? $model->data_json['depreciation'] : ''?>
                                    ต่อปี</span>
                                <span><i class="fa-regular fa-clock"></i> อายุการใช้งาน
                                    <?=isset($model->data_json['service_life']) ? $model->data_json['service_life'] : ''?></span>
                            </div>

                            <?php if (isset($model->Retire()['progress'])): ?>
                            <div class="progress progress-sm mt-3 w-100">
                                <div class="progress-bar" role="progressbar"
                                    <?="style='width:" . $model->Retire()['progress'] . "%; background-color:" . $model->Retire()['color'] . ";  '"?>
                                    aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-2" style="width:100%;">
                                <div>
                                    <i class="fa-regular fa-clock"></i> <span class="fw-semibold">เหลือเวลา</span> :
                                    <?=AppHelper::CountDown($model->Retire()['date'])[0] != '-' ? AppHelper::CountDown($model->Retire()['date']) : "หมดอายุการใช้งาน"?>
                                </div>
                                |
                                <div>
                                    <i class="fa-solid fa-calendar-xmark"></i> <span class="fw-semibold">หมดอายุ</span>
                                    <span class="text-danger"><?=$model->Retire()['date'];?></span>
                                </div>
                            </div>
                            <?php endif;?>

                            <div
                                class="d-flex justify-content-between total font-weight-bold mt-4 bg-secondary-subtle rounded p-2">
                                <?=$model->getOwner()?>

                            </div>

                        </div>

                    </div>


                </div>
                <!-- End Card body -->

            </div>
            <!-- End card -->
        </div>


    </div>
</div>

<?php
$js = <<< JS
// เลือก upload รูปภาพ
$(".select-photo").click(function() {
    $("input[id='my_file']").click();
});

$("input[id='my_file']").on("change", function() {
    var fileInput = $(this)[0];
    if (fileInput.files && fileInput.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
        $(".avatar-profile").attr("src", e.target.result);
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
			url: '/filemanager/uploads/single',
			type: "POST",
			data: formdata,
			processData: false,
			contentType: false,
			success: function (res) {
                success('แก้ไขภาพสำเร็จ')
                console.log(res)
			}
		});
    }
}

$("button[id='summit']").on('click', function() {
    formdata = new FormData();
    if($("input[id='my_file']").prop('files').length > 0)
    {
		file = $("input[id='my_file']").prop('files')[0];
        formdata.append("avatar", file);
        formdata.append("id", 1);
        formdata.append("ref", '$model->ref');
        formdata.append("name", 'avatar');

        console.log(file);
		$.ajax({
			url: '/filemanager/uploads/single',
			type: "POST",
			data: formdata,
			processData: false,
			contentType: false,
			success: function (res) {
                // success('แก้ไขภาพ')
                console.log(res)
			}
		});
    }
})
JS;
$this->registerJs($js);
?>