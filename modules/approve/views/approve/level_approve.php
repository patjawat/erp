<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use app\components\UserHelper;

$this->registerCssFile('@web/css/timeline.css');
$me = UserHelper::GetEmployee();
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



<?php $form = ActiveForm::begin([
    'id' => 'form-approve',
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/me/leave/approve-validator'],
])
?>
        <?php  echo $form->field($model, 'id')->textInput()->label(false)?>
        <?php  echo $form->field($model, 'status')->textInput()->label(false)?>
        <?php  echo $form->field($model, 'data_json[note]')->textArea()->label(false)?>


<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>

<?php ActiveForm::end(); ?>



<div class="container mb-5">
    <div class="timeline">

        <?php foreach ($model->leave->listApprove() as $item): ?>
        <div class="timeline-item">
            <div class="circle">
                <?php echo Html::img($item->getAvatar()['photo'],['class' => 'avatar avatar-sm','style' =>'margin-right: 6px;'])?></div>
            <div class="year">
                <?php
                                                $approveDate = $item->viewApproveDate();
                                                if ($item->status == 'None') {
                                                    echo '<i class="fa-solid fa-clock-rotate-left"></i> รอดำเนินการ';
                                                }else if ($item->status == 'Pending') {
                                                    echo '<i class="bi bi-hourglass-bottom  fw-semibold text-warning"></i> รอ' . ($item->level == 3 ? $item->title : ($item->data_json['topic'] ?? ''));
                                                } else if ($item->status == 'Approve') {
                                                    echo '<i class="bi bi-check-circle fw-semibold text-success"></i> ' . ($item->data_json['topic'] ?? '');
                                                } else if ($item->status == 'Reject') {
                                                    echo '<i class="bi bi-stop-circle  fw-semibold text-danger"></i> ไม่' . ($item->data_json['topic'] ?? '') . ' <i class="bi bi-clock-history"></i> ' . $approveDate;
                                                } else if ($item->status == 'Cancel') {
                                                }
                                                ?>
            </div>
            <div class="description"><?php echo $item->getAvatar()['fullname']?></div>
            <?php if($item->status == 'Approve'):?>
            <i class="bi bi-clock-history"></i> <?php echo $approveDate?>
            <?php endif;?>

            <?php if($model->status == 'ReqCancel' || $model->status == 'Cancel'):?>
            -
            <?php else:?>

            <?php if($item->level == 3 && $item->status == 'Pending'):?>
                
            <button type="button" class="btn btn-sm btn-primary rounded-pill shadow approve"
                data-topic="ตรวจ<?php echo ($item->data_json['topic'] ?? '')?>" data-id="<?php echo $item->id?>"
                data-status="Approve">
                <i class="fa-solid fa-circle-check"></i> <?php echo ($item->data_json['topic'] ?? '')?>
            </button>
            
            <button type="button" class="btn btn-sm btn-danger rounded-pill shadow approve"
                data-topic="ตรวจสอบไม่<?php echo ($item->data_json['topic'] ?? '')?>" data-id="<?php echo $item->id?>"
                data-status="Reject">
                <i class="fa-solid fa-circle-xmark"></i> ไม่<?php echo ($item->data_json['topic'] ?? '')?>
            </button>

            <?php else:?>
                
            <?php if($item->emp_id == $me->id && $item->status =="Pending"):?>
                <button class="btn btn-sm btn-primary rounded-pill shadow btn-approve" data-id="<?php echo $item['id']?>" data-topic="<?php echo ($item->data_json['topic'] ?? '')?>"><i class="fa-solid fa-circle-check"></i> <?php echo $item->data_json['topic'] ?? ''?></button>
                <button class="btn btn-sm btn-danger rounded-pill shadow btn-reject" data-id="<?php echo $item['id']?>" data-topic="<?php echo ($item->data_json['topic'] ?? '')?>"><i class="fa-solid fa-circle-check"></i> <?php echo $item->data_json['topic'] ?? ''?></button>
            <?php // echo Html::a('<i class="fa-solid fa-circle-check"></i> '.($item->data_json['topic'] ?? ''),['/approve/'.$model->name.'/approve','id' => $item['id'],'status' => 'Approve','title' => '<i class="fa-solid fa-circle-check text-primary"></i> '.($item->data_json['topic'] ?? '')],['class' => 'btn btn-sm btn-primary rounded-pill shadow approve','data' => ['size' => 'model-md']])?>
            <?php //echo Html::a('<i class="fa-solid fa-circle-xmark"></i> ไม่'.($item->data_json['topic'] ?? ''),['/me/leave/approve','id' => $item['id'],'status' => 'Reject','title' => '<i class="fa-solid fa-circle-xmark text-danger"></i> ไม่'.($item->data_json['topic'] ?? '')],['class' => 'btn btn-sm btn-danger rounded-pill shadow open-modal','data' => ['size' => 'model-md']])?>
            <?php endif;?>

            <?php endif?>
            <?php endif?>
        </div>
        
        <?php endforeach ?>

    </div>
</div>


<?php
$urlApprove = Url::to(['/approve/leave/approve']);
$js = <<<JS

        //การอนุมัติ
    $("body").on("click", ".btn-approve", async function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    var topic = $(this).data('topic');
    var status = $(this).data('status');
            
        Swal.fire({
                title: 'ยืนยัน?',
                text: topic+"ใช่หรือไม!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ใช่',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                console.log(result)
                if(result.isConfirmed){
                    $('#approve-status').val('Approve')
                    saveApprove()
                }
            })      
        });


    $('.btn-reject').on('click', function (e) {
        e.preventDefault();
        let modal = $('#main-modal');
        modal.attr('inert', 'true'); // ปิดการโฟกัสของ Modal
        modal.modal('hide');

        setTimeout(() => {
            Swal.fire({
                title: 'ปฏิเสธ',
                input: 'textarea',
                inputLabel: 'กรุณากรอกเหตุผล',
                inputPlaceholder: 'ใส่เหตุผลที่นี่...',
                inputAttributes: {
                    'aria-label': 'กรุณากรอกเหตุผล',
                    'style': 'height: 100px; resize: vertical;' // ปรับขนาดให้ใช้งานง่าย
                },
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                didOpen: () => {
                    $('.swal2-textarea').focus(); // โฟกัส `textarea`
                },
                preConfirm: (note) => {
                    if (!note) {
                        Swal.showValidationMessage('กรุณากรอกเหตุผลก่อนกดส่ง');
                    }
                    return note;
                }
            }).then((result) => {
                modal.removeAttr('inert'); // เปิดการโฟกัส Modal กลับเมื่อ Swal ปิด
                if (result.isConfirmed) {
                    // แสดง Loading ก่อนส่งข้อมูล
                    Swal.fire({
                        title: 'กำลังบันทึกข้อมูล...',
                        text: 'โปรดรอสักครู่',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // ใส่ค่าลงฟอร์ม
                    $('#approve-data_json-note').val(result.value);
                    $('#approve-status').val('Reject');
                    
                    saveApprove()

                } else {
                    modal.modal('show'); // เปิด Modal กลับถ้ากดยกเลิก
                }
            });
        }, 300); // รอ 300ms ให้ Modal Bootstrap ปิดสนิทก่อน
    });

    function saveApprove()
    {
        var form = $('#form-approve');
        $.ajax({
            type: form.attr('method'),
            url:  form.attr('action'),
            data:  form.serialize(),
            dataType: "json",
            success: function (res) {
                if(res.status == 'success'){
                    Swal.fire({
                    icon: 'success',
                    title: 'บันทึกสำเร็จ',
                    showConfirmButton: false,
                    timer: 1000
                }).then(() => {
                    window.location.reload();
                });

                }
            }
        });
    }

    JS;
$this->registerJS($js, View::POS_END);
?>
