<?php
use yii\web\View;
use yii\helpers\Url;
use yii\bootstrap5\Html;
?>


    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th scope="col">ปีงบประมาณ</th>
            <th scope="col">ผู้ขออนุมัติการลา</th>
            <th class="text-center" scope="col">วัน</th>
            <th scope="col">จากวันที่</th>
            <th scope="col">ถึงวันที่</th>
            <th class="text-start" scope="col">หน่วยงาน</th>
            <!-- <th scope="col">มอบหมาย</th> -->
            <th scope="col">ผู้ตรวจสอบและอนุมัติ</th>
            <th class="text-start">ความคืบหน้า</th>
            <th class="text-start">สถานะ</th>
            <th class="text-center">ดำเนินการ</th>
        </tr>
    </thead>
    <tbody class="align-middle table-group-divider">
        <?php foreach ($dataProvider->getModels() as $model): ?>
            <tr class="">
            <td class="text-center fw-semibold"><?php echo $model->thai_year ?></td>
            <td class="text-truncate" style="max-width: 230px;">
                <a href="<?php echo Url::to(['/hr/leave/view', 'id' => $model->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา']) ?>" class="open-modal" data-size="modal-xl">
                <?= $model->getAvatar(false)['avatar'] ?>
                </a>
            </td>
            <td class="text-center fw-semibold"><?php echo $model->total_days ?></td>
            <td><?= Yii::$app->thaiFormatter->asDate($model->date_start, 'medium') ?></td>
            <td><?= Yii::$app->thaiFormatter->asDate($model->date_end, 'medium') ?></td>
            <td class="text-start text-truncate" style="max-width:150px;"><?= $model->getAvatar(false)['department'] ?>
            
            </td>
                </td>
            <td><?php echo $model->stackChecker() ?>
<?php
    try {
        $data = $model->checkerName(1)['employee'];
    } catch (\Throwable $th) {
    }
    ?>
        </td>
            <td class="fw-light align-middle text-start" style="width:150px;"><?php echo $model->showStatus(); ?></td>
            <td class="fw-center align-middle text-start">

                <?php
                try {
                    echo $model->viewStatus();
                } catch (\Throwable $th) {
                    // throw $th;
                }
                ?>
            </td>

            <td class="text-center">
<div class="d-flex gap-2 justify-content-center">

    <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>', ['/hr/leave/view', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
    <?php // echo Html::a('<i class="fa-regular fa-pen-to-square text-warning  fa-2x"></i>',['/hr/leave/update','id' => $model->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],['class' => 'open-modal','data' => ['size' => 'modal-lg']]) ?>
    <?php if ($model->status == 'Allow'): ?>
                        <?php echo Html::a('<i class="fa-solid fa-file-arrow-down fa-2x text-success"></i>',
                                [$model->leave_type_id == 'LT4' ? '/hr/document/leavelt4' : '/hr/document/leavelt1', 'id' => $model->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> พิมพ์เอกสาร'],
                                ['class' => 'download-leave', 'data' => [
                                    'filename' => $model->leaveType->title . '-' . $model->employee->fullname
                                ]]) ?>
                            <?php endif; ?>
                            
</div>
    <!-- <div class="dropdown">
                    <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" style="">

                    <?= Html::a('<i class="fa-solid fa-eye me-1"></i> แสดงรายละเอียด', ['/hr/leave/view', 'id' => $model->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'], ['class' => 'dropdown-item open-modalx', 'data' => ['size' => 'modal-lg']]) ?>
                    <?php if ($model->status !== 'Allow'): ?>
                            <?php echo Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/hr/leave/update', 'id' => $model->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                    <?php endif; ?>
                    <?php if ($model->status == 'Allow'): ?>
                        <?php echo Html::a('<i class="fa-regular fa-circle-down me-1"></i> ดาน์โหลดเอกสาร',
                            [$model->leave_type_id == 'LT4' ? '/hr/document/leavelt4' : '/hr/document/leavelt1', 'id' => $model->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> พิมพ์เอกสาร'],
                            ['class' => 'dropdown-item download-leave', 'data' => [
                                'filename' => $model->leaveType->title . '-' . $model->employee->fullname
                            ]]) ?>
                            <?php endif; ?>
                    </div>
                </div> -->
            </td>
        </tr>
        <?php endforeach; ?>
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
$js = <<<JS
        var offcanvasElement = document.getElementById('offcanvasExample');
        var offcanvas = new bootstrap.Offcanvas(offcanvasElement, {
        backdrop: 'static'
        });

        \$("body").on("click", ".download-leave", function (e) {
        e.preventDefault();
        var filename = \$(this).data('filename');
        \$.ajax({
            url: \$(this).attr('href'), // ตรวจสอบให้แน่ใจว่า URL ถูกต้อง
            method: 'GET',
            xhrFields: {
                responseType: 'blob' // ให้ตอบกลับเป็น binary data
            },
            beforeSend: function() {
                \$("#main-modal").modal("show");
                \$("#main-modal-label").html("กำลังโหลด");
                \$(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
                \$(".modal-dialog").addClass("modal-sm");
                \$(".modal-body").html(
                    '<div class="d-flex justify-content-center"><div class="spinner-border" style="width: 3rem; height: 3rem;" role="status"></div></div><h6 class="text-center mt-3">Loading...</h6>'
                );
            },
            success: function(blob) {
                var getFilename = filename + '.docx';
                const file = new Blob([blob], { type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' });
                
                // สร้างลิงก์ชั่วคราวสำหรับดาวน์โหลดไฟล์
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(file);
                link.download = getFilename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(link.href);
                // ปิด modal หลังดาวน์โหลดเสร็จ
                \$("#main-modal").modal("hide");
                location.reload(true)
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Swal.fire({
                    title: "แจ้งเตือน!",
                    text: "ไม่สามารถดาวน์โหลดไฟล์ได้",
                    icon: "warning"
                    }).then((result) => {
                    if (result.isConfirmed) { // เช็คว่าผู้ใช้กดปุ่ม OK
                        console.log('OK');
                        \$("#main-modal").modal("hide");
                    }
                    });

            }
        });
    });


            

    JS;
$this->registerJs($js, View::POS_END);
?>