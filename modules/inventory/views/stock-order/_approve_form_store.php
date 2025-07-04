<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use app\components\UserHelper;
use app\modules\approve\models\Approve;

$this->registerCssFile('@web/css/timeline.css');
$me = UserHelper::GetEmployee();
?>
        <div class="d-flex justify-content-center flex-column align-items-center mt-3">
        <div class="timeline-item">
            <?php
            try {
                echo $model->status == 'Pass' ? $model->viewApproveDate() : '';
            } catch (\Throwable $th) {
                //throw $th;
            }
            ?>
            <?php if($model->status == 'Approve'):?>
            <i class="bi bi-clock-history"></i> <?php echo $approveDate ?? '-'?>
            <?php endif;?>
            <?php if($model->status == 'ReqCancel' || $model->status == 'Cancel'):?>
            -
            <?php else:?>

            <?php if($model->status =="Pending"):?>
                <div class="d-flex gap-2">

                    <?php  echo Html::a('<i class="fa-solid fa-circle-check"></i> '.($model->data_json['label'] ?? ''),
                    ['/inventory/stock-order/approve-form-store','id' => $model->id],
                    [
                        'class' => 'btn btn-primary rounded-pill shadow btn-approve',
                        'data' => [
                            'id' => $model->id ,
                            'status' => 'Pass',
                            'label' =>($model->data_json['label'] ?? '')
                            ]
                            
                            ])?>
            <?php  echo Html::a('<i class="fa-solid fa-circle-check"></i> ไม่'.($model->data_json['label'] ?? ''),
                    ['/inventory/stock-order/approve-form-store','id' => $model->id],
                    [
                        'class' => 'btn btn-danger rounded-pill shadow btn-approve',
                        'data' => [
                            'id' => $model->id ,
                            'status' => 'Reject',
                            'label' =>"ไม่".($model->data_json['label'] ?? '')
                            ]
                            
                            ])?>
                            </div>

            <?php endif?>
            <?php endif?>
        </div>
    </div>
<?php

$js = <<<JS

//การอนุมัติ
$("body").on("click", ".btn-approve", async function (e) {
    e.preventDefault();

    var id = $(this).data('id');
    var topic = $(this).data('label');
    var status = $(this).data('status');
    var url = $(this).attr('href');

    Swal.fire({
        title: 'ยืนยัน?',
        text: topic + " ใช่หรือไม่!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                url: url,
                data: { id: id, status: status },
                dataType: "json",
                success: function (response) {
                    console.log('Response:', response);
                    if (response.status === 'success') {

                        Swal.fire({
                        title: 'กำลังบันทึกข้อมูล...',
                        text: 'โปรดรอสักครู่',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        timer: 1000,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    }).then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'บันทึกสำเร็จ',
                            showConfirmButton: false,
                            timer: 1000
                        }).then(() => {
                            window.location.reload();
                        });  
                    });
                    

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: response.message || 'โปรดลองอีกครั้ง',
                        });
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: error || 'โปรดลองอีกครั้ง',
                    });
                }
            });
        }
    });
});



JS;
$this->registerJS($js, View::POS_END);
?>