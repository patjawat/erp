<?php
use yii\helpers\Html;
$this->registerJsFile('@web/org-chart/jquery.orgchart.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerCssFile("@web/org-chart/jquery.orgchart.css");
?>
<style>
.text-wrap {
    white-space: normal;
}
</style>
<div class="card">
    <div class="card-body">
        <h4 class="card-title text-center">ผังองค์กร</h4>
    </div>
</div>


<div id="orgChartContainer">
      <div id="orgChart"></div>
    </div>
    <div id="consoleOutput">
    </div>

<?php
$js = <<< JS

var testData = [
        {id: 1, name: 'ผู้อำนวยการ', parent: 0},
        {id: 2, name: 'กลุ่มงานบริหารทั่วไป', parent: 1},
        {id: 3, name: 'กลุ่มงานเทคนิคการแพทย์', parent: 1},
        {id: 4, name: 'Division 2', parent: 1},
        {id: 6, name: 'Division 3', parent: 1},
        {id: 7, name: 'Division 4', parent: 1},
        {id: 8, name: 'Division 5', parent: 1},
        {id: 5, name: 'Sub Division', parent: 3},
        
    ];
    $(function(){
        org_chart = $('#orgChart').orgChart({
            data: testData,
            showControls: true,
            allowEdit: true,
            onAddNode: function(node){ 
                log('Created new node on node '+node.data.id);
                org_chart.newNode(node.data.id); 
            },
            onDeleteNode: function(node){
                log('Deleted node '+node.data.id);
                org_chart.deleteNode(node.data.id); 
            },
            onClickNode: function(node){
                log('Clicked node '+node.data.id);
            }

        });
    });

    // just for example purpose
    function log(text){
        $('#consoleOutput').append('<p>'+text+'</p>')
    }

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-36251023-1']);
  _gaq.push(['_setDomainName', 'jqueryscript.net']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();


JS;
$this->registerJS($js);
?>