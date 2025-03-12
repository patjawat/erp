<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use app\components\UserHelper;
use app\modules\approve\models\Approve;

$this->registerCssFile('@web/css/timeline.css');
$me = UserHelper::GetEmployee();

$listApprove = Approve::find()->where(['name' => $name,'from_id' => $model->id])->all();
?>


<style>
.timeline {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    margin: 10px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 20%;
    left: 0;
    right: 0;
    height: 2px;
    background: #d3d3d3;
    z-index: 1;
}

.timeline-item {
    text-align: center;
    position: relative;
    z-index: 2;
}

.timeline-item .circle::before {
    content: '';
    width: 10px;
    height: 10px;
    background: #3b82f6;
    border-radius: 50%;
}

.timeline-item .year {
    font-weight: bold;
    margin-bottom: 5px;
    margin-top: 10px;
}

.timeline-item .description {
    color: #6b7280;
}
</style>


            <div class="alert alert-primary" role="alert">
                <h5 class="font-weight-bold text-center">การอนุมัติเห็นชอบ</h5>
            </div>

<div class="container mb-5">
    <div class="timeline">

        <?php foreach ($listApprove as $item): ?>
        <div class="timeline-item">
            <div class="circle">
                <?php echo Html::img($item->getAvatar()['photo'],['class' => 'avatar avatar-sm','style' =>'margin-right: 6px;'])?></div>
            <div class="year">
                <?php
                try {

                                                if ($item->status == 'None') {
                                                    echo '<i class="fa-solid fa-clock-rotate-left"></i> รอดำเนินการ';
                                                }else if ($item->status == 'Pending') {
                                                    echo '<i class="bi bi-hourglass-bottom  fw-semibold text-warning"></i> รอ' . ($item->level == 3 ? $item->title : ($item->data_json['label'] ?? ''));
                                                } else if ($item->status == 'Pass') {
                                                    echo '<i class="bi bi-check-circle fw-semibold text-success"></i> ' . ($item->data_json['label'] ?? '');
                                                } else if ($item->status == 'Reject') {
                                                    echo '<i class="bi bi-stop-circle  fw-semibold text-danger"></i> ไม่' . ($item->data_json['label'] ?? '') . ' <i class="bi bi-clock-history"></i> ';
                                                } else if ($item->status == 'Cancel') {
                                                }

                } catch (\Throwable $th) {
                    //throw $th;
                }
                                                
                                                ?>
            </div>
            <div class="description"><?php echo $item->getAvatar()['fullname']?></div>
            <?php
            try {
                echo $item->status == 'Pass' ? $item->viewApproveDate() : '';
            } catch (\Throwable $th) {
                //throw $th;
            }
            ?>
            <?php if($item->status == 'Approve'):?>
            <i class="bi bi-clock-history"></i> <?php echo $approveDate?>
            <?php endif;?>
            <?php if($model->order_status == 'ReqCancel' || $model->order_status == 'Cancel'):?>
            -
            <?php else:?>
                <?php if($item->emp_id == $me->id && $item->status =="Pending"):?>
                <?php  echo Html::a('<i class="fa-solid fa-circle-check"></i> '.($item->data_json['label'] ?? ''),
                    ['update','id' => $item->id],
                    [
                        'class' => 'btn btn-sm btn-primary rounded-pill shadow btn-approve',
                        'data' => [
                            'id' => $item->id ,
                            'status' => 'Pass',
                            'label' =>($item->data_json['label'] ?? '')
                            ]
                    
                    ])?>
                    <?php  echo Html::a('<i class="fa-solid fa-circle-check"></i> ไม่'.($item->data_json['label'] ?? ''),
                    ['update','id' => $item->id],
                    [
                        'class' => 'btn btn-sm btn-danger rounded-pill shadow btn-approve',
                        'data' => [
                            'id' => $item->id ,
                            'status' => 'Reject',
                            'label' =>"ไม่".($item->data_json['label'] ?? '')
                            ]
                    
                    ])?>

                <?php endif?>
            <?php endif?>
        </div>
        
        <?php endforeach ?>

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
                beforeSend: function() {
                   
                },
                success: function (response) {
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
