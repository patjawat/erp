<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'สำรองข้อมูล';
?>
<?php $this->beginBlock('page-title'); ?>
<?= $this->title; ?>
<?php $this->endBlock(); ?>
<!-- SweetAlert2 CSS -->

<div class="card">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>สำรองข้อมูล</h3>
            <div class="mb-3">
                <button id="backupAllBtn" class="btn btn-success"><i class="fa-solid fa-server"></i> สำรองทั้งหมด</button>
                <button id="backupDbBtn" class="btn btn-primary"><i class="fa-solid fa-database"></i> สำรองฐานข้อมูล</button>
                <button id="backupFileBtn" class="btn btn-success"><i class="fa-solid fa-file-circle-check"></i> สำรองไฟล์รูปภาพ</button>
            </div>

        </div>
                <!-- Progress Bar -->
        <div class="progress mb-3" style="height: 25px; display:none;">
            <?= Html::img('@web/img/loading.gif') ?> กำลังสำรองข้อมูล...
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                style="width: 0%;" id="backupProgress">0%</div>
        </div>

        <!-- <h3>ไฟล์สำรองข้อมูล</h3> -->
        <ul id="backupList" class="list-group">
            <?php foreach ($backupFiles as $file): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                        <a href="<?= Url::to(['download', 'file' => basename($file)]) ?>" target="_blank">
                            <?= basename($file) ?>
                        </a>
                    </span>
                    <span>
                        <button class="btn btn-sm btn-danger deleteFileBtn" data-file="<?= basename($file) ?>">ลบ</button>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>


<!-- SweetAlert2 JS -->
<?php
$js = <<< JS

function startBackup(url) {
    const progressContainer = $('.progress');
    const progressBar = $('#backupProgress');

    progressContainer.show();
    progressBar.css('width', '0%').text('0%');

    $.ajax({
        url: url,
        method: 'POST',
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function(evt){
                if (evt.lengthComputable) {
                    var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                    progressBar.css('width', percentComplete + '%').text(percentComplete + '%');
                }
            }, false);
            return xhr;
        },
        success: function(res){
            progressBar.css('width', '100%').text('100%');
            if(res.success){
                Swal.fire('สำเร็จ', 'สำรองข้อมูลเสร็จสิ้น: ' + res.file, 'success');
                // เพิ่มไฟล์ใหม่ในรายการ
                $('#backupList').append(
                    '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                    '<span><a href="download?file=' + res.file + '" target="_blank">' + res.file + '</a></span>' +
                    '<span><button class="btn btn-sm btn-danger deleteFileBtn" data-file="' + res.file + '">ลบ</button></span>' +
                    '</li>'
                );
            } else {
                Swal.fire('ผิดพลาด', (res.error || 'สำรองข้อมูลไม่สำเร็จ'), 'error');
            }
            setTimeout(() => progressContainer.hide(), 2000);
        },
        error: function() {
            progressContainer.hide();
            Swal.fire('ผิดพลาด', 'การร้องขอ AJAX ล้มเหลว', 'error');
        }
    });
}

    // Backup Database
    $('#backupDbBtn').on('click', function(){
        Swal.fire({
            title: 'ยืนยัน',
            text: "คุณต้องการสำรองฐานข้อมูลหรือไม่?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ใช่'
        }).then((result) => {
            if(result.isConfirmed){
                startBackup('backup/default/backup-database');
            }
        });
    });

    // Backup File Upload
    $('#backupFileBtn').on('click', function(){
        Swal.fire({
            title: 'ยืนยัน',
            text: "คุณต้องการสำรองไฟล์ที่อัปโหลดหรือไม่?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ใช่'
        }).then((result) => {
            if(result.isConfirmed){
                startBackup('backup/default/backup-files');
            }
        });
    });

    // Delete file
    $('body').on('click', '.deleteFileBtn', function(){
    var btn = $(this);
    var file = btn.data('file');
        console.log(file);
    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "คุณต้องการลบ " + file + " หรือไม่?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ใช่, ลบเลย!'
    }).then((result) => {
        if(result.isConfirmed){
            $.post('backup/default/delete', {file: file}, function(res){
                if(res.success){
                    btn.closest('li').fadeOut(500, function() {
                        $(this).remove();
                    });
                    Swal.fire('ลบแล้ว!', file + ' ถูกลบเรียบร้อยแล้ว', 'success');
                } else {
                    Swal.fire('ผิดพลาด', res.error || 'ไม่สามารถลบไฟล์ได้', 'error');
                }
            });
        }
    });
});

    $('#backupAllBtn').on('click', function(){
        Swal.fire({
            title:'Confirm',
            text:'Do you want to backup database and files?',
            icon:'question',
            showCancelButton:true,
            confirmButtonText:'Yes'
        }).then((result)=>{
            if(result.isConfirmed){
                startBackup('backup/default/backup-all');
            }
        });
    });


JS;
$this->registerJs($js, View::POS_END);
?>