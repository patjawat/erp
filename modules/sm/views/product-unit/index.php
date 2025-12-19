<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\sm\models\ProductType;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductTypeSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ตั้งค่าหน่วยนับ';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2  text-primary-gradient">
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


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
        </div>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>



<?php Pjax::begin(['enablePushState' => false]); ?>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white mt-2">
                <i class="bi bi-ui-checks"></i> ทะเบียน<?= $this->title ?>
                <span class="badge text-bg-light"> <?= $dataProvider->getTotalCount() ?></span> รายการ
            </h6>
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/sm/product-unit/create', 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> สร้างใหม่'], ['class' => 'btn btn-light open-modal', 'data' => ['size' => 'modal-md']]) ?>

        </div>
    </div>

    <div class="card-body">
        <table class="table table-striped custom-table">
            <thead>
                <tr>
                    <th scope="col">รายการ</th>
                    <th scope="col" style="width:100px;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr class="">
                        <td scope="row"><?= $model->title ?></td>
                        <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                            id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                            จัดการ
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/sm/product-unit/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?></li>
                                            <li><?= Html::a('<i class="fa-solid fa-trash me-1"></i> ลบทิ้ง', ['/sm/product-unit/delete', 'id' => $model->id], ['class' => 'dropdown-item delete-item']) ?></li>
                                        </ul>
                                    </div>
                                </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="d-flex justify-content-center">
            <div class="text-muted">
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
</div>


<?php Pjax::end(); ?>