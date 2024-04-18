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

<div class="card">
              <div class="card-body">
                <div class="invoice-title">
                  <h4 class="float-end fs-16">เลขที่สั่ง # PO6701180</h4>
                  <div class="mb-4">
                    <img src="#" alt="logo" height="20">
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-sm-6">
                    <address>
                      <strong>ร้านค้าผู้จำหน่าย:</strong><br>
                      บริษัท ออฟฟิศ ดี จำกัด สาขาที่ 00001<br>
                      <strong>ใบเสนอราคาเลขที่:</strong><br>
                     15417441-58<br>
                  
                    </address>
                  </div>
                  <div class="col-sm-6 text-sm-end">
                    <address class="mt-2 mt-sm-0">
                      <strong>เลขที่สั่งซื้ออ้างอิง:</strong><br>
                      67-1196<br>
                      <strong>การรับประกัน:</strong><br>
                      1 ปี 2 เดือน<br>
                      <strong>ผู้สั่งซื้อ:</strong><br>
                      จิระ ทันนา
                    </address>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-6 mt-3">
                    <address>
                      <strong>ผู้รับใบสั่งซื้อ</strong><br>
                      นางสาวกัญญาลักษณ์ ประเสริฐจุมพล<br>
                      jDoe@example.com
                    </address>
                  </div>
                  <div class="col-sm-6 mt-3 text-sm-end">
                    <address>
                      <strong>ลงวันที่ :</strong><br>
                      22-02-2024<br><br>
                    </address>
                  </div>
                </div>
                <div class="py-2 mt-3">
                  <h3 class="fs-15 fw-bold">Order summary</h3>
                </div>
                <div class="table-responsive">
                <table class="table mt-4 table-centered">
                    <thead class="table-dark">
                      <tr>
                        <th>#</th>
                        <th>รายการ</th>
                        <th>จำนวน</th>
                        <th>ราคาต่อหน่วย</th>
                        <th class="text-end">ราคา</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>1</td>
                        <td>
                          <h5 class="mb-2">	โทรศัพท์ภายในไร้สาย</h5>
                         
                        </td>
                        <td>1 เครื่อง</td>
                        <td>1,490.00</td>
                        <td class="text-end">1,490.00</td>
                      </tr>

                       <tr>
                        <td>2</td>
                        <td>
                          <h5 class="mb-2">โทรศัพท์ ธรรมดา</h5>
                         
                        </td>
                        <td>1 เครื่อง</td>
                        <td>1,590.00</td>
                        <td class="text-end">1,590.00</td>
                      </tr>
            
                      <!-- <tr>
                        <td colspan="4" class="text-end">Sub Total</td>
                        <td class="text-end">$8080.00</td>
                      </tr>
                      <tr>
                        <td colspan="4" class="border-0 text-end">
                          <strong>Shipping</strong></td>
                        <td class="border-0 text-end">$20.00</td>
                      </tr> -->
                      <tr>
                        <td colspan="4" class="border-0 text-end">
                          <strong>ราคารวม</strong></td>
                        <td class="border-0 text-end">
                          <h4 class="m-0">3,080.00</h4>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="d-print-none">
                  <div class="float-end">
                    <a href="javascript:window.print()" class="btn btn-info waves-effect waves-light me-1"><i class="bx bxs-printer"></i> Print</a>
                    <a href="javascript:void(0)" class="btn btn-primary w-md waves-effect waves-light" data-effect="wave">Send</a>
                  </div>
                </div>
              </div>
            </div>