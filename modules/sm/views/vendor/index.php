<?php
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\bootstrap5\LinkPager;
use app\modules\sm\models\SupVendor;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\SupVendorSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ผู้แทนจำหน่าย';
$this->params['breadcrumbs'][] = ['label' => 'บริหารพัสดุ', 'url' => ['/sm']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('@app/modules/sm/views/default/menu',['active' => 'setting'])?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'sm-container']); ?>


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
            <h6 class="text-white mt-2"><i class="bi bi-ui-checks"></i> รายการผู้แทนจำหน่าย</h6>
            <div>
                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['create', 'name' => 'order', 'title' => ''], ['class' => 'btn btn-light shadow open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                <?= Html::a('<i class="fa-solid fa-file-import me-1"></i> Import', ['/sm/vendor/import-csv'], [
                'class' => 'btn btn-warning',
                'title' => 'นำเข้าข้อมูลจากไฟล์ .csv',
                'data' => [
                    'bs-placement' => 'top',
                    'bs-toggle' => 'tooltip',
                ],
                ]) ?>
            </div>
        </div>
    </div>

    <div class="card-body">


        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                    <th class="fw-semibold text-start">รายการ</th>
                    <th class="fw-semibold text-start">โทรศัพท์</th>
                    <th class="fw-semibold text-center">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider" id="pjax-loading" style="background-color: #f0f8ff;">
                <?php foreach($dataProvider->getModels() as $key => $item):?>
                <tr>
                    <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?>
                    </td>
                    <td>
                        <p class="fw-semibold mb-0"><?php echo $item->title?></p>
                        <p class="fs-12 mb-0"><?php echo $item->data_json['address'] ?? '-'?></p>
                    </td>
                    <td>
                        <p class="fw-semibold mb-0"><?php echo $item->data_json['phone'] ?? '-'?></p>
                    </td>

                    <td class="fw-light text-end">
                        <div class="btn-group">
                            <?=html::a('<i class="fa-solid fa-pen-to-square"></i>', ['update', 'id' => $item->id], ['class' => 'btn btn-light w-100 open-modal', 'data' => ['size' => 'modal-lg']]);;?>

                            <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                <i class="bi bi-caret-down-fill"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <?= Html::a('<i class="fa-solid fa-eye me-1"></i> แสดง',
                                            ['view','id' => $item->id],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-md']]
                                        ) ?>
                                </li>
                                <li>
                                    <?= Html::a('<i class="fa-solid fa-trash me-1"></i> ลบ',
                                            ['delete','id' => $item->id],['class' => 'dropdown-item delete-item']
                                        ) ?>
                                </li>


                            </ul>
                        </div>
                    </td>


                </tr>
                <?php endforeach;?>
            </tbody>
        </table>

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
    </div>
</div>

<?php Pjax::end(); ?>