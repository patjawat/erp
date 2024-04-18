<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */

$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>


<div class="row">
    <div class="col-lg-8 col-xl-9">
        <div class="card">
            <div class="card-header py-3 bg-default rounded-top">
                <h5 class=" mb-0">ข้อมูลจัดซื้อจัดจ้าง</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-centered mb-0 table-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>รายการ</th>
                                <th>รายละเอียดเพิ่มเติม</th>
                                <th>Price</th>
                                <th style="min-width:140px;width:140px">Quantity</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>

                            <tr>
                                <td>
                                    เครื่องคอมพิวเตอร์
                                </td>
                                <td>
                                    <p class="text-muted mb-2 text-truncate">
                                        Color Block Men Round Neck Half sleeve T-Shirt
                                    </p>

                                </td>
                                <td class="base-price" data-price="500">
                                    <b>$500</b>
                                </td>
                                <td>
                                    <div class="input-group bootstrap-touchspin">
                                        <span
                                            class="input-group-btn input-group-prepend bootstrap-touchspin-injected"><button
                                                class="btn btn-primary bootstrap-touchspin-down"
                                                type="button">-</button></span><input type="text" value="02" name="qty"
                                            class="touchspin-qty form-control"><span
                                            class="input-group-btn input-group-append bootstrap-touchspin-injected"><button
                                                class="btn btn-primary bootstrap-touchspin-up"
                                                type="button">+</button></span>
                                    </div>
                                </td>
                                <td class="total-price">
                                    <b>$1000</b>
                                </td>
                                <td data-id="3">
                                    <a href="javascript:void(0);"
                                        class="avatar avatar-sm me-0 bg-soft-danger text-danger remove-cart-item">
                                        <i class="mdi mdi-close fs-sm"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    ปริ้นเตอร์ Laser
                                </td>
                                <td>

                                    <p class="text-muted mb-2 text-truncate">
                                        Color Block Men Round Neck Half sleeve T-Shirt
                                    </p>

                                </td>
                                <td class="base-price" data-price="600">
                                    <b>$600</b>
                                </td>
                                <td>
                                    <div class="input-group bootstrap-touchspin">
                                        <span
                                            class="input-group-btn input-group-prepend bootstrap-touchspin-injected"><button
                                                class="btn btn-primary bootstrap-touchspin-down"
                                                type="button">-</button></span><input type="text" value="01" name="qty"
                                            class="touchspin-qty form-control"><span
                                            class="input-group-btn input-group-append bootstrap-touchspin-injected"><button
                                                class="btn btn-primary bootstrap-touchspin-up"
                                                type="button">+</button></span>
                                    </div>
                                </td>
                                <td class="total-price">
                                    <b>$600</b>
                                </td>
                                <td data-id="4">
                                    <a href="javascript:void(0);"
                                        class="avatar avatar-sm me-0 bg-soft-danger text-danger remove-cart-item">
                                        <i class="mdi mdi-close fs-sm"></i></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="row mt-4">
                    <div class="col-sm-6">
                        <a href="ecommerce-product.html" class="btn btn-secondary waves-effect waves-light"
                            data-effect="wave">
                            <i class="fa-solid fa-trash-can"></i> ยกเลิกใบสั้งซื้อ </a>
                    </div> <!-- end col -->
                    <div class="col-sm-6">
                        <div class="text-sm-end mt-2 mt-sm-0">
                            <a href="ecommerce-checkout.html" class="btn btn-success waves-effect waves-light"
                                data-effect="wave">
                                <i class="mdi mdi-cart me-1"></i> <i class="bi bi-check2-circle"></i> ดำเนินการต่อ </a>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row-->
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-xl-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">#เลขที่ขอ RE20240200009</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table mb-0 table-borderless table-no-head">
                        <tbody>
                            <tr>
                                <td>Grand Total :</td>
                                <td>$1,857</td>
                            </tr>
                            <tr>
                                <td>Discount : </td>
                                <td class="text-danger">- $157</td>
                            </tr>
                            <tr>
                                <td>Shipping Charge :</td>
                                <td>$25</td>
                            </tr>
                            <tr>
                                <td>Estimated Tax : </td>
                                <td>$19.22</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Total :</td>
                                <td class="fw-bold">$1744.22</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-warning mt-3 fs-13" role="alert" id="dis-coupon">
                    สถานะ <strong>ขอเบิก</strong>
                </div>
                <div class="alert alert-info mt-3" role="alert" id="coupon-applied" style="display: none;">
                    Coupon Applied
                </div>
                
             <div class="d-grid gap-2">
                <button
                    type="button"
                    name=""
                    id=""
                    class="btn btn-primary"
                >
                <i class="fa-solid fa-print"></i> พิมพ์
                </button>
             </div>
             
                <!-- end table-responsive -->
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="d-flex mb-2 mt-0">
                    <img class="avatar rounded-circle" src="/img/patjwat2.png" width="20" alt="">
                    <div class="flex-grow-1 overflow-hidden">
                        <h6 class="mb-0 text-truncate">
                            <a href="/hr/workgroup/view?id=4670" class="text-dark">ผู้ขอเบิก
                            </a>
                        </h6>
                        <p class="text-muted mb-0">นายปัจวัฒน์ ศรีบุญเรือง</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h6 class="fs-16 fw-bold mb-3">
                    <i class="bx bx-mobile-alt align-middle fs-sm text-primary"></i>
                    <span class="align-middle">โทร : 0909748044</span>
                </h6>
                <!-- <p class="mb-0"></p> -->
            </div>
        </div>
        <!-- end card -->
    </div>
</div>