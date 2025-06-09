<?php
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
use yii\base\ErrorException;
use app\components\AppHelper;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */

$this->title = 'รายละเอียดครุภัณฑ์ ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนทรัพย์สิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
$title = Yii::$app->request->get('title');
$group = Yii::$app->request->get('group');
?>

<?php $this->beginBlock('page-title'); ?>
<?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu',['active' => 'asset'])?>
<?php $this->endBlock(); ?>

<style>
.field-asset-q {
    margin-bottom: 0px !important;
}
</style>

<?php Pjax::begin(['id' => 'am-container','timeout' => 50000 ]); ?>



<!-- Equipment Details -->
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>รูปภาพครุภัณฑ์</span>
                <span class="badge text-bg-primary">สถานะ: ใช้งานได้</span>
            </div>
            <div class="card-body text-center">
                <div class="position-relative p-2 d-flex">
                    <img src="<?=$model->QrCode()?>" width="140" class="position-absolute start-0 top-0 m-2" alt="QR Code">
                    <?= Html::img($model->showImg()['image'], ['class' => 'avatar-profile object-fit-cover rounded m-auto border border-2 border-secondary-subtle', 'style' => 'max-width:100%;min-width: 320px;']) ?>
                </div>
                <?php if (isset($model->Retire()['progress'])): ?>
                <div class="container px-5">

                    <div class="progress progress-sm mt-3 w-100">
                        <div class="progress-bar" role="progressbar"
                        <?= "style='width:" . $model->Retire()['progress'] . '%; background-color:' . $model->Retire()['color'] . ";  '" ?>
                        aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-2" style="width:100%;">
                    <div>
                        <i class="fa-regular fa-clock"></i> <span class="fw-semibold">เหลือ</span> :
                        <?= AppHelper::CountDown($model->Retire()['date'])[0] != '-' ? AppHelper::CountDown($model->Retire()['date']) : 'หมดอายุการใช้งาน' ?>
                    </div>|<div>
                        <i class="fa-solid fa-calendar-xmark"></i> <span class="fw-semibold">ครบ <?= isset($model->data_json['service_life']) ? $model->data_json['service_life'] : '' ?> ปี</span>
                        <span class="text-danger"><?= $model->Retire()['date']; ?></span>
                    </div>
                </div>
                </div>
                <?php endif; ?>
                
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <?= $model->getOwner() ?>
                    <h6>ผู้รับผิดชอบ</h6>
                </div>
        </div>
        </div>
    </div>

  
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-info-circle"></i> ข้อมูลทั่วไป
                    </div>
                    <div class="d-feles gap-2">
                        <?= Html::a('<i class="fa-solid fa-triangle-exclamation"></i> แจ้งซ่อม', ['/helpdesk/repair/create', 'code' => $model->code, 'send_type' => 'asset', 'container' => 'ma-container', 'title' => '<i class="fa-solid fa-circle-info fs-3 text-danger"></i>  ส่งซ่อม'], ['class' => 'open-modal btn btn-danger rounded-pill shadow', 'data' => ['size' => 'modal-lg']]) ?>
                        <?= Html::a('<i class="fa-solid fa-qrcode"></i> QR-Code', ['qrcode', 'id' => $model->id], ['class' => 'open-modal btn btn-success rounded-pill shadow', 'data' => ['size' => 'modal-md']]) ?>
                        <?= Html::a('<i class="fa-solid fa-chart-line"></i> ค่าเสื่อม', ['depreciation', 'id' => $model->id], ['class' => 'open-modal btn btn-primary rounded-pill shadow', 'data' => ['size' => 'modal-lg']]) ?>
                        <?= Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-warning rounded-pill shadow']) ?>
                        <?= Html::a('<i class="fa-solid fa-trash"></i> ลบ', ['delete', 'id' => $model->id], ['class' => 'btn btn-secondary rounded-pill shadow delete-asset']) ?>
                    </div>

                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">ชื่อครุภัณฑ์:</div>
                    <div class="col-md-8"><?=$model->assetItem->title;?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">รหัสครุภัณฑ์:</div>
                    <div class="col-md-8"><?=$model->code?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">หมวดหมู่:</div>
                    <div class="col-md-8">ครุภัณฑ์คอมพิวเตอร์</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">ประเภท:</div>
                    <div class="col-md-8"><?=$model->AssetTypeName()?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">ยี่ห้อ/รุ่น:</div>
                    <div class="col-md-8">
                        <?=$model->data_json['band'] ?? 'ไม่ระบุ'?>/<?=$model->data_json['model'] ?? 'ไม่ระบุ'?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">หน่วยงานที่รับผิดชอบ:</div>
                    <div class="col-md-8">ฝ่ายเทคโนโลยีสารสนเทศ</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">สถานที่ตั้ง:</div>
                    <div class="col-md-8">อาคารสำนักงานใหญ่ ชั้น 3 ห้อง 305</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">สถานะ:</div>
                    <div class="col-md-8"><?=$model->statusName()?></div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-currency-dollar"></i> ข้อมูลการจัดซื้อ
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">วิธีการได้มา:</div>
                    <div class="col-md-8"><?=$model->purchaseName->title?></div>
                </div>
                <div class="row mb-3">
                    <!-- <div class="col-md-4 fw-bold">แหล่งงบประมาณ:</div> -->
                    <div class="col-md-4 fw-bold">ประเภทเงิน:</div>
                    <div class="col-md-8"><?=$model->budgetTypeName()?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">วันที่ได้รับ:</div>
                    <div class="col-md-8"><?=ThaiDateHelper::formatThaiDate($model->receive_date,'medium')?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">ราคา:</div>
                    <div class="col-md-8"><?=number_format($model->price)?> บาท</div>
                </div>
                <!-- <div class="row mb-3">
                            <div class="col-md-4 fw-bold">เลขที่สัญญา:</div>
                            <div class="col-md-8">IT-PO-2565-0042</div>
                        </div> -->
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">ผู้ขาย/ผู้จำหน่าย:</div>
                    <div class="col-md-8"><?=$model->vendorName()?></div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="asset-view">
    <?php echo $this->render('./asset_detail',['model' => $model,'searchModel' => $searchModel,
    'dataProvider' => $dataProvider])?>
</div>

<!-- Tabs for Additional Information -->
<!-- <div class="card mb-4">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs mb-3" id="equipmentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs"
                    type="button" role="tab" aria-selected="true">รายละเอียดทางเทคนิค</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance"
                    type="button" role="tab" aria-selected="false" tabindex="-1">ประวัติการซ่อมบำรุง</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="depreciation-tab" data-bs-toggle="tab" data-bs-target="#depreciation"
                    type="button" role="tab" aria-selected="false" tabindex="-1">ค่าเสื่อมราคา</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents"
                    type="button" role="tab" aria-selected="false" tabindex="-1">เอกสารที่เกี่ยวข้อง</button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="equipmentTabsContent">
            <!-- Technical Specifications Tab -->

            <!-- <div class="tab-pane fade active show" id="specs" role="tabpanel" aria-labelledby="specs-tab">
                <h5 class="card-title fw-bold">คุณลักษณะเฉพาะ</h5>
                <?=$model->data_json['asset_options'] ?? '-'?>
            </div> -->

            <!-- Maintenance History Tab -->
            <div class="tab-pane fade" id="maintenance" role="tabpanel" aria-labelledby="maintenance-tab">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">ประวัติการซ่อมบำรุง</h5>
                    <button class="btn btn-sm btn-primary no-print" data-bs-toggle="modal"
                        data-bs-target="#addMaintenanceModal">
                        <i class="bi bi-plus-circle"></i> เพิ่มประวัติการซ่อม
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>วันที่</th>
                                <th>ประเภท</th>
                                <th>รายละเอียด</th>
                                <th>ผู้ดำเนินการ</th>
                                <th>ค่าใช้จ่าย</th>
                                <th class="no-print">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>15/07/2566</td>
                                <td><span class="badge bg-info">บำรุงรักษา</span></td>
                                <td>ทำความสะอาดเครื่อง และอัพเดทระบบปฏิบัติการ</td>
                                <td>นายสมชาย ใจดี</td>
                                <td>0.00 บาท</td>
                                <td class="no-print">
                                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>03/04/2566</td>
                                <td><span class="badge bg-warning text-dark">ซ่อมแซม</span></td>
                                <td>เปลี่ยนพัดลมระบายความร้อน CPU</td>
                                <td>บริษัท คอมพิวเตอร์ โซลูชั่น จำกัด</td>
                                <td>1,200.00 บาท</td>
                                <td class="no-print">
                                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Depreciation Tab -->
            <div class="tab-pane fade" id="depreciation" role="tabpanel" aria-labelledby="depreciation-tab">
                <?=$this->render('depreciation',['model' => $model])?>
            </div>

            <!-- Documents Tab -->
            <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">เอกสารที่เกี่ยวข้อง</h5>
                    <button class="btn btn-sm btn-primary no-print" data-bs-toggle="modal"
                        data-bs-target="#addDocumentModal">
                        <i class="bi bi-plus-circle"></i> เพิ่มเอกสาร
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ชื่อเอกสาร</th>
                                <th>ประเภท</th>
                                <th>วันที่อัปโหลด</th>
                                <th>ผู้อัปโหลด</th>
                                <th class="no-print">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>ใบส่งของ-ใบกำกับภาษี</td>
                                <td><span class="badge bg-secondary">เอกสารการจัดซื้อ</span></td>
                                <td>15/01/2565</td>
                                <td>นางสาวสมศรี มีทรัพย์</td>
                                <td class="no-print">
                                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> ดู</button>
                                    <button class="btn btn-sm btn-outline-success"><i class="bi bi-download"></i>
                                        ดาวน์โหลด</button>
                                </td>
                            </tr>
                            <tr>
                                <td>คู่มือการใช้งาน</td>
                                <td><span class="badge bg-info">คู่มือ</span></td>
                                <td>15/01/2565</td>
                                <td>นางสาวสมศรี มีทรัพย์</td>
                                <td class="no-print">
                                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> ดู</button>
                                    <button class="btn btn-sm btn-outline-success"><i class="bi bi-download"></i>
                                        ดาวน์โหลด</button>
                                </td>
                            </tr>
                            <tr>
                                <td>ใบรับประกันสินค้า</td>
                                <td><span class="badge bg-warning text-dark">การรับประกัน</span></td>
                                <td>15/01/2565</td>
                                <td>นางสาวสมศรี มีทรัพย์</td>
                                <td class="no-print">
                                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> ดู</button>
                                    <button class="btn btn-sm btn-outline-success"><i class="bi bi-download"></i>
                                        ดาวน์โหลด</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> -->

<!-- Transfer History -->
<?php // $this->render('transfer_history')?>

<?php
$js = <<< JS


$('.delete-asset').click(function (e) { 
    e.preventDefault();
    let url = $(this).attr('href');

    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "ข้อมูลนี้จะถูกลบและไม่สามารถกู้คืนได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: url,
                dataType: "json",
                success: function (res) {
                    if (res.status == 'success') {
                        Swal.fire({
                            title: 'ลบข้อมูลสำเร็จ!',
                            text: 'รายการถูกลบเรียบร้อยแล้ว',
                            icon: 'success',
                            timer: 1000, // ตั้งค่าให้ Swal ปิดอัตโนมัติหลัง 1 วินาที
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '/am/asset'; // Redirect หลังจาก timer หมด
                        });
                    } else {
                        Swal.fire(
                            'เกิดข้อผิดพลาด!',
                            res.message || 'ไม่สามารถลบข้อมูลได้',
                            'error'
                        );
                    }
                },
                error: function () {
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                        'error'
                    );
                }
            });
        }
    });
});


JS;
$this->registerJS($js);

?>
<?php Pjax::end(); ?>