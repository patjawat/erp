<?php

use app\components\widgets\DataSummaryWidget;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'วัสดุ';
$this->params['breadcrumbs'][] = $this->title;
//  sql update ยาและเวชภัณฑ์
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
<?= $this->render('@app/modules/sm/views/default/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>


<?php Pjax::begin(['id' => 'sm-container']); ?>
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between">
            <h6 class="mt-2"><i data-lucide="search"></i> การค้นหา</h6>
        </div>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between text-white">
        <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2 text-body">
            <div class="bg-primary bg-opacity-10 text-primary rounded-pill">
            </div>
            <i data-lucide="file-text"></i>
            ทะเบียนวัสดุ
        </h6>
        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/sm/product/create', 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เพิ่มวัสดุใหม่'], ['class' => 'btn btn-light open-modal', 'data' => ['size' => 'modal-lg']]) ?>
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
                                        <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/sm/product/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไขวัสดุ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                                        <li><?= Html::a('<i class="fa-solid fa-trash me-1"></i> ลบทิ้ง', ['/sm/product/delete', 'id' => $model->id], ['class' => 'dropdown-item delete-item']) ?></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>


    </div>
    <div class="card-footer bg-body border-top py-3 px-4">
        <?php
        echo DataSummaryWidget::widget([
            'dataProvider' => $dataProvider,
            'pagerOptions' => [],
        ]);
        ?>
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