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

$this->title = 'ตั้งค่าห้องประชุม';
$this->params['breadcrumbs'][] = ['label' => 'ระบบจัดการห้องประชุม', 'url' => ['/booking/meeting/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-people-fill"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
จัดการข้อมูลห้องประชุมทั้งหมดในระบบ
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/booking/views/meeting/menu') ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../meeting/menu',['active' => 'room'])?>
<?php $this->endBlock(); ?>

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียนห้องประชุม <span class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ</h6>
         <div class="d-flex justify-content-between align-items-center gap-3">    
            <?php echo $this->render('_search', ['model' => $searchModel]); ?>
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างห้องประชุม', ['/booking/room/create','title' => '<i class="fa-solid fa-circle-plus"></i> สร้างห้องประชุม'], ['class' => 'btn btn-primary rounded-pill mt-3 shadow open-modal','data' => ['size' => 'modal-lg']]) ?>
        </div>
        </div>
       
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="fw-semibold">ชื่อห้องประชุม</th>
                        <th class="fw-semibold">ความจุ</th>
                        <th class="fw-semibold d-none d-md-table-cell">สถานที่</th>
                        <th class="fw-semibold d-none d-md-table-cell">อุปกรณ์</th>
                        <th class="fw-semibold">สถานะ</th>
                        <th class="fw-semibold text-end">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach(Room::find()->where(['name' => 'meeting_room'])->all() as $item):?>
                    <tr>
                        <td class="fw-medium"><?=$item->title?></td>
                        <td><?=$item->data_json['seat_capacity'] ?? '-'?> คน</td>
                        <td class="d-none d-md-table-cell"><?=$item->data_json['location'] ?? '-';?></td>
                        <td class="d-none d-md-table-cell"><?=$item->showAccessory()?></td>
                        <td>พร้อมใช้งาน</td>
                <td class="fw-light text-end">
                <div class="btn-group">
                    <?= Html::a('<i class="fa-solid fa-pen-to-square"></i>', ['update', 'id' => $item->id,'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-light w-100 open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                    <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                        data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                        <i class="bi bi-caret-down-fill"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><?php echo Html::a('<i class="fa-solid fa-trash me-1"></i> ลบทิ้ง', ['delete', 'id' => $item->id], ['class' => 'dropdown-item delete-item'])?>
                        </li>
                    </ul>
                </div>
            </td>
                    <?php endforeach;?>

                </tbody>
            </table>
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