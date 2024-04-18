<style>
    #card {
  max-width: 290px;
  margin: 35px auto;
  padding-top: 0px;
  text-align: center;
}

h2 {
  font-size: 18px;
  margin-bottom: 0;
  color: rgb(192, 192, 192);
}
h1 {
  font-size: 22px;
  margin-top: 0;
  color: #333;
}

.footer {
  position: relative;
  bottom: 16px;
  left: 0;
  right: 0;
  font-size: 14px;
  text-align: center;
}

</style>
<div id="card">
  <h2>Calories</h2>
  <h1>Monday, Oct 4, 2016</h1>
  <div id="radial-chart">

  </div>

</div>


<?php
use yii\web\View;
$js = <<< JS
var options = {
  chart: {
    height: 220,
    type: "radialBar",
    dropShadow: {
      enabled: true,
      enabledSeries: undefined,
      top: 5,
      left: 2,
      blur: 10,
      opacity: 0.25
    }
  },
  plotOptions: {
    radialBar: {
      hollow: {
        margin: 0,
        size: "80%",
        background: "#fff",
        position: "front"
      },
      track: {
        background: "#e7e7e7",
        strokeWidth: "97%",
        margin: 5, // margin is in pixels
        dropShadow: {
          enabled: false,
          top: 0,
          left: 0,
          blur: 3,
          opacity: 0.5
        }
      },

      dataLabels: {
        showOn: "always",
        name: {
          offsetY: -20,
          show: true,
          color: "#888",
          fontSize: "13px"
        },
        value: {
          formatter: function(val) {
            return 2280;
          },
          color: "#111",
          fontSize: "30px",
          show: true
        }
      }
    }
  },

  series: [74],
  stroke: {
    lineCap: "round"
  },
  labels: ["Goal"]
};

var chart = new ApexCharts(document.querySelector("#radial-chart"), options);

chart.render();

JS;
$this->registerJS($js, View::POS_END);
?>
