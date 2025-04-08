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

$this->title = 'ห้องประชุม';
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


<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6>
                <i class="bi bi-ui-checks"></i> ทะเบียนห้องประชุม
                <span
                    class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                รายการ
            </h6>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">    
            <?php echo $this->render('_search', ['model' => $searchModel]); ?>
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/me/leave/create','title' => '<i class="fa-solid fa-calendar-plus"></i> บันทึกขออนุมัติการลา'], ['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']]) ?>
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
                        <td>30 คน</td>
                        <td class="d-none d-md-table-cell">อาคาร A ชั้น 2</td>
                        <td class="d-none d-md-table-cell">โปรเจคเตอร์, ไมโครโฟน, เครื่องเสียง, Wi-Fi</td>
                        <td><span class="badge badge-available">พร้อมใช้งาน</span></td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                <?=Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/booking/room/view','id' => $item->id,'title' => '<i class="fa-solid fa-eye"></i> ดู'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                                <?=Html::a('<i class="fa-solid fa-pen-to-square fa-2x text-warning"></i>',['/booking/room/update','id' => $item->id,'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                                <?=Html::a('<i class="fa-solid fa-trash fa-2x text-danger"></i>',['/booking/room/delete','id' => $item->id,'title' => '<i class="fa-solid fa-trash"></i> ลบ'],['data-method' => 'post','data-confirm' => 'คุณต้องการลบห้องประชุมนี้หรือไม่?'])?>
                            </div>
                        </td>
                    </tr>
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