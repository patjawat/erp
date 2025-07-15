<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>

            <table class="table table-striped table-fixed">
                <thead>
                    <tr>
                        <th class="text-center fw-semibold" style="width:50px;">ลำดับ</th>
                        <th class="text-center fw-semibold" style="min-width:100px; width:100px;">เลขที่รับ</th>
                        <th class="fw-semibold" style="min-width:320px;">เรื่อง</th>
                        <th class="fw-semibold" style="min-width:250px;">ผู้บันทึก</th>
                        <th class="fw-semibold" style="min-width:100px;">สถานะ</th>
                        <th class="fw-semibold" style="width:70px;">แก้ไข</th>
                    </tr>
                </thead>
                <tbody class="align-middle  table-group-divider table-hover">
                    <?php foreach($dataProvider->getModels() as $key => $item):?>
                    <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?>
                    </td>
                    <td class="text-center fw-semibold"><?php echo $item->doc_regis_number?></td>
                    <td class="fw-light align-middle">
                        <div>
                            <h6 style="width:600px" class="text-truncate fw-semibold mb-0">
                                <?php if($item->doc_speed == 'ด่วนที่สุด'):?>
                                <span class="badge text-bg-danger fs-13">
                                    <i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด
                                </span>
                                <?php endif;?>

                                <?php if($item->secret == 'ลับที่สุด'):?>
                                <span class="badge text-bg-danger fs-13"><i class="fa-solid fa-lock"></i>
                                    ลับที่สุด
                                </span>
                                <?php endif;?>
                                <a href="<?php echo Url::to(['/dms/documents/view','id' => $item->id])?>"
                                    class="open-modal" data-size="modal-xxl">
                                    เรื่อง : <?php echo $item->topic?>
                                </a>

                                <?php echo $item->isFile() ? '<i class="fas fa-paperclip"></i>' : ''?>
                            </h6>
                        </div>
                        <p class="fw-normal fs-13 mb-0">
                            <?=$item->data_json['des'] ?? ''?>
                        </p>
                        <?php // echo Html::img('@web/img/krut.png',['style' => 'width:20px']);?>
                        <span class="text-danger">
                            <?php echo $item->doc_number?>
                        </span>
                        <span class="text-primary fw-normal fs-13">
                            |
                            <i class="fa-solid fa-inbox"></i>
                            <?php  echo $item->documentOrg->title ?? '-';?>
                            <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                                <i class="fa-regular fa-eye"></i> <?php echo $item->viewCount()?>
                            </span>
                        </span>
                        <?php echo $item->StackDocumentTags('comment')?>
                    </td>
                    <td class="fw-light align-middle">
                        <div class=" d-flex flex-column">
                            <?=$item->viewCreate()['avatar'];?>
                        </div>
                    </td>
                    <td> <?=$item->documentStatus->title ?? '-'?></td>
                    <td>
                        <?php echo Html::a('<i class="fa-regular fa-pen-to-square fa-2x"></i>',['update', 'id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],['class' => 'open-modal','data' =>['size' => 'modal-xxl']])?>
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
$("body").on("click", ".export-document", function (e) {
    e.preventDefault();
    let form = $('#w0');
    let action = form.attr('action');
    let data = form.serialize();

    Swal.fire({
        title: 'ยืนยันการส่งออกข้อมูล?',
        text: 'คุณต้องการส่งออกข้อมูลหรือไม่',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ส่งออก',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'กำลังส่งออกข้อมูล...',
                text: 'กรุณารอสักครู่',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                type: "get",
                url: '/dms/documents/export',
                data: form.serialize(),
                xhrFields: {
                    responseType: 'blob' 
                },
                success: function (response) {
                    Swal.close();

                    $('#page-content').show();
                    $('#loader').hide();
                    $('#documentsearch-data_json-export').val(''); // Reset export flag

                    const blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'รายงาน.xlsx'; // The default file name
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);

                    Swal.fire({
                        icon: 'success',
                        title: 'ส่งออกสำเร็จ',
                        text: 'ไฟล์ถูกดาวน์โหลดเรียบร้อยแล้ว',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function (xhr, status, error) {
                    Swal.close();
                    $('#page-content').show();
                    $('#loader').hide();
                    warning(xhr.responseText);
                    console.log('Error occurred:', error);
                    console.log('Status:', status);
                    console.log('Response:', xhr.responseText);
                }
            });
        }
    });
});


JS;
$this->registerJS($js);

?>
