<?php

use yii\helpers\Url;
use yii\helpers\Json;
use yii\bootstrap5\Html;
use yii\web\View;

$this->title = 'ทะเบียนหนังสือ';
$this->params['breadcrumbs'][] = ['label' => 'บริการ', 'url' => ['/me']];
$this->params['breadcrumbs'][] = ['label' => 'หนังสือ', 'url' => ['/me']];

$viewQuery = array_merge(Yii::$app->request->queryParams, []);
$viewListUrl = Url::to(array_merge(['/me/documents/index'], $viewQuery, ['view' => 'list']));
$viewGridUrl = Url::to(array_merge(['/me/documents/index'], $viewQuery, ['view' => 'grid']));
$isTableView = Yii::$app->request->get('view', 'list') !== 'grid';

/** @var app\modules\dms\models\DocumentSearch $searchModel */
/** @var string|null $activeKpi */
/** @var yii\data\ActiveDataProvider|null $dataProvider เฉพาะโหมดฝังรายการ (isset($list)) */
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 w-100">
    <h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
        <span class="text-primary"><i class="bi bi-journal-text" aria-hidden="true"></i></span>
        <?= Html::encode($to) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex flex-wrap gap-2 align-items-center justify-content-center justify-content-lg-end">
    <?= $this->render('@app/components/ui/btnReturn') ?>
</div>
<?php $this->endBlock(); ?>

<?php if (!isset($list)): ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <?= $this->render('_search', ['model' => $searchModel, 'action' => $action]) ?>
        </div>
    </div>

    <div id="me-documents-kpi-wrap">
        <?= $this->render('_register_ajax_placeholder_kpi') ?>
    </div>
    <?php
    $meDocumentsIndexJs = 'window.meDocumentsIndexConfig = ' . Json::encode([
        'refreshUrl' => Url::to(['/me/documents/ajax-refresh']),
        'queryParams' => Yii::$app->request->getQueryParams(),
    ]) . ";\n" . <<<'ME_DOCS_JS'
(function ($) {
  "use strict";
  var refreshTimer = null;
  function getCfg() {
    return window.meDocumentsIndexConfig || {};
  }
  function isMeDocumentsRegisterShell() {
    return document.getElementById("me-documents-list-wrap") !== null;
  }
  function applyRegisterPayload(res) {
    if (!res || !res.success) {
      return;
    }
    var $kpi = $("#me-documents-kpi-wrap");
    var $list = $("#me-documents-list-wrap");
    if (typeof res.kpiHtml === "string" && $kpi.length) {
      $kpi.html(res.kpiHtml);
    }
    if (typeof res.listHtml === "string" && $list.length) {
      $list.html(res.listHtml);
    }
    if (res.totalCount !== undefined && $("#totalCount").length) {
      $("#totalCount").text(String(res.totalCount));
    }
  }
  function showRegisterError(message) {
    var $list = $("#me-documents-list-wrap");
    var $kpi = $("#me-documents-kpi-wrap");
    var msg = message || "โหลดทะเบียนหนังสือไม่สำเร็จ กรุณารีเฟรชหน้า";
    if ($list.length) {
      $list.html(
        '<div class="p-4 text-center text-danger small">' +
          $("<div>").text(msg).html() +
          "</div>"
      );
    }
    if ($kpi.length) {
      $kpi.html(
        '<div class="p-3 text-center text-danger small">' +
          $("<div>").text(msg).html() +
          "</div>"
      );
    }
  }
  function fetchRegister() {
    var cfg = getCfg();
    if (!cfg.refreshUrl) {
      return;
    }
    $.ajax({
      url: cfg.refreshUrl,
      type: "GET",
      dataType: "json",
      data: cfg.queryParams || {},
      cache: false,
      headers: { "X-Requested-With": "XMLHttpRequest" },
    })
      .done(function (res) {
        if (!res || res.success !== true) {
          showRegisterError(
            (res && res.message) || "เซิร์ฟเวอร์ตอบกลับไม่ถูกต้อง"
          );
          return;
        }
        applyRegisterPayload(res);
      })
      .fail(function (jqXHR, textStatus, errorThrown) {
        var hint =
          textStatus === "parsererror"
            ? "รูปแบบข้อมูลไม่ใช่ JSON (ตรวจสอบ URL / actionAjaxRefresh)"
            : errorThrown || textStatus || "เครือข่ายผิดพลาด";
        showRegisterError(hint);
      });
  }
  function scheduleFetchRegister() {
    var cfg = getCfg();
    if (!cfg.refreshUrl) {
      return;
    }
    clearTimeout(refreshTimer);
    refreshTimer = setTimeout(fetchRegister, 150);
  }
  function urlSignalsMeDocumentsViewOrBookmark(url) {
    if (!url || typeof url !== "string") {
      return false;
    }
    return /me(\/|%2F)documents(\/|%2F)(view|bookmark)/i.test(url);
  }
  $(function () {
    if (isMeDocumentsRegisterShell() && getCfg().refreshUrl) {
      fetchRegister();
    }
  });
  $(document).ajaxComplete(function (event, xhr, settings) {
    if (!isMeDocumentsRegisterShell() || !getCfg().refreshUrl) {
      return;
    }
    if (!settings || xhr.status !== 200) {
      return;
    }
    var url = settings.url || "";
    if (!urlSignalsMeDocumentsViewOrBookmark(url)) {
      return;
    }
    var ct = xhr.getResponseHeader("Content-Type") || "";
    if (/documents(\/|%2F)view/i.test(url) && ct.indexOf("json") === -1) {
      return;
    }
    scheduleFetchRegister();
  });
})(jQuery);
ME_DOCS_JS;
    $this->registerJs($meDocumentsIndexJs, View::POS_READY);
    ?>
<?php endif; ?>

<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-body border-bottom py-3 px-3 px-md-4">
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-lg-between gap-3">
                    <h6 class="mb-0 d-flex align-items-center gap-2 text-body">
                        <span class="bg-primary bg-opacity-10 text-primary rounded-pill p-2 d-inline-flex align-items-center justify-content-center">
                            <i class="bi bi-ui-checks" aria-hidden="true"></i>
                        </span>
                        ทะเบียนหนังสือ
                    </h6>
                    <div class="d-flex flex-wrap align-items-center gap-2 w-lg-auto justify-content-start justify-content-lg-end ms-lg-auto">
                        <?= Html::a('<i class="fa-solid fa-circle-plus me-1" aria-hidden="true"></i> ลงทะเบียน', ['/dms/documents/create'], [
                            'class' => 'btn btn-sm btn-primary text-white shadow-sm open-modal',
                            'data' => ['size' => 'modal-fullscreen'],
                            'data-pjax' => 0,
                        ]) ?>
                        <div class="btn-group btn-group-sm" role="group" aria-label="มุมมอง">
                            <?= Html::a('<i class="fa-solid fa-table me-1" aria-hidden="true"></i> ตาราง', $viewListUrl, [
                                'class' => 'btn ' . ($isTableView ? 'btn-primary' : 'btn-outline-primary'),
                                'data-pjax' => 0,
                            ]) ?>
                            <?= Html::a('<i class="fa-solid fa-grip me-1" aria-hidden="true"></i> การ์ด', $viewGridUrl, [
                                'class' => 'btn ' . (!$isTableView ? 'btn-primary' : 'btn-outline-primary'),
                                'data-pjax' => 0,
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <?php if (isset($list)): ?>
                    <div class="p-3 border-bottom">
                        <?= Html::a('แสดงทั้งหมด', ['/me/documents'], ['class' => 'btn btn-light rounded-pill', 'data' => ['pjax' => 0]]) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($list) && isset($dataProvider)): ?>
                    <?php if ($isTableView): ?>
                        <?= $this->render('_list', [
                            'dataProvider' => $dataProvider,
                            'unreadOpenDetailIdByDocument' => $unreadOpenDetailIdByDocument ?? [],
                            'unreadOpenDocumentsDetailById' => $unreadOpenDocumentsDetailById ?? [],
                            'readAtByRoutingId' => $readAtByRoutingId ?? [],
                        ]) ?>
                    <?php else: ?>
                        <?= $this->render('_grid', [
                            'dataProvider' => $dataProvider,
                            'unreadOpenDetailIdByDocument' => $unreadOpenDetailIdByDocument ?? [],
                            'unreadOpenDocumentsDetailById' => $unreadOpenDocumentsDetailById ?? [],
                            'readAtByRoutingId' => $readAtByRoutingId ?? [],
                        ]) ?>
                    <?php endif; ?>
                <?php elseif (!isset($list)): ?>
                    <div id="me-documents-list-wrap">
                        <?= $this->render('_register_ajax_placeholder_list') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<span id="totalCount" class="d-none"><?= (isset($list) && isset($dataProvider)) ? (int) $dataProvider->getTotalCount() : 0 ?></span>

<?php
$js = <<< JS
$(document).ready(function() {
    $('.view-btn').on('click', function() {
        const docId = $(this).data('id');
        const docTitle = $(this).data('title');
        $('#doc-content').text(docTitle + " (ID: " + docId + ")");
        var myModal = new bootstrap.Modal(document.getElementById('view-fullscreen'));
        myModal.show();
    });
});
JS;
$this->registerJS($js);
?>
