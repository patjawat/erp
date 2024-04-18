<div class="card">
    <div class="card-body">
        <div id="line-chart-container" style="width:100%;height:390px;"></div>
       
    </div>
</div>


<?php
use yii\web\View;
$js = <<< JS


var dom = document.getElementById('line-chart-container');
var myChart = echarts.init(dom, null, {
  renderer: 'canvas',
  useDirtyRect: false
});
var app = {};

var option;


option = {
    title : {
        text: 'คะแนนประเมินงาน',
    },
    tooltip : {
        trigger: 'axis'
    },
    legend: {
        data:['蒸发量','降水量']
    },
    toolbox: {
        show : true,
        feature : {
            mark : {show: true},
            dataView : {show: true, readOnly: false},
            magicType : {show: true, type: ['line', 'bar']},
            restore : {show: true},
            saveAsImage : {show: true}
        }
    },
    calculable : true,
    xAxis : [
        {
            type : 'category',
            data : ['ปี 2563','ปี 2564','ปี 2565','ปี 2566','ปี 2567','ปี 2568','ปี 2569','ปี 2570']
        }
    ],
    yAxis : [
        {
            type : 'value'
        }
    ],
    series : [
        {
            name:'คะแนน',
            type:'bar',
            data:[2.0, 4.9, 7.0, 23.2, 25.6, 76.7, 135.6, 162.2, 32.6, 20.0, 6.4, 3.3],
            markPoint : {
                data : [
                    {type : 'max', name: '8tcoo'},
                    {type : 'min', name: '最小值'}
                ]
            },
            markLine : {
                data : [
                    {type : 'average', name: '平均值'}
                ]
            }
        },
        {
            name:'เงิน',
            type:'bar',
            data:[2.6, 5.9, 9.0, 26.4, 28.7, 70.7, 175.6, 182.2, 48.7, 18.8, 6.0, 2.3],
            markPoint : {
                data : [
                    {name : '年最高', value : 182.2, xAxis: 7, yAxis: 183, symbolSize:18},
                    {name : '年最低', value : 2.3, xAxis: 11, yAxis: 3}
                ]
            },
            markLine : {
                data : [
                    {type : 'average', name : '平均值'}
                ]
            }
        }
    ]
};
                    

// option = {
//     title : {
//         text: 'คะแนนประเมินงาน',
//         // subtext: 'ปป'
//     },
//     tooltip : {
//         trigger: 'axis'
//     },
//     legend: {
//         data:['蒸发量']
//     },
//     toolbox: {
//         show : true,
//         feature : {
//             mark : {show: true},
//             dataView : {show: true, readOnly: false},
//             magicType : {show: true, type: ['line', 'bar']},
//             restore : {show: true},
//             saveAsImage : {show: true}
//         }
//     },
//     calculable : true,
//     xAxis : [
//         {
//             type : 'category',
//             data : ['ปี 2563','ปี 2564','ปี 2565','ปี 2566','ปี 2567','ปี 2568','ปี 2569','ปี 2570']
//         }
//     ],
//     yAxis : [
//         {
//             type : 'value'
//         }
//     ],
//     series : [
//         {
//             name:'คะแนนการประเมิณ',
//             type:'bar',
//             data:[2.0, 4.9, 7.0, 23.2, 25.6, 76.7, 135.6, 162.2],
//             markPoint : {
//                 data : [
//                     {type : 'max', name: '最大值'},
//                     {type : 'min', name: '最小值'}
//                 ]
//             },
//             markLine : {
//                 data : [
//                     {type : 'average', name: '平均值'}
//                 ]
//             }
//         },
//         // {
//         //     name:'降水量',
//         //     type:'bar',
//         //     data:[2.6, 5.9, 9.0, 26.4, 28.7, 70.7, 175.6, 182.2, 48.7, 18.8, 6.0, 2.3],
//         //     markPoint : {
//         //         data : [
//         //             {name : '年最高', value : 182.2, xAxis: 7, yAxis: 183, symbolSize:18},
//         //             {name : '年最低', value : 2.3, xAxis: 11, yAxis: 3}
//         //         ]
//         //     },
//         //     markLine : {
//         //         data : [
//         //             {type : 'average', name : '平均值'}
//         //         ]
//         //     }
//         // }
//     ]
// };
       
if (option && typeof option === 'object') {
  myChart.setOption(option);
}

window.addEventListener('resize', myChart.resize);


JS;
$this->registerJS($js,View::POS_END)
?>