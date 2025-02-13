<?php
use yii\web\View;
use yii\helpers\Url;
use yii\bootstrap5\Html;
?>


    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th scope="col">ผู้ขออนุมัติการลา</th>
            <th class="text-center" scope="col">วัน</th>
            <th scope="col">จากวันที่</th>
            <th scope="col">ถึงวันที่</th>
            <th scope="col">ปีงบประมาณ</th>
            <th class="text-start" scope="col">หนวยงาน</th>
            <th scope="col">มอบหมาย</th>
            <th scope="col">ผู้ตรวจสอบและอนุมัติ</th>
            <th class="text-start">ความคืบหน้า</th>
            <th class="text-start">สถานะ</th>
            <th class="text-center">ดำเนินการ</th>
        </tr>
    </thead>
    <tbody class="align-middle table-group-divider">
        <?php foreach($dataProvider->getModels() as $model):?>
        <tr class="">
            <td class="text-truncate" style="max-width: 230px;">
                <a href="<?php echo Url::to(['/me/leave/view','id' => $model->id,'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'])?>" class="open-modal" data-size="modal-xl">
                <?=$model->getAvatar(false)['avatar']?>
                </a>
            </td>
            <td class="text-center fw-semibold"><?php echo $model->total_days?></td>
            <td><?=Yii::$app->thaiFormatter->asDate($model->date_start, 'medium')?></td>
            <td><?=Yii::$app->thaiFormatter->asDate($model->date_end, 'medium')?></td>
            <td class="text-center fw-semibold"><?php echo $model->thai_year?></td>
            <td class="text-start text-truncate" style="max-width:150px;"><?=$model->getAvatar(false)['department']?>
            </td>
            <td><?php echo $model->leaveWorkSend()['avatar']?></td>
            <td><?php echo $model->stackChecker()?></td>
            <td class="fw-light align-middle text-start" style="width:150px;"><?php echo $model->showStatus();?></td>
            <td class="fw-center align-middle text-start">

                <?php
                        try {
                            echo $model->viewStatus();
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                        ?>
            </td>

            <td class="text-center">

            <div class="d-flex gap-2 justify-content-center">

    <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/me/leave/view','id' => $model->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?>
    <?php if($model->status == 'Allow'):?>
        <i class="fa-solid fa-pencil fa-2x text-secondary"></i>
        <?php else:?>
            <?php echo Html::a('<i class="fa-solid fa-pencil fa-2x text-warning"></i>',['/me/leave/update','id' => $model->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
    <?php endif?>
    <?php if($model->status == 'Allow'):?>
                        <?php echo Html::a('<i class="fa-solid fa-file-arrow-down fa-2x text-success"></i>', 
                            [$model->leave_type_id == 'LT4' ? '/hr/document/leavelt4' : '/hr/document/leavelt1', 'id' => $model->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> พิมพ์เอกสาร'], 
                            ['class' => 'download-leave','data' => [
                                'filename' => $model->leaveType->title.'-'.$model->employee->fullname
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