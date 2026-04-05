/**
 * ทะเบียนหนังสือ /me/documents — อัปเดต KPI + รายการแบบ Ajax หลังเปิดอ่าน (view) / ปักดาว (bookmark)
 * ไม่พึ่งการแก้ erp.js — ใช้ ajaxComplete ดักคำขอที่สำเร็จ
 */
(function ($) {
  "use strict";

  var refreshTimer = null;
  var cfg = window.meDocumentsIndexConfig || {};

  function isDocumentsIndexPath() {
    return (
      typeof window.location.pathname === "string" &&
      window.location.pathname.indexOf("/me/documents") !== -1
    );
  }

  function scheduleRefresh() {
    if (!cfg.refreshUrl) {
      return;
    }
    clearTimeout(refreshTimer);
    refreshTimer = setTimeout(function () {
      $.ajax({
        url: cfg.refreshUrl,
        type: "GET",
        dataType: "json",
        data: cfg.queryParams || {},
        headers: { "X-Requested-With": "XMLHttpRequest" },
      })
        .done(function (res) {
          if (!res || !res.success) {
            return;
          }
          var $kpi = $("#me-documents-kpi-wrap");
          var $list = $("#me-documents-list-wrap");
          if (res.kpiHtml && $kpi.length) {
            $kpi.html(res.kpiHtml);
          }
          if (res.listHtml && $list.length) {
            $list.html(res.listHtml);
          }
          if (res.totalCount !== undefined && $("#totalCount").length) {
            $("#totalCount").text(String(res.totalCount));
          }
        })
        .fail(function () {
          /* เงียบ — ผู้ใช้ยังใช้หน้าเดิมได้ */
        });
    }, 150);
  }

  function shouldHandleAjaxUrl(url) {
    if (!url || typeof url !== "string") {
      return false;
    }
    return (
      url.indexOf("/me/documents/view") !== -1 ||
      url.indexOf("/me/documents/bookmark") !== -1
    );
  }

  $(document).ajaxComplete(function (event, xhr, settings) {
    if (!isDocumentsIndexPath() || !cfg.refreshUrl) {
      return;
    }
    if (!settings || xhr.status !== 200) {
      return;
    }
    var url = settings.url || "";
    if (!shouldHandleAjaxUrl(url)) {
      return;
    }
    var ct = xhr.getResponseHeader("Content-Type") || "";
    if (url.indexOf("/me/documents/view") !== -1 && ct.indexOf("json") === -1) {
      return;
    }
    scheduleRefresh();
  });
})(jQuery);
