<?php
use yii\web\View;
use yii\helpers\Url;
use yii\bootstrap5\Html;
use app\components\UserHelper;
$me = UserHelper::GetEmployee();
?>


    <table class="table table-striped table-hover">
        <thead>
        <tr>
        <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
            <th class="fw-semibold" scope="col">ผู้ขออนุมัติการลา</th>
            <th class="fw-semibold">การลา</th>
            <th class="fw-semibold" scope="col">ปีงบประมาณ</th>
            <th class="fw-semibold text-start" scope="col">หนวยงาน</th>
            <!-- <th class="fw-semibold" scope="col">มอบหมาย</th> -->
            <th class="fw-semibold" scope="col">ผู้ตรวจสอบและอนุมัติ</th>
            <th class="fw-semibold text-start">ความคืบหน้า</th>
            <th class="fw-semibold text-start">สถานะ</th>
            <th class="fw-semibold text-center">ดำเนินการ</th>
        </tr>
    </thead>
    <tbody class="align-middle table-group-divider">
        <?php foreach($dataProvider->getModels() as $key => $item):?>
        <tr>
            <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?>
        </td>
            <td class="text-truncate" style="max-width: 230px;">
                <a href="<?php echo Url::to(['/me/leave/view','id' => $item->id,'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'])?>" class="open-modal" data-size="modal-xl">
                <?=$item->getAvatar(false)['avatar']?>
                </a>
            </td>
            <td>
                <div class="d-flex flex-column justofy-content-start align-items-start">
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 "><i class="bi bi-exclamation-circle-fill"></i> <?php echo $item->leaveType->title ?> <code><?php echo $item->total_days?> </code> วัน</span>
                    <div>
                       <?=$item->showLeaveDate()?>
                    </div>
                </div>
            </td>
            <!-- <td class="text-center fw-semibold"><?php echo $item->total_days?></td> -->
            <!-- <td><?=Yii::$app->thaiFormatter->asDate($item->date_start, 'medium')?></td>
            <td><?=Yii::$app->thaiFormatter->asDate($item->date_end, 'medium')?></td> -->
            <td class="text-center fw-semibold"><?php echo $item->thai_year?></td>
            <td class="text-start text-truncate" style="max-width:150px;"><?=$item->getAvatar(false)['department']?>
            </td>
            <!-- <td><?php // echo $item->leaveWorkSend()?->getAvatar(false) ?? '-' ?></td> -->
            <td><?php echo $item->stackChecker()?></td>
            <td class="fw-light align-middle text-start" style="width:150px;"><?php echo $item->showStatus();?></td>
            <td class="fw-center align-middle text-start">

                <?php
                        try {
                            echo $item->viewStatus();
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                        ?>
            </td>

            <td class="text-center">

            <div class="d-flex gap-2 justify-content-center">

    <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/me/leave/view','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?>
    <?php if($item->status == 'Approve'):?>
        <i class="fa-solid fa-pencil fa-2x text-secondary"></i>
        <?php else:?>
            <?php echo ($me->id == $item->emp_id) ? Html::a('<i class="fa-solid fa-pencil fa-2x text-warning"></i>',['/me/leave/update','id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],['class' => 'open-modal','data' => ['size' => 'modal-lg']]) : ''?>
    <?php endif?>
    <?php if($item->status == 'Approve'):?>

        <?php echo Html::a('<i class="fa-solid fa-file-arrow-down fa-2x text-success"></i>', 
                            [$item->leave_type_id == 'LT4' ? '/hr/document/leavelt4' : '/hr/document/leavelt1', 'id' => $item->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> พิมพ์เอกสาร'], 
                            ['class' => 'open-modal','data' => [
                                'size' => 'modal-xl',
                                'filename' => $item->leaveType->title.'-'.$item->employee->fullname
                            ]]) ?>
                        <?php echo Html::a('<i class="fa-solid fa-file-arrow-down fa-2x text-success"></i>', 
                            [$item->leave_type_id == 'LT4' ? '/hr/document/leavelt4' : '/hr/document/leavelt1', 'id' => $item->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> พิมพ์เอกสาร'], 
                            ['class' => 'download-leave','data' => [
                                'filename' => $item->leaveType->title.'-'.$item->employee->fullname
                            ]]) ?>
                            <?php else:?>
                                <i class="fa-solid fa-file-arrow-down fa-2x text-secondary ms-1"></i>
                            <?php endif;?>
</div>


            </td>
        </tr>
        <?php endforeach;?>
    </tbody>
</table>
<div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
    <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
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
$this->registerJs($js,View::POS_END);
?>