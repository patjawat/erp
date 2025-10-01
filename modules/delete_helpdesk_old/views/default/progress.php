<?php
use yii\web\View;
use yii\helpers\Url;
use app\modules\helpdesk\models\Helpdesk;

$total = Helpdesk::find()->where(['in','status',[1,2,3,4]])
->andFilterWhere(['repair_group' => $model->repair_group])
->andFilterWhere(['thai_year' => $model->thai_year])
->count();
$status2 = Helpdesk::find()->where(['in','status',[1,2,3]])
->andFilterWhere(['repair_group' => $model->repair_group])
->andFilterWhere(['thai_year' => $model->thai_year])
->count();
$status4 = Helpdesk::find()->where(['status' => 4])
->andFilterWhere(['repair_group' => $model->repair_group])
->andFilterWhere(['thai_year' => $model->thai_year])
->count();
try {
    $percen = ROUND(((($total - $status2) / $total)* 100),0);
} catch (\Throwable $th) {
    $percen = 0;
}
?>

<div class="card" style="height: 378px;">
    <div class="card-body">
        <div id="leaveChart"></div>
        <div class="d-flex">
            <div class="flex-fill border-primary border-end" style="width: 129px;">
                <div class="d-flex flex-column align-items-center justify-content-start">
                    <div class="position-relative">
                        <div class="d-flex flex-column">
                            <span class="h5">อยู่ในกระบวนการ</span>
                            <span class="text-center text-muted mb-0"><?=number_format($status2,0);?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex-fill">
                <div class="d-flex flex-column align-items-center justify-content-start">
                    <div class="position-relative">
                        <div class="d-flex flex-column">
                            <span class="h5">เสร็จสิ้น</span>
                            <span class="text-muted mb-0 text-center"><?=number_format($status4,0);?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php

$url = Url::to(['/helpdesk/repair']);
$js = <<< JS

    var optionsleaveChart = {
          series: [$percen],
          chart: {
          height: 250,
          fontFamily: 'Prompt, sans-serif',
          type: 'radialBar',
          dropShadow: {
        enabled: false,
        enabledOnSeries: undefined,
        top: 0,
        left: 0,
        blur: 3,
        color: '#000',
        opacity: 0.35
    }
        },
        plotOptions: {
            radialBar: {
                hollow: {
                    margin: 15,
                    size: '85%' // 
                },
                track: {
                dropShadow: {
                    enabled: true,
                    top: 2,
                    left: 5,
                    blur: 10,
                    opacity: 0.05,
                }
            },
                dataLabels: {
                    showOn: 'always',
                    name: {
                        offsetY: -10,
                        show: true,
                        color: '#888',
                        fontSize: '16px',
                        fontWeight:'300'
                    },
                    value: {
                        color: '#111',
                        fontSize: '32px',
                        show: true
                    }
                }
            }
        },
        stroke: {
            lineCap: 'round'
        },
        labels: ['เป้าหมาย'],
        };

var chart = new ApexCharts(document.querySelector("#leaveChart"), optionsleaveChart);
chart.render();


JS;
$this->registerJS($js,View::POS_END);
?>