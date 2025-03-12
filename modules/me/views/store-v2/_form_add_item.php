<?php
use yii\helpers\Html;
use kartik\form\ActiveForm;
?>

<?php $form = ActiveForm::begin([
        'id' => 'form',
    ]); ?>


<div class="card position-relative shadow-none">
            <p class="position-absolute top-0 end-0 p-2">
                <i class="fa-solid fa-circle-info fs-4"></i>
            </p>
            <?php echo isset($model->product) ? Html::img($model->product->ShowImg(), ['class' => 'card-top object-fit-cover','style' => 'max-height: 155px;']) : ''; ?>
            <div class="card-body w-100">
                <div class="d-flex justify-content-start align-items-center">
                    <?php if($model->SumQty() >= 1):?>
                    <span class="badge text-bg-primary  mt--45"><?php echo $model->SumQty(); ?>
                        <?php echo $model->product->unit_name; ?></span>
                    <?php else:?>
                    <span class="btn btn-sm btn-secondary fs-13 mt--45 rounded-pill"> หมด</span>
                    <?php endif;?>
                </div>

                
                <div class="row">
                    <div class="col-8">
                        
                        <p class="text-truncate mb-0" id="product-name"><?php echo $model->product->title; ?></p>
                        
                        <div class="fw-semibold text-danger">
                            <?php echo number_format($model->unit_price,2); ?>
                        </div>
                    </div>
                    <div class="col-4">
                    <?php echo $form->field($model, 'qty')->textInput(['placeholder' => 'จำนวน','class' => 'form-control form-control-md rounded-pill border-0 bg-secondary text-opacity-100 bg-opacity-10 is-valid fs-5'])->label(false);?>
                </div>
            </div>
        </div>
    </div>
    <?php echo $form->field($model, 'asset_item')->textInput()->label(true);?>

        <div class="form-group mt-3 d-flex justify-content-center">
        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']); ?>
    </div>

    <?php ActiveForm::end(); ?>
    <?php
$js = <<< JS
    $('#form').on('beforeSubmit', function (e) {
    e.preventDefault();
    Swal.fire({
        title: 'ยืนยัน',
        text: 'เพิ่ม '+$('#product-name').text()+' ในตะกร้า',
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "ใช่, ยืนยัน!",
        cancelButtonText: "ยกเลิก",
    }).then(async (result) => {
        if (result.isConfirmed) { // ใช้ isConfirmed แทน result.value == true
            var form = $(this);
            $.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: async function (response) {
                    form.yiiActiveForm('updateMessages', response, true);
                    
                    if (response.status === 'error') {
                        Swal.fire({
                            title: "เกิดข้อผิดพลาด!",
                            text: response.message,
                            icon: "error"
                        });
                    }
                    
                    if (response.status === 'success') {
                        Swal.fire({
                            title: "บันทึกสำเร็จ!",
                            text: "ระบบจะรีโหลดอัตโนมัติ",
                            icon: "success",
                            timer: 1000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        title: "เกิดข้อผิดพลาด!",
                        text: "ไม่สามารถส่งข้อมูลได้",
                        icon: "error"
                    });
                }
            });
        }
    });
    return false;
});

JS;
$this->registerJS($js);
?>
