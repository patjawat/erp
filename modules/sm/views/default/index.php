<?php

/**
 * @var yii\web\View $this
 */

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'ภาพรวมระบบขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'ระบบขอซื้อ', 'url' => ['/sm']];
$this->params['breadcrumbs'][] = 'ภาพรวม';
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2  text-primary-gradient">
  <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
        </svg>
    <?= $this->title ?>
  </h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('menu', ['active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>

<div class="row">
  <div class="col-9">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <p><i class="fa-solid fa-chart-simple me-1"></i>ภาพรวมการสั่งซื้อทั้งหมด <span class="badge rounded-pill text-bg-primary"> <?= $dataProvider->getTotalCount() ?> </span> รายการ</p>
          <div class="mb-3">
            <?= $this->render('_search_year', ['model' => $searchModel]) ?></div>
        </div>
        <?= $this->render('order_summary', ['model' => $searchModel]) ?>
        <?= $this->render('order_chart_column', ['model' => $searchModel]) ?>
      </div>
    </div>

  </div>
  <div class="col-3">
    <?= $this->render('budget_balanced', ['model' => $searchModel]) ?>
  </div>
</div>
<div class="row">
  <div class="col-6">
    <div id="showPrOrderList"></div>
    <div id="showPrAcceptOrderList"></div>
  </div>
  <div class="col-6">
    <div id="showPqOrder"></div>
  </div>
</div>






<?php

use yii\widgets\Pjax;

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
<?php // Pjax::end(); 
?>