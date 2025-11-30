<?php

use yii\web\View;
use yii\helpers\Html;
use app\components\UserHelper;
use app\modules\approve\models\Approve;

$this->registerCssFile('@web/css/timeline.css');
$me = UserHelper::GetEmployee();

$listApprove = Approve::find()
    ->where([
        'name' => $name,
        'from_id' => $model->id
    ])
    ->orderBy(['level' => SORT_ASC]) // เรียงจากน้อยไปมาก 1 → 2
    ->all();
?>


<h6 class="mb-4 d-flex align-items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
        stroke-linejoin="round" class="lucide lucide-clock text-primary" aria-hidden="true">
        <path d="M12 6v6l4 2"></path>
        <circle cx="12" cy="12" r="10"></circle>
    </svg>สถานะการตรวจสอบ</h6>
<div class="position-relative ps-2">
    <div class="position-absolute top-0 bottom-0 start-0 border-start border-2 border-light ms-4" style="z-index: 0;">
    </div>
    <div class="d-flex flex-column gap-4 position-relative">
        <?php foreach ($model->listApprove() as $item): ?>
            <div class="d-flex gap-3 align-items-center bg-white z-1 p-2 <?= $item->status == 'Pending' ? 'border border-1 rounded-2 border-primary' : '' ?>">
                <div class="d-flex align-items-center flex-grow-1">
                    <p class="mb-0 small fw-bold text-dark"></p>
                    <div>
                        <?php if ($item->status == 'Pass'): ?>
                            <?= $item->getAvatar($item->viewApproveDate())['avatar']; ?>
                        <?php else: ?>
                            <!-- แสดงชื่อ/หัวข้อ  -->
                            <?php if ($item->level == 3): ?>

                                <div class="d-flex gap-3 align-items-start bg-white z-1">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 border border-2
                                                                    bg-light border-light text-muted
                                                                " style="width: 32px; height: 32px;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text" aria-hidden="true">
                                            <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path>
                                            <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
                                            <path d="M10 9H8"></path>
                                            <path d="M16 13H8"></path>
                                            <path d="M16 17H8"></path>
                                        </svg></div>
                                    <div>
                                        <p class="mb-0 small fw-bold text-muted">จนท.ตรวจสอบ</p><small class="text-muted d-block" style="font-size: 0.75rem;">รอตรวจสอบ</small>
                                    </div>
                                </div>
                            <?php else: ?>

                                <?= $item->getAvatar($item->title)['avatar']; ?>
                            <?php endif; ?>

                        <?php endif; ?>
                        <?php
                        // **NOTE:** ต้องแน่ใจว่าได้มีการประกาศฟังก์ชัน viewApproveMsg() ไว้แล้วในไฟล์เดียวกัน
                        // echo $item->viewApproveMsg();
                        ?>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <?php
                    $userIsChecker = Yii::$app->user->can($name); // สิทธิ์ตรวจสอบ
                    $userIsOwner = ($item->emp_id == $me->id); // เจ้าของรายการ
                    ?>
                    <?php if ($item->status == 'Pending' && ($userIsOwner || ($item->emp_id == null && $userIsChecker))): ?>

                        <?php
                        echo Html::a(
                            '<i class="fa-solid fa-circle-check"></i> ไม่' . ($item->data_json['label'] ?? ''),
                            ['/approve/'.$name.'/update', 'id' => $item->id],
                            [
                                'class' => 'btn btn-sm btn-outline-danger rounded-pill border-1 shadow btn-approve',
                                'data' => ['id' => $item->id, 'status' => 'Reject', 'label' => "ไม่" . ($item->data_json['label'] ?? '')]
                            ]
                        );
                        ?>
                        <?php
                        echo Html::a(
                            '<i class="fa-solid fa-circle-check"></i> ' . ($item->data_json['label'] ?? ''),
                            ['/approve/'.$name.'/update', 'id' => $item->id],
                            [
                                'class' => 'btn btn-sm btn-primary rounded-pill shadow btn-approve',
                                'data' => ['id' => $item->id, 'status' => 'Pass', 'label' => ($item->data_json['label'] ?? '')]
                            ]
                        );
                        ?>


                    <?php else: ?>
                        <?= $item->viewApproveStatus() ?>
                    <?php endif; ?>
                </div>

            </div>

        <?php endforeach; ?>


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