<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'นำเข้าไฟล์ CSV';
?>


<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data', 'id' => 'upload-form']]) ?>
    <?= Html::fileInput('csvFile', null, ['id' => 'csvFile']) ?>
    <?= Html::hiddenInput('order_id', $order_id ?? null, ['id' => 'order_id']) ?>
<?php ActiveForm::end() ?>

<div id="preview-table"></div>

<div id="import-btn" style="display:none; margin-top:10px; text-align:center;">
    <button class="btn btn-success" id="btn-import" type="button">
        <i class="fa-solid fa-file-import me-2"></i>ยืนยันนำเข้า
    </button>
    <?= Html::hiddenInput('filePath', null, ['id' => 'filePath']) ?>
</div>

<?php
$js = <<<JS
// 1️⃣ AJAX preview
$('#csvFile').on('change', function() {
    var file = this.files[0];
    if(!file) return;

    var formData = new FormData();
    formData.append('csvFile', file);

    $.ajax({
        url: '/inventory/import/preview',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            if(res.status === 'success'){

                      $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl modal-xxl");
                        $(".modal-dialog").addClass('modal-xl');

                var html = '<table class="table table-striped table-bordered table-sm"><thead><tr>';
                res.preview[0].forEach(function(h){ html += '<th>' + h + '</th>'; });
                html += '</tr></thead><tbody class="table-group-divider align-middle">';
                
                res.preview.slice(1).forEach(function(row){
                    html += '<tr>';
                    row.forEach(function(cell){ html += '<td>' + cell + '</td>'; });
                    html += '</tr>';
                });
                html += '</tbody></table>';
                $('#preview-table').html(html);
                $('#filePath').val(res.filePath);
                $('#import-btn').show();
            } else {
                alert('เกิดข้อผิดพลาดในการอัปโหลด');
            }
        },
        error: function(){
            alert('เกิดข้อผิดพลาดในการอัปโหลด');
        }
    });
});

// 2️⃣ AJAX import
$('#btn-import').on('click', function() {
    var filePath = $('#filePath').val();
    var orderId = $('#order_id').val();
    if(!filePath) { 
        Swal.fire('ไม่พบไฟล์', '', 'warning'); 
        return; 
    }

    // แสดง SweetAlert Confirm
    Swal.fire({
        title: 'ยืนยันการนำเข้า?',
        text: "คุณต้องการนำเข้าข้อมูลนี้ใช่หรือไม่",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ใช่, นำเข้าเลย',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: false
    }).then((result) => {
        if (result.isConfirmed) {
            // ซ่อน modal
            $('#main-modal').hide();

            // แสดง sweetalert loading
            Swal.fire({
                title: 'กำลังนำเข้า...',
                text: 'กรุณารอสักครู่',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/inventory/import/import-csv',
                type: 'POST',
                data: { filePath: filePath, order_id: orderId },
                success: function(res) {
                    if(res.status === 'success'){
                        $('#preview-table').html('');
                        $('#import-btn').hide();
                        $('#csvFile').val('');

                        // รอ 1 วินาทีค่อยปิด loading
                        setTimeout(function(){
                            Swal.close(); 
                            window.location.reload(true);
                        }, 1000);

                    } else {
                        Swal.fire('เกิดข้อผิดพลาด', res.message, 'error');
                    }
                }
            });
        }
    });
});


JS;

$this->registerJs($js);
?>
