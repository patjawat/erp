<?php

use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\components\UserHelper;
use app\modules\dms\models;
use app\modules\dms\models\Documents;
use yii\bootstrap5\Html;
use yii\db\Expression;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

$me = UserHelper::GetEmployee();


?>

<?php Pjax::begin(['id' => 'document-container', 'enablePushState' => false, 'timeout' => 5000]); ?>

<div class="d-flex flex-column gap-2">
    <?php foreach ($dataProvider->getModels() as $key => $item): ?>
        <?php $detailId = $item->detail_id ?? $item->id; ?>
        <div id="<?= $detailId ?>" class="card border border-light shadow-sm hover-shadow transition-all overflow-hidden p-0 mb-2"
            style="border-radius: 16px;">
            <div class="row g-0 align-items-center">
                <div class="position-absolute start-0 top-0 bottom-0 bg-primary" style="width: 4px;"></div>
                <div class="col-auto py-3 ps-4 pe-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3"
                        style="width: 40px; height: 40px; background-color: #eff6ff; color: #2563eb;"><svg
                            xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            data-lucide="mail" class="lucide lucide-mail">
                            <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"></path>
                            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        </svg></div>
                </div>
                <div class="col py-3 px-2">
                    
                    <span class="mb-1 text-truncate fw-medium fs-14"><?= $item->topic ?></span>
                    <div>
                        <small>
                            จาก: <?= $item->documentOrg->title ?? '-' ?></span><span class="d-flex align-items-center gap-1">
                                </small>
                            </div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1"><span
                            class="badge rounded-2 fw-bold px-2 py-1"
                            style="background-color: #eff6ff; color: #1d4ed8; font-size: 0.65rem;">
                            <?= $item->documentType?->title ?? 'ไมระบุ' ?>
                        </span>
                        <div class="d-flex align-items-center gap-1 text-muted fw-bold"><svg
                                xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" data-lucide="hash" class="lucide lucide-hash">
                                <line x1="4" x2="20" y1="9" y2="9"></line>
                                <line x1="4" x2="20" y1="15" y2="15"></line>
                                <line x1="10" x2="8" y1="3" y2="21"></line>
                                <line x1="16" x2="14" y1="3" y2="21"></line>
                            </svg><?= $item->doc_number ?></div>
                    </div>
                        <small class="d-flex align-items-center gap-1">
                            <span class="text-muted">
                                เลขรับ:
                            </span>
                            <?= $item->doc_regis_number ?>
                        </small>
                        <small class="d-flex align-items-center gap-1">
                            <span class="text-muted">
                                ลงรับวันที่:
                            </span>
                            <?= ThaiDateHelper::formatThaiDate($item->doc_transactions_date, 'short') ?>
                        </small>
                    </div>
                </div>
                <div class="col-auto py-3 pe-4 ps-2 d-flex align-items-center gap-3">
                    <div class="text-end d-none d-md-block">
                        <?php if ($item->doc_speed == 'ด่วนที่สุด'): ?>
                            <span class="badge text-bg-danger fs-12 mb-1">ด่วนที่สุด</span>
                        <?php endif; ?>

                        <?php if ($item->secret == 'ลับที่สุด'): ?>
                            <span class="badge text-bg-dark fs-12 mb-1">ลับที่สุด</span>
                        <?php endif; ?>

                    </div>
                    <button class="btn btn-light rounded-circle p-2 border-0 text-muted hover-text-primary"
                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><svg
                            xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            data-lucide="check-circle" class="lucide lucide-check-circle">
                            <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                            <path d="m9 11 3 3L22 4"></path>
                        </svg></button>
                    <?= Html::a('เปิดอ่าน >>', ['view', 'id' => $detailId, 'callback' => '/me'], ['class' => 'btn btn-sm btn-primary rounded-3 d-flex align-items-center gap-1 px-3 py-1 shadow-sm open-modal view-document', 'data' => ['size' => 'modal-fullscreen', 'tr-id' => $detailId]]) ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>



<div class="mt-4 d-flex justify-content-center">
    <nav aria-label="Page navigation">
        <?= yii\bootstrap5\LinkPager::widget([
            'pagination' => $dataProvider->pagination,
            'firstPageLabel' => '«',
            'lastPageLabel' => '»',
            'options' => ['class' => 'pagination pagination-sm flex-wrap justify-content-center'],
        ]); ?>
    </nav>
</div>


<?php Pjax::end() ?>
<?php
$js = <<< JS

$('body').on('click', '.view-document', function (e) {
    let trId = $(this).data('tr-id');
     $('#'+trId).remove();

})
JS;
$this->registerJS($js, View::POS_END);
?>