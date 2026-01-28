window.onbeforeunload = function () {
  showTableLoading();
  // NProgress.start();
};

jQuery(document).on("pjax:start", function () {
  NProgress.start();
  showTableLoading();
  //  var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('offcanvasRight'));
  //     offcanvas.hide();
  const el = document.getElementById("offcanvasRight");
  if (el) bootstrap.Offcanvas.getOrCreateInstance(el).hide();
  console.log("pjax start");
});

jQuery(document).on("pjax:end", function () {
  NProgress.done();
  tableLoading1.style.display = "none";
  // ตัวอย่าง: รีโหลด Offcanvas
  var offcanvasElList = [].slice.call(document.querySelectorAll(".offcanvas"));
  if (offcanvasElList.length > 0) {
    var offcanvasList = offcanvasElList.map(function (offcanvasEl) {
      return new bootstrap.Offcanvas(offcanvasEl);
    });
  }
});

// ฟังก์ชันเลื่อนขึ้นบนสุด
document.getElementById("btnScrollTop").addEventListener("click", function () {
  window.scrollTo({ top: 0, behavior: "smooth" });
});

// ฟังก์ชันเลื่อนลงล่างสุด
document
  .getElementById("btnScrollBottom")
  .addEventListener("click", function () {
    window.scrollTo({ top: document.body.scrollHeight, behavior: "smooth" });
  });

// แสดงปุ่มเมื่อ scroll ลงมา
window.addEventListener("scroll", function () {
  const buttons = document.getElementById("scroll-buttons");
  if (window.scrollY > 100) {
    buttons.style.display = "block";
  } else {
    buttons.style.display = "none";
  }
});

//แก้ treeview ไม่ปิดเวลาเลือก
$("#treeID").on("treeview:change", function (event, key, name) {
  $("body").find(".kv-tree-input").removeClass("show");
  $("body").find(".kv-tree-dropdown").removeClass("show");
});

$("body").on("click", ".form-submit", async function (e) {
  e.preventDefault();
  var formId = $(this).data("id");
  $("#" + formId).submit();
});
/**
 * Handle AJAX form submission with confirmation and success feedback.
 * @param {string} formSelector - jQuery selector for the form.
 * @param {string} [actionUrl] - Optional URL to submit the form to.
 * @param {function} [successCallback] - Optional callback on success.
 */
function handleFormSubmit(formSelector, actionUrl, successCallback) {
  $(document).on("beforeSubmit", formSelector, function (e) {
    e.preventDefault();
    const form = $(this);

    Swal.fire({
      title: "ยืนยันการบันทึกข้อมูล?",
      text: "โปรดตรวจสอบความถูกต้องของข้อมูลก่อนกดยืนยัน",
      icon: "question", // เปลี่ยนจาก warning เป็น question เพื่อความรู้สึกที่ซอฟต์ลง
      showCancelButton: true,
      confirmButtonColor: "#28a745", // สีเขียว Success (Bootstrap Standard)
      cancelButtonColor: "#6c757d", // สีเทา Secondary (Bootstrap Standard)
      confirmButtonText: '<i class="fa fa-save"></i> ยืนยันบันทึก',
      cancelButtonText: "ยกเลิก",
      reverseButtons: false, // เอาปุ่มยกเลิกไว้ซ้าย ปุ่มยืนยันไว้ขวา (UX Standard)
    }).then((result) => {
      if (result.isConfirmed) {
        // ปิด Modal เดิม (ถ้ามี)
        $("#main-modal").modal("hide");

        // Loading State
        Swal.fire({
          title: "กำลังดำเนินการ",
          text: "ระบบกำลังบันทึกข้อมูลของคุณลงฐานข้อมูล...",
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          },
        });

        $.ajax({
          url: actionUrl || form.attr("action"),
          type: "POST",
          data: form.serialize(),
          dataType: "json",
          success: function (response) {
            if (response.status === "success") {
              Swal.fire({
                icon: "success",
                title: "ดำเนินการสำเร็จ",
                text: response.message || "บันทึกข้อมูลเรียบร้อยแล้ว",
                timer: 1500, // เพิ่มเวลาเล็กน้อยให้ User ได้อ่านชื่อชั้นตราที่บันทึก
                showConfirmButton: false,
              }).then(async () => {
                if (typeof successCallback === "function") {
                  await successCallback(response);
                }

                if (response.redirect_url) {
                  window.location.href = response.redirect_url;
                } else if (typeof successCallback !== "function") {
                  location.reload();
                }
              });
            } else {
              Swal.fire({
                icon: "error",
                title: "ไม่สามารถบันทึกข้อมูลได้",
                text: response.message || "เกิดข้อผิดพลาดบางประการ กรุณาลองใหม่อีกครั้ง",
                confirmButtonText: "ตกลง",
                confirmButtonColor: "#d33",
              });
            }
          },
          error: function (xhr) {
            Swal.fire({
              icon: "error",
              title: "การเชื่อมต่อขัดข้อง",
              text: "ไม่สามารถติดต่อ Server ได้ (Error " + xhr.status + ")",
              confirmButtonText: "รับทราบ",
            });
          },
        });
      }
    });
    return false;
  });
}

// #### การอัพโหลดรูปภาพ ####

function isFile() {
  var isFile = $("#editImagePreview").data("isfile");
  console.log(isFile);

  if (isFile == true) {
    $("#editImagePreview").show();
    $("#editUploadBtn").hide();
    $("#editRemoveImage").show();
  } else {
    $("#editImagePreview").hide();
    $("#editUploadBtn").show();
    $("#editRemoveImage").hide();
  }
}

// ติดดาวหนังสือ
$("body").on("click", ".bookmark", function (e) {
  e.preventDefault();
  var title = $(this).data("title");
  var id = $(this).attr("id");
  console.log("update commetn", id);
  $.ajax({
    type: "get",
    url: $(this).attr("href"),
    dataType: "json",
    success: async function (res) {
      // var bookmark = $(this).find('i').attr('class', 'fa-solid fa-star text-warning fs-4');
      var data = $("body")
        .find(".bookmark-star-" + id)
        .html("<h1>1</h1>");
      console.log(id);
      if (res.data.bookmark == "Y") {
        $("body")
          .find(".bookmark-star-" + id)
          .html('<i class="fa-solid fa-star text-warning"></i>');
        success("ติดดาว");
      } else if (res.data.bookmark == "N") {
        $("body")
          .find(".bookmark-star-" + id)
          .html('<i class="fa-regular fa-star"></i>');
        success("ยกเลิกติดดาว");
      }
      // location.reload();
    },
  });
});

$("body").on("change", ".file-upload-input", function (e) {
  $("#editImagePreview").data("newfile", true);

  console.log("file upload input change");

  var fileInput = $(this)[0];
  if (fileInput.files && fileInput.files[0]) {
    var reader = new FileReader();
    reader.onload = function (e) {
      $("#editPreviewImg").attr("src", e.target.result);
      $("#editImagePreview").show();
      $("#editUploadBtn").hide();
      $("#editRemoveImage").show();
    };
    reader.readAsDataURL(fileInput.files[0]);
  }
});

$("body").on("click", "#editRemoveImage", function (e) {
  e.preventDefault();
  $("#editPreviewImg").attr("src", "");
  $("#editImagePreview").hide();
  $("#editUploadBtn").show();
  $("#editRemoveImage").hide();
  console.log("remove image");
});

async function uploadImage(name, ref) {
  console.log("uploadImage", name, ref);
  var newFile = $("#editImagePreview").data("newfile");
  if (newFile) {
    formdata = new FormData();
    if ($("input[id='my_file']").prop("files").length > 0) {
      file = $("input[id='my_file']").prop("files")[0];
      formdata.append(name, file);
      formdata.append("id", 1);
      formdata.append("ref", ref);
      formdata.append("name", name);
      await $.ajax({
        url: "/filemanager/uploads/single",
        type: "POST",
        data: formdata,
        processData: false,
        contentType: false,
        success: function (res) {
          success("upload success");
        },
      });
    }
  }
}
// #### จบการอัพโหลดรูปภาพ ####

// focus เวลาเปิก select2
$(document).on("select2:open", () => {
  document
    .querySelector(".select2-container--open .select2-search__field")
    .focus();
});

$(".link-loading").click(function (e) {
  $(".loading-page").show();
});

function showLoading() {
  $("#page-content").hide();
  $("#loader").show();
}

function hideLoading() {
  $("#page-content").show();
  $("#loader").hide();
}

function beforLoadModal() {
  console.log("beforLoadModal");
  $("#main-modal").modal("show");
  // $('#modal-dialog').modal('show');
  $("#main-modal-label").html("กำลังโหลด");
  $(".modal-dialog").removeClass(
    "modal-sm modal-md modal-lg modal-xl modal-xxl"
  );
  $(".modal-dialog").addClass("modal-sm");
  $("#modal-dialog").removeClass("fade");
  $(".modal-body").html(
    '<div class="d-flex justify-content-center"><div class="spinner-border" style="width: 3rem; height: 3rem;" role="status"></div></div><h6 class="text-center mt-3">Loading...</h6>'
  );
}

function closeModal() {
  $("#main-modal").modal("toggle");
  Swal.fire({
    icon: "success",
    title: "บันทึกสำเร็จ",
    showConfirmButton: false,
    timer: 1500,
  });
}

function success($msg = "") {
  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener("mouseenter", Swal.stopTimer);
      toast.addEventListener("mouseleave", Swal.resumeTimer);
    },
  });
  Toast.fire({
    icon: "success",
    title: $msg ? $msg : "ดำเนินการสำเร็จ",
  });
  // $('#main-modal').modal('toggle');
}

function warning($msg = "") {
  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener("mouseenter", Swal.stopTimer);
      toast.addEventListener("mouseleave", Swal.resumeTimer);
    },
  });
  Toast.fire({
    icon: "warning",
    title: $msg ? $msg : "เกิดข้อผิดพล",
  });
}

function confirm(text) {
  Swal.fire({
    title: "ยืนยัน",
    text: text,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "ใช่, ยืนยัน!",
    cancelButtonText: "ยกเลิก",
  }).then(async (result) => {
    return result.value;
  });
}
$("body").on("click", ".setview", function (e) {
  var url = $(this).attr("href");
  e.preventDefault();
  $.ajax({
    type: "get",
    url: url,
    dataType: "json",
    success: function (res) {
      console.log(res);
      location.reload();
    },
  });
});
$("body").on("click", ".open-modal", function (e) {
    e.preventDefault();
    var url = $(this).attr("href");
    var size = $(this).data("size");
    
    // แสดง loading หรืออื่นๆ
    if (typeof beforLoadModal === "function") beforLoadModal();

    $.ajax({
        type: "get",
        url: url,
        dataType: "json",
        success: function (response) {
            var modal = $("#main-modal");
            modal.find("#main-modal-label").html(response.title);
            modal.find(".modal-body").html(response.content);
            modal.find(".modal-footer").html(response.footer);
            
            modal.find(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl modal-xxl")
                 .addClass(size);
            
            modal.modal("show");
        },
        error: function (xhr) {
            // จัดการ error เหมือนเดิม
        }
    });
});

// ส่วนสำคัญ: บังคับให้ Pjax ทำงานใหม่เมื่อเนื้อหาใน Modal ถูกเปลี่ยนหน้า
$(document).on('pjax:complete', '#product-pjax-container', function() {
    console.log("Pjax ใน Modal ทำงานเสร็จสมบูรณ์");
});
$("body").on("click", ".open-sub-modal", function (e) {
  e.preventDefault();
  var url = $(this).attr("href");
  var size = $(this).data("size");
  // beforLoadModal();

  $.ajax({
    type: "get",
    url: url,
    dataType: "json",
    success: function (response) {
      $("#sub-modal").modal("show");
      // $("#sub-modal-label").html(response.title);
      $("#sub-modal .modal-body").html(response.content);
      // $(".modal-footer").html(response.footer);
      // $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
      // $(".modal-dialog").addClass(size);
      // $(".modal-content").addClass("card-outline card-primary");
    },
    error: function (xhr) {
      // $("#sub-modal-label").html('เกิดข้อผิดพลาด');
      // $(".modal-body").html('<h5 class="text-center"><i class="fa-solid fa-triangle-exclamation text-danger"></i> ไม่อนุญาต</h5>');
      // $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
      // $(".modal-dialog").addClass("modal-md");
    },
  });
});

$("body").on("click", ".open-modal-fullscreen", function (e) {
  e.preventDefault();
  var url = $(this).attr("href");
  var size = $(this).data("size");

  $.ajax({
    type: "get",
    url: url,
    dataType: "json",
    success: function (response) {
      $("#fullscreen-modal").modal("show");
      $("#fullscreen-modal-label").html(response.title);
      $(".modal-body").html(response.content);
      $(".modal-footer").html(response.footer);
      $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
      $(".modal-dialog").addClass(size);
      $(".modal-content").addClass("card-outline card-primary");
    },
    error: function (xhr) {
      $("#fullscreen-modal-label").html("เกิดข้อผิดพลาด");
      $(".modal-body").html(
        '<h5 class="text-center"><i class="fa-solid fa-triangle-exclamation text-danger"></i> ไม่อนุญาต</h5>'
      );
      $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
      $(".modal-dialog").addClass("modal-md");
    },
  });
});

$("body").on("click", ".confirm-order", async function (e) {
  e.preventDefault();
  var title = $(this).data("title");
  var text = $(this).data("text");
  await Swal.fire({
    title: title,
    text: text,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "ใช่, ยืนยัน!",
    cancelButtonText: "ยกเลิก",
  }).then(async (result) => {
    if (result.value == true) {
      await $.ajax({
        type: "post",
        url: $(this).attr("href"),
        dataType: "json",
        success: async function (response) {
          if (response.status == "success") {
            location.reload();
            // await  $.pjax.reload({container:response.container, history:false,url:response.url});
            success(text + "บัำเร็จ!.");
          }
        },
      });
    }
  });
});

$("body").on("click", ".delete-item", async function (e) {
  e.preventDefault();
  var url = $(this).attr("href");

  await Swal.fire({
    title: "คุณแน่ใจไหม?",
    text: "ลบรายการที่เลือก!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "ใช่, ลบเลย!",
    cancelButtonText: "ยกเลิก",
  }).then(async (result) => {
    console.log("result", result.value);
    if (result.value == true) {
      await $.ajax({
        type: "post",
        url: url,
        dataType: "json",
        success: function (response) {
          if (response.status == "success" && response.container) {
            $.pjax.reload({
              container: response.container,
              history: false,
              url: response.url,
            });
          } else if (response.status == "success" && response.close) {
            success("ดำเนินการลบสำเร็จ!.");
            $("#main-modal").modal("hide");
          } else if (response.status == "success" && response.url) {
            window.location.href = response.url;
          } else {
            location.reload();
          }
        },
      });
    }
  });
});

$(".edit-avatar").change(function (e) {
  e.preventDefault();
  formdata = new FormData();
  if ($(this).prop("files").length > 0) {
    file = $(this).prop("files")[0];
    formdata.append("avatar", file);
    formdata.append("id", 1);
    $.ajax({
      // url: '$urlUpload',
      url: "profile/upload",
      type: "POST",
      data: formdata,
      processData: false,
      contentType: false,
      success: function (result) {
        $(".view-avatar").attr("src", result);
        success("แก้ไขภาพ");
      },
    });
  }
});

$("body").on("click", ".select-employee", function (e) {
  e.preventDefault();
  var fullname = $(this).data("fullname");
  var address = $(this).data("address");
  var avatar = $(this).data("avatar")
    ? "/" + $(this).data("avatar")
    : "images/user/01.jpg";
  console.log(fullname);

  Swal.fire({
    title: fullname,
    text: address,
    icon: "info",
    imageUrl: avatar,
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    cancelButtonText: '<i class="fa-solid fa-circle-minus"></i> ยกเลิก',
    confirmButtonText: '<i class="fa-regular fa-circle-check"></i> ตกลง',
  }).then((result) => {
    if (result.value == true) {
      $.ajax({
        type: "get",
        url: $(this).attr("href"),
        dataType: "json",
        success: function (response) {
          console.log(response);
        },
      });
    }
  });
});

$(document).on("click", ".cancel-order", function (e) {
  e.preventDefault();
  let url = $(this).attr("href");
  Swal.fire({
    title: "คุณแน่ใจหรือไม่?",
    text: "คุณต้องการยกเลิกคำขอนี้หรือไม่?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "ใช่, ยกเลิก!",
    cancelButtonText: "ยกเลิก",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: url,
        type: "POST",
        success: function (response) {
          Swal.fire(
            "ยกเลิกสำเร็จ!",
            "คำขอของคุณถูกยกเลิกแล้ว.",
            "success"
          ).then(() => {
            location.reload(); // Reload the page to reflect changes
          });
        },
        /*************  ✨ Windsurf Command ⭐  *************/
        /**
         * If the request fails, show an error message in a modal.
/*******  eff9be3f-c24a-493d-816a-4f934d6757f2  *******/
        error: function () {
          Swal.fire("เกิดข้อผิดพลาด!", "ไม่สามารถยกเลิกคำขอได้.", "error");
        },
      });
    }
  });
});

//เพิ่ม function a-action สำหรับทำงานร้วมกัน
$(document).on("click", ".a-action", function (e) {
    e.preventDefault();
    const url = $(this).attr('href');

    // 1. ถามยืนยันก่อนดำเนินการ
    Swal.fire({
        title: 'คุณแน่ใจยกเลิกหรือไม่?',
        text: "การดำเนินการนี้ไม่สามารถย้อนกลับได้",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ตกลง',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            
            // แสดง Loading ระหว่างรอ AJAX
            Swal.fire({
                title: 'กำลังประมวลผล...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // 2. ส่ง AJAX
            $.ajax({
                type: "post",
                url: url,
                dataType: "json",
                success: function (res) {
                    if (res.status === 'success') {
                        // 3. แจ้งเตือนสำเร็จแล้วค่อย Reload
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: res.message || 'ดำเนินการเรียบร้อยแล้ว',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        // กรณี Server ตอบกลับแต่สถานะไม่ใช่ success
                        Swal.fire('เกิดข้อผิดพลาด', res.message || 'กรุณาลองใหม่อีกครั้ง', 'error');
                    }
                },
                error: function (xhr, status, error) {
                    // กรณี Server Error หรือ Connection มีปัญหา
                    Swal.fire('ผิดพลาด!', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้ (' + error + ')', 'error');
                }
            });
        }
    });
});

$(".show-setting").on("click", function () {
  $(".right-setting").addClass("show");
});

$(".setting-close").on("click", function () {
  $(".right-setting").removeClass("show");
});
