<?php
use app\models\Tree;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\tree\TreeView;


use kartik\tree\TreeViewInput;

// use muhsamsul\treeimage\TreeImage;
use app\widgets\orgchart\TreeImage;
use app\modules\hr\models\Organization;
$this->title = "ผังโครงสร้างองค์กร";

$this->params['breadcrumbs'][] = $this->title;

\app\assets\JsTreeAsset::register($this);
$this->registerCssFile('https://cdn.jsdelivr.net/npm/jstree/dist/themes/default/style.min.css');
?>


<?php

$getUrl = \yii\helpers\Url::to(['tree/get-nodes']);
$createUrl = \yii\helpers\Url::to(['tree/create-node']);
$renameUrl = \yii\helpers\Url::to(['tree/rename-node']);
$deleteUrl = \yii\helpers\Url::to(['tree/delete-node']);

$this->registerJsFile('https://cdn.jsdelivr.net/npm/apextree', ['depends' => \yii\web\JqueryAsset::class]);
$orgUrl = \yii\helpers\Url::to(['tree/org-tree']);

?>

<?php
$js = <<<JS
$('#jstree_demo').jstree({
    'core': {
        'check_callback': true,
        'data': {
            'url': '$getUrl',
            'dataType': 'json'
        }
    },
    'plugins': ['contextmenu', 'dnd', 'state']
});

// Create node
$('#jstree_demo').on('create_node.jstree', function(e, data){
    $.post('$createUrl', { parent_id: data.parent === '#' ? null : data.parent, name: data.node.text }, function(res){
        if(res.id) data.instance.set_id(data.node, res.id);
    }, 'json');
});

// Rename node
$('#jstree_demo').on('rename_node.jstree', function(e, data){
    $.post('$renameUrl', { id: data.node.id, name: data.text });
});

// Delete node
$('#jstree_demo').on('delete_node.jstree', function(e, data){
    $.post('$deleteUrl', { id: data.node.id });
});

fetch('/hr/tree/org-tree')
  .then(r => r.json())
  .then(data => {
    const tree = new ApexTree(document.getElementById('org-chart'), {
      direction: 'left',
      nodeWidth: 200,
      nodeHeight: 70,
      enableToolbar: true,
      nodeTemplate: content => `
        <div style="padding:8px; border-radius:8px; background:#f0f4ff; border:1px solid #ccc; text-align:center">
          <strong>\${content.name || ''}</strong><br>
          <span style="font-size:12px;color:#666">\${content.title || ''}</span>
        </div>
      `
    });
    tree.render(data);
  });

JS;
$this->registerJs($js);
?>

<div class="card">
    <div class="card-body">
        <div id="jstree_demo"></div>
    </div>
</div>

<div id="org-chart"></div>
