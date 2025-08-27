<?php

use app\models\Tree;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;


use kartik\tree\TreeView;

// use muhsamsul\treeimage\TreeImage;
use kartik\tree\TreeViewInput;
use app\widgets\orgchart\TreeImage;
use app\modules\hr\models\Organization;

$this->title = "ผังโครงสร้างองค์กร";

$this->params['breadcrumbs'][] = $this->title;

\app\assets\JsTreeAsset::register($this);
$this->registerCssFile('https://cdn.jsdelivr.net/npm/jstree/dist/themes/default/style.min.css');
?>




<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('@app/modules/hr/views/employees/menu',['active' => 'employees'])?>
<?php $this->endBlock(); ?>

<?php

$getUrl = \yii\helpers\Url::to(['tree/get-nodes']);
$createUrl = \yii\helpers\Url::to(['tree/create-node']);
$renameUrl = \yii\helpers\Url::to(['tree/rename-node']);
$deleteUrl = \yii\helpers\Url::to(['tree/delete-node']);

$this->registerJsFile('https://cdn.jsdelivr.net/npm/apextree', ['depends' => \yii\web\JqueryAsset::class]);
$orgUrl = \yii\helpers\Url::to(['tree/org-tree']);
$formUrl = Url::to(['tree/form']);
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


$('#jstree_demo').on('select_node.jstree', function(e, data){
    var nodeId = data.node.id;

    // โหลด form edit ผ่าน AJAX
    $.get("$formUrl", { id: nodeId }, function(html){
        $('#modalBody').html(html);
        $('#modal').modal('show');
    });
});



fetch('/hr/tree/org-tree')
  .then(r => r.json())
  .then(data => {
    const tree = new ApexTree(document.getElementById('org-chart'), {
    width: '100%',
      height: '100%', // <-- ให้เต็ม container
        nodeWidth: 150,
        nodeHeight: 100,
        fontColor: '#fff',
        borderColor: '#333',
        childrenSpacing: 50,
        siblingSpacing: 20,
        direction: 'top',
    //   enableToolbar: true,
       contentKey: 'data', // <-- เพิ่มบรรทัดนี้
     nodeTemplate: (content) => {
  const name = content?.name ?? 'No Name';
  const imageURL = content?.imageURL ?? 'https://i.pravatar.cc/50?img=1';
  const avatar = content?.avatar ?? 'https://i.pravatar.cc/50?img=1';
  const borderColor = content?.borderColor ?? '#cccccc';

  return `

  <div class="card">
      <div class="card-body d-flex justify-content-center p-1">
        \${avatar}
    </div>
    <div class="card-footer text-muted p-2">\${name}</div>
</div>


  `;
},
    });
    tree.render(data);
  });
JS;
$this->registerJs($js);
?>
<div id="org-chart" style="width: 100%; height: 100vh;"></div>

<!-- <div class="card">
    <div class="card-body">
        <div id="jstree_demo"></div>
    </div>
</div> -->