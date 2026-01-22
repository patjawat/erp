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


<div class="container mt-5">
    <h2>Document List</h2>
    <div class="list-group">
        <div class="list-group-item d-flex justify-content-between align-items-center">
            Document #001
            <button class="btn btn-primary view-btn" data-id="001" data-title="Document 001 Content">View</button>
        </div>
        <div class="list-group-item d-flex justify-content-between align-items-center">
            Document #002
            <button class="btn btn-primary view-btn" data-id="002" data-title="Document 002 Content">View</button>
        </div>
    </div>
</div>


<div class="modal fade" id="view-fullscreen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-primary text-white">
            <div class="modal-header border-0">
                <h1 class="modal-title fs-5">View Document</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <!-- <h1 id="doc-content">Loading...</h1> -->
                <div class="d-flex vh-100 overflow-hidden">
    <section class="flex-grow-1 d-flex flex-column bg-secondary bg-opacity-25" style="flex: 3;">
        <div class="d-flex flex-column h-100">
            <div class="d-flex align-items-center justify-content-between px-3 bg-dark text-white shrink-0" style="height: 48px;">
                <div class="d-flex align-items-center gap-3 small">
                    <span class="fw-medium text-truncate" style="max-width: 300px;">แจ้งการเปลี่ยนแปลงฉลาก และกล่องบรรจุรายการยา TENOFVOIR...</span>
                    <div class="vr bg-secondary my-3"></div>
                    <span>1 / 1</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-link text-white p-0"><i class="fas fa-minus-circle"></i></button>
                    <span class="small text-center" style="width: 40px;">100%</span>
                    <button class="btn btn-link text-white p-0"><i class="fas fa-plus-circle"></i></button>
                    <div class="vr bg-secondary my-3 mx-2"></div>
                    <button class="btn btn-link text-white p-0"><i class="fas fa-print"></i></button>
                    <button class="btn btn-link text-white p-0"><i class="fas fa-download"></i></button>
                </div>
            </div>

            <div class="flex-grow-1 overflow-auto p-5 d-flex justify-content-center bg-dark">
                <div class="bg-white shadow-lg p-5" style="width: 800px; min-height: 1000px;">
                    <div class="d-flex justify-content-between mb-4 small">
                        <div>ที่ สธ ๕๓๐๒/ตป/ว.๕๕๙/๒๕๖๘</div>
                        <div class="text-end">
                            โรงพยาบาลสมเด็จพระยุพราชด่านซ้าย<br>
                            รับที่ : ๘๘<br>
                            วันที่ : ๑๒ ม.ค. ๒๕๖๙<br>
                            เวลา : ๐๙:๓๖ น.
                        </div>
                    </div>

                    <div class="text-center mb-4">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/e/e6/Krut_standard.png" class="mb-2" style="width: 80px;">
                        <h4 class="fw-bold">องค์การเภสัชกรรม</h4>
                        <p class="small">๗๕/๑ ถ.พระรามที่ ๖ เขตราชเทวี กทม. ๑๐๔๐๐</p>
                    </div>

                    <div class="mb-4">
                        <p>๒๗ ธันวาคม ๒๕๖๘</p>
                        <div class="d-flex gap-2 mb-2">
                            <span class="fw-bold text-nowrap">เรื่อง</span>
                            <span>แจ้งการเปลี่ยนแปลงฉลาก และกล่องบรรจุรายการยา TENOFVOIR DISOPROXIL FUMARATE</span>
                        </div>
                        <div class="d-flex gap-2 mb-3">
                            <span class="fw-bold text-nowrap">เรียน</span>
                            <span>นายแพทย์สาธารณสุขจังหวัด/ ผู้อำนวยการโรงพยาบาลศูนย์/ โรงพยาบาลทั่วไป/ โรงพยาบาลชุมชน/ โรงพยาบาลในสังกัด นอกสังกัด กระทรวงสาธารณสุข</span>
                        </div>
                        <p style="text-indent: 50px; line-height: 1.6;">
                            ด้วยองค์การเภสัชกรรมได้เปลี่ยนแปลงฉลาก และกล่องพิมพ์รายละเอียดยา TENOFVOIR DISOPROXIL FUMARATE TABLETS ๓๐๐ mg ๓๐'s (TENVIR) โดยการเปลี่ยนแปลงดังกล่าวมีรายละเอียดดังนี้...
                        </p>
                    </div>

                    <table class="table table-bordered border-dark small text-start">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 30%;">รายการ</th>
                                <th style="width: 50%;">สิ่งที่แก้ไขเปลี่ยนแปลง</th>
                                <th>หมายเหตุ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>TENVIR TABLETS 300 mg</td>
                                <td>๑. ยกเลิกสามเหลี่ยม ต้องติดตาม...<br>๒. เพิ่ม bar mark จาก ๑ ขีด เป็น ๒ ขีด</td>
                                <td>เริ่มตั้งแต่ Lot No. เป็นต้นไป</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white border-start shadow-sm d-flex flex-column" style="flex: 2; min-width: 400px;">
        <div class="d-flex border-bottom">
            <button class="btn flex-fill py-3 fw-bold border-bottom border-primary text-primary rounded-0">ลงความเห็น (เกษียน)</button>
            <button class="btn flex-fill py-3 fw-bold border-bottom border-transparent text-muted rounded-0">ประวัติการอ่าน (1)</button>
        </div>

        <div class="flex-grow-1 overflow-auto p-4">
            <div class="card border-light bg-light bg-opacity-50 mb-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-uppercase fw-bold text-muted small mb-0"><i class="fas fa-magic me-1"></i> ข้อความที่ใช้บ่อย (3)</h6>
                        <button class="btn btn-sm btn-link text-decoration-none p-0">จัดการแม่แบบ</button>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button class="btn btn-sm btn-outline-secondary bg-white text-dark text-truncate" style="max-width: 250px;">เรียน ผอ.รพร.ด่านซ้าย เพื่อโปรดทราบ เห็นควรแจ้งผู้เกี่ยวข้องทราบ...</button>
                        <button class="btn btn-sm btn-outline-secondary bg-white text-dark text-truncate" style="max-width: 250px;">เรียน ผอ.รพร.ด่านซ้าย เพื่อโปรดทราบ เห็นควรโปรดพิจารณา</button>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <div class="mb-3 d-flex align-items-center gap-2 small">
                    <span class="text-muted fw-bold">ถึงหน่วยงาน:</span>
                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">งานบริหารทั่วไป</span>
                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">กลุ่มงานเภสัชกรรมฯ</span>
                </div>
                
                <textarea class="form-control bg-light border-2 p-3" rows="8" placeholder="พิมพ์ข้อความเกษียนหรือเลือกจากแม่แบบด้านบน..."></textarea>
                
                <div class="d-flex justify-content-between mt-2 px-1">
                    <div class="d-flex gap-3">
                        <button class="btn btn-link btn-sm text-muted text-decoration-none p-0"><i class="fas fa-trash-alt me-1"></i> ล้างข้อความ</button>
                        <button class="btn btn-link btn-sm text-muted text-decoration-none p-0 opacity-50"><i class="fas fa-save me-1"></i> บันทึกเป็นแม่แบบ</button>
                    </div>
                    <span class="badge bg-secondary opacity-50 rounded-pill small">0 ตัวอักษร</span>
                </div>
            </div>

            <div class="mb-4">
                <label class="small fw-bold text-muted text-uppercase mb-2 d-block tracking-widest"><i class="fas fa-user-tie text-primary me-1"></i> ส่งต่อถึง</label>
                <select class="form-select border-2 p-2">
                    <option selected>-- เลือกผู้รับมอบ/พิจารณา --</option>
                    <option>ผอ.รพร.ด่านซ้าย (นายแพทย์สมชาย พากเพียร)</option>
                    <option>รองผู้อำนวยการฝ่ายการแพทย์</option>
                </select>
            </div>

            <button class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm" disabled>
                <i class="fas fa-paper-plane me-2"></i> ลงความเห็นและส่งต่อเอกสาร
            </button>
        </div>

        <div class="p-3 bg-light border-top text-center opacity-50">
            <div class="d-flex align-items-center justify-content-center gap-2">
                <img src="https://upload.wikimedia.org/wikipedia/commons/e/e6/Krut_standard.png" style="height: 16px;">
                <p class="mb-0 fw-bold" style="font-size: 9px; letter-spacing: -0.5px;">DIGITAL SARABUN v2.4 • DANSAI CROWN PRINCE HOSPITAL</p>
            </div>
        </div>
    </section>
</div>

            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
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