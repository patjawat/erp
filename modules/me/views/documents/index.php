<?php

use yii\helpers\Url;
use yii\helpers\Json;
use yii\bootstrap5\Html;
use yii\web\View;
use yii\web\JqueryAsset;

$this->title = 'ทะเบียนหนังสือ';
$this->params['breadcrumbs'][] = ['label' => 'บริการ', 'url' => ['/me']];
$this->params['breadcrumbs'][] = ['label' => 'หนังสือ', 'url' => ['/me']];

$viewQuery = array_merge(Yii::$app->request->queryParams, []);
$viewListUrl = Url::to(array_merge(['/me/documents/index'], $viewQuery, ['view' => 'list']));
$viewGridUrl = Url::to(array_merge(['/me/documents/index'], $viewQuery, ['view' => 'grid']));
$isTableView = Yii::$app->request->get('view', 'list') !== 'grid';

/** @var array{total:int,unread:int,bookmarked:int,urgent:int} $documentStats */
/** @var string|null $activeKpi */
$documentStats = $documentStats ?? ['total' => 0, 'unread' => 0, 'bookmarked' => 0, 'urgent' => 0];
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
        <?= $this->render('kpi_summary', [
            'documentStats' => $documentStats,
            'activeKpi' => $activeKpi ?? null,
        ]) ?>
    </div>
    <?php
    $this->registerJs(
        'window.meDocumentsIndexConfig = ' . Json::encode([
            'refreshUrl' => Url::to(['/me/documents/ajax-refresh']),
            'queryParams' => Yii::$app->request->getQueryParams(),
        ]) . ';',
        View::POS_HEAD
    );
    $this->registerJsFile('@web/js/me-documents-index.js', [
        'depends' => [JqueryAsset::class],
        'position' => View::POS_END,
    ]);
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

                <?php if (!isset($list)): ?>
                <div id="me-documents-list-wrap">
                <?php endif; ?>
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
                <?php if (!isset($list)): ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<span id="totalCount" class="d-none"><?= (int) $dataProvider->getTotalCount() ?></span>

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
