<?php

use app\modules\helpdesk3\models\DeviceType;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk3\models\DeviceTypeSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ประเภทอุปกรณ์';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-gear"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/helpdesk3/menu',['active' => 'setting']) ?>
<?php $this->endBlock(); ?>


    <?php Pjax::begin(); ?>
<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?=$this->render('_search', ['model' => $searchModel])?>
    </div>
</div>

    <div class="card">
        <div class="card-header bg-primary-gradient text-white">
            <div class="d-flex justify-content-between">
                <h6 class="text-white mt-2">
                    <i class="bi bi-ui-checks"></i> ทะเบียนรายการอุปกรณ์
                    <span class="badge text-bg-light"><?= $dataProvider->getTotalCount() ?></span> รายการ
                </h6>
                <div class="d-flex justify-content-between">
                    <p>
                        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['create','title' => '<i class="fa-solid fa-circle-plus"></i> สร้างใหม่'], ['class' => 'btn btn-light open-modal','data' => ['size' => 'modal-md']]) ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table
                class="table">
                <thead>
                    <tr>
                        <th class="text-center" scope="col" style="width: 5%">#ลำดับ</th>
                        <th scope="col" style="width: 15%">รหัส</th>
                        <th scope="col">ชื่อรายการ</th>
                        <th class="text-center" scope="col" style="width:120px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider align-middle">
                    <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                        <tr>
                            <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                            <td class="fw-semibold text-primary"><?= $item->code ?></td>
                            <td class="fw-semibold"><?= $item->title ?></td>

                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                        id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                        จัดการ
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                        <li><?= Html::a('<i class="bi bi-pencil me-2"></i> แก้ไข', ['update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข' . $this->title], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?></li>
                                        <li><?= Html::a('<i class="bi bi-trash me-2"></i> ลบทิ้ง', ['delete', 'id' => $item->id], ['class' => 'dropdown-item delete-item']) ?></li>
                                    </ul>
                                </div>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>


    <?php Pjax::end(); ?>