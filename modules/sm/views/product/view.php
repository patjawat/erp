<?php

use app\models\Categorise;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Product $model */
$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<!-- <div class="card">
    <div class="card-body">
        <?= Html::a('Update', ['update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square text-warning"></i> แก้ไข'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </div>
</div> -->


<div class="row">
    <div class="col-4">
        <?= Html::img($model->ShowImg(), ['class' => ' card-img-top', 'style' => 'max-width:100%']) ?>
    </div>
    <div class="col-8">
        <div class="card">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex flex-column">
                        <span class="h6">วัสดุ</span>
                    </div>
                    <div class="dropdown float-end px-2 py-1  bg-light rounded">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <!-- <i class="fa-solid fa-ellipsis"></i> -->
                            <i class="bi bi-three-dots"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="">
                            <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/sm/product/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขเพิ่มสินค้า/บริการ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                            <?= Html::a('<i class="bx bx-trash me-1"></i> ลบ', ['/sm/asset-type/delete', 'id' => $model->id], [
                                'class' => 'dropdown-item  delete-item',
                            ]) ?>
                        </div>
                    </div>
                </div>


                <div class="table-responsive">
                    <table class="table table-striped-columns">
                        <tbody>
                            <tr class="">
                                <td class="fw-semibold">ชื่อ</td>
                                <td colspan="5"><?= $model->title ?></td>

                            </tr>
                            <tr class="">
                                <td class="fw-semibold">ประเภท</td>
                                <td><?= isset($model->productType->title) ? $model->productType->title : '-' ?></td>
                                <td class="fw-semibold">คงเหลือ</td>
                                <td>0</td>
                                <td class="fw-semibold">หน่วยนับหลัก</td>
                                <td><?= isset($model->data_json['unit']) ? $model->data_json['unit'] : '-' ?></td>
                            </tr>
                            <tr class="">
                                <td class="fw-semibold">ราคาล่าสุด</td>
                                <td colspan="5">0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>



            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex flex-column">
                                <span class="h6"><i class="bi bi-box"></i> หน่วยบรรจุ</span>
                            </div>
                            <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> เพิ่มใหม่', ['/sm/product-unit/create', 'product_id' => $model->id, 'title' => '<i class="bi bi-box"></i> เพิ่มหน่วยบรรจุ'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                        </div>
                        <table class="table table-primary">
                            <thead>
                                <tr>
                                    <th>ขนาด</th>
                                    <th>จำนวน</th>
                                    <th class="align-middle text-center" style="width:100px">ดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($model->ListProductUnit() as $unit): ?>
                                <tr class="">
                                    <td><?= $unit->title ?></td>
                                    <td><?= $unit->qty; ?></td>
                                    <td class="align-middle gap-2">
                                        <div class="d-flex justify-content-center gap-2">
                                            <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/sm/product-unit/update', 'id' => $unit->id, 'title' => '<i class="bi bi-box"></i> แก้ไขหน่วยบรรจุ'], ['class' => 'btn btn-sm btn-warning rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                            <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['/sm/order/delete-item', 'id' => $unit->id], ['class' => 'btn btn-sm btn-danger rounded-pill delete-item']) ?>
                                        </div>

                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

        </div>


    </div>
</div>

<div class="d-flex justify-content-center gap-2">
    <?= Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['/sm/product/update','id' => $model->id,'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> แก้ไข'], ['class' => 'btn btn-warning open-modal', 'data' => ['size' => 'modal-lg']]) ?>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
    <i class="fa-regular fa-circle-xmark"></i>  ปิด
    </button>

</div>