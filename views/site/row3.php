<?php
use app\components\CategoriseHelper;
?>
<div class="row">
    <!-- Begin recent orders -->
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="card-title"><i class="fa-solid fa-circle-exclamation text-warning"></i> ทรัพย์ขายทอดตลาด</h5>
                
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ประเภท</th>
                                <th>จำนวน</th>
                            </tr>
                        </thead>
                        <tbody>
							<?php foreach(CategoriseHelper::Categorise('asset_type') as $key => $assetType):?>
                            <tr>
                                <td><?=$key+1?></b></td>
                                <td><?=$assetType->title?></td>
                                <td>0</td>
                            </tr>
                          <?php endforeach;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div><!-- End recent orders -->
<div class="col-4">

<div class="card">
	<div class="card-body" id="pieChart3">
	</div>
</div>

</div>
</div>



<?php
use yii\web\View;
$js = <<< JS
var options = {
    series: [35, 65],
    chart: {
        width: 450,
        type: 'pie',
    },
    legend: {
        position: 'bottom'
    },
    dataLabels: {
        enabled: true,
        offsetX: 50,
        offsetY: 50
    },
    colors: ['#ffb01a', '#0866ad'],
    labels: ['ขายทอดตลาด','ใช้งานปกติ']
};

var chart = new ApexCharts(document.querySelector("#pieChart3"), options);

chart.render();

JS;
$this->registerJS($js,View::POS_END);
?>