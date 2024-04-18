<div id="chart">
</div>
<hr>

<?php
use yii\helpers\Url;
$url = Url::to(['/profile/history']);
$js = <<< JS

$.ajax({
    type: "get",
    url: "$url",
    dataType: "json",
    success: function (res) {
        

var options = {
  chart: {
    height: 280,
    // type: "area"
    type: "bar"
  },
  dataLabels: {
    enabled: false
  },
  series: [
    {
      name: "Series 1",
      data: res.data
    }
  ],
  plotOptions: {
          bar: {
            borderRadius: 10,
            columnWidth: '50%',
          }
        },
  fill: {
          type: 'gradient',
          gradient: {
            shade: 'light',
            type: "horizontal",
            shadeIntensity: 0.25,
            gradientToColors: undefined,
            inverseColors: true,
            opacityFrom: 0.85,
            opacityTo: 0.85,
            stops: [50, 0, 100]
          },
        },
  xaxis: {
    categories: res.categories
  },
  
};

var chart = new ApexCharts(document.querySelector("#chart"), options);

chart.render();
}
});

JS;
$this->registerJS($js);
?>