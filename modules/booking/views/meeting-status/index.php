<?php

use yii\helpers\Url;
use yii\helpers\Html;

use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\booking\models\RoomType;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\RoomSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ตั้งค่าสถานะประชุม';
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['/booking/meeting/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php
$palette =  ['#2196f3', '#4caf50', '#ffeb3b', '#ff9800', '#f44336', '#9c27b0', '#00bcd4', '#e91e63', '#607d8b'];
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
   
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
        <?= $this->title ?>
    </h4>
</div>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/components/ui/btnReturn') ?>
<?php $this->endBlock(); ?>


<?php Pjax::begin(); ?>
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
                <i class="bi bi-ui-checks"></i> ทะเบียนขอใช้ห้องประชุม
                <span class="badge text-bg-light">
                    <?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
            </h6>
            <div class="d-flex justify-content-between">
                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['create', 'title' => '<i class="fa-solid fa-circle-plus"></i> สร้างรูปแบบห้องประชุม'], ['class' => 'btn btn-light shadow open-modal', 'data' => ['size' => 'modal-md']]) ?>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th class="text-center" style="width:5%">ลำดับ</th>
                    <th class="text-center" style="width:20%px">สีพื้นหลัง</th>
                    <th class="text-center" style="width:20%px">สีตัวหนังสือ</th>
                    <th style="width:10%">รหัส</th>
                    <th style="width:40%">สถานะ</th>
                    <th class="fw-semibold text-end" style="width:10%">จัดการ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center">
                            <?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                        <td>
                            <?php
                            echo kartik\color\ColorInput::widget([
                                'name' => 'color_' . $item->id,
                                'value' => $item->data_json['color'] ?? '', // assuming 'color' is the attribute
                                'options' => [
                                    'placeholder' => 'Choose your color ...',
                                    'class' => 'leave-color-input',
                                    'data-id' => $item->id,
                                    'value' => $item->data_json['color'] ?? '#2196f3', // preset default color if not set
                                ],
                                'pluginOptions' => [
                                    'showDefaultPalette' => true,
                                    'palette' => [$palette],
                                    'allowEmpty' => false,
                                ],
                                'pluginEvents' => [
                                    "change" => "function(event) {
                                        let color = $(this).val();
                                        let id = $(this).data('id');
                                        $.ajax({
                                            url: '" . Url::to(['/hr/leave-type/update-color', 'id' => $item->id]) . "',
                                            type: 'POST',
                                            data: {id: id, color: color},
                                            success: function(res) {
                                            console.log(res.data.data_json.color)
                                                $('body').find('.' + res.data.code).css('background-color', res.data.data_json.color);
                                            }
                                        });
                                    }"
                                ]
                            ]);
                            ?>
                        </td>
                        <td>
                            <?php
                            echo kartik\color\ColorInput::widget([
                                'name' => 'color_' . $item->id,
                                'value' => $item->data_json['text_color'] ?? '', // assuming 'color' is the attribute
                                'options' => [
                                    'placeholder' => 'Choose your color ...',
                                    'class' => 'leave-color-input',
                                    'data-id' => $item->id,
                                    'value' => $item->data_json['text_color'] ?? '#2196f3', // preset default color if not set
                                ],
                                'pluginOptions' => [
                                    'showDefaultPalette' => true,
                                    'palette' => [$palette],
                                    'allowEmpty' => false,
                                ],
                                'pluginEvents' => [
                                    "change" => "function(event) {
                                        let color = $(this).val();
                                        let id = $(this).data('id');
                                        $.ajax({
                                            url: '" . Url::to(['/hr/leave-type/update-color', 'id' => $item->id]) . "',
                                            type: 'POST',
                                            data: {id: id, text_color: color},
                                            success: function(res) {
                                            console.log(res.data.data_json.color)
                                                $('body').find('.' + res.data.code).css('background-color', res.data.data_json.color);
                                            }
                                        });
                                    }"
                                ]
                            ]);
                            ?>
                        </td>
                        <td class="fw-medium align-middle"><?= $item->code ?></td>
                        <td class="fw-medium align-middle"><?= $item->title ?></td>
                        <td class="fw-light text-end align-middle">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    จัดการ
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    <li><?= Html::a('<i class="fa-solid fa-pen-to-square me-1"></i> แก้ไข', ['update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?></li>
                                    <li><?php echo Html::a('<i class="fa-solid fa-trash me-1"></i> ลบทิ้ง', ['delete', 'id' => $item->id], ['class' => 'dropdown-item delete-item']) ?></li>
                                </ul>
                            </div>
                        </td>
                    <?php endforeach; ?>
            </tbody>
        </table>
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
<?php Pjax::end() ?>