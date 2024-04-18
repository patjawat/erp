
<style>

</style>
<div class="card rounded-4 border-0 h-100">
            <div class="card-body">
<div id="pie-chart-container" style="width:100%;height:390px;"></div>


<!-- <div id="main" style="width: 600px;height:400px;"></div> -->
</div>
        </div>
<?php
use yii\web\View;
$js = <<< JS




var dom = document.getElementById('pie-chart-container');
var myChart = echarts.init(dom, null, {
  renderer: 'canvas',
  useDirtyRect: false
});
var app = {};

var option;

option = {
  tooltip: {
    trigger: 'item'
  },
  legend: {
    top: '5%',
    left: 'center'
  },
  series: [
    {
      name: 'Access From',
      type: 'pie',
      radius: ['40%', '70%'],
      avoidLabelOverlap: false,
      itemStyle: {
        borderRadius: 10,
        borderColor: '#fff',
        borderWidth: 2
      },
      label: {
        show: false,
        position: 'center'
      },
      emphasis: {
        label: {
          show: true,
          fontSize: 40,
          fontWeight: 'bold'
        }
      },
      labelLine: {
        show: false
      },
      data: [
        { value: 1048, name: 'แพทย์' },
        { value: 735, name: 'พยาบาล' },
        { value: 580, name: 'เภสัชกร' },
        { value: 484, name: 'บัญชี' },
        { value: 300, name: 'นักวิชาการคอมพิวเตอร์' }
      ]
    }
  ]
};

if (option && typeof option === 'object') {
  myChart.setOption(option);
}

window.addEventListener('resize', myChart.resize);

JS;
$this->registerJS($js,View::POS_END);
?>
