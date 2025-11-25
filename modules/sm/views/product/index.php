<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap5\LinkPager;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'วัสดุ';
$this->params['breadcrumbs'][] = $this->title;
//  sql update ยาและเวชภัณฑ์
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
<?= $this->render('@app/modules/sm/views/default/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>


<?php Pjax::begin(['id' => 'sm-container']); ?>
<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
            <div class="dropdown float-end btn btn-sm btn-light">
                <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-gear fs-5"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <?= Html::a('<i class="bi bi-grid-fill me-1"></i>  ประเภทวัสดุ', ['/sm/product-type', 'title' => '<i class="bi bi-grid-fill"></i> ประเภทวัสดุ'], ['class' => 'dropdown-item open-modal-x', 'data' => ['size' => 'modal-md', 'pjax' => false]]) ?>
                    <?= Html::a('<i class="bi bi-grid-fill me-1"></i>  หน่วยนับ', ['/sm/product-unit', 'title' => 'หน่วยนับ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2">
            <i class="bi bi-ui-checks"></i> รายการ<?= $this->title ?> 
            <?= number_format($dataProvider->getTotalCount()) ?> รายการ
        </h6>
    </div>

    <div class="card-body">
        <!-- เพิ่ม table-responsive -->
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle custom-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width:30px">ลำดับ</th>
                        <th class="text-center" style="min-width: 150px;">หมวดหมู่</th>
                        <th class="text-center" style="min-width: 150px;">ประเภทวัสดุ</th>
                        <th style="min-width: 350px;">รายการวัสดุ</th>
                        <th style="min-width: 100px;">หน่วยนับ</th>
                        <th style="min-width: 120px;">บัญชีนวัตกรรม</th>
                        <th class="text-center" style="min-width: 100px;">จำนวนสูงสุด</th>
                        <th class="text-center" style="min-width: 100px;">จำนวนต่ำสุด</th>
                        <th class="text-center" style="min-width: 100px;">สถานะ</th>

                        <!--ใช้ min-width ป้องกันการบีบ -->
                        <th class="text-center" style="min-width: 130px;">จัดการ</th>
                    </tr>
                </thead>

                <tbody class="table-group-divider">
                    <?php foreach ($dataProvider->getModels() as $key => $model): ?>
                        <tr>
                            <td class="text-center">
                                <?= (($dataProvider->pagination->offset + 1) + $key) ?>
                            </td>

                            <td class="text-center"><?= $model->ViewTypeName()['title'] ?></td>
                            <td class="text-center"><?= $model->data_json['metter_type'] ?? '-' ?></td>
                            <td><?= $model->Avatar() ?></td>
                            <td><?= $model->data_json['unit'] ?? '-' ?></td>

                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center">
                                    <input class="form-check-input" type="checkbox"
                                           <?= ($model->data_json['innovation_account'] ?? 0) == 1 ? 'checked' : '' ?>>
                                </div>
                            </td>

                            <td class="text-center"><?= $model->qty_max ?></td>
                            <td class="text-center"><?= $model->qty_min ?></td>

                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center">
                                    <input class="form-check-input" type="checkbox"
                                           <?= $model->active == 1 ? 'checked' : '' ?>>
                                </div>
                            </td>

                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                            type="button" data-bs-toggle="dropdown">
                                        จัดการ
                                    </button>

                                    <ul class="dropdown-menu">
                                        <li><?= Html::a('<i class="fa-solid fa-eye me-1"></i> แสดง', ['/sm/product/view', 'id' => $model->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                                        <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/sm/product/update', 'id' => $model->id,'title' => '<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไขวัสดุ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                                        <li><?= Html::a('<i class="fa-solid fa-trash me-1"></i> ลบทิ้ง', ['/sm/product/delete', 'id' => $model->id], ['class' => 'dropdown-item delete-item']) ?></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            <?= LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => ['class' => 'pagination pagination-sm'],
            ]) ?>
        </div>

    </div>
</div>

<?php Pjax::end(); ?>

<?php
$chageActiveUrl = Url::to(['/sm/product/set-active']);
$js = <<< JS
        $("body").on("change", ".form-check-input", function (e) {

          var id = $(this).attr('id');
          $.ajax({
            type: "post",
            url: "$chageActiveUrl",
            data:{
              id:id
            },
            dataType: "json",
            success: function (res) {
              if(res.status == 'success'){
              success()
                 $.pjax.reload({container:res.container, history:false});
              }
            }
          });
          
                        if ($(this).is(':checked')) {
                            // alert('Checkbox is checked!');
                        } else {
                            // alert('Checkbox is unchecked!');
                        }
                    });

              
JS;
$this->registerJS($js)
?>