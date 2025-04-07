<?php

use yii\helpers\Url;
use yii\helpers\Html;

use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\booking\models\Room;
/** @var yii\web\View $this */
/** @var app\modules\booking\models\RoomSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Rooms';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>


<style>
.room-card .card-img-top {
    height: 150px;
    object-fit: cover;
}

.badge-available {
    background-color: #d1e7dd;
    color: #0f5132;
}

.badge-maintenance {
    background-color: #f8d7da;
    color: #842029;
}
</style>

<div class="container py-4">
    <?=$this->render('../meeting/navbar')?>
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">จัดการห้องประชุม</h2>
                    <p class="text-muted">จัดการข้อมูลห้องประชุมทั้งหมดในระบบ</p>
                </div>
                <button type="button" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>เพิ่มห้องประชุม
                </button>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-pills mb-4" id="roomTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button"
                role="tab" aria-controls="all" aria-selected="true">ทั้งหมด</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="available-tab" data-bs-toggle="tab" data-bs-target="#available" type="button"
                role="tab" aria-controls="available" aria-selected="false">พร้อมใช้งาน</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance"
                type="button" role="tab" aria-controls="maintenance" aria-selected="false">ปิดปรับปรุง</button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="roomTabContent">
        <!-- All Rooms Tab -->
        <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ชื่อห้องประชุม</th>
                                    <th>ความจุ</th>
                                    <th class="d-none d-md-table-cell">สถานที่</th>
                                    <th class="d-none d-md-table-cell">อุปกรณ์</th>
                                    <th>สถานะ</th>
                                    <th class="text-end">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach(Room::find()->where(['name' => 'meeting_room'])->all() as $item):?>
                                <tr>
                                    <td class="fw-medium"><?=$item->title?></td>
                                    <td>30 คน</td>
                                    <td class="d-none d-md-table-cell">อาคาร A ชั้น 2</td>
                                    <td class="d-none d-md-table-cell">โปรเจคเตอร์, ไมโครโฟน, เครื่องเสียง, Wi-Fi</td>
                                    <td><span class="badge badge-available">พร้อมใช้งาน</span></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-pencil"></i><span class="visually-hidden">แก้ไข</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-trash"></i><span class="visually-hidden">ลบ</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach;?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <!-- Available Rooms Tab -->
    <div class="tab-pane fade" id="available" role="tabpanel" aria-labelledby="available-tab">
        <!-- Content would be similar but filtered for available rooms -->
         ไม่มีข้อมูล
        </div>
        
        <!-- Maintenance Rooms Tab -->
        <div class="tab-pane fade" id="maintenance" role="tabpanel" aria-labelledby="maintenance-tab">
            <!-- Content would be similar but filtered for maintenance rooms -->
            ไม่มีข้อมูล
    </div>
</div>
</div>





        <div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
        </div>
