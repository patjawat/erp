<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
use app\components\UserHelper;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */
$this->title = $model->topic;
\yii\web\YiiAsset::register($this);
?>

<div class="container-fluid p-0 min-vh-100 overflow-x-hidden">
    <div class="row g-0 min-vh-100">
        
        <section class="col-12 col-lg-6 col-xl-6 d-flex flex-column bg-secondary bg-opacity-25 p-2 p-lg-3">
            <div class="flex-grow-1 overflow-auto bg-dark rounded-3 shadow-inner d-flex justify-content-center align-items-start p-2">
                <div id="iframeWrapper" style="width: 100%; max-width: 1000px; min-height: 600px; height: 85vh;">
                    <iframe id="myIframe"
                        src="<?= \yii\helpers\Url::to(['/me/documents/show', 'ref' => $model->ref]); ?>&embedded=true"
                        frameborder="0" 
                        class="w-100 h-100 rounded-3 shadow-sm bg-white">
                    </iframe>
                </div>
            </div>
        </section>

        <section class="col-12 col-lg-6 col-xl-6 bg-white border-start shadow-sm d-flex flex-column">
            <div class="p-3 p-lg-4 h-100 d-flex flex-column">
                
                <div class="d-flex flex-nowrap overflow-auto mb-3 gap-2 pb-2">
                    <ul class="nav nav-pills nav-custom-sm flex-nowrap" role="tablist" style="white-space: nowrap;">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="pill" href="#home">ลงความเห็น (เกษียน)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#comment">ประวัติการอ่าน/ลงความเห็น</a>
                        </li>
                    </ul>
                </div>

                <div class="tab-content flex-grow-1">
                    <div id="home" class="tab-pane fade show active">
                        <div class="card border border-primary-subtle p-3 mb-3"
                            style="background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);">
                            <div>
                                <strong class="text-primary d-block mb-1"><?=$model->documentOrg->title?></strong>
                                <span class="fs-6 text-dark"><?= $model->topic?></span>
                            </div>
                            <div class="mt-2">
                                <?php if ($model->doc_speed == 'ด่วนที่สุด'): ?>
                                    <span class="badge bg-danger mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i>ด่วนที่สุด</span>
                                <?php endif; ?>
                                <?php if ($model->secret == 'ลับที่สุด'): ?>
                                    <span class="badge bg-danger mb-1"><i class="fa-solid fa-lock me-1"></i>ลับที่สุด</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="small mb-3">
                            <span class="text-muted fw-bold">ถึงหน่วยงาน:</span>
                            <div class="d-inline-block gap-2"><?= $model->viewTagsDepartment() ?></div>
                        </div>

                        <div class="card border-light bg-light bg-opacity-50 mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-uppercase fw-bold text-muted small mb-0">
                                        <i class="fas fa-magic me-1"></i> ข้อความที่ใช้บ่อย (<span id="counttemplate">0</span>)
                                    </h6>
                                    <button id="btn-save-temp-now" class="btn btn-sm btn-success shadow-sm" style="display: none;">
                                        <i class="fas fa-save me-1"></i> บันทึกแม่แบบ
                                    </button>
                                </div>
                                <div id="viewlistCommenttemplate"></div>
                            </div>
                        </div>

                        <div class="viewFormComment border-top pt-3">
                            </div>
                    </div>

                    <div id="comment" class="tab-pane fade h-100">
                        <div class="card shadow-none border-0 d-flex flex-column h-100" style="min-height: 500px;">
                            <div class="card-header bg-transparent border-0 ps-0">
                                <h5 class="mb-0 fw-bold">รายการลงความเห็น</h5>
                            </div>
                            <div class="listComment flex-grow-1 overflow-y-auto pe-2" style="max-height: 70vh;">
                                <div class="text-center p-5 text-muted">
                                    <div class="spinner-border spinner-border-sm me-2"></div> กำลังโหลด...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<style>
    /* ปรับแต่งความสวยงามของหน้าจอเล็ก */
    @media (max-width: 991.98px) {
        #iframeWrapper {
            height: 60vh !important; /* บนมือถือให้ Iframe เตี้ยลงหน่อยเพื่อให้เห็นฟอร์มข้างล่าง */
            min-height: 400px !important;
        }
        .border-start {
            border-left: none !important;
            border-top: 1px solid #dee2e6 !important;
        }
    }

    /* สไตล์ Scrollbar สำหรับความเห็น */
    .listComment::-webkit-scrollbar { width: 5px; }
    .listComment::-webkit-scrollbar-track { background: #f1f1f1; }
    .listComment::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
</style>




<?php
$getCommentUrl = Url::to(['/me/documents/comment', 'id' => $model->id]);
$listCommentUrl = Url::to(['/me/documents/list-comment', 'id' => $model->id]);
$saveCommentTemplate = Url::to(['/me/documents/save-comment-template']);
$js = <<<JS
(function(){

listCommentTemplate()
 updateCharCount()

    $(function(){
        // ฟังก์ชันปรับความสูง Iframe ตามขนาดหน้าจอ
        function updateIframeHeight() {
            let iframe = document.getElementById("myIframe");
            if (iframe) {
                let winHeight = window.innerHeight;
                let winWidth = window.innerWidth;
                
                if (winWidth < 992) {
                    // มือถือ/แท็บเล็ตแนวตั้ง
                    iframe.style.height = "500px";
                } else {
                    // จอคอมพิวเตอร์
                    iframe.style.height = (winHeight - 200) + "px";
                }
            }
        }

        updateIframeHeight();
        $(window).on('resize', updateIframeHeight);

        getComment();
        listComment();
    });
})();

async function getComment() {
    await $.ajax({
        type: "get",
        url: "$getCommentUrl",
        dataType: "json",
        success: function (res) {
            $('.viewFormComment').html(res.content);
        }
    });
}

async function listComment() {
    await $.ajax({
        type: "get",
        url: "$listCommentUrl",
        dataType: "json",
        success: function (res) {
            $('.listComment').html(res.content);
        }
    });
}

// Event delegation สำหรับการทำงานใน AJAX content
$("body").on("click", ".update-comment", function (e) {
    e.preventDefault();
    $.ajax({
        type: "get",
        url: $(this).attr('href'),
        dataType: "json",
        success: function (res) {
            // ลบ class active/show จาก tab อื่นๆ ก่อน (ถ้ามี)
           // ค้นหาลิงก์ที่มี href="#home" แล้วสั่งคลิก หรือเติม class
            let homeTab = $('a[href="#home"]');
            
            // ลบ active จากตัวอื่น
            $('.nav-link, .tab-pane').removeClass('active show');
            
            // เพิ่ม active ให้เมนูและเนื้อหา
            homeTab.addClass('active').attr('aria-selected', 'true');
            $('#home').addClass('active show');
            $('.viewFormComment').html(res.content);
            // Scroll down ไปที่ฟอร์มแก้ไขในมือถือ
            if($(window).width() < 992) {
                $('html, body').animate({ scrollTop: $('.viewFormComment').offset().top - 100 }, 500);
            }
        }
    });
});

$("body").on("click", ".delete-comment", function (e) {
    e.preventDefault();
    Swal.fire({
        title: 'ยืนยัน',
        text: 'ต้องการลบหรือไม่',
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "ใช่, ยืนยัน!",
        cancelButtonText: "ยกเลิก",
    }).then((result) => {
        if(result.isConfirmed) {
            $.ajax({
                type: "post",
                url: $(this).attr('href'),
                dataType: "json",
                success: function (res) {
                    if(res.status == 'success'){
                        listComment();
                    }
                }
            });
        }
    }); 
});


$("body").off("click", ".text-template").on("click", ".text-template", function (e) {
    e.preventDefault();
    var textDist = $('#documentsdetail-data_json-comment');
    var text = $(this).text().trim(); 
    
    textDist.val(function(i, oldVal) {
        return oldVal + text;
    });
    
    textDist.focus();
    if (typeof updateCharCount === "function") updateCharCount();
});


$(document).ready(function() {
    // อ้างอิง Element ปุ่มด้วยชื่อตัวแปรปกติ
    var saveBtn = $('#btn-save-temp-now');
    var selectedText = "";

    // 1. ดักจับการเลือกข้อความใน Textarea
    $("body").on("mouseup", "#documentsdetail-data_json-comment", function (e) {
        var el = $(this);
        var start = el.prop('selectionStart');
        var end = el.prop('selectionEnd');
        console.log('ดักจับการเลือกข้อความใน Textarea');
        
        
        // ดึงข้อความที่เลือก (Highlight)
        selectedText = el.val().substring(start, end).trim();

        // เงื่อนไข: ถ้ามีข้อความที่เลือกให้แสดงปุ่ม ถ้าไม่มีให้ซ่อน
        if (selectedText.length > 0) {
            saveBtn.show(); 
        } else {
            saveBtn.hide();
        }
    });

    // 2. เมื่อคลิกปุ่มบันทึก
    saveBtn.on('click', function(e) {
        e.preventDefault();

        if (selectedText !== "") {
            // ส่ง AJAX บันทึกลง Database
            $.ajax({
                url:"$saveCommentTemplate", // ตรวจสอบว่าตัวแปร PHP นี้มีค่า
                type: 'POST',
                data: { 
                    text: selectedText, 
                },
                success: function(res) {
                    listCommentTemplate()
                    saveBtn.hide(); 
                    selectedText = ""; 
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล');
                }
            });
        }
    });
});

function listCommentTemplate()
{
    $.ajax({
        type: "get",
        url: "/me/documents/list-comment-template",
        dataType: "json",
        success: function (res) {
            // 1. อัปเดตตัวเลขจำนวนแม่แบบ
            if (res.totalCount !== undefined) {
                $('#counttemplate').html(res.totalCount);
            }
            $('#viewlistCommenttemplate').html(res.content)
        }
    });
}

function updateCharCount() {
    // ใช้ตัวแปรอ้างอิงและเช็คก่อนว่ามีอยู่จริงไหม
    var textArea = $('#documentsdetail-data_json-comment');
    
    // ตรวจสอบว่า textArea มีค่าหรือไม่ (length > 0 หมายถึงหา element เจอ)
    if (textArea.length > 0) {
        var content = textArea.val(); // ดึงค่า
        
        // กันเหนียว: ถ้า content เป็น null หรือ undefined ให้ใช้ค่าว่าง ""
        var len = (content ? content.length : 0);
        
        // อัปเดตไปยัง Badge
        $('#char-count').html(len + ' ตัวอักษร');
        
        // ปรับ Opacity
        if (len > 0) {
            $('#char-count').removeClass('opacity-50').addClass('opacity-100');
        } else {
            $('#char-count').removeClass('opacity-100').addClass('opacity-50');
        }
    } else {
        // console.error("ไม่พบ Element ID: #documentsdetail-data_json-comment");
    }
}

// การเรียกใช้งาน
$(document).on('input keyup', '#documentsdetail-data_json-comment', function() {
    updateCharCount();
});

    // 3. กรณีพิเศษ: หากมีการเพิ่มข้อความผ่านปุ่มแม่แบบ (JS .val())
    // ให้สั่งเรียกฟังก์ชันนี้ต่อท้ายคำสั่ง .val() ในส่วนของปุ่มแม่แบบด้วย
    // หรือใช้ trigger ช่วย:
    // $('#documentsdetail-data_json-comment').val(newText).trigger('input');

    // 4. เรียกทำงานทันทีเมื่อโหลดหน้า (เผื่อมีข้อมูลเก่า)

$(document).on('click', '.btn-delete-action', function(e) {
    e.preventDefault();
    var btnDelete = $(this);
    var templateId = btnDelete.data('id');

    // เรียกใช้งาน SweetAlert2
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: "คุณต้องการลบแม่แบบนี้ใช่หรือไม่? ข้อมูลจะหายไปถาวร",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444', // สีแดง
        cancelButtonColor: '#6b7280', // สีเทา
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: false // ให้ปุ่มยกเลิกอยู่ซ้าย ปุ่มยืนยันอยู่ขวา
    }).then((result) => {
        console.log(result.isConfirmed);
        
        // ถ้าผู้ใช้กดปุ่มยืนยัน (Confirm)
        if (result.isConfirmed) {
            
            $.ajax({
                url: '/me/documents/delete-comment-template',
                type: 'POST',
                data: { 
                    id: templateId,
                },
                success: function(res) {
                   
                    if(res.status == 'success'){
                    listCommentTemplate()
                   
                    // 2. ลบออกจากหน้าจอด้วย Animation
                    // ถ้าใช้ Dropdown แบบล่าสุดที่ผมออกแบบ ให้เปลี่ยนจาก .template-wrapper เป็น li หรือคลาสที่คุณใช้ครอบแถวนั้น
                    btnDelete.closest('.template-item, li, .template-wrapper').fadeOut(300, function() {
                        $(this).remove();
                    });
                    // 3. แจ้งเตือนว่าลบสำเร็จ
                    Swal.fire({
                        title: 'ลบสำเร็จ!',
                        text: 'แม่แบบของคุณถูกลบออกแล้ว',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                     }
                },
                error: function() {
                    Swal.fire('เกิดข้อผิดพลาด!', 'ไม่สามารถลบข้อมูลได้ กรุณาลองใหม่', 'error');
                }
            });
        }
    });
});

JS;
$this->registerJS($js, View::POS_END);
?>