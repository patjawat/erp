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
    $.post('$createUrl', { parent_id: data.parent, name: data.node.text }, function(res){
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
JS;
$this->registerJs($js);
?>

<div id="jstree_demo"></div>