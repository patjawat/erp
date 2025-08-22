<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'นำเข้าไฟล์ CSV';
?>

<h1><?= Html::encode($this->title) ?></h1>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data', 'id' => 'upload-form']]) ?>
    <?= Html::fileInput('csvFile', null, ['id' => 'csvFile']) ?>
    <?= Html::hiddenInput('order_id', $order_id ?? null, ['id' => 'order_id']) ?>
<?php ActiveForm::end() ?>

<div id="preview-table"></div>

<div id="import-btn" style="display:none; margin-top:10px;">
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
                var html = '<table class="table table-bordered table-sm"><thead><tr>';
                res.preview[0].forEach(function(h){ html += '<th>' + h + '</th>'; });
                html += '</tr></thead><tbody>';
                
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
    if(!filePath) { alert('ไม่พบไฟล์'); return; }

    $.ajax({
        url: '/inventory/import/import-csv',
        type: 'POST',
        data: { filePath: filePath, order_id: orderId },
        success: function(res) {
            if(res.status === 'success'){
                // alert(res.message);
                $('#preview-table').html('');
                $('#import-btn').hide();
                $('#csvFile').val('');
            } else {
                // alert(res.message);
            }
        }
    });
});
JS;

$this->registerJs($js);
?>
