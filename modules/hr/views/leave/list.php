<?php

use yii\web\View;
use yii\helpers\Url;
use yii\bootstrap5\Html;
use app\components\UserHelper;
use app\components\ApproveHelper;

$me = UserHelper::GetEmployee();

$currentSort = Yii::$app->request->get('sort', '');
$isAsc = $currentSort === 'total_days';
$isDesc = $currentSort === '-total_days';

$newSort = $isAsc ? '-total_days' : 'total_days';
$sortIcon = $isAsc ? '↑' : ($isDesc ? '↓' : '');

?>


<div class="table-responsive" style="max-height: 600px;max-height: 600px;min-height:300px; overflow: auto;">
    <table class="table table-striped table-hover mb-0">
        <thead style="position: sticky; top: 0; z-index: 10;">
            <tr>
                <th class="text-center" style="width:30px">ลำดับ</th>
                <th class="text-center" scope="col" style="width:150px">ประเภทบุคลากร</th>
                <th scope="col">ผู้ขออนุมัติการลา</th>
                <th scope="col" style="width:100px">ประเภทเวร</th>
                <th><?= Html::a("การลา $sortIcon", Url::current(['sort' => $newSort])) ?></th>
                <th>ระหว่างวันที่</th>
                <th class="text-start" scope="col">หน่วยงาน</th>
                <th scope="col" style="width: 127px;">ผู้อนุมัติ</th>
                <th class="text-start" style="width: 165px;">สถานะ/ความคืบหน้า</th>
                <th class="text-center">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody class="align-middle table-group-divider" id="pjax-loading" style="background-color: #f0f8ff;">
            <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                <tr>
                    <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                    <td class="text-center "><?php echo $item->employee->positionType->title ?? '-' ?></td>
                    <td class="text-truncate" style="max-width: 230px;">
                        <a href="<?php echo Url::to(['/me/leave/view', 'id' => $item->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา']) ?>"
                            class="open-modal" data-size="modal-xl">
                            <?php echo  $item->employee->getAvatar(false) ?>
                        </a>
                    </td>
                    <td><?= $item->work_shift_name ?></td>
                    <td>
                        <?= $item->data_json['reason'] ?>
                        <div class="d-flex flex-column justofy-content-start align-items-start">
                            <span class="badge rounded-pill badge-soft-primary text-primary fs-13 "><i
                                    class="bi bi-exclamation-circle-fill"></i>
                                <?php echo $item->leaveType?->title ?? '-' ?>
                                <code><?php echo $item->total_days ?> </code> วัน</span>
                        </div>
                    </td>
                    <td><?php echo $item->showLeaveDate() ?></td>
                    <td class="text-start text-truncate" style="max-width:150px;"><?php echo $item->employee->departmentName() ?></td>
                    <td><?php echo $item->stackChecker() ?></td>
                    <td class="fw-light align-middle text-start" style="width:150px;">
                        <?php echo $item->viewStatus(); ?>
                        <?php echo ApproveHelper::viewStep('leave',$item->id); ?>
                    </td>

                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                จัดการ
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">

                                <li>
                                    <!-- แต่เป็น admin แก้ไขได้ -->
                                    <?php if (Yii::$app->user->can('admin')): ?>
                                        <?= Html::a(
                                            '<i class="fa-solid fa-pen-to-square me-1"></i> แก้ไข',
                                            ['/hr/leave/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],
                                            ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]
                                        ) ?>
                                    <?php endif; ?>
                                </li>
                                <li><?= Html::a('<i class="fa-solid fa-eye me-2"></i>แสดง', ['view', 'id' => $item->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
                                <?php if ($item->status == 'Approve'): ?>
                                    <li>
                                        <?php echo Html::a(
                                            '<i class="fa-solid fa-print me-1"></i> พิมพ์ใบลา',
                                            [$item->leave_type_id == 'LT4' ? '/hr/document/leavelt4' : '/hr/document/leavelt1', 'id' => $item->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> พิมพ์เอกสาร'],
                                            ['class' => 'dropdown-item open-modal', 'data' => [
                                                'size' => 'modal-xl',
                                                'filename' => $item->leaveType?->title ?? '-' . '-' . $item->employee->fullname
                                            ]]
                                        ) ?>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>



</div>
<?php
$js = <<< JS

    $("body").on("click", ".download-leave", function (e) {
        e.preventDefault();
        var filename = $(this).data('filename');
        $.ajax({
            url: $(this).attr('href'), // ตรวจสอบให้แน่ใจว่า URL ตรงกับ controller/action ของคุณ
            method: 'GET',
            xhrFields: {
                responseType: 'blob' // กำหนดให้ตอบกลับเป็น binary data
            },
            beforeSend: function() {
                $("#main-modal").modal("show");
                $("#main-modal-label").html("กำลังโหลด");
                $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
                $(".modal-dialog").addClass("modal-sm");
                $("#modal-dialog").removeClass("fade");
                $(".modal-body").html(
                    '<div class="d-flex justify-content-center"><div class="spinner-border" style="width: 3rem; height: 3rem;" role="status"></div></div><h6 class="text-center mt-3">Loading...</h6>'
                );
            },
            success: function(blob) { // ใช้ 'blob' เป็นชื่อพารามิเตอร์เพื่อหลีกเลี่ยงความสับสน
                var getFilename = filename+ '.docx'; // ชื่อไฟล์ที่ต้องการดาวน์โหลด
                const file = new Blob([blob], { type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' });
                
                // สร้างลิงก์ชั่วคราวสำหรับดาวน์โหลดไฟล์
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(file);
                link.download = getFilename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link); // ลบลิงก์ออกหลังจากใช้งานเสร็จ
                window.URL.revokeObjectURL(link.href); // ลบ URL Object เพื่อลดการใช้หน่วยความจำ

                $("#main-modal").modal("hide");
            },
            error: function() {
                alert('ไม่สามารถดาวน์โหลดไฟล์ได้');
                $("#main-modal").modal("hide");
            }
        });
    });


JS;
$this->registerJs($js, View::POS_END);
?>


