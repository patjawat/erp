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

<section class="mt-5">
    <div class="d-flex align-items-center gap-3 mb-3">
        <div class="bg-primary-subtle text-primary rounded-4 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="inbox" class="lucide lucide-inbox">
                <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline>
                <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path>
            </svg></div>
        <div>
            <h3 class="fw-black text-dark mb-0" style="font-size: 1rem;">หนังสือราชการที่รอการจัดการ</h3>
            <p class="text-muted mb-0" style="font-size: 0.75rem;">รายการหนังสือรับเข้าจากระบบสารบรรณที่ส่งถึงคุณ</p>
        </div>
    </div>
    <div class="d-flex gap-2 overflow-auto hide-scrollbar pb-2 mb-2">
        <button class="btn btn-primary rounded-pill fw-bold text-nowrap px-3 py-1 shadow-sm border-0" style="font-size: 0.75rem; padding-left: 20px; padding-right: 20px;">ทั้งหมด</button>
        <button class="btn btn-white text-muted fw-bold text-nowrap rounded-pill px-3 py-1 border hover-bg-light" style="font-size: 0.75rem;">ด่วนที่สุด</button>
        <button class="btn btn-white text-muted fw-bold text-nowrap rounded-pill px-3 py-1 border hover-bg-light" style="font-size: 0.75rem;">บันทึกข้อความ</button>
        <button class="btn btn-white text-muted fw-bold text-nowrap rounded-pill px-3 py-1 border hover-bg-light" style="font-size: 0.75rem;">หนังสือภายนอก</button>
        <button class="btn btn-white text-muted fw-bold text-nowrap rounded-pill px-3 py-1 border hover-bg-light" style="font-size: 0.75rem;">คำสั่ง</button>
    </div>
    <div class="d-flex flex-column gap-2">
        <?php foreach ($dataProvider->getModels() as $key => $item): ?>
            <div class="card border border-light shadow-sm hover-shadow transition-all overflow-hidden p-0" style="border-radius: 16px;">
                <div class="row g-0 align-items-center">
                    <div class="position-absolute start-0 top-0 bottom-0 bg-danger" style="width: 4px;"></div>
                    <div class="col-auto py-3 ps-4 pe-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background-color: #fef2f2; color: #dc2626;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="file-warning" class="lucide lucide-file-warning">
                                <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path>
                                <path d="M12 9v4"></path>
                                <path d="M12 17h.01"></path>
                            </svg></div>
                    </div>
                    <div class="col py-3 px-2">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1"><span class="badge rounded-2 fw-bold px-2 py-1" style="background-color: #eff6ff; color: #1d4ed8; font-size: 0.65rem;">บันทึกข้อความ</span>
                            <div class="d-flex align-items-center gap-1 text-muted fw-bold" style="font-size: 0.65rem;"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="hash" class="lucide lucide-hash">
                                    <line x1="4" x2="20" y1="9" y2="9"></line>
                                    <line x1="4" x2="20" y1="15" y2="15"></line>
                                    <line x1="10" x2="8" y1="3" y2="21"></line>
                                    <line x1="16" x2="14" y1="3" y2="21"></line>
                                </svg><?= isset($item->document) ? $item->document->doc_number : ''?></div>
                        </div>
                        <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.85rem;"><?php echo $item->document ? $item->document->topic : '' ?></h6>
                        <div class="d-flex flex-wrap gap-3 text-muted" style="font-size: 0.65rem;"><span class="d-flex align-items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="building-2" class="lucide lucide-building-2">
                                    <path d="M10 12h4"></path>
                                    <path d="M10 8h4"></path>
                                    <path d="M14 21v-3a2 2 0 0 0-4 0v3"></path>
                                    <path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"></path>
                                    <path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path>
                                </svg> จาก: ฝ่ายบริหารงานทั่วไป</span><span class="d-flex align-items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="user" class="lucide lucide-user">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg> ถึง: ทุกหน่วยงาน</span></div>
                    </div>
                    <div class="col-auto py-3 pe-4 ps-2 d-flex align-items-center gap-3">
                        <div class="text-end d-none d-md-block"><span class="badge bg-danger text-white rounded-pill fw-bold px-2" style="font-size: 0.6rem;">ด่วนที่สุด</span>
                            <div class="d-flex align-items-center justify-content-end gap-1 text-muted mt-1" style="font-size: 0.6rem;"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="clock" class="lucide lucide-clock">
                                    <path d="M12 6v6l4 2"></path>
                                    <circle cx="12" cy="12" r="10"></circle>
                                </svg> 15 นาทีที่แล้ว</div>
                        </div>
                        <button class="btn btn-light rounded-circle p-2 border-0 text-muted hover-text-primary" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check-circle" class="lucide lucide-check-circle">
                                <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                <path d="m9 11 3 3L22 4"></path>
                            </svg></button>
                        <button class="btn btn-primary rounded-3 fw-bold d-flex align-items-center gap-1 px-3 py-1 shadow-sm" style="font-size: 0.75rem;">เปิดอ่าน <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="chevron-right" class="lucide lucide-chevron-right">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg></button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between p-3 rounded-4 mt-3 border border-light" style="background-color: #f8fafc; border-radius: 20px;">
        <div class="d-flex align-items-center gap-2 mb-2 mb-sm-0">
            <span class="spinner-grow spinner-grow-sm text-primary" style="width: 8px; height: 8px; animation-duration: 2s;" role="status"></span>
            <p class="text-muted fw-bold mb-0" style="font-size: 0.75rem;">พบหนังสือใหม่ <span class="text-primary">2 รายการ</span> ที่ยังไม่ได้ดำเนินการ</p>
        </div>
        <a href="#" class="text-decoration-none fw-bold d-flex align-items-center gap-1 hover-text-dark" style="font-size: 0.75rem; color: #2563eb;">เข้าสู่ระบบงานสารบรรณเต็มรูปแบบ <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right" class="lucide lucide-arrow-right">
                <path d="M5 12h14"></path>
                <path d="m12 5 7 7-7 7"></path>
            </svg></a>
    </div>
</section>



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