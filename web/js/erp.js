$("#page-content").show();
$("#loader").hide();

window.onbeforeunload = function () {
  $("#page-content").hide();
  $("#loader").show();
  // NProgress.start();
};

jQuery(document).on("pjax:start", function () {
  NProgress.start();
   var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('offcanvasRight'));
      offcanvas.hide();
  console.log("pjax start");
});
jQuery(document).on("pjax:end", function () {
  NProgress.done();
  // ตัวอย่าง: รีโหลด Offcanvas
    var offcanvasElList = [].slice.call(document.querySelectorAll('.offcanvas'))
    var offcanvasList = offcanvasElList.map(function (offcanvasEl) {
        return new bootstrap.Offcanvas(offcanvasEl)
    })
 
});



function handleFormSubmit(formSelector, actionUrl, successCallback) {
  $(formSelector).on('beforeSubmit', function (e) {
    var form = $(this);
    Swal.fire({
      title: "ยืนยัน?",
      text: "บันทึกข้อมูล!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      cancelButtonText: "ยกเลิก!",
      confirmButtonText: "ใช่, ยืนยัน!"
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'กำลังบันทึก...',
          text: 'กรุณารอสักครู่',
          allowOutsideClick: false,
          didOpen: () => {
            $('#main-modal').hide();
            Swal.showLoading();
            Swal.close();
            $.ajax({
              url: actionUrl || form.attr('action'),
              type: 'post',
              data: form.serialize(),
              dataType: 'json',
              success: async function (response) {
                // form.yiiActiveForm('updateMessages', response, true);
                if (response.status == 'success') {
                  Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: 'บันทึกข้อมูลเรียบร้อยแล้ว',
                    timer: 1000,
                    showConfirmButton: false
                  }).then(() => {
                    if (typeof successCallback === 'function') {
                      successCallback(response);
                    } else {
                      location.reload();
                    }
                  });
                }
              }
            });
          }
        });
      }
    });
    return false;
  });
}


// #### การอัพโหลดรูปภาพ ####

function isFile(){
    var isFile = $('#editImagePreview').data('isfile');
    console.log(isFile);
    
    if(isFile == true){
        $("#editImagePreview").show();
        $("#editUploadBtn").hide();
        $("#editRemoveImage").show();
    }else{
        $("#editImagePreview").hide();
        $("#editUploadBtn").show();
        $("#editRemoveImage").hide();
    }
}

$('body').on('change', '.file-upload-input', function(e) {

$('#editImagePreview').data('newfile', true);

   console.log('file upload input change');
   
   
    var fileInput = $(this)[0];
    if (fileInput.files && fileInput.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
        $("#editPreviewImg").attr("src", e.target.result);
        $("#editImagePreview").show();
        $("#editUploadBtn").hide();
        $("#editRemoveImage").show();
        };
        reader.readAsDataURL(fileInput.files[0]);
    }
});

$('body').on('click', '#editRemoveImage', function (e) {
    e.preventDefault();
    $("#editPreviewImg").attr("src", '');
    $("#editImagePreview").hide();
    $("#editUploadBtn").show();
    $("#editRemoveImage").hide();
    console.log('remove image');
    

});

async function uploadImage(name,ref) {
  console.log('uploadImage', name, ref);
    var newFile = $('#editImagePreview').data('newfile');
    if(newFile){
    formdata = new FormData();
    if($("input[id='my_file']").prop('files').length > 0)
    {
		file = $("input[id='my_file']").prop('files')[0];
        formdata.append(name, file);
        formdata.append("id", 1);
        formdata.append("ref", ref);
        formdata.append("name", name);
        await $.ajax({
          url: '/filemanager/uploads/single',
          type: "POST",
          data: formdata,
          processData: false,
          contentType: false,
          success: function (res) {
                    success('upload success')
          }
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
  $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
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
  beforLoadModal();

  $.ajax({
    type: "get",
    url: url,
    dataType: "json",
    success: function (response) {
      $("#main-modal").modal("show");
      $("#main-modal-label").html(response.title);
      $(".modal-body").html(response.content);
      $(".modal-footer").html(response.footer);
      $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
      $(".modal-dialog").addClass(size);
      $(".modal-content").addClass("card-outline card-primary");
    },
    error: function (xhr) {
      $("#main-modal-label").html("เกิดข้อผิดพลาด");
      $(".modal-body").html(
        '<h5 class="text-center"><i class="fa-solid fa-triangle-exclamation text-danger"></i> ไม่อนุญาต</h5>'
      );
      $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
      $(".modal-dialog").addClass("modal-md");
      console.log(xhr);

      // if error occured
      // alert("Error occured.please try again");
      // console.log(xhr.statusText + xhr.responseText);
      // Swal.fire({
      //   icon: "error",
      //   title: "Oops...",
      //   html: xhr.statusText + "</hr>" + xhr.responseText,
      //   // text: xhr.responseText,
      //   // footer: '<a href="#">Why do I have this issue?</a>'
      // }).then(function (dismiss) {
      //   console.log(dismiss);
      //   if (dismiss.isConfirmed) {
      //     $("#main-modal").modal("hide");
      //   }
      // });

      // $(placeholder).append(xhr.statusText + xhr.responseText);
      // $(placeholder).removeClass('loading');
    },
  });
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
  // console.log('delete',url);
  // $('#main-modal').modal('show');

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
          if (response.status == "success") {
              $.pjax.reload({
                container: response.container,
                history: false,
                url: response.url,
              });

              success("ดำเนินการลบสำเร็จ!.");
            if (response.close) {
              $("#main-modal").modal("hide");
            }
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

$(document).on('click', '.cancel-order', function(e) {
  e.preventDefault();
  let url = $(this).attr('href');
  Swal.fire({
      title: 'คุณแน่ใจหรือไม่?',
      text: "คุณต้องการยกเลิกคำขอนี้หรือไม่?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'ใช่, ยกเลิก!',
      cancelButtonText: 'ยกเลิก'
  }).then((result) => {
      if (result.isConfirmed) {
          $.ajax({
              url: url,
              type: 'POST',
              success: function(response) {
                  Swal.fire(
                      'ยกเลิกสำเร็จ!',
                      'คำขอของคุณถูกยกเลิกแล้ว.',
                      'success'
                  ).then(() => {
                      location.reload(); // Reload the page to reflect changes
                  });
              },
/*************  ✨ Windsurf Command ⭐  *************/
        /**
         * If the request fails, show an error message in a modal.
/*******  eff9be3f-c24a-493d-816a-4f934d6757f2  *******/
              error: function() {
                  Swal.fire(
                      'เกิดข้อผิดพลาด!',
                      'ไม่สามารถยกเลิกคำขอได้.',
                      'error'
                  );
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
