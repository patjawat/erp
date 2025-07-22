<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\bootstrap5\LinkPager;
use app\modules\sm\models\Order;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ทะเบียนประวัติแจ้งซ่อม';
$this->params['breadcrumbs'][] = ['label' => 'แจ้งซ่อม', 'url' => ['/me/repair']];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-screwdriver-wrench"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me/menu',['active' => 'repair']) ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>
<div class="card">
<div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white mt-2">
                <i class="bi bi-ui-checks"></i> ทะเบียนงานซ่อม
                <span class="badge text-bg-light">
                    <?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
            </h6>
            <div class="d-flex justify-content-between gap-3">
                <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/me/repair-v2/create', 'title' => '<i class="fa-regular fa-circle-check"></i> เลือกประเภทการซ่อม'],['class' => 'btn btn-light shadow'])?>
            </div>
        </div>
    </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">รหัส</th>
                                    <th scope="col">อุปกรณ์</th>
                                    <th scope="col">ปัญหา</th>
                                    <th scope="col">สถานที่</th>
                                    <th scope="col">ผู้แจ้ง</th>
                                    <th scope="col">วันที่แจ้ง</th>
                                    <th scope="col">ความเร่งด่วน</th>
                                    <th scope="col">สถานะ</th>
                                    <th scope="col">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>MR-2023-001</td>
                                    <td>เครื่องปรับอากาศ</td>
                                    <td>ไม่เย็น</td>
                                    <td>ห้องประชุม 301</td>
                                    <td>สมชาย ใจดี</td>
                                    <td>15/10/2023</td>
                                    <td><span class="badge bg-warning">สูง</span></td>
                                    <td><span class="badge status-pending">รอดำเนินการ</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                จัดการ
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1" style="">
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewRequestModal"><i class="bi bi-eye me-2"></i>ดูรายละเอียด</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editRequestModal"><i class="bi bi-pencil me-2"></i>แก้ไข</a></li>
                                                <li><a class="dropdown-item" href="#"><i class="bi bi-trash me-2"></i>ลบ</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>MR-2023-002</td>
                                    <td>คอมพิวเตอร์</td>
                                    <td>เปิดไม่ติด</td>
                                    <td>แผนกบัญชี</td>
                                    <td>สมศรี มีสุข</td>
                                    <td>14/10/2023</td>
                                    <td><span class="badge bg-danger">วิกฤต</span></td>
                                    <td><span class="badge status-in-progress">กำลังดำเนินการ</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                                                จัดการ
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewRequestModal"><i class="bi bi-eye me-2"></i>ดูรายละเอียด</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editRequestModal"><i class="bi bi-pencil me-2"></i>แก้ไข</a></li>
                                                <li><a class="dropdown-item" href="#"><i class="bi bi-trash me-2"></i>ลบ</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>MR-2023-003</td>
                                    <td>ระบบไฟฟ้า</td>
                                    <td>ไฟดับเป็นบางจุด</td>
                                    <td>โรงอาหาร</td>
                                    <td>วิชัย รักดี</td>
                                    <td>13/10/2023</td>
                                    <td><span class="badge bg-info">ปานกลาง</span></td>
                                    <td><span class="badge status-completed">เสร็จสิ้น</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-expanded="false">
                                                จัดการ
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton3">
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewRequestModal"><i class="bi bi-eye me-2"></i>ดูรายละเอียด</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editRequestModal"><i class="bi bi-pencil me-2"></i>แก้ไข</a></li>
                                                <li><a class="dropdown-item" href="#"><i class="bi bi-trash me-2"></i>ลบ</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>MR-2023-004</td>
                                    <td>ระบบประปา</td>
                                    <td>น้ำรั่วจากเพดาน</td>
                                    <td>ห้องเก็บเอกสาร</td>
                                    <td>นภา สดใส</td>
                                    <td>12/10/2023</td>
                                    <td><span class="badge bg-danger">วิกฤต</span></td>
                                    <td><span class="badge status-in-progress">กำลังดำเนินการ</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton4" data-bs-toggle="dropdown" aria-expanded="false">
                                                จัดการ
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton4">
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewRequestModal"><i class="bi bi-eye me-2"></i>ดูรายละเอียด</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editRequestModal"><i class="bi bi-pencil me-2"></i>แก้ไข</a></li>
                                                <li><a class="dropdown-item" href="#"><i class="bi bi-trash me-2"></i>ลบ</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>MR-2023-005</td>
                                    <td>เครื่องพิมพ์</td>
                                    <td>กระดาษติด</td>
                                    <td>แผนกการตลาด</td>
                                    <td>พิชัย ชัยมงคล</td>
                                    <td>11/10/2023</td>
                                    <td><span class="badge bg-success">ต่ำ</span></td>
                                    <td><span class="badge status-completed">เสร็จสิ้น</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton5" data-bs-toggle="dropdown" aria-expanded="false">
                                                จัดการ
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton5">
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewRequestModal"><i class="bi bi-eye me-2"></i>ดูรายละเอียด</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editRequestModal"><i class="bi bi-pencil me-2"></i>แก้ไข</a></li>
                                                <li><a class="dropdown-item" href="#"><i class="bi bi-trash me-2"></i>ลบ</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-4">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">ก่อนหน้า</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">ถัดไป</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>