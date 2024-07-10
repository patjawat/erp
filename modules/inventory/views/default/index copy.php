<?php

/**
 * @var yii\web\View $this
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'ระบบคลัง';
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('./menu') ?>
<?php $this->endBlock(); ?>


<div class="row">
    <div class="col-8">


        <div class="row">

            <div class="col-lg-4 col-md-4 col-sm-12 col-sx-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <a href="<?= Url::to(['/hr/employees']) ?>">
                                    <span class="text-muted text-uppercase fs-6">รอรับเข้าคลัง</span>
                                </a>
                                <h2 class="text-muted text-uppercase fs-4">694 เรื่อง</h2>
                            </div>
                            <div class="text-center" style="position: relative;">
                                <div id="t-rev" style="min-height: 45px;">
                                    <div id="apexchartsdlqwjkgl"
                                        class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                                        style="width: 90px; height: 45px;">
                                        <i class="fa-solid fa fa-recycle fs-1"></i>
                                        <div class="apexcharts-legend"></div>

                                    </div>
                                </div>
                                <div class="resize-triggers">
                                    <div class="expand-trigger">
                                        <div style="width: 91px; height: 70px;"></div>
                                    </div>
                                    <div class="contract-trigger"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End-col -->

            <div class="col-lg-4 col-md-12 col-sm-12 col-sx-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <a href="#">
                                    <span class="text-muted text-uppercase fs-6">ผ่านการตรวจสอบ</span>
                                </a>
                                <h2 class="text-muted text-uppercase fs-4">50 รายการ</h2>
                            </div>

                            <div class="text-center" style="position: relative;">
                                <div id="t-rev" style="min-height: 45px;">
                                    <div id="apexchartsdlqwjkgl"
                                        class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                                        style="width: 90px; height: 45px;">
                                        <i class="fa-solid fa-cash-register fs-1"></i>
                                        <div class="apexcharts-legend"></div>

                                    </div>
                                </div>

                                <div class="resize-triggers">
                                    <div class="expand-trigger">
                                        <div style="width: 91px; height: 70px;"></div>
                                    </div>
                                    <div class="contract-trigger"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End-col -->

            <!-- End-col -->
            <div class="col-lg-4 col-md-12 col-sm-12 col-sx-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <span class="text-muted text-uppercase fs-6">อนุมัติ</span>
                                <h2 class="text-muted text-uppercase fs-4">316 รายการ</h2>
                            </div>
                            <div class="text-center" style="position: relative;">
                                <div id="t-rev" style="min-height: 45px;">
                                    <div id="apexchartsdlqwjkgl"
                                        class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                                        style="width: 90px; height: 45px;">
                                        <i class="fa-solid fa-building fs-1"></i>
                                        <div class="apexcharts-legend"></div>

                                    </div>
                                </div>

                                <div class="resize-triggers">
                                    <div class="expand-trigger">
                                        <div style="width: 91px; height: 70px;"></div>
                                    </div>
                                    <div class="contract-trigger"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End-col -->

        </div>
        <div class="card">
            <div class="card-body">
                จำนวนมูลค่าการเบิก และจ่ายวัสดุ
                <div id="sales_charts"></div>
            </div>
        </div>
        <!-- <div class="card">
                              <div class="card-body">
                              มูลค่าประเภทงบการเงิน (ย้อนหลัง 10 ปี)
                              <div id="line-stack" style="width:100%;height:550px;"></div>
                              </div>
                          </div> -->
    </div>

    <div class="col-4">


        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted text-uppercase fs-6">คลังย่อย</span>
                        <h2 class="text-muted text-uppercase fs-4">13 รายการ</h2>
                    </div>
                    <div class="text-center" style="position: relative;">
                        <div id="t-rev" style="min-height: 45px;">
                            <div id="apexchartsdlqwjkgl"
                                class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                                style="width: 90px; height: 45px;">
                                <i class="fa-solid fa-building fs-1"></i>
                                <div class="apexcharts-legend"></div>

                            </div>
                        </div>

                        <div class="resize-triggers">
                            <div class="expand-trigger">
                                <div style="width: 91px; height: 70px;"></div>
                            </div>
                            <div class="contract-trigger"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted text-uppercase fs-6">คลังย่อย</span>
                        <h2 class="text-muted text-uppercase fs-4">13 รายการ</h2>
                    </div>
                    <div class="text-center" style="position: relative;">
                        <div id="t-rev" style="min-height: 45px;">
                            <div id="apexchartsdlqwjkgl"
                                class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                                style="width: 90px; height: 45px;">
                                <i class="fa-solid fa-building fs-1"></i>
                                <div class="apexcharts-legend"></div>

                            </div>
                        </div>

                        <div class="resize-triggers">
                            <div class="expand-trigger">
                                <div style="width: 91px; height: 70px;"></div>
                            </div>
                            <div class="contract-trigger"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        


        <div class="card flex-fill">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">วัสดุจำนวนต่ำกว่ากำหนด</h4>
                <div class="dropdown">
                    <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="dropset">
                        <i class="fa fa-ellipsis-v"></i>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li>
                            <a href="productlist.html" class="dropdown-item">Product List</a>
                        </li>
                        <li>
                            <a href="addproduct.html" class="dropdown-item">Product Add</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive dataview">
                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
    
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table datatable dataTable no-footer" id="DataTables_Table_0" role="grid"
                                    aria-describedby="DataTables_Table_0_info">
                                    <thead>
                                        <tr role="row">
                                            <th class="sorting_asc" tabindex="0" aria-controls="DataTables_Table_0"
                                                rowspan="1" colspan="1" aria-sort="ascending"
                                                aria-label="Sno: activate to sort column descending"
                                                style="width: 94.5781px;">Sno</th>
                                            <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0"
                                                rowspan="1" colspan="1"
                                                aria-label="Products: activate to sort column ascending"
                                                style="width: 287.781px;">Products</th>
                                            <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0"
                                                rowspan="1" colspan="1"
                                                aria-label="Price: activate to sort column ascending"
                                                style="width: 110.391px;">Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>




                                        <tr class="odd">
                                            <td class="sorting_1">1</td>
                                            <td class="productimgname">
                                                <a href="productlist.html" class="product-img">
                                                    <img src="assets/img/product/product22.jpg" alt="product">
                                                </a>
                                                <a href="productlist.html">Apple Earpods</a>
                                            </td>
                                            <td>$891.2</td>
                                        </tr>
                                        <tr class="even">
                                            <td class="sorting_1">2</td>
                                            <td class="productimgname">
                                                <a href="productlist.html" class="product-img">
                                                    <img src="assets/img/product/product23.jpg" alt="product">
                                                </a>
                                                <a href="productlist.html">iPhone 11</a>
                                            </td>
                                            <td>$668.51</td>
                                        </tr>
                                        <tr class="odd">
                                            <td class="sorting_1">3</td>
                                            <td class="productimgname">
                                                <a href="productlist.html" class="product-img">
                                                    <img src="assets/img/product/product24.jpg" alt="product">
                                                </a>
                                                <a href="productlist.html">samsung</a>
                                            </td>
                                            <td>$522.29</td>
                                        </tr>
                                        <tr class="even">
                                            <td class="sorting_1">4</td>
                                            <td class="productimgname">
                                                <a href="productlist.html" class="product-img">
                                                    <img src="assets/img/product/product6.jpg" alt="product">
                                                </a>
                                                <a href="productlist.html">Macbook Pro</a>
                                            </td>
                                            <td>$291.01</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="DataTables_Table_0_info" role="status"
                                    aria-live="polite">Showing 1 to 4 of 4 entries</div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate">
                                    <ul class="pagination">
                                        <li class="paginate_button page-item previous disabled"
                                            id="DataTables_Table_0_previous"><a href="#"
                                                aria-controls="DataTables_Table_0" data-dt-idx="0" tabindex="0"
                                                class="page-link">Previous</a></li>
                                        <li class="paginate_button page-item active"><a href="#"
                                                aria-controls="DataTables_Table_0" data-dt-idx="1" tabindex="0"
                                                class="page-link">1</a></li>
                                        <li class="paginate_button page-item next disabled"
                                            id="DataTables_Table_0_next"><a href="#" aria-controls="DataTables_Table_0"
                                                data-dt-idx="2" tabindex="0" class="page-link">Next</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>
</div>




<div class="card mb-0">
    <div class="card-body">
        <h4 class="card-title">ขอเบิกวัสดุ</h4>
        <div class="table-responsive dataview">
            <div id="DataTables_Table_1_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                <div class="row">
                    <div class="col-sm-12 col-md-6">
                        <div class="dataTables_length" id="DataTables_Table_1_length"><label>Show <select
                                    name="DataTables_Table_1_length" aria-controls="DataTables_Table_1"
                                    class="custom-select custom-select-sm form-control form-control-sm">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select> entries</label></div>
                    </div>
                    <div class="col-sm-12 col-md-6"></div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <table class="table datatable dataTable no-footer" id="DataTables_Table_1" role="grid"
                            aria-describedby="DataTables_Table_1_info">
                            <thead>
                                <tr role="row">
                                    <th class="sorting_asc" tabindex="0" aria-controls="DataTables_Table_1" rowspan="1"
                                        colspan="1" aria-sort="ascending"
                                        aria-label="SNo: activate to sort column descending" style="width: 108.25px;">
                                        SNo</th>
                                    <th class="sorting" tabindex="0" aria-controls="DataTables_Table_1" rowspan="1"
                                        colspan="1" aria-label="Product Code: activate to sort column ascending"
                                        style="width: 247.297px;">Product Code</th>
                                    <th class="sorting" tabindex="0" aria-controls="DataTables_Table_1" rowspan="1"
                                        colspan="1" aria-label="Product Name: activate to sort column ascending"
                                        style="width: 254.141px;">Product Name</th>
                                    <th class="sorting" tabindex="0" aria-controls="DataTables_Table_1" rowspan="1"
                                        colspan="1" aria-label="Brand Name: activate to sort column ascending"
                                        style="width: 227px;">Brand Name</th>
                                    <th class="sorting" tabindex="0" aria-controls="DataTables_Table_1" rowspan="1"
                                        colspan="1" aria-label="Category Name: activate to sort column ascending"
                                        style="width: 271.109px;">Category Name</th>
                                    <th class="sorting" tabindex="0" aria-controls="DataTables_Table_1" rowspan="1"
                                        colspan="1" aria-label="Expiry Date: activate to sort column ascending"
                                        style="width: 215.203px;">Expiry Date</th>
                                </tr>
                            </thead>
                            <tbody>




                                <tr class="odd">
                                    <td class="sorting_1">1</td>
                                    <td><a href="javascript:void(0);">IT0001</a></td>
                                    <td class="productimgname">
                                        <a class="product-img" href="productlist.html">
                                            <img src="assets/img/product/product2.jpg" alt="product">
                                        </a>
                                        <a href="productlist.html">Orange</a>
                                    </td>
                                    <td>N/D</td>
                                    <td>Fruits</td>
                                    <td>12-12-2022</td>
                                </tr>
                                <tr class="even">
                                    <td class="sorting_1">2</td>
                                    <td><a href="javascript:void(0);">IT0002</a></td>
                                    <td class="productimgname">
                                        <a class="product-img" href="productlist.html">
                                            <img src="assets/img/product/product3.jpg" alt="product">
                                        </a>
                                        <a href="productlist.html">Pineapple</a>
                                    </td>
                                    <td>N/D</td>
                                    <td>Fruits</td>
                                    <td>25-11-2022</td>
                                </tr>
                                <tr class="odd">
                                    <td class="sorting_1">3</td>
                                    <td><a href="javascript:void(0);">IT0003</a></td>
                                    <td class="productimgname">
                                        <a class="product-img" href="productlist.html">
                                            <img src="assets/img/product/product4.jpg" alt="product">
                                        </a>
                                        <a href="productlist.html">Stawberry</a>
                                    </td>
                                    <td>N/D</td>
                                    <td>Fruits</td>
                                    <td>19-11-2022</td>
                                </tr>
                                <tr class="even">
                                    <td class="sorting_1">4</td>
                                    <td><a href="javascript:void(0);">IT0004</a></td>
                                    <td class="productimgname">
                                        <a class="product-img" href="productlist.html">
                                            <img src="assets/img/product/product5.jpg" alt="product">
                                        </a>
                                        <a href="productlist.html">Avocat</a>
                                    </td>
                                    <td>N/D</td>
                                    <td>Fruits</td>
                                    <td>20-11-2022</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-5">
                        <div class="dataTables_info" id="DataTables_Table_1_info" role="status" aria-live="polite">
                            Showing 1 to 4 of 4 entries</div>
                    </div>
                    <div class="col-sm-12 col-md-7">
                        <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_1_paginate">
                            <ul class="pagination">
                                <li class="paginate_button page-item previous disabled"
                                    id="DataTables_Table_1_previous"><a href="#" aria-controls="DataTables_Table_1"
                                        data-dt-idx="0" tabindex="0" class="page-link">Previous</a></li>
                                <li class="paginate_button page-item active"><a href="#"
                                        aria-controls="DataTables_Table_1" data-dt-idx="1" tabindex="0"
                                        class="page-link">1</a></li>
                                <li class="paginate_button page-item next disabled" id="DataTables_Table_1_next"><a
                                        href="#" aria-controls="DataTables_Table_1" data-dt-idx="2" tabindex="0"
                                        class="page-link">Next</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?php
use yii\web\View;

$js = <<< JS


    var options = {
        plotOptions: {
              bar: { 
                horizontal: false,
                 columnWidth: "20%", 
                 endingShape: "rounded",
                 borderRadius: 10 
                },
              
            },
            series: [
              { name: "รับเข้า", data: [50, 45, 60, 70, 50, 45, 60, 70] },
              { name: "จ่ายออก", data: [-21, -54, -45, -35, -21, -54, -45, -35] },
            ],
            // colors: ["#28C76F", "#EA5455"],
            chart: {
              type: "bar",
              height: 500,
              stacked: true,
              zoom: { enabled: true },
            },
            responsive: [
              {
                breakpoint: 280,
                options: { legend: { position: "bottom", offsetY: 0 } },
              },
            ],

            xaxis: {
              categories: [
                " Jan ",
                "feb",
                "march",
                "april",
                "may",
                "june",
                "july",
                "auguest",
              ],
            },
            legend: { position: "right", offsetY: 40 },
            fill: { opacity: 1 },
          };
          var chart = new ApexCharts(
            document.querySelector("#sales_charts"),
            options
          );
          chart.render();    
    JS;
$this->registerJS($js, View::POS_END);
?>