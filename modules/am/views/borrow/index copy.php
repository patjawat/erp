<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetailSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'รายการบำรุงรักษา';
$this->params['breadcrumbs'][] = $this->title;
$iconClean = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="calendar-sync" class="lucide lucide-calendar-sync"><path d="M11 10v4h4"></path><path d="m11 14 1.535-1.605a5 5 0 0 1 8 1.5"></path><path d="M16 2v4"></path><path d="m21 18-1.535 1.605a5 5 0 0 1-8-1.5"></path><path d="M21 22v-4h-4"></path><path d="M21 8.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h4.3"></path><path d="M3 10h4"></path><path d="M8 2v4"></path></svg>'
?>
<div class="asset-detail-index">
    <div class="d-flex justify-content-between">
        <h6><?= Html::encode($this->title) ?></h6>

        <p>
            <?= Html::a('<i class="fa-solid fa-plus"></i> บันทึกการยืม', ['create', 'code' => $searchModel->code, 'title' => $iconClean . ' บันทึกการยืม'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        </p>
    </div>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center" style="width:30px">ลำดับ</th>
                    <th>ชื่อรายการ</th>
                    <th>วันที่</th>
                    <th>ผู้ดำเนินการ</th>
                    <th class="text-center" style="width:130px">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                        <td><?= $item->data_json['title'] ?? '-' ?></td>
                        <td><?= Yii::$app->thaiDate->toThaiDate($item->created_at, true, false); ?></td>
                        <td><?= $item->createdBy->employees->fullname ?? '-' ?></td>
                        <td class="text-center py-2">
                            <div class="d-flex justify-content-center">
                                <a href="<?= Url::to(['/am/maintenance/view', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary open-modal" data-size="modal-lg" title="ดูรายละเอียด">
                                    <i class="fa-regular fa-eye"></i></a>
                                <a href="<?= Url::to(['/am/maintenance/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข']) ?>" class="btn btn-icon btn-ghost-secondary open-modal" data-size="modal-lg" title="ดูรายละเอียด">
                                 <i class="fa-regular fa-pen-to-square"></i></a>

                                <a href="<?= Url::to(['/am/maintenance/delete', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary delete-item" title="ดูรายละเอียด">
                                   <i class="fa-regular fa-trash-can"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php Pjax::end(); ?>

</div>


<div class="tab-pane fade show active" id="borrow-history">
    <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
        <h6 class="fw-bold"><i class="bi bi-clock-history me-1"></i> รายการยืม-คืนทั้งหมด</h6>
        <button class="btn btn-sm btn-primary rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> บันทึกการยืมใหม่
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle border-top">
            <thead class="bg-light">
                <tr>
                     <th class="text-center" style="width:30px">ลำดับ</th>
                    <th class="py-3">ผู้ยืม / หน่วยงาน</th>
                    <th class="text-center">วันที่ยืม</th>
                    <th class="text-center">กำหนดคืน</th>
                    <th class="text-center">วันที่คืนจริง</th>
                    <th class="text-center">สถานะ</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                  <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr class="table-warning-subtle">
                        <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?>
                    </td>
                    <td>
                        <div class="fw-bold">นพ.สมชาย วิริยะ</div>
                        <small class="text-muted">วอร์ดอายุรกรรมชาย (MED 1)</small>
                    </td>
                    <td class="text-center">28 ธ.ค. 2568</td>
                    <td class="text-center">30 ธ.ค. 2568</td>
                    <td class="text-center">-</td>
                    <td class="text-center">
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                            <i class="bi bi-hourglass-split me-1"></i> กำลังยืม
                        </span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-success px-3">รับคืน</button>
                    </td>
                </tr>
<?php endforeach;?>
                <tr>
                    <td>
                        <div class="fw-bold">พว.ใจดี รักงาน</div>
                        <small class="text-muted">ห้องฉุกเฉิน (ER)</small>
                    </td>
                    <td class="text-center">15 ธ.ค. 2568</td>
                    <td class="text-center">17 ธ.ค. 2568</td>
                    <td class="text-center">17 ธ.ค. 2568</td>
                    <td class="text-center">
                        <span class="badge bg-light text-success border border-success-subtle rounded-pill px-3 py-2">
                            <i class="bi bi-check-circle-fill me-1"></i> คืนแล้ว
                        </span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


<div class="modal-content border-0 shadow-lg">
    <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title fw-bold">
            <i class="bi bi-box-arrow-right me-2"></i>บันทึกการยืมเครื่องมือแพทย์
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body p-4">
        
    </div>

    <div class="modal-footer bg-light border-0 py-3">
        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary px-5 fw-bold">
            <i class="bi bi-check-lg me-1"></i> ยืนยันบันทึกการยืม
        </button>
    </div>
</div>



<div class="modal-content border-0 shadow-lg">
    <div class="modal-header bg-success text-white py-3">
        <h5 class="modal-title fw-bold">
            <i class="bi bi-arrow-return-left me-2"></i>บันทึกการรับคืนเครื่องมือแพทย์
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body p-4">
        <div class="row mb-4 bg-light p-3 rounded-3 g-2">
            <div class="col-6">
                <small class="text-muted d-block">ผู้ยืมล่าสุด:</small>
                <span class="fw-bold">นพ.สมชาย วิริยะ (วอร์ด 5)</span>
            </div>
            <div class="col-6">
                <small class="text-muted d-block">ยืมเมื่อวันที่:</small>
                <span class="fw-bold">28 ธ.ค. 2568 (3 วันที่แล้ว)</span>
            </div>
        </div>

        <form>
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-bold">สภาพเครื่องมือหลังใช้งาน</label>
                    <div class="d-flex gap-3 mt-1">
                        <div class="form-check border p-2 px-4 rounded-pill">
                            <input class="form-check-input" type="radio" name="condition" id="cond-normal" checked>
                            <label class="form-check-label text-success fw-bold" for="cond-normal">
                                <i class="bi bi-check-circle-fill"></i> ปกติ
                            </label>
                        </div>
                        <div class="form-check border p-2 px-4 rounded-pill border-danger">
                            <input class="form-check-input" type="radio" name="condition" id="cond-broken">
                            <label class="form-check-label text-danger fw-bold" for="cond-broken">
                                <i class="bi bi-exclamation-triangle-fill"></i> ชำรุด/แจ้งซ่อม
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mt-4">
                    <label class="form-label fw-bold">วันที่คืนจริง</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                        <input type="date" class="form-control" value="2025-12-30">
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <label class="form-label fw-bold">รายละเอียด/ปัญหาที่พบ (ถ้ามี)</label>
                    <textarea class="form-control" rows="3" placeholder="ระบุสภาพเครื่อง หรือปัญหาที่พบหลังการใช้งาน..."></textarea>
                </div>
            </div>
        </form>
    </div>

    <div class="modal-footer border-0 py-3 bg-light">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-success px-5 fw-bold">
            <i class="bi bi-check-lg me-1"></i> ยืนยันการรับคืน
        </button>
    </div>
</div>
