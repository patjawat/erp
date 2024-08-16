<?php

/**
 * @var yii\web\View $this
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

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
<?php // Pjax::begin(['id' => 'purchase-container']); ?>
<div class="row">
    <div class="col-9">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <p><i class="fa-solid fa-chart-simple me-1"></i>ภาพรวมการสั่งซื้อ</p>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
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
<?php // yii\widgets\Pjax::begin(['id' => 'order','timeout' => 50000 ]); ?>
<?php //yii\widgets\Pjax::begin(['id' => 'order-list','timeout' => 50000,'enablePushState' => true ]); ?>
<div class="row">
    <div class="col-6">
    <?php  // yii\widgets\Pjax::begin(['enablePushState' => false ]); ?>
      <div id="showPrOrderList"></div>
      <?php  // yii\widgets\Pjax::end(); ?>
      <div id="showPrAcceptOrderList"></div>
    </div>
    <div class="col-6">
      <div id="showPqOrder"></div>
        <?php //  $this->render('pr_order_list') ?>
    </div>
</div>






<?php
use yii\web\View;

$PrOrderListUrl = Url::to(['/sm/default/pr-order']);
$PrAcceptOrderListUrl = Url::to(['/sm/default/accept-pr-order']);
$PqOrderListUrl = Url::to(['/sm/default/pq-order']);
$AcceptOrderListUrl = Url::to(['/purchase/pr-order/accept-order-list']);
$ListPqOrderUrl = Url::to(['/purchase/pq-order']);
$js = <<< JS
         
         getPrOrderList()  
         getAcceptPrOrderList()
         getPQOrderList() 

         async function getPQOrderList()
         {
            await \$.ajax({
                type: "get",
                // url: "$ListPqOrderUrl",
                url: "$PqOrderListUrl",
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

         async function getAcceptPrOrderList()
         {
            await \$.ajax({
                type: "get",
                url: "$PrAcceptOrderListUrl",
                dataType: "json",
                success: function (res) {
                    \$('#showPrAcceptOrderList').html(res.content)
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
<?php // Pjax::end(); ?>