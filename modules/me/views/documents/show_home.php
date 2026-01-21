<?php

use yii\web\View;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\db\Expression;
use yii\bootstrap5\Html;
use app\modules\dms\models;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\dms\models\Documents;

$me = UserHelper::GetEmployee();


?>

<?php Pjax::begin(['id' => 'document-container', 'enablePushState' => false, 'timeout' => 5000]); ?>

<div class="card">
    <div class="card-body p-2 p-md-3">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3">
            <h6 class="mb-2 mb-sm-0">
                <i class="bi bi-ui-checks"></i> ทะเบียนหนังสือ
                <span class="badge rounded-pill text-bg-primary"><?php echo number_format(($dataProvider->getTotalCount()), 0) ?></span>
                รายการ
            </h6>
            <?= Html::a('แสดงทั้งหมด', ['/me/documents'], ['class' => 'btn btn-sm btn-light rounded-pill w-auto', 'data' => ['pjax' => 0]]) ?>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr class="text-nowrap">
                        <th class="text-center" style="width:50px;">ลำดับ</th>
                        <th class="text-center" style="width:100px;">เลขที่รับ</th>
                        <th style="min-width:300px;">เรื่อง</th>
                        <th style="min-width:180px;">ผู้บันทึก</th>
                        <th style="width:100px;">สถานะ</th>
                        <th class="text-center" style="width:70px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                        <tr id="<?= $item->id ?>">
                            <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                            <td class="text-center fw-bold">
                                <?= isset($item->document) ? $item->document->doc_regis_number : '' ?>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1 mb-1"> <?php if (isset($item->document) &&  $item->document->doc_speed == 'ด่วนที่สุด'): ?>
                                        <span class="badge text-bg-danger fs-12"><i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด</span>
                                    <?php endif; ?>

                                    <?php if (isset($item->document) && $item->document->secret == 'ลับที่สุด'): ?>
                                        <span class="badge text-bg-danger fs-12"><i class="fa-solid fa-lock"></i> ลับที่สุด</span>
                                    <?php endif; ?>
                                </div>

                                <a href="<?php echo Url::to(['/me/documents/view', 'id' => $item->id, 'callback' => '/me']) ?>"
                                    class="open-modal view-document fw-bold text-decoration-none"
                                    data-size="modal-xxl"
                                    data-tr-id="<?= $item->id ?>"
                                    style="display: block; max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: normal;">
                                    เรื่อง : <?php echo $item->document ? $item->document->topic : '' ?>
                                    <?php echo  $item->document ? ($item->document->isFile() ? ' <i class="fas fa-paperclip text-muted"></i>' : '') : '' ?>
                                </a>

                                <p class="text-muted small mb-1">
                                    <?= $item->document ? $item->data_json['des'] ?? '' : '' ?>
                                </p>

                                <div class="fs-12">
                                    <span class="text-danger border-end pe-2 mr-2">
                                        <?= $item->document ? $item->document->doc_number : '' ?>
                                    </span>
                                    <span class="text-primary ps-1">
                                        <i class="fa-solid fa-inbox"></i>
                                        <?php echo $item->document ? $item->documentOrg->title ?? '-' : ''; ?>
                                        <span class="badge rounded-pill bg-light text-dark ms-1">
                                            <i class="fa-regular fa-eye"></i>
                                            <?php echo $item->document ? $item->document->viewCount() : '' ?>
                                        </span>
                                    </span>
                                </div>
                                <?php echo $item->document ? $item->document->StackDocumentTags('comment') : '' ?>
                            </td>
                            <td>
                                <div class="small text-nowrap">
                                    <?= $item->document ? $item->document->viewCreate()['avatar'] : ''; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info">
                                    <?= $item->document->documentStatus->title ?? '-' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php echo $item->document ? Html::a('<i class="fa-regular fa-pen-to-square fa-lg"></i>', ['view', 'id' => $item->id, 'callback' => '/me'], ['class' => 'btn btn-outline-primary btn-sm open-modal view-document', 'data' => ['size' => 'modal-xxl']]) : '' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
    </div>
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