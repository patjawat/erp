<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Order $model */
$this->title = 'ระบบพัสดุ';
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>


<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>


<div class="card">
    <div class="card-body">
        <p class="card-text">ขอซื้อขอจ้าง</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-2 col-md-4 col-sm-12">
        <?= $this->render('step') ?>
    </div>
    <div class="col-lg-8 col-md-8 col-sm-12">


        <div class="card">
            <div class="card-body">
            <table class="table table-striped-columns">
                    <tbody>
                        <tr class="">
                            <td class="text-end" style="width:150px;">เลขที่ขอซื้อ</td>
                            <td>PR-670001</td>
                            <td>ผู้ขอ</td>
                            <td>นายปัจวัฒน์ ศรีบุญเรือง</td>
                        </tr>
                        <tr class="">
                            <td class="text-end">วันที่ขอซื้อ</td>
                            <td>Item</td>
                            <td>Item</td>
                            <td>Item</td>
                        </tr>
                        <tr class="">
                            <td class="text-end">วันที่ต้องการ</td>
                            <td>Item</td>
                            <td>Item</td>
                            <td>Item</td>
                        </tr>
                    </tbody>
                </table>
               
            </div>
        </div>


        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">รายการ</th>
                                <th scope="col">หน่วย</th>
                                <th scope="col">ราคาต่อหน่วย</th>
                                <th scope="col">จำนวนเงิน</th>
                                <th class="d-flex justify-content-center gap-2"><button
                                        class="btn btn-sm btn-primary rounded-pill"><i
                                            class="fa-solid fa-circle-plus"></i> เพิ่มรายการ</button></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="">
                                <td>กระดาษ A4</td>
                                <td>รีม</td>
                                <td>240</td>
                                <td>1</td>
                                <td class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-sm btn-warning rounded-pill"><i
                                            class="fa-regular fa-pen-to-square"></i></button>
                                    <button class="btn btn-sm btn-danger rounded-pill"><i
                                            class="fa-regular fa-trash-can"></i></button>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>



    </div>
</div>