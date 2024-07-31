<?php

/**
 * @var yii\web\View $this
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'บริหารพัสดุ';
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

<div class="row">
    <div class="col-9">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <p>ภาพรวมการสั่งซื้อ</p>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="">
                            <?= Html::a('<i class="fa-solid fa-circle-info text-primary me-2"></i> เพิ่มเติม', ['/sm/order'], ['class' => 'dropdown-item']) ?>
                        </div>
                    </div>
                </div>
                <?= $this->render('order_summary') ?>
                <?= $this->render('order_chart_column') ?>
            </div>
        </div>

    </div>
    <div class="col-3">
        <?= $this->render('budget_balanced') ?>
    </div>
</div>
<div class="row">
    <div class="col-6">
        <?= $this->render('top_product') ?>
    </div>
    <div class="col-3">
        <?php //  $this->render('pr_order_list') ?>
        <div id="showPrOrderList"></div>
    </div>
    <div class="col-3">
                <div id="showOrderList"></div>
    </div>
</div>

<div class="row">
    <div class="col-12">
     <!-- รายการใบขอซื้อ -->
        <div id="showPqOrder"></div>
    </div>
</div>




<?php
use yii\web\View;

$PrOrderListUrl = Url::to(['/purchase/pr-order/list?status=1']);
$AcceptOrderListUrl = Url::to(['/purchase/pr-order/accept-order-list']);
$ListPqOrderUrl = Url::to(['/purchase/pq-order']);
$js = <<< JS
         
         getPQOrderList() 
         getPrOrderList()  
         getAcceptOrderList()
         async function getPQOrderList()
         {
            await \$.ajax({
                type: "get",
                url: "$ListPqOrderUrl",
                dataType: "json",
                success: function (res) {
                    \$('#showPqOrder').html(res.content)
                }
            });
         }

         async function getPrOrderList()
         {
            await \$.ajax({
                type: "get",
                url: "$PrOrderListUrl",
                dataType: "json",
                success: function (res) {
                    \$('#showPrOrderList').html(res.content)
                }
            });
         }

         async function getAcceptOrderList()
         {
            await \$.ajax({
                type: "get",
                url: "$AcceptOrderListUrl",
                dataType: "json",
                success: function (res) {
                    \$('#showOrderList').html(res.content)
                }
            });
         }


    var options = {
              series: [{
              name: 'มูลคาการจัดซื้อจัดจ้างตามแผน',
              data: [2.0, 4.9, 7.0, 23.2, 25.6, 76.7, 135.6, 162.2]
            }, {
              name: 'มูลคาการจัดซื้อจัดจ้างทั้งหมด',
              data: [2.6, 5.9, 9.0, 26.4, 28.7, 70.7, 175.6, 182.2]
            }],
              chart: {
              type: 'bar',
              height: 350,
              fontFamily: 'kanit,sans-serif',
                  
            },
            plotOptions: {
              bar: {
                horizontal: false,
                columnWidth: '55%',
                endingShape: 'rounded'
              },
            },
            dataLabels: {
              enabled: false
            },
            stroke: {
              show: true,
              width: 2,
              colors: ['transparent']
            },
            xaxis: {
              categories: ['ปี 2563','ปี 2564','ปี 2565','ปี 2566','ปี 2567','ปี 2568','ปี 2569','ปี 2570'],
            },
            yaxis: {
              title: {
                text: 'มูลค่า (ล้านบาท)'
              }
            },
            fill: {
              opacity: 1
            },
            tooltip: {
              y: {
                formatter: function (val) {
                  return  val + " ล้านบาท"
                }
              }
            }
            };

            var chart = new ApexCharts(document.querySelector("#line-chart-container"), options);
            chart.render();



    JS;
$this->registerJS($js, View::POS_END);
?>