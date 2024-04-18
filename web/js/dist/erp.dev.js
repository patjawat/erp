"use strict";

// alert()
// const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
// const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
window.onbeforeunload = function () {
  $('#cover-spin').show(); // $('#modal-dialog').modal('hide');
  // $('#awaitLogin').show();
  // $('#content-container').hide();
  // $('#cover-spin').hide(0)
}; // focus เวลาเปิก select2


$(document).on("select2:open", function () {
  document.querySelector(".select2-container--open .select2-search__field").focus();
});
$('.loading-page').hide();
$('.link-loading').click(function (e) {
  $('.loading-page').show();
});

function beforLoadModal() {
  console.log('beforLoadModal');
  $('#main-modal').modal('show'); // $('#modal-dialog').modal('show');

  $('#main-modal-label').html('กำลังโหลด');
  $(".modal-dialog").removeClass('modal-sm modal-md modal-lg');
  $(".modal-dialog").addClass('modal-sm');
  $('#modal-dialog').removeClass('fade');
  $('.modal-body').html('<div class="d-flex justify-content-center"><div class="spinner-border" style="width: 3rem; height: 3rem;" role="status"></div></div><h6 class="text-center mt-3">Loading...</h6>');
}

function closeModal() {
  $('#main-modal').modal('toggle');
  Swal.fire({
    icon: 'success',
    title: 'บันทึกสำเร็จ',
    showConfirmButton: false,
    timer: 1500
  });
}

function _success() {
  var $msg = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
  var Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: function didOpen(toast) {
      toast.addEventListener('mouseenter', Swal.stopTimer);
      toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
  });
  Toast.fire({
    icon: 'success',
    title: $msg ? $msg : 'ดำเนินการสำเร็จ'
  }); // $('#main-modal').modal('toggle');
}

function warning() {
  var $msg = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
  var Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: function didOpen(toast) {
      toast.addEventListener('mouseenter', Swal.stopTimer);
      toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
  });
  Toast.fire({
    icon: 'warning',
    title: $msg ? $msg : 'เกิดข้อผิดพล'
  });
}

$('body').on('click', '.open-modal', function (e) {
  e.preventDefault();
  var url = $(this).attr('href');
  var size = $(this).data('size');
  beforLoadModal();
  $.ajax({
    type: "get",
    url: url,
    dataType: "json",
    success: function success(response) {
      $('#main-modal').modal('show');
      $('#main-modal-label').html(response.title);
      $('.modal-body').html(response.content);
      $('.modal-footer').html(response.footer);
      $(".modal-dialog").removeClass('modal-sm modal-md modal-lg');
      $(".modal-dialog").addClass(size);
      $('.modal-content').addClass('card-outline card-primary');
    }
  });
});
$('body').on('click', '.delete-item', function (e) {
  e.preventDefault();
  var url = $(this).attr('href'); // console.log('delete',url);
  // $('#main-modal').modal('show');

  Swal.fire({
    title: 'คุณแน่ใจไหม?',
    text: "ลบรายการที่เลือก!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ใช่, ลบเลย!',
    cancelButtonText: 'ยกเลิก'
  }).then(function (result) {
    console.log('result', result.value);

    if (result.value == true) {
      console.log('ok');
      $.ajax({
        type: "post",
        url: url,
        dataType: "json",
        success: function success(response) {
          return regeneratorRuntime.async(function success$(_context) {
            while (1) {
              switch (_context.prev = _context.next) {
                case 0:
                  if (!(response.status == 'success')) {
                    _context.next = 6;
                    break;
                  }

                  _context.next = 3;
                  return regeneratorRuntime.awrap($.pjax.reload({
                    container: response.container,
                    history: false,
                    url: response.url
                  }));

                case 3:
                  _context.next = 5;
                  return regeneratorRuntime.awrap(_success('ถูกลบไปแล้ว.'));

                case 5:
                  if (response.close) {
                    $('#main-modal').modal('hide');
                  }

                case 6:
                case "end":
                  return _context.stop();
              }
            }
          });
        }
      });
    }
  });
});
$('.edit-avatar').change(function (e) {
  e.preventDefault();
  formdata = new FormData();

  if ($(this).prop('files').length > 0) {
    file = $(this).prop('files')[0];
    formdata.append("avatar", file);
    formdata.append("id", 1);
    $.ajax({
      // url: '$urlUpload',
      url: 'profile/upload',
      type: "POST",
      data: formdata,
      processData: false,
      contentType: false,
      success: function success(result) {
        $('.view-avatar').attr('src', result);

        _success('แก้ไขภาพ');
      }
    });
  }
});
$('body').on('click', '.select-employee', function (e) {
  var _this = this;

  e.preventDefault();
  var fullname = $(this).data('fullname');
  var address = $(this).data('address');
  var avatar = $(this).data('avatar') ? '/' + $(this).data('avatar') : 'images/user/01.jpg';
  console.log(fullname);
  Swal.fire({
    title: fullname,
    text: address,
    icon: 'info',
    imageUrl: avatar,
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    cancelButtonText: '<i class="fa-solid fa-circle-minus"></i> ยกเลิก',
    confirmButtonText: '<i class="fa-regular fa-circle-check"></i> ตกลง'
  }).then(function (result) {
    if (result.value == true) {
      $.ajax({
        type: "get",
        url: $(_this).attr('href'),
        dataType: "json",
        success: function success(response) {
          console.log(response);
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