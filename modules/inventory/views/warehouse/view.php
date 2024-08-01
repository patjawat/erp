<?php

/**
 * @var yii\web\View $this
 */

use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Php;
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\inventory\models\Stock;
use app\models\Categorise;

$this->title = $model->warehouse_name;
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-6 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h4 class="card-title">ปริมาณรวม</h4>
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
                <div id="inventoryCharts"></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-sm-12">

        <div class="d-flex justify-conent-betwee gap-3">
            <div class="card w-100">
                <div class="card-body">
                    <h4 class="card-title">100 เรื่อง</h4>
                    <p class="card-text">จำนวนการขอเบิกวัสดุ</p>
                </div>
            </div>
        </div>
        <div class="d-flex justify-conent-betwee gap-3">
            <div class="card w-50">
                <div class="card-body">
                    <h4 class="card-title">50 เรื่อง</h4>
                    <p class="card-text">หัวหน้าหน่วยงานเห็นชอบ</p>
                </div>
            </div>
            <div class="card w-50">
                <div class="card-body">
                    <h4 class="card-title">50 เรื่อง</h4>
                    <p class="card-text">พัสดุตรวจสอบ</p>
                </div>
            </div>
        </div>
        
        <div class="card">
          <div class="card-body">
          <h6 class="card-title">วัสดุ</h6>
          <?php
          $warehouse = Yii::$app->session->get('warehouse');
          $models = Stock::find()
          ->select(['p.id','stock.asset_item', 'sum(stock.qty) as sum_qty'])
          ->join('INNER JOIN', 'categorise p', 'p.id = stock.asset_item')
          ->where(['stock.to_warehouse_id' => $warehouse['warehouse_id']])
          ->groupBy('p.id')
          ->all();
          
          ?>
          <div
            class="table-responsive"
          >
            <table
              class="table table-primary"
            >
              <thead>
                <tr>
                  <th scope="col">รายการ</th>
                  <th scope="col">คงเหลือ</th>
                  <th scope="col">ดำเนินการ</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($models as $model):?>
                <tr class="">
                  <td scope="row"><?=$model->getProduct()->Avatar()?></td>
                  <td><?=$model['sum_qty']?></td>
                  <td>
                    <?=Html::a('<i class="bi bi-clock-history"></i>',['/inventory/stock-movement/product','id' => $model->id])?>
                  </td>
                </tr>
                <?php endforeach;?>
              </tbody>
            </table>
          </div>
          


          </div>
          <!-- End Card body -->
        </div>
        <!-- End Card -->
        

    </div>
    <!-- end col-6 -->
</div>

<div class="row">
    <div class="col-12">
        <?= $this->render(
          'list_request',
          ['model' => $model]
        ) ?>
    </div>
</div>

<?php
use yii\web\View;

$js = <<< JS


  var options = {
      plotOptions: {
            bar: { 
              horizontal: false,
               columnWidth: "50%", 
               endingShape: "rounded",
               startingShape: 'rounded',
               borderRadius: 10 
              },
          },
          series: [
            { name: "เบิก", data: [50, 45, 60, 70, 50, 45, 60, 70,30,40,23,19] },
            { name: "จ่าย", data: [-21, -54, -45, -35, -21, -54, -45, -35,-87,-40,-23,-34] },
          ],
          colors: ["#0966ad", "#EA5455"],
          chart: {
            type: "bar",
            height: 500,
            stacked: true,
            zoom: { enabled: true },
          },
          responsive: [
            {
              breakpoint: 280,
              options: { legend: { position: "top", offsetY: 0 } },
            },
          ],

          xaxis: {
            categories: [
              "ม.ค.",
              "ก.พ.",
              "มี.ค.",
              "เม.ย.",
              "พ.ย.",
              "มิ.ย.",
              "ก.ค.",
              "ส.ค.",
              "ก.ย.",
              "ต.ค.",
              "พ.ย.",
              "ธ.ค.",
            ],
          },
          legend: { position: "bottom"},
          fill: { opacity: 1 },
        };
        var chart = new ApexCharts(
          document.querySelector("#inventoryCharts"),
          options
        );
        chart.render();    
  JS;
$this->registerJS($js, View::POS_END);
?>