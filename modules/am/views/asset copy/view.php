<?php
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
use yii\base\ErrorException;
use app\components\AppHelper;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */

$this->title = 'ครุภัณฑ์';
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


<?php $this->beginBlock('action'); ?>
<!-- Action Buttons -->
<div class="row mb-4 no-print">
    <div class="col-md-12">

    </div>
</div>
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
                <?= Html::img($model->showImg(), ['class' => 'avatar-profile object-fit-cover rounded m-auto border border-2 border-secondary-subtle', 'style' => 'max-width:100%;min-width: 320px;']) ?>
                
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
                
                <div class="mt-3">
                    <div class="d-flex justify-content-center">
                        <div class="qr-code border p-2 bg-white">
                            <svg width="100" height="100" viewBox="0 0 100 100">
                                <rect x="0" y="0" width="100" height="100" fill="white"></rect>
                                <path d="M10,10 h30 v30 h-30 z" fill="black"></path>
                                <path d="M15,15 h20 v20 h-20 z" fill="white"></path>
                                <path d="M20,20 h10 v10 h-10 z" fill="black"></path>
                                <path d="M60,10 h30 v30 h-30 z" fill="black"></path>
                                <path d="M65,15 h20 v20 h-20 z" fill="white"></path>
                                <path d="M70,20 h10 v10 h-10 z" fill="black"></path>
                                <path d="M10,60 h30 v30 h-30 z" fill="black"></path>
                                <path d="M15,65 h20 v20 h-20 z" fill="white"></path>
                                <path d="M20,70 h10 v10 h-10 z" fill="black"></path>
                                <path d="M50,50 h10 v10 h-10 z" fill="black"></path>
                                <path d="M70,50 h10 v10 h-10 z" fill="black"></path>
                                <path d="M50,70 h10 v10 h-10 z" fill="black"></path>
                                <path d="M70,70 h10 v10 h-10 z" fill="black"></path>
                                <path d="M60,80 h10 v10 h-10 z" fill="black"></path>
                                <path d="M80,60 h10 v10 h-10 z" fill="black"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-muted mt-2">รหัสครุภัณฑ์: 7110-001-0001</p>
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

<!-- Tabs for Additional Information -->
<div class="card mb-4">
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
            <div class="tab-pane fade active show" id="specs" role="tabpanel" aria-labelledby="specs-tab">
                <h5 class="card-title fw-bold">คุณลักษณะเฉพาะ</h5>
                <?=$model->data_json['asset_options'] ?? '-'?>
            </div>

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
                <h5 class="card-title">ข้อมูลค่าเสื่อมราคา</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">ข้อมูลพื้นฐาน</h6>
                                <div class="row mb-2">
                                    <div class="col-md-6 fw-bold">ราคาทุน:</div>
                                    <div class="col-md-6"><?=number_format($model->price)?> บาท</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6 fw-bold">อายุการใช้งาน:</div>
                                    <div class="col-md-6"><?= isset($model->data_json['service_life']) ? $model->data_json['service_life'] : '' ?> ปี</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6 fw-bold">วิธีคำนวณค่าเสื่อม:</div>
                                    <div class="col-md-6">วิธีเส้นตรง</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6 fw-bold">ค่าเสื่อมต่อปี:</div>
                                    <div class="col-md-6">7,000.00 บาท</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6 fw-bold">มูลค่าปัจจุบัน:</div>
                                    <div class="col-md-6">21,000.00 บาท</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border h-100">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">กราฟแสดงค่าเสื่อมราคา</h6>
                                <div style="height: 200px; position: relative;">
                                    <svg viewBox="0 0 500 200" width="100%" height="100%" style="overflow: visible">
                                        <!-- X and Y Axes -->
                                        <line x1="50" y1="170" x2="450" y2="170" stroke="#dee2e6" stroke-width="2">
                                        </line>
                                        <line x1="50" y1="20" x2="50" y2="170" stroke="#dee2e6" stroke-width="2"></line>

                                        <!-- Y-axis labels -->
                                        <text x="45" y="170" text-anchor="end" font-size="12">0</text>
                                        <text x="45" y="130" text-anchor="end" font-size="12">10,000</text>
                                        <text x="45" y="90" text-anchor="end" font-size="12">20,000</text>
                                        <text x="45" y="50" text-anchor="end" font-size="12">30,000</text>
                                        <text x="45" y="20" text-anchor="end" font-size="12">40,000</text>

                                        <!-- X-axis labels -->
                                        <text x="50" y="185" text-anchor="middle" font-size="12">2565</text>
                                        <text x="130" y="185" text-anchor="middle" font-size="12">2566</text>
                                        <text x="210" y="185" text-anchor="middle" font-size="12">2567</text>
                                        <text x="290" y="185" text-anchor="middle" font-size="12">2568</text>
                                        <text x="370" y="185" text-anchor="middle" font-size="12">2569</text>
                                        <text x="450" y="185" text-anchor="middle" font-size="12">2570</text>

                                        <!-- Grid lines -->
                                        <line x1="50" y1="130" x2="450" y2="130" stroke="#f5f5f5" stroke-width="1">
                                        </line>
                                        <line x1="50" y1="90" x2="450" y2="90" stroke="#f5f5f5" stroke-width="1"></line>
                                        <line x1="50" y1="50" x2="450" y2="50" stroke="#f5f5f5" stroke-width="1"></line>

                                        <!-- Value line -->
                                        <polyline points="50,50 130,90 210,130 290,170 370,170 450,170" fill="none"
                                            stroke="#0d6efd" stroke-width="3"></polyline>

                                        <!-- Data points -->
                                        <circle cx="50" cy="50" r="5" fill="#0d6efd"></circle>
                                        <circle cx="130" cy="90" r="5" fill="#0d6efd"></circle>
                                        <circle cx="210" cy="130" r="5" fill="#0d6efd"></circle>
                                        <circle cx="290" cy="170" r="5" fill="#0d6efd"></circle>
                                        <circle cx="370" cy="170" r="5" fill="#0d6efd"></circle>
                                        <circle cx="450" cy="170" r="5" fill="#0d6efd"></circle>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>ปีงบประมาณ</th>
                                <th>มูลค่าต้นปี</th>
                                <th>ค่าเสื่อมราคาประจำปี</th>
                                <th>ค่าเสื่อมราคาสะสม</th>
                                <th>มูลค่าปลายปี</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2565</td>
                                <td>35,000.00</td>
                                <td>7,000.00</td>
                                <td>7,000.00</td>
                                <td>28,000.00</td>
                            </tr>
                            <tr>
                                <td>2566</td>
                                <td>28,000.00</td>
                                <td>7,000.00</td>
                                <td>14,000.00</td>
                                <td>21,000.00</td>
                            </tr>
                            <tr>
                                <td>2567</td>
                                <td>21,000.00</td>
                                <td>7,000.00</td>
                                <td>21,000.00</td>
                                <td>14,000.00</td>
                            </tr>
                            <tr>
                                <td>2568</td>
                                <td>14,000.00</td>
                                <td>7,000.00</td>
                                <td>28,000.00</td>
                                <td>7,000.00</td>
                            </tr>
                            <tr>
                                <td>2569</td>
                                <td>7,000.00</td>
                                <td>7,000.00</td>
                                <td>35,000.00</td>
                                <td>0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
</div>

<!-- Transfer History -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-arrow-left-right"></i> ประวัติการโอนย้าย
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>วันที่</th>
                        <th>จากหน่วยงาน</th>
                        <th>ไปยังหน่วยงาน</th>
                        <th>ผู้รับผิดชอบเดิม</th>
                        <th>ผู้รับผิดชอบใหม่</th>
                        <th>เหตุผล</th>
                        <th>ผู้อนุมัติ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>01/04/2566</td>
                        <td>ฝ่ายบัญชี</td>
                        <td>ฝ่ายเทคโนโลยีสารสนเทศ</td>
                        <td>นางสาวรักษ์ดี มีสุข</td>
                        <td>นายสมชาย ใจดี</td>
                        <td>ปรับโครงสร้างหน่วยงาน</td>
                        <td>นายใหญ่ บริหารดี</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>




<div class="asset-view">
    <?php if($model->asset_group == 1):?>
    <?=$this->render('@app/modules/am/views/asset/asset_detail_group_1',['model' => $model])?>
    <?php endif?>

    <?php if($model->asset_group == 2):?>
    <?=$this->render('@app/modules/am/views/asset/asset_detail_group_2',['model' => $model])?>
    <?php endif?>

    <?php if($model->asset_group == 3):?>
    <?=$this->render('@app/modules/am/views/asset/asset_detail_group_3',['model' => $model])?>

    <?= $model->asset_group == 3 ? $this->render('./asset_detail',['model' => $model,'searchModel' => $searchModel,
    'dataProvider' => $dataProvider]) :  ''?>

    <?php endif?>
</div>

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