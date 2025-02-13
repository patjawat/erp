

$('#page-content').show()
$('#loader').hide()

window.onbeforeunload = function () {
  $('#page-content').hide()
  $('#loader').show()
  // NProgress.start();

};

jQuery(document).on("pjax:start", function () {
  // NProgress.start();
  $('#page-content').hide()
  $('#loader').show()
  
  console.log("pjax start");
});
jQuery(document).on("pjax:end", function () {
  // NProgress.done();
  $('#page-content').show()
$('#loader').hide()
});

// focus เวลาเปิก select2
$(document).on("select2:open", () => {
  document
    .querySelector(".select2-container--open .select2-search__field")
    .focus();
});


$(".link-loading").click(function (e) {
  $(".loading-page").show();
});

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


function confirm(text)
{
   Swal.fire({
    title: 'ยืนยัน',
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
  var url = $(this).attr('href');
          e.preventDefault();
          $.ajax({
              type: "get",
              url: url,
              dataType: "json",
              success: function (res) {
                  console.log(res);
                  location.reload();
                  
              }
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

      $("#main-modal-label").html('เกิดข้อผิดพลาด');
      $(".modal-body").html('<h5 class="text-center"><i class="fa-solid fa-triangle-exclamation text-danger"></i> ไม่อนุญาต</h5>');
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
      $("#fullscreen-modal-label").html('เกิดข้อผิดพลาด');
      $(".modal-body").html('<h5 class="text-center"><i class="fa-solid fa-triangle-exclamation text-danger"></i> ไม่อนุญาต</h5>');
      $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
      $(".modal-dialog").addClass("modal-md");
    },
  });
});



$("body").on("click", ".confirm-order", async function (e) {
  e.preventDefault();
  var title = $(this).data('title');
  var text = $(this).data('text');
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
      url: $(this).attr('href'),
      dataType: "json",
      success: async function (response) {
        if (response.status == "success") {
          location.reload();
          // await  $.pjax.reload({container:response.container, history:false,url:response.url});
          success(text+"บัำเร็จ!.");
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
        success:  function (response) {
          if (response.status == "success") {
            // await  $.pjax.reload({container:response.container, history:false,url:response.url});
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



$(".show-setting").on("click", function () {
  $(".right-setting").addClass("show");
});

$(".setting-close").on("click", function () {
  $(".right-setting").removeClass("show");
});
