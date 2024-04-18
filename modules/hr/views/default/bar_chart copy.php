<div id="chart"></div>
<?php
use yii\helpers\Url;
$url = Url::to(['/hr/default/data-summary']);
$js = <<< JS

$.ajax({
    type: "get",
    url: "$url",
    dataType: "json",
    success: function (res) {


var options = {
          series: [{
          name: 'ชาย',
          data: res.age_rang.Males,
          
        },
        {
          name: 'หญิง',
          data:res.age_rang.Females
        }
        ],
          chart: {
          type: 'bar',
          height: 440,
          stacked: true,
        
        },
        colors: ['#0866ad', '#B0578D'],
        plotOptions: {
          bar: {
            horizontal: true,
            borderRadius: 8,
            barHeight: '80%',
            dataLabels: {
            position: 'top'
            }
          },

        },
        dataLabels: {
        enabled: true,
        formatter: function(val, opt) {
    //   return opt.w.globals.labels[opt.dataPointIndex] + ":  " + val
      return Math.abs(Math.round(val*100 / 264) ) + "%"
  },
        style: {
            colors: ['#333']
        },
         offsetX: 30
  },
        stroke: {
          width: 1,
          colors: ["#fff"]
        },

        grid: {
          xaxis: {
            lines: {
              show: true
            }
          },
        },
        yaxis: {
          min: -50,
          max: 50,
          title: {
            text: 'Age',
          },
        },
        tooltip: {
          shared: false,
          x: {
            formatter: function (val) {
              return val
            }
          },
          y: {
            formatter: function (val) {
              return Math.abs(val)
            }
          }
        },
        title: {
          text: 'ประชากรโรงพยาบาลด่านซ้าย 2566'
        },
        xaxis: {
          categories:res.age_rang.categories,
          title: {
            // text: 'Percent'
          },
          labels: {
            formatter: function (val) {
              return Math.abs(Math.round(val)) + "%"
            }
          }
        },
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();

    }
});

JS;
$this->registerJS($js);
?>
<!-- <div class="card rounded-4 h-100">

           <div class="card-body">
           <div class="justify-content-between align-items-center d-sm-flex d-block">
               <div class="card-title mb-sm-0 mb-2">แผนก/หน่วยงาน </div>
               <div class="btn-group" role="group" aria-label="Basic example"> <button type="button"
                       class="btn btn-priผู้ป่วยนอกy-light btn-sm btn-wave waves-effect waves-light">1W</button>
                   <button type="button"
                       class="btn btn-priผู้ป่วยนอกy-light btn-sm btn-wave waves-effect waves-light">1M</button>
                   <button type="button"
                       class="btn btn-priผู้ป่วยนอกy-light btn-sm btn-wave waves-effect waves-light">6M</button>
                   <button type="button"
                       class="btn btn-priผู้ป่วยนอกy btn-sm btn-wave waves-effect waves-light">1Y</button>
               </div>
           </div>
               <div id="performanceReport" style="min-height: 325px;">
                   <div id="apexchartsbryzc1kj" class="apexcharts-canvas apexchartsbryzc1kj apexcharts-theme-light"
                       style="width: 907px; height: 310px;"><svg id="SvgjsSvg1416" width="907" height="310"
                           xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"
                           xmlns:svgjs="http://svgjs.dev" class="apexcharts-svg" xmlns:data="ApexChartsNS"
                           transform="translate(0, 0)" style="background: transparent;">
                           <foreignObject x="0" y="0" width="907" height="310">
                               <div class="apexcharts-legend" xmlns="http://www.w3.org/1999/xhtml"
                                   style="max-height: 155px;"></div>
                           </foreignObject>
                           <g id="SvgjsG1576" class="apexcharts-yaxis" rel="0" transform="translate(15.359375, 0)">
                               <g id="SvgjsG1577" class="apexcharts-yaxis-texts-g"><text id="SvgjsText1579"
                                       font-family="Helvetica, Arial, sans-serif" x="20" y="31.5" text-anchor="end"
                                       dominant-baseline="auto" font-size="11px" font-weight="400" fill="#373d3f"
                                       class="apexcharts-text apexcharts-yaxis-label "
                                       style="font-family: Helvetica, Arial, sans-serif;">
                                       <tspan id="SvgjsTspan1580">100</tspan>
                                       <title>100</title>
                                   </text><text id="SvgjsText1582" font-family="Helvetica, Arial, sans-serif" x="20"
                                       y="79.9696" text-anchor="end" dominant-baseline="auto" font-size="11px"
                                       font-weight="400" fill="#373d3f" class="apexcharts-text apexcharts-yaxis-label "
                                       style="font-family: Helvetica, Arial, sans-serif;">
                                       <tspan id="SvgjsTspan1583">80</tspan>
                                       <title>80</title>
                                   </text><text id="SvgjsText1585" font-family="Helvetica, Arial, sans-serif" x="20"
                                       y="128.4392" text-anchor="end" dominant-baseline="auto" font-size="11px"
                                       font-weight="400" fill="#373d3f" class="apexcharts-text apexcharts-yaxis-label "
                                       style="font-family: Helvetica, Arial, sans-serif;">
                                       <tspan id="SvgjsTspan1586">60</tspan>
                                       <title>60</title>
                                   </text><text id="SvgjsText1588" font-family="Helvetica, Arial, sans-serif" x="20"
                                       y="176.90879999999999" text-anchor="end" dominant-baseline="auto"
                                       font-size="11px" font-weight="400" fill="#373d3f"
                                       class="apexcharts-text apexcharts-yaxis-label "
                                       style="font-family: Helvetica, Arial, sans-serif;">
                                       <tspan id="SvgjsTspan1589">40</tspan>
                                       <title>40</title>
                                   </text><text id="SvgjsText1591" font-family="Helvetica, Arial, sans-serif" x="20"
                                       y="225.3784" text-anchor="end" dominant-baseline="auto" font-size="11px"
                                       font-weight="400" fill="#373d3f" class="apexcharts-text apexcharts-yaxis-label "
                                       style="font-family: Helvetica, Arial, sans-serif;">
                                       <tspan id="SvgjsTspan1592">20</tspan>
                                       <title>20</title>
                                   </text><text id="SvgjsText1594" font-family="Helvetica, Arial, sans-serif" x="20"
                                       y="273.848" text-anchor="end" dominant-baseline="auto" font-size="11px"
                                       font-weight="400" fill="#373d3f" class="apexcharts-text apexcharts-yaxis-label "
                                       style="font-family: Helvetica, Arial, sans-serif;">
                                       <tspan id="SvgjsTspan1595">0</tspan>
                                       <title>0</title>
                                   </text></g>
                           </g>
                           <g id="SvgjsG1418" class="apexcharts-inner apexcharts-graphical"
                               transform="translate(45.359375, 30)">
                               <defs id="SvgjsDefs1417">
                                   <linearGradient id="SvgjsLinearGradient1421" x1="0" y1="0" x2="0" y2="1">
                                       <stop id="SvgjsStop1422" stop-opacity="0.4" stop-color="rgba(216,227,240,0.4)"
                                           offset="0"></stop>
                                       <stop id="SvgjsStop1423" stop-opacity="0.5" stop-color="rgba(190,209,230,0.5)"
                                           offset="1"></stop>
                                       <stop id="SvgjsStop1424" stop-opacity="0.5" stop-color="rgba(190,209,230,0.5)"
                                           offset="1"></stop>
                                   </linearGradient>
                                   <clipPath id="gridRectMaskbryzc1kj">
                                       <rect id="SvgjsRect1426" width="855.640625" height="242.348" x="-2" y="0" rx="0"
                                           ry="0" opacity="1" stroke-width="0" stroke="none" stroke-dasharray="0"
                                           fill="#fff"></rect>
                                   </clipPath>
                                   <clipPath id="forecastMaskbryzc1kj"></clipPath>
                                   <clipPath id="nonForecastMaskbryzc1kj"></clipPath>
                                   <clipPath id="gridRectผู้ป่วยนอกkerMaskbryzc1kj">
                                       <rect id="SvgjsRect1427" width="855.640625" height="246.348" x="-2" y="-2"
                                           rx="0" ry="0" opacity="1" stroke-width="0" stroke="none"
                                           stroke-dasharray="0" fill="#fff"></rect>
                                   </clipPath>
                               </defs>
                               <rect id="SvgjsRect1425" width="14.194010416666664" height="242.348" x="0" y="0" rx="0"
                                   ry="0" opacity="1" stroke-width="0" stroke-dasharray="3"
                                   fill="url(#SvgjsLinearGradient1421)" class="apexcharts-xcrosshairs" y2="242.348"
                                   filter="none" fill-opacity="0.9"></rect>
                               <line id="SvgjsLine1514" x1="0" y1="243.348" x2="0" y2="249.348" stroke="#e0e0e0"
                                   stroke-dasharray="0" stroke-linecap="butt" class="apexcharts-xaxis-tick"></line>
                               <line id="SvgjsLine1515" x1="70.97005208333333" y1="243.348" x2="70.97005208333333"
                                   y2="249.348" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                   class="apexcharts-xaxis-tick"></line>
                               <line id="SvgjsLine1516" x1="141.94010416666666" y1="243.348" x2="141.94010416666666"
                                   y2="249.348" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                   class="apexcharts-xaxis-tick"></line>
                               <line id="SvgjsLine1517" x1="212.91015625" y1="243.348" x2="212.91015625" y2="249.348"
                                   stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                   class="apexcharts-xaxis-tick"></line>
                               <line id="SvgjsLine1518" x1="283.8802083333333" y1="243.348" x2="283.8802083333333"
                                   y2="249.348" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                   class="apexcharts-xaxis-tick"></line>
                               <line id="SvgjsLine1519" x1="354.85026041666663" y1="243.348" x2="354.85026041666663"
                                   y2="249.348" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                   class="apexcharts-xaxis-tick"></line>
                               <line id="SvgjsLine1520" x1="425.82031249999994" y1="243.348" x2="425.82031249999994"
                                   y2="249.348" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                   class="apexcharts-xaxis-tick"></line>
                               <line id="SvgjsLine1521" x1="496.79036458333326" y1="243.348" x2="496.79036458333326"
                                   y2="249.348" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                   class="apexcharts-xaxis-tick"></line>
                               <line id="SvgjsLine1522" x1="567.7604166666666" y1="243.348" x2="567.7604166666666"
                                   y2="249.348" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                   class="apexcharts-xaxis-tick"></line>
                               <line id="SvgjsLine1523" x1="638.73046875" y1="243.348" x2="638.73046875" y2="249.348"
                                   stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                   class="apexcharts-xaxis-tick"></line>
                               <line id="SvgjsLine1524" x1="709.7005208333334" y1="243.348" x2="709.7005208333334"
                                   y2="249.348" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                   class="apexcharts-xaxis-tick"></line>
                               <line id="SvgjsLine1525" x1="780.6705729166667" y1="243.348" x2="780.6705729166667"
                                   y2="249.348" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                   class="apexcharts-xaxis-tick"></line>
                               <line id="SvgjsLine1526" x1="851.6406250000001" y1="243.348" x2="851.6406250000001"
                                   y2="249.348" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                   class="apexcharts-xaxis-tick"></line>
                               <g id="SvgjsG1510" class="apexcharts-grid">
                                   <g id="SvgjsG1511" class="apexcharts-gridlines-horizontal">
                                       <line id="SvgjsLine1528" x1="0" y1="48.4696" x2="851.640625" y2="48.4696"
                                           stroke="#f1f1f1" stroke-dasharray="3" stroke-linecap="butt"
                                           class="apexcharts-gridline"></line>
                                       <line id="SvgjsLine1529" x1="0" y1="96.9392" x2="851.640625" y2="96.9392"
                                           stroke="#f1f1f1" stroke-dasharray="3" stroke-linecap="butt"
                                           class="apexcharts-gridline"></line>
                                       <line id="SvgjsLine1530" x1="0" y1="145.40879999999999" x2="851.640625"
                                           y2="145.40879999999999" stroke="#f1f1f1" stroke-dasharray="3"
                                           stroke-linecap="butt" class="apexcharts-gridline"></line>
                                       <line id="SvgjsLine1531" x1="0" y1="193.8784" x2="851.640625" y2="193.8784"
                                           stroke="#f1f1f1" stroke-dasharray="3" stroke-linecap="butt"
                                           class="apexcharts-gridline"></line>
                                   </g>
                                   <g id="SvgjsG1512" class="apexcharts-gridlines-vertical"></g>
                                   <line id="SvgjsLine1534" x1="0" y1="242.348" x2="851.640625" y2="242.348"
                                       stroke="transparent" stroke-dasharray="0" stroke-linecap="butt"></line>
                                   <line id="SvgjsLine1533" x1="0" y1="1" x2="0" y2="242.348" stroke="transparent"
                                       stroke-dasharray="0" stroke-linecap="butt"></line>
                               </g>
                               <g id="SvgjsG1513" class="apexcharts-grid-borders">
                                   <line id="SvgjsLine1527" x1="0" y1="0" x2="851.640625" y2="0" stroke="#f1f1f1"
                                       stroke-dasharray="3" stroke-linecap="butt" class="apexcharts-gridline"></line>
                                   <line id="SvgjsLine1532" x1="0" y1="242.348" x2="851.640625" y2="242.348"
                                       stroke="#f1f1f1" stroke-dasharray="3" stroke-linecap="butt"
                                       class="apexcharts-gridline"></line>
                                   <line id="SvgjsLine1575" x1="0" y1="243.348" x2="851.640625" y2="243.348"
                                       stroke="#e0e0e0" stroke-dasharray="0" stroke-width="1" stroke-linecap="butt">
                                   </line>
                               </g>
                               <g id="SvgjsG1428" class="apexcharts-bar-series apexcharts-plot-series">
                                   <g id="SvgjsG1429" class="apexcharts-series" seriesName="Designing" rel="1"
                                       data:realIndex="0">
                                       <path id="SvgjsPath1433"
                                           d="M 28.388020833333332 242.34900000000002 L 28.388020833333332 135.71588 L 42.58203125 135.71588 L 42.58203125 242.34900000000002 z"
                                           fill="rgba(223, 90, 90, 1)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 28.388020833333332 242.34900000000002 L 28.388020833333332 135.71588 L 42.58203125 135.71588 L 42.58203125 242.34900000000002 z"
                                           pathFrom="M 28.388020833333332 242.34900000000002 L 28.388020833333332 135.71588 L 42.58203125 135.71588 L 42.58203125 242.34900000000002 z L 28.388020833333332 242.34900000000002 L 42.58203125 242.34900000000002 L 42.58203125 242.34900000000002 L 42.58203125 242.34900000000002 L 42.58203125 242.34900000000002 L 42.58203125 242.34900000000002 L 28.388020833333332 242.34900000000002 z"
                                           cy="135.71488" cx="99.35807291666666" j="0" val="44" barHeight="106.63312"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1435"
                                           d="M 99.35807291666666 242.34900000000002 L 99.35807291666666 109.05760000000001 L 113.55208333333331 109.05760000000001 L 113.55208333333331 242.34900000000002 z"
                                           fill="rgba(223, 90, 90, 1)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 99.35807291666666 242.34900000000002 L 99.35807291666666 109.05760000000001 L 113.55208333333331 109.05760000000001 L 113.55208333333331 242.34900000000002 z"
                                           pathFrom="M 99.35807291666666 242.34900000000002 L 99.35807291666666 109.05760000000001 L 113.55208333333331 109.05760000000001 L 113.55208333333331 242.34900000000002 z L 99.35807291666666 242.34900000000002 L 113.55208333333331 242.34900000000002 L 113.55208333333331 242.34900000000002 L 113.55208333333331 242.34900000000002 L 113.55208333333331 242.34900000000002 L 113.55208333333331 242.34900000000002 L 99.35807291666666 242.34900000000002 z"
                                           cy="109.0566" cx="170.328125" j="1" val="55" barHeight="133.2914"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1437"
                                           d="M 170.328125 242.34900000000002 L 170.328125 142.98632 L 184.52213541666666 142.98632 L 184.52213541666666 242.34900000000002 z"
                                           fill="rgba(223, 90, 90, 1)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 170.328125 242.34900000000002 L 170.328125 142.98632 L 184.52213541666666 142.98632 L 184.52213541666666 242.34900000000002 z"
                                           pathFrom="M 170.328125 242.34900000000002 L 170.328125 142.98632 L 184.52213541666666 142.98632 L 184.52213541666666 242.34900000000002 z L 170.328125 242.34900000000002 L 184.52213541666666 242.34900000000002 L 184.52213541666666 242.34900000000002 L 184.52213541666666 242.34900000000002 L 184.52213541666666 242.34900000000002 L 184.52213541666666 242.34900000000002 L 170.328125 242.34900000000002 z"
                                           cy="142.98532" cx="241.29817708333331" j="2" val="41" barHeight="99.36268"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1439"
                                           d="M 241.29817708333331 242.34900000000002 L 241.29817708333331 79.97584 L 255.49218749999997 79.97584 L 255.49218749999997 242.34900000000002 z"
                                           fill="rgba(223, 90, 90, 1)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 241.29817708333331 242.34900000000002 L 241.29817708333331 79.97584 L 255.49218749999997 79.97584 L 255.49218749999997 242.34900000000002 z"
                                           pathFrom="M 241.29817708333331 242.34900000000002 L 241.29817708333331 79.97584 L 255.49218749999997 79.97584 L 255.49218749999997 242.34900000000002 z L 241.29817708333331 242.34900000000002 L 255.49218749999997 242.34900000000002 L 255.49218749999997 242.34900000000002 L 255.49218749999997 242.34900000000002 L 255.49218749999997 242.34900000000002 L 255.49218749999997 242.34900000000002 L 241.29817708333331 242.34900000000002 z"
                                           cy="79.97484" cx="312.26822916666663" j="3" val="67" barHeight="162.37316"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1441"
                                           d="M 312.26822916666663 242.34900000000002 L 312.26822916666663 189.03244 L 326.4622395833333 189.03244 L 326.4622395833333 242.34900000000002 z"
                                           fill="rgba(223, 90, 90, 1)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 312.26822916666663 242.34900000000002 L 312.26822916666663 189.03244 L 326.4622395833333 189.03244 L 326.4622395833333 242.34900000000002 z"
                                           pathFrom="M 312.26822916666663 242.34900000000002 L 312.26822916666663 189.03244 L 326.4622395833333 189.03244 L 326.4622395833333 242.34900000000002 z L 312.26822916666663 242.34900000000002 L 326.4622395833333 242.34900000000002 L 326.4622395833333 242.34900000000002 L 326.4622395833333 242.34900000000002 L 326.4622395833333 242.34900000000002 L 326.4622395833333 242.34900000000002 L 312.26822916666663 242.34900000000002 z"
                                           cy="189.03144" cx="383.23828124999994" j="4" val="22" barHeight="53.31656"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1443"
                                           d="M 383.23828124999994 242.34900000000002 L 383.23828124999994 138.13936000000004 L 397.43229166666663 138.13936000000004 L 397.43229166666663 242.34900000000002 z"
                                           fill="rgba(223, 90, 90, 1)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 383.23828124999994 242.34900000000002 L 383.23828124999994 138.13936000000004 L 397.43229166666663 138.13936000000004 L 397.43229166666663 242.34900000000002 z"
                                           pathFrom="M 383.23828124999994 242.34900000000002 L 383.23828124999994 138.13936000000004 L 397.43229166666663 138.13936000000004 L 397.43229166666663 242.34900000000002 z L 383.23828124999994 242.34900000000002 L 397.43229166666663 242.34900000000002 L 397.43229166666663 242.34900000000002 L 397.43229166666663 242.34900000000002 L 397.43229166666663 242.34900000000002 L 397.43229166666663 242.34900000000002 L 383.23828124999994 242.34900000000002 z"
                                           cy="138.13836000000003" cx="454.20833333333326" j="5" val="43"
                                           barHeight="104.20964" barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1445"
                                           d="M 454.20833333333326 242.34900000000002 L 454.20833333333326 135.71588 L 468.40234374999994 135.71588 L 468.40234374999994 242.34900000000002 z"
                                           fill="rgba(223, 90, 90, 1)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 454.20833333333326 242.34900000000002 L 454.20833333333326 135.71588 L 468.40234374999994 135.71588 L 468.40234374999994 242.34900000000002 z"
                                           pathFrom="M 454.20833333333326 242.34900000000002 L 454.20833333333326 135.71588 L 468.40234374999994 135.71588 L 468.40234374999994 242.34900000000002 z L 454.20833333333326 242.34900000000002 L 468.40234374999994 242.34900000000002 L 468.40234374999994 242.34900000000002 L 468.40234374999994 242.34900000000002 L 468.40234374999994 242.34900000000002 L 468.40234374999994 242.34900000000002 L 454.20833333333326 242.34900000000002 z"
                                           cy="135.71488" cx="525.1783854166666" j="6" val="44" barHeight="106.63312"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1447"
                                           d="M 525.1783854166666 242.34900000000002 L 525.1783854166666 109.05760000000001 L 539.3723958333333 109.05760000000001 L 539.3723958333333 242.34900000000002 z"
                                           fill="rgba(223, 90, 90, 1)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 525.1783854166666 242.34900000000002 L 525.1783854166666 109.05760000000001 L 539.3723958333333 109.05760000000001 L 539.3723958333333 242.34900000000002 z"
                                           pathFrom="M 525.1783854166666 242.34900000000002 L 525.1783854166666 109.05760000000001 L 539.3723958333333 109.05760000000001 L 539.3723958333333 242.34900000000002 z L 525.1783854166666 242.34900000000002 L 539.3723958333333 242.34900000000002 L 539.3723958333333 242.34900000000002 L 539.3723958333333 242.34900000000002 L 539.3723958333333 242.34900000000002 L 539.3723958333333 242.34900000000002 L 525.1783854166666 242.34900000000002 z"
                                           cy="109.0566" cx="596.1484375" j="7" val="55" barHeight="133.2914"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1449"
                                           d="M 596.1484375 242.34900000000002 L 596.1484375 142.98632 L 610.3424479166666 142.98632 L 610.3424479166666 242.34900000000002 z"
                                           fill="rgba(223, 90, 90, 1)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 596.1484375 242.34900000000002 L 596.1484375 142.98632 L 610.3424479166666 142.98632 L 610.3424479166666 242.34900000000002 z"
                                           pathFrom="M 596.1484375 242.34900000000002 L 596.1484375 142.98632 L 610.3424479166666 142.98632 L 610.3424479166666 242.34900000000002 z L 596.1484375 242.34900000000002 L 610.3424479166666 242.34900000000002 L 610.3424479166666 242.34900000000002 L 610.3424479166666 242.34900000000002 L 610.3424479166666 242.34900000000002 L 610.3424479166666 242.34900000000002 L 596.1484375 242.34900000000002 z"
                                           cy="142.98532" cx="667.1184895833334" j="8" val="41" barHeight="99.36268"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1451"
                                           d="M 667.1184895833334 242.34900000000002 L 667.1184895833334 79.97584 L 681.3125 79.97584 L 681.3125 242.34900000000002 z"
                                           fill="rgba(223, 90, 90, 1)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 667.1184895833334 242.34900000000002 L 667.1184895833334 79.97584 L 681.3125 79.97584 L 681.3125 242.34900000000002 z"
                                           pathFrom="M 667.1184895833334 242.34900000000002 L 667.1184895833334 79.97584 L 681.3125 79.97584 L 681.3125 242.34900000000002 z L 667.1184895833334 242.34900000000002 L 681.3125 242.34900000000002 L 681.3125 242.34900000000002 L 681.3125 242.34900000000002 L 681.3125 242.34900000000002 L 681.3125 242.34900000000002 L 667.1184895833334 242.34900000000002 z"
                                           cy="79.97484" cx="738.0885416666667" j="9" val="67" barHeight="162.37316"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1453"
                                           d="M 738.0885416666667 242.34900000000002 L 738.0885416666667 189.03244 L 752.2825520833334 189.03244 L 752.2825520833334 242.34900000000002 z"
                                           fill="rgba(223, 90, 90, 1)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 738.0885416666667 242.34900000000002 L 738.0885416666667 189.03244 L 752.2825520833334 189.03244 L 752.2825520833334 242.34900000000002 z"
                                           pathFrom="M 738.0885416666667 242.34900000000002 L 738.0885416666667 189.03244 L 752.2825520833334 189.03244 L 752.2825520833334 242.34900000000002 z L 738.0885416666667 242.34900000000002 L 752.2825520833334 242.34900000000002 L 752.2825520833334 242.34900000000002 L 752.2825520833334 242.34900000000002 L 752.2825520833334 242.34900000000002 L 752.2825520833334 242.34900000000002 L 738.0885416666667 242.34900000000002 z"
                                           cy="189.03144" cx="809.0585937500001" j="10" val="22" barHeight="53.31656"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1455"
                                           d="M 809.0585937500001 242.34900000000002 L 809.0585937500001 138.13936000000004 L 823.2526041666667 138.13936000000004 L 823.2526041666667 242.34900000000002 z"
                                           fill="rgba(223, 90, 90, 1)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 809.0585937500001 242.34900000000002 L 809.0585937500001 138.13936000000004 L 823.2526041666667 138.13936000000004 L 823.2526041666667 242.34900000000002 z"
                                           pathFrom="M 809.0585937500001 242.34900000000002 L 809.0585937500001 138.13936000000004 L 823.2526041666667 138.13936000000004 L 823.2526041666667 242.34900000000002 z L 809.0585937500001 242.34900000000002 L 823.2526041666667 242.34900000000002 L 823.2526041666667 242.34900000000002 L 823.2526041666667 242.34900000000002 L 823.2526041666667 242.34900000000002 L 823.2526041666667 242.34900000000002 L 809.0585937500001 242.34900000000002 z"
                                           cy="138.13836000000003" cx="880.0286458333335" j="11" val="43"
                                           barHeight="104.20964" barWidth="14.194010416666664"></path>
                                       <g id="SvgjsG1431" class="apexcharts-bar-goals-ผู้ป่วยนอกkers">
                                           <g id="SvgjsG1432" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1434" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1436" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1438" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1440" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1442" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1444" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1446" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1448" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1450" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1452" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1454" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                       </g>
                                   </g>
                                   <g id="SvgjsG1456" class="apexcharts-series" seriesName="Development" rel="2"
                                       data:realIndex="1">
                                       <path id="SvgjsPath1460"
                                           d="M 28.388020833333332 135.71688 L 28.388020833333332 104.21164 L 42.58203125 104.21164 L 42.58203125 135.71688 z"
                                           fill="rgba(223, 90, 90, 0.5)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 28.388020833333332 135.71688 L 28.388020833333332 104.21164 L 42.58203125 104.21164 L 42.58203125 135.71688 z"
                                           pathFrom="M 28.388020833333332 135.71688 L 28.388020833333332 104.21164 L 42.58203125 104.21164 L 42.58203125 135.71688 z L 28.388020833333332 135.71688 L 42.58203125 135.71688 L 42.58203125 135.71688 L 42.58203125 135.71688 L 42.58203125 135.71688 L 42.58203125 135.71688 L 28.388020833333332 135.71688 z"
                                           cy="104.21064" cx="99.35807291666666" j="0" val="13" barHeight="31.50524"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1462"
                                           d="M 99.35807291666666 109.05860000000001 L 99.35807291666666 53.318560000000005 L 113.55208333333331 53.318560000000005 L 113.55208333333331 109.05860000000001 z"
                                           fill="rgba(223, 90, 90, 0.5)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 99.35807291666666 109.05860000000001 L 99.35807291666666 53.318560000000005 L 113.55208333333331 53.318560000000005 L 113.55208333333331 109.05860000000001 z"
                                           pathFrom="M 99.35807291666666 109.05860000000001 L 99.35807291666666 53.318560000000005 L 113.55208333333331 53.318560000000005 L 113.55208333333331 109.05860000000001 z L 99.35807291666666 109.05860000000001 L 113.55208333333331 109.05860000000001 L 113.55208333333331 109.05860000000001 L 113.55208333333331 109.05860000000001 L 113.55208333333331 109.05860000000001 L 113.55208333333331 109.05860000000001 L 99.35807291666666 109.05860000000001 z"
                                           cy="53.31756000000001" cx="170.328125" j="1" val="23" barHeight="55.74004"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1464"
                                           d="M 170.328125 142.98732 L 170.328125 94.51772000000001 L 184.52213541666666 94.51772000000001 L 184.52213541666666 142.98732 z"
                                           fill="rgba(223, 90, 90, 0.5)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 170.328125 142.98732 L 170.328125 94.51772000000001 L 184.52213541666666 94.51772000000001 L 184.52213541666666 142.98732 z"
                                           pathFrom="M 170.328125 142.98732 L 170.328125 94.51772000000001 L 184.52213541666666 94.51772000000001 L 184.52213541666666 142.98732 z L 170.328125 142.98732 L 184.52213541666666 142.98732 L 184.52213541666666 142.98732 L 184.52213541666666 142.98732 L 184.52213541666666 142.98732 L 184.52213541666666 142.98732 L 170.328125 142.98732 z"
                                           cy="94.51672" cx="241.29817708333331" j="2" val="20" barHeight="48.4696"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1466"
                                           d="M 241.29817708333331 79.97684000000001 L 241.29817708333331 60.589000000000006 L 255.49218749999997 60.589000000000006 L 255.49218749999997 79.97684000000001 z"
                                           fill="rgba(223, 90, 90, 0.5)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 241.29817708333331 79.97684000000001 L 241.29817708333331 60.589000000000006 L 255.49218749999997 60.589000000000006 L 255.49218749999997 79.97684000000001 z"
                                           pathFrom="M 241.29817708333331 79.97684000000001 L 241.29817708333331 60.589000000000006 L 255.49218749999997 60.589000000000006 L 255.49218749999997 79.97684000000001 z L 241.29817708333331 79.97684000000001 L 255.49218749999997 79.97684000000001 L 255.49218749999997 79.97684000000001 L 255.49218749999997 79.97684000000001 L 255.49218749999997 79.97684000000001 L 255.49218749999997 79.97684000000001 L 241.29817708333331 79.97684000000001 z"
                                           cy="60.58800000000001" cx="312.26822916666663" j="3" val="8"
                                           barHeight="19.38784" barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1468"
                                           d="M 312.26822916666663 189.03344 L 312.26822916666663 157.5282 L 326.4622395833333 157.5282 L 326.4622395833333 189.03344 z"
                                           fill="rgba(223, 90, 90, 0.5)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 312.26822916666663 189.03344 L 312.26822916666663 157.5282 L 326.4622395833333 157.5282 L 326.4622395833333 189.03344 z"
                                           pathFrom="M 312.26822916666663 189.03344 L 312.26822916666663 157.5282 L 326.4622395833333 157.5282 L 326.4622395833333 189.03344 z L 312.26822916666663 189.03344 L 326.4622395833333 189.03344 L 326.4622395833333 189.03344 L 326.4622395833333 189.03344 L 326.4622395833333 189.03344 L 326.4622395833333 189.03344 L 312.26822916666663 189.03344 z"
                                           cy="157.5272" cx="383.23828124999994" j="4" val="13" barHeight="31.50524"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1470"
                                           d="M 383.23828124999994 138.14036000000004 L 383.23828124999994 72.70640000000004 L 397.43229166666663 72.70640000000004 L 397.43229166666663 138.14036000000004 z"
                                           fill="rgba(223, 90, 90, 0.5)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 383.23828124999994 138.14036000000004 L 383.23828124999994 72.70640000000004 L 397.43229166666663 72.70640000000004 L 397.43229166666663 138.14036000000004 z"
                                           pathFrom="M 383.23828124999994 138.14036000000004 L 383.23828124999994 72.70640000000004 L 397.43229166666663 72.70640000000004 L 397.43229166666663 138.14036000000004 z L 383.23828124999994 138.14036000000004 L 397.43229166666663 138.14036000000004 L 397.43229166666663 138.14036000000004 L 397.43229166666663 138.14036000000004 L 397.43229166666663 138.14036000000004 L 397.43229166666663 138.14036000000004 L 383.23828124999994 138.14036000000004 z"
                                           cy="72.70540000000004" cx="454.20833333333326" j="5" val="27"
                                           barHeight="65.43396" barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1472"
                                           d="M 454.20833333333326 135.71688 L 454.20833333333326 104.21164 L 468.40234374999994 104.21164 L 468.40234374999994 135.71688 z"
                                           fill="rgba(223, 90, 90, 0.5)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 454.20833333333326 135.71688 L 454.20833333333326 104.21164 L 468.40234374999994 104.21164 L 468.40234374999994 135.71688 z"
                                           pathFrom="M 454.20833333333326 135.71688 L 454.20833333333326 104.21164 L 468.40234374999994 104.21164 L 468.40234374999994 135.71688 z L 454.20833333333326 135.71688 L 468.40234374999994 135.71688 L 468.40234374999994 135.71688 L 468.40234374999994 135.71688 L 468.40234374999994 135.71688 L 468.40234374999994 135.71688 L 454.20833333333326 135.71688 z"
                                           cy="104.21064" cx="525.1783854166666" j="6" val="13" barHeight="31.50524"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1474"
                                           d="M 525.1783854166666 109.05860000000001 L 525.1783854166666 53.318560000000005 L 539.3723958333333 53.318560000000005 L 539.3723958333333 109.05860000000001 z"
                                           fill="rgba(223, 90, 90, 0.5)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 525.1783854166666 109.05860000000001 L 525.1783854166666 53.318560000000005 L 539.3723958333333 53.318560000000005 L 539.3723958333333 109.05860000000001 z"
                                           pathFrom="M 525.1783854166666 109.05860000000001 L 525.1783854166666 53.318560000000005 L 539.3723958333333 53.318560000000005 L 539.3723958333333 109.05860000000001 z L 525.1783854166666 109.05860000000001 L 539.3723958333333 109.05860000000001 L 539.3723958333333 109.05860000000001 L 539.3723958333333 109.05860000000001 L 539.3723958333333 109.05860000000001 L 539.3723958333333 109.05860000000001 L 525.1783854166666 109.05860000000001 z"
                                           cy="53.31756000000001" cx="596.1484375" j="7" val="23" barHeight="55.74004"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1476"
                                           d="M 596.1484375 142.98732 L 596.1484375 94.51772000000001 L 610.3424479166666 94.51772000000001 L 610.3424479166666 142.98732 z"
                                           fill="rgba(223, 90, 90, 0.5)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 596.1484375 142.98732 L 596.1484375 94.51772000000001 L 610.3424479166666 94.51772000000001 L 610.3424479166666 142.98732 z"
                                           pathFrom="M 596.1484375 142.98732 L 596.1484375 94.51772000000001 L 610.3424479166666 94.51772000000001 L 610.3424479166666 142.98732 z L 596.1484375 142.98732 L 610.3424479166666 142.98732 L 610.3424479166666 142.98732 L 610.3424479166666 142.98732 L 610.3424479166666 142.98732 L 610.3424479166666 142.98732 L 596.1484375 142.98732 z"
                                           cy="94.51672" cx="667.1184895833334" j="8" val="20" barHeight="48.4696"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1478"
                                           d="M 667.1184895833334 79.97684000000001 L 667.1184895833334 60.589000000000006 L 681.3125 60.589000000000006 L 681.3125 79.97684000000001 z"
                                           fill="rgba(223, 90, 90, 0.5)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 667.1184895833334 79.97684000000001 L 667.1184895833334 60.589000000000006 L 681.3125 60.589000000000006 L 681.3125 79.97684000000001 z"
                                           pathFrom="M 667.1184895833334 79.97684000000001 L 667.1184895833334 60.589000000000006 L 681.3125 60.589000000000006 L 681.3125 79.97684000000001 z L 667.1184895833334 79.97684000000001 L 681.3125 79.97684000000001 L 681.3125 79.97684000000001 L 681.3125 79.97684000000001 L 681.3125 79.97684000000001 L 681.3125 79.97684000000001 L 667.1184895833334 79.97684000000001 z"
                                           cy="60.58800000000001" cx="738.0885416666667" j="9" val="8"
                                           barHeight="19.38784" barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1480"
                                           d="M 738.0885416666667 189.03344 L 738.0885416666667 157.5282 L 752.2825520833334 157.5282 L 752.2825520833334 189.03344 z"
                                           fill="rgba(223, 90, 90, 0.5)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 738.0885416666667 189.03344 L 738.0885416666667 157.5282 L 752.2825520833334 157.5282 L 752.2825520833334 189.03344 z"
                                           pathFrom="M 738.0885416666667 189.03344 L 738.0885416666667 157.5282 L 752.2825520833334 157.5282 L 752.2825520833334 189.03344 z L 738.0885416666667 189.03344 L 752.2825520833334 189.03344 L 752.2825520833334 189.03344 L 752.2825520833334 189.03344 L 752.2825520833334 189.03344 L 752.2825520833334 189.03344 L 738.0885416666667 189.03344 z"
                                           cy="157.5272" cx="809.0585937500001" j="10" val="13" barHeight="31.50524"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1482"
                                           d="M 809.0585937500001 138.14036000000004 L 809.0585937500001 72.70640000000004 L 823.2526041666667 72.70640000000004 L 823.2526041666667 138.14036000000004 z"
                                           fill="rgba(223, 90, 90, 0.5)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 809.0585937500001 138.14036000000004 L 809.0585937500001 72.70640000000004 L 823.2526041666667 72.70640000000004 L 823.2526041666667 138.14036000000004 z"
                                           pathFrom="M 809.0585937500001 138.14036000000004 L 809.0585937500001 72.70640000000004 L 823.2526041666667 72.70640000000004 L 823.2526041666667 138.14036000000004 z L 809.0585937500001 138.14036000000004 L 823.2526041666667 138.14036000000004 L 823.2526041666667 138.14036000000004 L 823.2526041666667 138.14036000000004 L 823.2526041666667 138.14036000000004 L 823.2526041666667 138.14036000000004 L 809.0585937500001 138.14036000000004 z"
                                           cy="72.70540000000004" cx="880.0286458333335" j="11" val="27"
                                           barHeight="65.43396" barWidth="14.194010416666664"></path>
                                       <g id="SvgjsG1458" class="apexcharts-bar-goals-ผู้ป่วยนอกkers">
                                           <g id="SvgjsG1459" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1461" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1463" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1465" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1467" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1469" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1471" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1473" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1475" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1477" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1479" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1481" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                       </g>
                                   </g>
                                   <g id="SvgjsG1483" class="apexcharts-series" seriesName="SEO" rel="3"
                                       data:realIndex="2">
                                       <path id="SvgjsPath1487"
                                           d="M 28.388020833333332 104.21264000000001 L 28.388020833333332 77.55436 L 42.58203125 77.55436 L 42.58203125 104.21264000000001 z"
                                           fill="rgba(223, 90, 90, 0.2)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 28.388020833333332 104.21264000000001 L 28.388020833333332 77.55436 L 42.58203125 77.55436 L 42.58203125 104.21264000000001 z"
                                           pathFrom="M 28.388020833333332 104.21264000000001 L 28.388020833333332 77.55436 L 42.58203125 77.55436 L 42.58203125 104.21264000000001 z L 28.388020833333332 104.21264000000001 L 42.58203125 104.21264000000001 L 42.58203125 104.21264000000001 L 42.58203125 104.21264000000001 L 42.58203125 104.21264000000001 L 42.58203125 104.21264000000001 L 28.388020833333332 104.21264000000001 z"
                                           cy="77.55336" cx="99.35807291666666" j="0" val="11" barHeight="26.65828"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1489"
                                           d="M 99.35807291666666 53.31956 L 99.35807291666666 12.120400000000005 L 113.55208333333331 12.120400000000005 L 113.55208333333331 53.31956 z"
                                           fill="rgba(223, 90, 90, 0.2)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 99.35807291666666 53.31956 L 99.35807291666666 12.120400000000005 L 113.55208333333331 12.120400000000005 L 113.55208333333331 53.31956 z"
                                           pathFrom="M 99.35807291666666 53.31956 L 99.35807291666666 12.120400000000005 L 113.55208333333331 12.120400000000005 L 113.55208333333331 53.31956 z L 99.35807291666666 53.31956 L 113.55208333333331 53.31956 L 113.55208333333331 53.31956 L 113.55208333333331 53.31956 L 113.55208333333331 53.31956 L 113.55208333333331 53.31956 L 99.35807291666666 53.31956 z"
                                           cy="12.119400000000006" cx="170.328125" j="1" val="17" barHeight="41.19916"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1491"
                                           d="M 170.328125 94.51872000000002 L 170.328125 58.16652000000001 L 184.52213541666666 58.16652000000001 L 184.52213541666666 94.51872000000002 z"
                                           fill="rgba(223, 90, 90, 0.2)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 170.328125 94.51872000000002 L 170.328125 58.16652000000001 L 184.52213541666666 58.16652000000001 L 184.52213541666666 94.51872000000002 z"
                                           pathFrom="M 170.328125 94.51872000000002 L 170.328125 58.16652000000001 L 184.52213541666666 58.16652000000001 L 184.52213541666666 94.51872000000002 z L 170.328125 94.51872000000002 L 184.52213541666666 94.51872000000002 L 184.52213541666666 94.51872000000002 L 184.52213541666666 94.51872000000002 L 184.52213541666666 94.51872000000002 L 184.52213541666666 94.51872000000002 L 170.328125 94.51872000000002 z"
                                           cy="58.165520000000015" cx="241.29817708333331" j="2" val="15"
                                           barHeight="36.352199999999996" barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1493"
                                           d="M 241.29817708333331 60.59 L 241.29817708333331 24.23780000000001 L 255.49218749999997 24.23780000000001 L 255.49218749999997 60.59 z"
                                           fill="rgba(223, 90, 90, 0.2)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 241.29817708333331 60.59 L 241.29817708333331 24.23780000000001 L 255.49218749999997 24.23780000000001 L 255.49218749999997 60.59 z"
                                           pathFrom="M 241.29817708333331 60.59 L 241.29817708333331 24.23780000000001 L 255.49218749999997 24.23780000000001 L 255.49218749999997 60.59 z L 241.29817708333331 60.59 L 255.49218749999997 60.59 L 255.49218749999997 60.59 L 255.49218749999997 60.59 L 255.49218749999997 60.59 L 255.49218749999997 60.59 L 241.29817708333331 60.59 z"
                                           cy="24.23680000000001" cx="312.26822916666663" j="3" val="15"
                                           barHeight="36.352199999999996" barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1495"
                                           d="M 312.26822916666663 157.5292 L 312.26822916666663 106.63612 L 326.4622395833333 106.63612 L 326.4622395833333 157.5292 z"
                                           fill="rgba(223, 90, 90, 0.2)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 312.26822916666663 157.5292 L 312.26822916666663 106.63612 L 326.4622395833333 106.63612 L 326.4622395833333 157.5292 z"
                                           pathFrom="M 312.26822916666663 157.5292 L 312.26822916666663 106.63612 L 326.4622395833333 106.63612 L 326.4622395833333 157.5292 z L 312.26822916666663 157.5292 L 326.4622395833333 157.5292 L 326.4622395833333 157.5292 L 326.4622395833333 157.5292 L 326.4622395833333 157.5292 L 326.4622395833333 157.5292 L 312.26822916666663 157.5292 z"
                                           cy="106.63512" cx="383.23828124999994" j="4" val="21" barHeight="50.89308"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1497"
                                           d="M 383.23828124999994 72.70740000000005 L 383.23828124999994 38.778680000000044 L 397.43229166666663 38.778680000000044 L 397.43229166666663 72.70740000000005 z"
                                           fill="rgba(223, 90, 90, 0.2)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 383.23828124999994 72.70740000000005 L 383.23828124999994 38.778680000000044 L 397.43229166666663 38.778680000000044 L 397.43229166666663 72.70740000000005 z"
                                           pathFrom="M 383.23828124999994 72.70740000000005 L 383.23828124999994 38.778680000000044 L 397.43229166666663 38.778680000000044 L 397.43229166666663 72.70740000000005 z L 383.23828124999994 72.70740000000005 L 397.43229166666663 72.70740000000005 L 397.43229166666663 72.70740000000005 L 397.43229166666663 72.70740000000005 L 397.43229166666663 72.70740000000005 L 397.43229166666663 72.70740000000005 L 383.23828124999994 72.70740000000005 z"
                                           cy="38.777680000000046" cx="454.20833333333326" j="5" val="14"
                                           barHeight="33.92872" barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1499"
                                           d="M 454.20833333333326 104.21264000000001 L 454.20833333333326 77.55436 L 468.40234374999994 77.55436 L 468.40234374999994 104.21264000000001 z"
                                           fill="rgba(223, 90, 90, 0.2)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 454.20833333333326 104.21264000000001 L 454.20833333333326 77.55436 L 468.40234374999994 77.55436 L 468.40234374999994 104.21264000000001 z"
                                           pathFrom="M 454.20833333333326 104.21264000000001 L 454.20833333333326 77.55436 L 468.40234374999994 77.55436 L 468.40234374999994 104.21264000000001 z L 454.20833333333326 104.21264000000001 L 468.40234374999994 104.21264000000001 L 468.40234374999994 104.21264000000001 L 468.40234374999994 104.21264000000001 L 468.40234374999994 104.21264000000001 L 468.40234374999994 104.21264000000001 L 454.20833333333326 104.21264000000001 z"
                                           cy="77.55336" cx="525.1783854166666" j="6" val="11" barHeight="26.65828"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1501"
                                           d="M 525.1783854166666 53.31956 L 525.1783854166666 12.120400000000005 L 539.3723958333333 12.120400000000005 L 539.3723958333333 53.31956 z"
                                           fill="rgba(223, 90, 90, 0.2)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 525.1783854166666 53.31956 L 525.1783854166666 12.120400000000005 L 539.3723958333333 12.120400000000005 L 539.3723958333333 53.31956 z"
                                           pathFrom="M 525.1783854166666 53.31956 L 525.1783854166666 12.120400000000005 L 539.3723958333333 12.120400000000005 L 539.3723958333333 53.31956 z L 525.1783854166666 53.31956 L 539.3723958333333 53.31956 L 539.3723958333333 53.31956 L 539.3723958333333 53.31956 L 539.3723958333333 53.31956 L 539.3723958333333 53.31956 L 525.1783854166666 53.31956 z"
                                           cy="12.119400000000006" cx="596.1484375" j="7" val="17" barHeight="41.19916"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1503"
                                           d="M 596.1484375 94.51872000000002 L 596.1484375 58.16652000000001 L 610.3424479166666 58.16652000000001 L 610.3424479166666 94.51872000000002 z"
                                           fill="rgba(223, 90, 90, 0.2)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 596.1484375 94.51872000000002 L 596.1484375 58.16652000000001 L 610.3424479166666 58.16652000000001 L 610.3424479166666 94.51872000000002 z"
                                           pathFrom="M 596.1484375 94.51872000000002 L 596.1484375 58.16652000000001 L 610.3424479166666 58.16652000000001 L 610.3424479166666 94.51872000000002 z L 596.1484375 94.51872000000002 L 610.3424479166666 94.51872000000002 L 610.3424479166666 94.51872000000002 L 610.3424479166666 94.51872000000002 L 610.3424479166666 94.51872000000002 L 610.3424479166666 94.51872000000002 L 596.1484375 94.51872000000002 z"
                                           cy="58.165520000000015" cx="667.1184895833334" j="8" val="15"
                                           barHeight="36.352199999999996" barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1505"
                                           d="M 667.1184895833334 60.59 L 667.1184895833334 24.23780000000001 L 681.3125 24.23780000000001 L 681.3125 60.59 z"
                                           fill="rgba(223, 90, 90, 0.2)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 667.1184895833334 60.59 L 667.1184895833334 24.23780000000001 L 681.3125 24.23780000000001 L 681.3125 60.59 z"
                                           pathFrom="M 667.1184895833334 60.59 L 667.1184895833334 24.23780000000001 L 681.3125 24.23780000000001 L 681.3125 60.59 z L 667.1184895833334 60.59 L 681.3125 60.59 L 681.3125 60.59 L 681.3125 60.59 L 681.3125 60.59 L 681.3125 60.59 L 667.1184895833334 60.59 z"
                                           cy="24.23680000000001" cx="738.0885416666667" j="9" val="15"
                                           barHeight="36.352199999999996" barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1507"
                                           d="M 738.0885416666667 157.5292 L 738.0885416666667 106.63612 L 752.2825520833334 106.63612 L 752.2825520833334 157.5292 z"
                                           fill="rgba(223, 90, 90, 0.2)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 738.0885416666667 157.5292 L 738.0885416666667 106.63612 L 752.2825520833334 106.63612 L 752.2825520833334 157.5292 z"
                                           pathFrom="M 738.0885416666667 157.5292 L 738.0885416666667 106.63612 L 752.2825520833334 106.63612 L 752.2825520833334 157.5292 z L 738.0885416666667 157.5292 L 752.2825520833334 157.5292 L 752.2825520833334 157.5292 L 752.2825520833334 157.5292 L 752.2825520833334 157.5292 L 752.2825520833334 157.5292 L 738.0885416666667 157.5292 z"
                                           cy="106.63512" cx="809.0585937500001" j="10" val="21" barHeight="50.89308"
                                           barWidth="14.194010416666664"></path>
                                       <path id="SvgjsPath1509"
                                           d="M 809.0585937500001 72.70740000000005 L 809.0585937500001 38.778680000000044 L 823.2526041666667 38.778680000000044 L 823.2526041666667 72.70740000000005 z"
                                           fill="rgba(223, 90, 90, 0.2)" fill-opacity="1" stroke-opacity="1"
                                           stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                           class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMaskbryzc1kj)"
                                           pathTo="M 809.0585937500001 72.70740000000005 L 809.0585937500001 38.778680000000044 L 823.2526041666667 38.778680000000044 L 823.2526041666667 72.70740000000005 z"
                                           pathFrom="M 809.0585937500001 72.70740000000005 L 809.0585937500001 38.778680000000044 L 823.2526041666667 38.778680000000044 L 823.2526041666667 72.70740000000005 z L 809.0585937500001 72.70740000000005 L 823.2526041666667 72.70740000000005 L 823.2526041666667 72.70740000000005 L 823.2526041666667 72.70740000000005 L 823.2526041666667 72.70740000000005 L 823.2526041666667 72.70740000000005 L 809.0585937500001 72.70740000000005 z"
                                           cy="38.777680000000046" cx="880.0286458333335" j="11" val="14"
                                           barHeight="33.92872" barWidth="14.194010416666664"></path>
                                       <g id="SvgjsG1485" class="apexcharts-bar-goals-ผู้ป่วยนอกkers">
                                           <g id="SvgjsG1486" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1488" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1490" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1492" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1494" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1496" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1498" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1500" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1502" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1504" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1506" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                           <g id="SvgjsG1508" className="apexcharts-bar-goals-groups"
                                               class="apexcharts-hidden-element-shown"
                                               clip-path="url(#gridRectผู้ป่วยนอกkerMaskbryzc1kj)"></g>
                                       </g>
                                   </g>
                                   <g id="SvgjsG1430" class="apexcharts-datalabels" data:realIndex="0"></g>
                                   <g id="SvgjsG1457" class="apexcharts-datalabels" data:realIndex="1"></g>
                                   <g id="SvgjsG1484" class="apexcharts-datalabels" data:realIndex="2"></g>
                               </g>
                               <line id="SvgjsLine1535" x1="0" y1="0" x2="851.640625" y2="0" stroke="#b6b6b6"
                                   stroke-dasharray="0" stroke-width="1" stroke-linecap="butt"
                                   class="apexcharts-ycrosshairs"></line>
                               <line id="SvgjsLine1536" x1="0" y1="0" x2="851.640625" y2="0" stroke-dasharray="0"
                                   stroke-width="0" stroke-linecap="butt" class="apexcharts-ycrosshairs-hidden"></line>
                               <g id="SvgjsG1537" class="apexcharts-xaxis" transform="translate(0, 0)">
                                   <g id="SvgjsG1538" class="apexcharts-xaxis-texts-g" transform="translate(0, -4)">
                                       <text id="SvgjsText1540" font-family="Helvetica, Arial, sans-serif"
                                           x="35.485026041666664" y="271.348" text-anchor="middle"
                                           dominant-baseline="auto" font-size="12px" font-weight="400" fill="#373d3f"
                                           class="apexcharts-text apexcharts-xaxis-label "
                                           style="font-family: Helvetica, Arial, sans-serif;">
                                           <tspan id="SvgjsTspan1541">บริหาร</tspan>
                                           <title>บริหาร</title>
                                       </text><text id="SvgjsText1543" font-family="Helvetica, Arial, sans-serif"
                                           x="106.455078125" y="271.348" text-anchor="middle" dominant-baseline="auto"
                                           font-size="12px" font-weight="400" fill="#373d3f"
                                           class="apexcharts-text apexcharts-xaxis-label "
                                           style="font-family: Helvetica, Arial, sans-serif;">
                                           <tspan id="SvgjsTspan1544">งานประกันสุขภาพ</tspan>
                                           <title>งานประกันสุขภาพ</title>
                                       </text><text id="SvgjsText1546" font-family="Helvetica, Arial, sans-serif"
                                           x="177.42513020833334" y="271.348" text-anchor="middle"
                                           dominant-baseline="auto" font-size="12px" font-weight="400" fill="#373d3f"
                                           class="apexcharts-text apexcharts-xaxis-label "
                                           style="font-family: Helvetica, Arial, sans-serif;">
                                           <tspan id="SvgjsTspan1547">ผู้ป่วยนอก</tspan>
                                           <title>ผู้ป่วยนอก</title>
                                       </text><text id="SvgjsText1549" font-family="Helvetica, Arial, sans-serif"
                                           x="248.39518229166666" y="271.348" text-anchor="middle"
                                           dominant-baseline="auto" font-size="12px" font-weight="400" fill="#373d3f"
                                           class="apexcharts-text apexcharts-xaxis-label "
                                           style="font-family: Helvetica, Arial, sans-serif;">
                                           <tspan id="SvgjsTspan1550">ผู้ป่วยใน</tspan>
                                           <title>ผู้ป่วยใน</title>
                                       </text><text id="SvgjsText1552" font-family="Helvetica, Arial, sans-serif"
                                           x="319.36523437499994" y="271.348" text-anchor="middle"
                                           dominant-baseline="auto" font-size="12px" font-weight="400" fill="#373d3f"
                                           class="apexcharts-text apexcharts-xaxis-label "
                                           style="font-family: Helvetica, Arial, sans-serif;">
                                           <tspan id="SvgjsTspan1553">ห้องบัตร</tspan>
                                           <title>ห้องบัตร</title>
                                       </text><text id="SvgjsText1555" font-family="Helvetica, Arial, sans-serif"
                                           x="390.33528645833326" y="271.348" text-anchor="middle"
                                           dominant-baseline="auto" font-size="12px" font-weight="400" fill="#373d3f"
                                           class="apexcharts-text apexcharts-xaxis-label "
                                           style="font-family: Helvetica, Arial, sans-serif;">
                                           <tspan id="SvgjsTspan1556">กายภาพบำบัด</tspan>
                                           <title>กายภาพบำบัด</title>
                                       </text><text id="SvgjsText1558" font-family="Helvetica, Arial, sans-serif"
                                           x="461.3053385416666" y="271.348" text-anchor="middle"
                                           dominant-baseline="auto" font-size="12px" font-weight="400" fill="#373d3f"
                                           class="apexcharts-text apexcharts-xaxis-label "
                                           style="font-family: Helvetica, Arial, sans-serif;">
                                           <tspan id="SvgjsTspan1559">ทันตกรรม</tspan>
                                           <title>ทันตกรรม</title>
                                       </text><text id="SvgjsText1561" font-family="Helvetica, Arial, sans-serif"
                                           x="532.275390625" y="271.348" text-anchor="middle" dominant-baseline="auto"
                                           font-size="12px" font-weight="400" fill="#373d3f"
                                           class="apexcharts-text apexcharts-xaxis-label "
                                           style="font-family: Helvetica, Arial, sans-serif;">
                                           <tspan id="SvgjsTspan1562">เภสัชกรรม</tspan>
                                           <title>เภสัชกรรม</title>
                                       </text><text id="SvgjsText1564" font-family="Helvetica, Arial, sans-serif"
                                           x="603.2454427083334" y="271.348" text-anchor="middle"
                                           dominant-baseline="auto" font-size="12px" font-weight="400" fill="#373d3f"
                                           class="apexcharts-text apexcharts-xaxis-label "
                                           style="font-family: Helvetica, Arial, sans-serif;">
                                           <tspan id="SvgjsTspan1565">พนักงานขับรถ</tspan>
                                           <title>พนักงานขับรถ</title>
                                       </text>
                                   </g>
                               </g>
                               <g id="SvgjsG1596" class="apexcharts-yaxis-annotations"></g>
                               <g id="SvgjsG1597" class="apexcharts-xaxis-annotations"></g>
                               <g id="SvgjsG1598" class="apexcharts-point-annotations"></g>
                           </g>
                       </svg>
                       <div class="apexcharts-tooltip apexcharts-theme-light">
                           <div class="apexcharts-tooltip-title"
                               style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;"></div>
                           <div class="apexcharts-tooltip-series-group" style="order: 1;"><span
                                   class="apexcharts-tooltip-ผู้ป่วยนอกker"
                                   style="background-color: rgb(223, 90, 90);"></span>
                               <div class="apexcharts-tooltip-text"
                                   style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                   <div class="apexcharts-tooltip-y-group"><span
                                           class="apexcharts-tooltip-text-y-label"></span><span
                                           class="apexcharts-tooltip-text-y-value"></span></div>
                                   <div class="apexcharts-tooltip-goals-group"><span
                                           class="apexcharts-tooltip-text-goals-label"></span><span
                                           class="apexcharts-tooltip-text-goals-value"></span></div>
                                   <div class="apexcharts-tooltip-z-group"><span
                                           class="apexcharts-tooltip-text-z-label"></span><span
                                           class="apexcharts-tooltip-text-z-value"></span></div>
                               </div>
                           </div>

                       </div>
                       <div
                           class="apexcharts-yaxistooltip apexcharts-yaxistooltip-0 apexcharts-yaxistooltip-left apexcharts-theme-light">
                           <div class="apexcharts-yaxistooltip-text"></div>
                       </div>
                       <div class="apexcharts-toolbar" style="top: 0px; right: 3px;">
                           <div class="apexcharts-menu-icon" title="Menu"><svg xmlns="http://www.w3.org/2000/svg"
                                   width="24" height="24" viewBox="0 0 24 24">
                                   <path fill="none" d="M0 0h24v24H0V0z"></path>
                                   <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"></path>
                               </svg></div>

                       </div>
                   </div>
               </div>
           </div>
       </div> -->