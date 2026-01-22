<?php

use yii\helpers\Url;
use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;
use app\components\UserHelper;
$me = UserHelper::GetEmployee();

$this->title = 'ทะเบียนหนังสือ';
$this->params['breadcrumbs'][] = ['label' => 'บริการ', 'url' => ['/me']];
$this->params['breadcrumbs'][] = ['label' => 'หนังสือ', 'url' => ['/me']];
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-check-icon lucide-clipboard-check"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/></svg>
      <?= $to ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex gap-2">
    <?php echo $this->render('@app/modules/me/views/documents/menu', ['action' => $action]) ?>
    <?= $this->render('@app/components/ui/btnReturn')?>
</div>
<?php $this->endBlock(); ?>



<?php if (!isset($list)): ?>
<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel, 'action' => $action]); ?>
    </div>
</div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary-gradient text-white py-3">
        <h6 class="text-white mb-0"> 
            <i class="bi bi-ui-checks"></i> ทะเบียนหนังสือ 
            <span class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
        </h6>
    </div>
    <div class="card-body p-0"> <div class="p-3">
            <?php if (isset($list)): ?>
                <?= Html::a('แสดงทั้งหมด', ['/me/documents'], ['class' => 'btn btn-sm btn-light rounded-pill mb-2', 'data' => ['pjax' => 0]]) ?>
            <?php endif; ?>
        </div>

        <div class="table-responsive"> <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center d-none d-md-table-cell" style="width:70px;">ลำดับ</th>
                        <th class="text-center" style="width:120px;">เลขรับ/หนังสือ</th>
                        <th style="min-width:300px;">เรื่อง/รายละเอียด</th>
                        <th class="d-none d-lg-table-cell" style="width:200px;">ผู้บันทึก</th>
                        <th class="text-center" style="width:100px;">สถานะ</th>
                        <th class="text-center" style="width:80px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center d-none d-md-table-cell text-muted">
                            <?php echo (($dataProvider->pagination->offset + 1) + $key) ?>
                        </td>

                        <td class="text-center">
                            <div class="fw-bold text-dark fs-14"><?php echo $item->doc_regis_number ?></div>
                            <div class="text-danger fs-12"><?php echo $item->doc_number ?></div>
                        </td>

                        <td>
                            <div class="d-flex flex-column gap-1">
                                <div class="topic-container">
                                    <?php if ($item->doc_speed == 'ด่วนที่สุด'): ?>
                                        <span class="badge text-bg-danger fs-12 mb-1">ด่วนที่สุด</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($item->secret == 'ลับที่สุด'): ?>
                                        <span class="badge text-bg-dark fs-12 mb-1">ลับที่สุด</span>
                                    <?php endif; ?>

                                    <?php 
                                        $id = $item->documentTags->id ?? $item->documentDepartment->id ?? null;
                                        if ($id): 
                                    ?>
                                        <a href="<?= Url::to(['/me/documents/view', 'id' => $id]) ?>" 
                                           class="open-modal fw-medium d-block text-primary text-decoration-none fs-15" 
                                           data-size="modal-fullscreen">
                                            <?= $item->topic ?>
                                            <?= $item->isFile() ? '<i class="fas fa-paperclip ms-1 text-muted fs-12"></i>' : '' ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="text-muted fs-13 d-none d-sm-block">
                                    <?= $item->data_json['des'] ?? '' ?>
                                </div>

                                <div class="d-flex flex-wrap gap-2 align-items-center mt-1">
                                    <span class="text-secondary fs-12">
                                        <i class="fa-solid fa-inbox me-1"></i><?= $item->documentOrg->title ?? '-' ?>
                                    </span>
                                    <span class="badge rounded-pill bg-light text-primary border fw-light fs-11">
                                        <i class="fa-regular fa-eye"></i> <?= $item->viewCount() ?>
                                    </span>
                                </div>
                            </div>
                        </td>

                        <td class="d-none d-lg-table-cell">
                            <div class="fs-13">
                                <?= $item->viewCreate()['avatar']; ?>
                            </div>
                        </td>

                        <td class="text-center">
                            <?php 
                                $doc = $item->documentTags ?? $item->documentDepartment ?? null;
                                if ($doc): 
                            ?>
                                <div class="d-flex flex-column align-items-center gap-1">
                                    <?= Html::a($doc->docRead('fs-5')['view'], ['/me/documents/bookmark', 'id' => $doc->id], [
                                        'class' => 'bookmark bookmark-star-'.$doc->id,
                                        'id' => $doc->id
                                    ]) ?>
                                    <span class="fs-12 text-nowrap"><?= $item->documentStatus->title ?? '-' ?></span>
                                </div>
                            <?php endif; ?>
                        </td>

                        <td class="text-center">
                             <?php if ($id): ?>
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['view', 'id' => $id], [
                                    'class' => 'btn btn-outline-primary btn-sm open-modal rounded-pill',
                                    'data' => ['size' => 'modal-fullscreen']
                                ]) ?>
                             <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card-footer bg-white border-0 py-3">
            <div class="d-flex justify-content-center overflow-auto">
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'options' => ['class' => 'pagination pagination-sm mb-0'],
                ]); ?>
            </div>
        </div>
    </div>
</div>


<?php
$js = <<< JS

$(document).ready(function() {
    $('.view-btn').on('click', function() {
        // 1. ดึงข้อมูลจาก Data Attribute ของปุ่มที่กด
        const docId = $(this).data('id');
        const docTitle = $(this).data('title');

        // 2. นำข้อมูลไปใส่ใน Modal
        $('#doc-content').text(docTitle + " (ID: " + docId + ")");

        // 3. สั่งเปิด Modal
        var myModal = new bootstrap.Modal(document.getElementById('view-fullscreen'));
        myModal.show();
    });
});

JS;
$this->registerJS($js);
?>