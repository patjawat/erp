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
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
  <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings-icon lucide-settings">
      <path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915"></path>
      <circle cx="12" cy="12" r="3"></circle>
    </svg>
    <?= $this->title ?>
  </h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
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
                    <th class="text-center" style="width:30px">ลำดับ</th>
                    <th class="fw-semibold text-start">รายการ</th>
                    <th class="fw-semibold text-start">โทรศัพท์</th>
                    <th class="fw-semibold text-center">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider" id="pjax-loading" style="background-color: #f0f8ff;">
                <?php foreach($dataProvider->getModels() as $key => $item):?>
                <tr>
                    <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1)+$key)?>
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