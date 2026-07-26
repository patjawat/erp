

// --- Global loading (ใช้ทั้ง pjax และ full page) ---
var _erpLoadingSafetyTimer = null;
function erpShowPageLoading() {
  var el = document.getElementById("erp-global-loading");
  if (el) el.classList.remove("erp-loading-hidden");
  // safety: กันค้าง — ถ้าไม่มีอะไรซ่อนภายใน 15 วิ ให้ซ่อนเอง (กันบั๊ก loading ค้างทุกกรณี)
  if (_erpLoadingSafetyTimer) clearTimeout(_erpLoadingSafetyTimer);
  _erpLoadingSafetyTimer = setTimeout(erpHidePageLoading, 15000);
}
function erpHidePageLoading() {
  if (_erpLoadingSafetyTimer) {
    clearTimeout(_erpLoadingSafetyTimer);
    _erpLoadingSafetyTimer = null;
  }
  var el = document.getElementById("erp-global-loading");
  if (el) el.classList.add("erp-loading-hidden");
}
window.erpShowPageLoading = erpShowPageLoading;
window.erpHidePageLoading = erpHidePageLoading;

if (typeof window.kvBs4InitForm !== "function") {
  window.kvBs4InitForm = function () {};
}

function erpGetBootstrapClass(className) {
  if (typeof window.bootstrap !== "undefined" && window.bootstrap && window.bootstrap[className]) {
    return window.bootstrap[className];
  }

  if (typeof bootstrap !== "undefined" && bootstrap && bootstrap[className]) {
    return bootstrap[className];
  }

  return undefined;
}

function erpBootstrapPluginShim(pluginName, bootstrapClassName) {
  if (typeof $.fn[pluginName] === "function") {
    return;
  }

  $.fn[pluginName] = function (option) {
    var BootstrapClass = erpGetBootstrapClass(bootstrapClassName);

    if (typeof BootstrapClass === "undefined") {
      return this;
    }

    return this.each(function () {
      var instance = BootstrapClass.getInstance(this);

      if (typeof option === "string") {
        if (instance && typeof instance[option] === "function") {
          instance[option]();
        }
        return;
      }

      if (!instance) {
        BootstrapClass.getOrCreateInstance(this, option || {});
      }
    });
  };
}

erpBootstrapPluginShim("tooltip", "Tooltip");
erpBootstrapPluginShim("popover", "Popover");

function erpBootstrapModalShim() {
  if (typeof $.fn.modal === "function") {
    return;
  }

  $.fn.modal = function (option) {
    var ModalClass = erpGetBootstrapClass("Modal");
    if (typeof ModalClass === "undefined") {
      return this;
    }

    var pluginArgs = Array.prototype.slice.call(arguments, 1);

    return this.each(function () {
      var instance = ModalClass.getInstance(this);

      if (typeof option === "string") {
        if (!instance) {
          instance = ModalClass.getOrCreateInstance(this);
        }

        if (instance && typeof instance[option] === "function") {
          instance[option].apply(instance, pluginArgs);
        }
        return;
      }

      var config = option && typeof option === "object" ? option : {};
      instance = ModalClass.getOrCreateInstance(this, config);

      if (!option || config.show !== false) {
        instance.show();
      }
    });
  };
}

erpBootstrapModalShim();

function erpInitBootstrapWidgets(root) {
  var $root = root && root.jquery ? root : $(root || document);

  function init(selector, pluginName) {
    if (typeof $.fn[pluginName] !== "function") {
      return;
    }

    var $elements = $root.filter(selector).add($root.find(selector));
    if (!$elements.length) {
      return;
    }

    $elements.each(function () {
      $(this)[pluginName]();
    });
  }

  init('[data-bs-toggle="tooltip"]', "tooltip");
  init('[data-bs-toggle="popover"]', "popover");
}
window.erpInitBootstrapWidgets = erpInitBootstrapWidgets;

function erpHideModal(modalTarget) {
  var modalEl =
    typeof modalTarget === "string" ? document.querySelector(modalTarget) : modalTarget;

  if (!modalEl) {
    return Promise.resolve();
  }

  return new Promise(function (resolve) {
    var finished = false;
    var timeoutId = null;

    function cleanup() {
      if (finished) {
        return;
      }
      finished = true;
      if (timeoutId) {
        clearTimeout(timeoutId);
      }
      erpRestoreModalFocus(modalEl);
      resolve();
    }

    function forceCleanup() {
      try {
        if (modalEl.classList) {
          modalEl.classList.remove("show");
        }
        modalEl.style.display = "none";
        document.body.classList.remove("modal-open");
        document.body.style.removeProperty("padding-right");
        document.querySelectorAll(".modal-backdrop").forEach(function (backdrop) {
          backdrop.remove();
        });
      } catch (e) {}
      cleanup();
    }

    if (modalEl.addEventListener) {
      modalEl.addEventListener("hidden.bs.modal", cleanup, { once: true });
    }

    timeoutId = setTimeout(forceCleanup, 900);

    var ModalClass = erpGetBootstrapClass("Modal");
    if (ModalClass && typeof ModalClass.getInstance === "function") {
      try {
        var modalInstance = ModalClass.getInstance(modalEl) || ModalClass.getOrCreateInstance(modalEl);
        if (modalInstance && typeof modalInstance.hide === "function") {
          modalInstance.hide();
          return;
        }
      } catch (e) {}
    }

    if (typeof jQuery !== "undefined" && typeof jQuery.fn.modal === "function") {
      try {
        jQuery(modalEl).one("hidden.bs.modal", cleanup);
        jQuery(modalEl).modal("hide");
        return;
      } catch (e) {}
    }
  });
}
window.erpHideModal = erpHideModal;

function erpRestoreModalFocus(modalEl) {
  var trigger = modalEl && modalEl.erpReturnFocus;
  if (!trigger) {
    return;
  }

  modalEl.erpReturnFocus = null;
  window.setTimeout(function () {
    if (document.contains(trigger) && typeof trigger.focus === "function") {
      trigger.focus();
    }
  }, 0);
}

function erpShowModal(modalTarget, config) {
  var modalEl =
    typeof modalTarget === "string" ? document.querySelector(modalTarget) : modalTarget;

  if (!modalEl) {
    return Promise.resolve();
  }

  return new Promise(function (resolve) {
    function finish() {
      resolve();
    }

    var ModalClass = erpGetBootstrapClass("Modal");
    if (ModalClass && typeof ModalClass.getInstance === "function") {
      try {
        var modalInstance =
          ModalClass.getInstance(modalEl) || ModalClass.getOrCreateInstance(modalEl, config || {});
        if (modalInstance && typeof modalInstance.show === "function") {
          modalInstance.show();
          finish();
          return;
        }
      } catch (e) {}
    }

    if (typeof jQuery !== "undefined" && typeof jQuery.fn.modal === "function") {
      try {
        jQuery(modalEl).modal("show");
        finish();
        return;
      } catch (e) {}
    }

    try {
      modalEl.classList.add("show");
      modalEl.style.display = "block";
      document.body.classList.add("modal-open");
    } catch (e) {}

    finish();
  });
}
window.erpShowModal = erpShowModal;

function erpReloadPjax(container, options) {
  var pjaxApi = null;
  if (typeof window.jQuery !== "undefined" && jQuery.pjax && typeof jQuery.pjax.reload === "function") {
    pjaxApi = jQuery.pjax;
  } else if (typeof $.pjax !== "undefined" && $.pjax && typeof $.pjax.reload === "function") {
    pjaxApi = $.pjax;
  }

  if (!pjaxApi || !container) {
    return false;
  }

  var reloadOptions = $.extend(
    {
      container: container,
      history: false,
      replace: false,
      timeout: false,
    },
    options || {}
  );

  pjaxApi.reload(reloadOptions);
  return true;
}
window.erpReloadPjax = erpReloadPjax;

jQuery(document).on("pjax:send", function () {
  erpShowPageLoading();
});
jQuery(document).on("pjax:start", function () {
  erpShowPageLoading();
  const el = document.getElementById("offcanvasRight");
  const Offcanvas = erpGetBootstrapClass("Offcanvas");
  if (el && Offcanvas) Offcanvas.getOrCreateInstance(el).hide();
});
jQuery(document).on("pjax:end", function () {
  erpHidePageLoading();

  if (typeof lucide !== "undefined" && lucide.createIcons) lucide.createIcons();
  erpInitBootstrapWidgets(document);
  var offcanvasElList = [].slice.call(document.querySelectorAll(".offcanvas"));
  const Offcanvas = erpGetBootstrapClass("Offcanvas");
  if (offcanvasElList.length > 0) {
    offcanvasElList.map(function (offcanvasEl) {
      if (!Offcanvas) {
        return null;
      }
      return new Offcanvas(offcanvasEl);
    });
  }
});
jQuery(document).on("pjax:complete", function () {
  erpHidePageLoading();
});
// กันค้าง: pjax ล้มเหลว/timeout ต้องซ่อน loading ด้วย
jQuery(document).on("pjax:error pjax:timeout", function () {
  erpHidePageLoading();
});

// แสดง loading เมื่อคลิกลิงก์ที่นำไปหน้าใหม่ (full page)
$(document).on("click", "a[href]", function (e) {
  var $a = $(this);
  if ($a.attr("target") === "_blank") return;
  if ($a.hasClass("open-modal") || $a.hasClass("open-modal-fullscreen")) return;
  if ($a.data("pjax") === false || $a.data("pjax") === 0) return;
  // ลิงก์ดาวน์โหลด (download attr) ไม่ได้เปลี่ยนหน้า → อย่าโชว์ loading
  if ($a.is("[download]")) return;
  var href = ($a.attr("href") || "").trim();
  if (!href || href === "#" || href.indexOf("javascript:") === 0) return;
  var sameOrigin;
  try {
    sameOrigin = new URL(href, location.origin).origin === location.origin;
  } catch (err) { return; }
  if (!sameOrigin) return;
  // เลื่อนไปเช็คใน tick ถัดไป — ถ้ามี handler อื่น preventDefault (เช่นปุ่มดาวน์โหลด/JS ดัก)
  // แปลว่าไม่ได้เปลี่ยนหน้าจริง จึงไม่ต้องโชว์ loading (กันค้าง)
  setTimeout(function () {
    if (e.isDefaultPrevented()) return;
    erpShowPageLoading();
  }, 0);
});

$(function () {
  erpInitBootstrapWidgets(document);
});

// ซ่อน loading เมื่อโหลดหน้าเสร็จ (ทั้งเปิดหน้าแรกและหลัง full page)
$(window).on("load", function () {
  erpHidePageLoading();
});



// // แสดงปุ่มเมื่อ scroll ลงมา
window.addEventListener("scroll", function () {
  const buttons = document.getElementById("scroll-buttons");
  if (buttons && window.scrollY > 100) {
    buttons.style.display = "block";
  } else if (buttons) {
    buttons.style.display = "none";
  }
});

// //แก้ treeview ไม่ปิดเวลาเลือก
$("#treeID").on("treeview:change", function (event, key, name) {
  $("body").find(".kv-tree-input").removeClass("show");
  $("body").find(".kv-tree-dropdown").removeClass("show");
});

$("body").on("click", ".form-submit", async function (e) {
  e.preventDefault();
  var formId = $(this).data("id");
  $("#" + formId).submit();
});

function erpInjectModalContent($target, html) {
  return new Promise(async function (resolve, reject) {
    try {
      var $wrapper = $("<div>").html(html || "");
      var externalScripts = [];
      var inlineScripts = [];

      // Collect ALL <script> tags (including nested ones) in document order,
      // then remove them from the wrapper so jQuery's .append() does not
      // auto-evaluate them out-of-order via domManip/DOMEval. We run them
      // ourselves below: externals first, then inline scripts in order.
      var scriptEls = $wrapper[0].querySelectorAll("script");
      Array.prototype.forEach.call(scriptEls, function (node) {
        var src = node.src || node.getAttribute("src");
        if (src) {
          externalScripts.push(src);
        } else {
          inlineScripts.push(node);
        }
        if (node.parentNode) {
          node.parentNode.removeChild(node);
        }
      });

      var fragment = document.createDocumentFragment();
      $wrapper.contents().toArray().forEach(function (node) {
        fragment.appendChild(node);
      });

      $target.empty().append(fragment);

      // Load dependency scripts first so inline modal init code runs after them.
      for (var i = 0; i < externalScripts.length; i++) {
        await erpLoadScript(externalScripts[i]);
      }

      for (var j = 0; j < inlineScripts.length; j++) {
        var script = inlineScripts[j];
        var scriptText = script.text || script.textContent || script.innerHTML || "";
        if (scriptText.trim()) {
          $.globalEval(scriptText);
        }
      }

      erpInitBootstrapWidgets($target);

      resolve();
    } catch (error) {
      reject(error);
    }
  });
}

function erpLoadScript(src) {
  window.__erpScriptLoadCache = window.__erpScriptLoadCache || {};

  if (window.__erpScriptLoadCache[src]) {
    return window.__erpScriptLoadCache[src];
  }

  window.__erpScriptLoadCache[src] = new Promise(function (resolve, reject) {
    if (document.querySelector('script[src="' + src.replace(/"/g, '\\"') + '"]')) {
      resolve();
      return;
    }

    var scriptEl = document.createElement("script");
    scriptEl.src = src;
    scriptEl.async = false;
    scriptEl.onload = function () {
      resolve();
    };
    scriptEl.onerror = function () {
      reject(new Error("Failed to load script: " + src));
    };
    document.head.appendChild(scriptEl);
  });

  return window.__erpScriptLoadCache[src];
}
/**
 * Handle AJAX form submission with confirmation and success feedback.
 * @param {string} formSelector - jQuery selector for the form.
 * @param {string} [actionUrl] - Optional URL to submit the form to.
 * @param {function} [successCallback] - Optional callback on success.
 */
function handleFormSubmit(formSelector, actionUrl, successCallback) {
  $(document)
    .off("beforeSubmit.handleFormSubmit", formSelector)
    .on("beforeSubmit.handleFormSubmit", formSelector, function (e) {
    e.preventDefault();
    const form = $(this);
    const formEl = form.get(0);
    const isMultipart =
      formEl &&
      String(formEl.enctype || "").toLowerCase().indexOf("multipart/form-data") !== -1 &&
      typeof FormData !== "undefined";
    const confirmTitle = form.data("confirmTitle") || "ยืนยันการบันทึกข้อมูล?";
    const confirmText = form.data("confirmText") || "โปรดตรวจสอบความถูกต้องของข้อมูลก่อนกดยืนยัน";
    const confirmButtonText = form.data("confirmButton") || '<i class="fa fa-save"></i> ยืนยันบันทึก';
    const loadingTitle = form.data("loadingTitle") || "กำลังดำเนินการ";
    const loadingText = form.data("loadingText") || "ระบบกำลังบันทึกข้อมูลของคุณลงฐานข้อมูล...";

    function submitAjax() {
      var ajaxOptions = {
        url: actionUrl || form.attr("action"),
        type: "POST",
        dataType: "json",
        success: async function (response) {
          if (response && typeof response === "object") {
            var isValidationResponse =
              response.status === undefined &&
              response.message === undefined &&
              response.redirect_url === undefined;

            if (response.errors && typeof response.errors === "object") {
              Swal.close();
              if (typeof form.yiiActiveForm === "function") {
                form.yiiActiveForm("updateMessages", response.errors, true);
              }
              if (isValidationResponse) {
                return;
              }
            } else if (isValidationResponse) {
              Swal.close();
              if (typeof form.yiiActiveForm === "function") {
                form.yiiActiveForm("updateMessages", response, true);
              }
              return;
            }
          }

          if (response.status === "success") {
            var isWarning = response.level === "warning";
            await erpHideModal("#main-modal");

            Swal.fire({
              icon: isWarning ? "warning" : "success",
              title: isWarning ? "บันทึกข้อมูลแล้ว แต่ต้องตรวจสอบ" : "ดำเนินการสำเร็จ",
              text: response.message || "บันทึกข้อมูลเรียบร้อยแล้ว",
              timer: isWarning ? 3000 : 1500,
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
            Swal.close();
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
          Swal.close();
          Swal.fire({
            icon: "error",
            title: "การเชื่อมต่อขัดข้อง",
            text: "ไม่สามารถติดต่อ Server ได้ (Error " + xhr.status + ")",
            confirmButtonText: "รับทราบ",
          });
        },
      };

      if (isMultipart) {
        ajaxOptions.data = new FormData(formEl);
        ajaxOptions.processData = false;
        ajaxOptions.contentType = false;
      } else {
        ajaxOptions.data = form.serialize();
      }

      $.ajax(ajaxOptions);
    }

    function showLoadingThenSubmit() {
      Swal.fire({
        title: loadingTitle,
        text: loadingText,
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });
      submitAjax();
    }

    if (typeof Swal === "undefined") {
      if (window.confirm(confirmTitle)) {
        if (formEl && typeof formEl.submit === "function") {
          formEl.submit();
        }
      }
      return false;
    }

    Swal.fire({
      title: confirmTitle,
      text: confirmText,
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#198754",
      cancelButtonColor: "#6c757d",
      confirmButtonText: confirmButtonText,
      cancelButtonText: "ยกเลิก",
      reverseButtons: false,
    }).then((result) => {
      if (result.isConfirmed) {
        showLoadingThenSubmit();
      }
    });
    return false;
  });
}

handleFormSubmit("#form-emp-detail", null, async function (response) {
  if (response && response.container) {
    if (!erpReloadPjax(response.container)) {
      location.reload();
    }
  }
});


// // #### การอัพโหลดรูปภาพ ####

function isFile() {
  var isFile = $("#editImagePreview").data("isfile");
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
          .html('<i class="fa-solid fa-bookmark text-warning"></i>');
        success("ติดดาว");
      } else if (res.data.bookmark == "N") {
        $("body")
          .find(".bookmark-star-" + id)
          .html('<i class="fa-regular fa-bookmark"></i>');
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

function beforLoadModal(size) {
  console.log("beforLoadModal");
  erpShowModal("#main-modal");
  // $('#modal-dialog').modal('show');
  $("#main-modal-label").html("กำลังโหลด");
  // scope เฉพาะ #main-modal — เดิมใช้ $(".modal-dialog")/$(".modal-body") แบบ global
  // ทำให้ไปเขียนทับ body ของ modal อื่น (เช่น #itemHistoryModal) พังตอนเปิดทีหลัง
  $("#main-modal .modal-dialog").removeClass(
    "modal-sm modal-md modal-lg modal-xl modal-xxl"
  );
  $("#main-modal .modal-dialog").addClass(size || "modal-sm");
  $("#modal-dialog").removeClass("fade");
  $("#main-modal .modal-body").html(
    '<div class="placeholder-glow" role="status" aria-live="polite" aria-busy="true">' +
      '<span class="visually-hidden">กำลังโหลดแบบฟอร์ม</span>' +
      '<div class="placeholder col-5 rounded mb-4" style="height: 1.5rem;" aria-hidden="true"></div>' +
      '<div class="row g-3" aria-hidden="true">' +
        '<div class="col-md-4"><span class="placeholder col-5 mb-2"></span><span class="placeholder col-12 rounded" style="height: 2.75rem;"></span></div>' +
        '<div class="col-md-8"><span class="placeholder col-4 mb-2"></span><span class="placeholder col-12 rounded" style="height: 2.75rem;"></span></div>' +
        '<div class="col-md-6"><span class="placeholder col-5 mb-2"></span><span class="placeholder col-12 rounded" style="height: 2.75rem;"></span></div>' +
        '<div class="col-md-6"><span class="placeholder col-5 mb-2"></span><span class="placeholder col-12 rounded" style="height: 2.75rem;"></span></div>' +
      '</div>' +
    '</div>'
  );
}

function closeModal() {
  erpHideModal("#main-modal").then(function () {
    Swal.fire({
      icon: "success",
      title: "บันทึกสำเร็จ",
      showConfirmButton: false,
      timer: 1500,
    });
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
  var trigger = this;
  var url = $(this).attr("href");
  var size = $(this).data("size");
  var modalElement = document.querySelector("#main-modal");

  if (modalElement) {
    modalElement.erpReturnFocus = trigger;
    var restoreModalTriggerFocus = function () {
      erpRestoreModalFocus(modalElement);
    };
    if (typeof jQuery !== "undefined") {
      jQuery(modalElement)
        .off("hidden.bs.modal.erpReturnFocus")
        .one("hidden.bs.modal.erpReturnFocus", restoreModalTriggerFocus);
      jQuery(modalElement)
        .off("click.erpReturnFocus", '[data-bs-dismiss="modal"]')
        .one("click.erpReturnFocus", '[data-bs-dismiss="modal"]', function () {
          window.setTimeout(restoreModalTriggerFocus, 400);
        });
    } else {
      modalElement.addEventListener("hidden.bs.modal", restoreModalTriggerFocus, { once: true });
    }
  }

  // แสดง loading หรืออื่นๆ
  if (typeof beforLoadModal === "function") beforLoadModal(size);

  $.ajax({
    type: "get",
    url: url,
    dataType: "json",
    success: async function (response) {
      if (response.status === "error") {
        if (typeof warning === "function") warning(response.message);
        return;
      }
      var modal = $("#main-modal");
      modal.find("#main-modal-label").html(response.title);
      await erpInjectModalContent(modal.find(".modal-body"), response.content);
      modal.find(".modal-footer").html(response.footer);

      modal.find(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl modal-xxl")
        .addClass(size);

      await erpShowModal(modal);
      if (response.initCallback && typeof window[response.initCallback] === "function") {
        try { window[response.initCallback](); } catch (err) { console.warn("initCallback error", err); }
      }
    },
    error: function (xhr) {
      // จัดการ error เหมือนเดิม
    }
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
    success: async function (response) {
      await erpShowModal("#fullscreen-modal");
      $("#fullscreen-modal-label").html(response.title);
      await erpInjectModalContent($(".modal-body"), response.content);
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


          if (response.status == "error") {
            Swal.fire(
              'ผิดพลาด!',
              response.msg,
              'error'
            );
          }
          if (response.status == "success") {
            location.reload();
            success(text + "สำเร็จ!.");
          }
        },
      });
    }
  });
});

// ใช้ .off("click") เพื่อเคลียร์ Event เก่าทิ้งก่อน
$("body").off("click", ".delete-item").on("click", ".delete-item", async function (e) {
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
            if (!erpReloadPjax(response.container, { url: response.url })) {
              location.reload();
            }
          } else if (response.status == "success" && response.close) {
            success("ดำเนินการลบสำเร็จ!.");
            erpHideModal("#main-modal");
          } else if (response.status == "success" && response.url) {
            window.location.href = response.url;
          } else if (response.status == "error" && response.message) {
            Swal.fire({ icon: "error", title: "ไม่สำเร็จ", text: response.message }).then(() => {
              location.reload();
            });
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
