<?php

namespace app\modules\hr\controllers;

use app\models\Categorise;
use app\models\CategoriseSearch;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\modules\hr\models\Organization;

use app\models\Tree;

class TreeController extends \yii\web\Controller
{

        // โหลด form สำหรับ create/edit
    public function actionForm($id = null, $parent_id = null)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if ($id) {
            $model = Organization::findOne($id);
            if (!$model) throw new NotFoundHttpException("Node not found");
        } else {
            $model = new Organization();
            $model->parent_id = $parent_id;
        }
        // return $model;

        return $this->renderAjax('@app/modules/hr/views/organization/diagram-v2/_form', ['model' => $model]);
    }


    public function actionGetNodes()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $nodes = Organization::find()->all();
    $result = [];

    foreach ($nodes as $node) {
        $parent = $node->isRoot() ? '#' : (string)$node->parents(1)->one()->id;
        $result[] = [
            'id' => (string)$node->id,   // ensure string
            'parent' => $parent ?: '#',
            'text' => $node->name,
            'icon' => $node->icon ?: 'folder',
            'state' => [
                'opened' => !$node->collapsed,
                'selected' => (bool)$node->selected,
                'disabled' => (bool)$node->disabled,
            ],
            'data' => $node->data_json,
        ];
    }

    return $result;
}

private function buildNode($node)
{
    $children = $node->children()->all();
    $childNodes = [];
    foreach($children as $child){
        $childNodes[] = $this->buildNode($child);
    }

    // ถ้า root → ใช้ "#"
    // ถ้าไม่ใช่ root → ดึง parent ถ้ามี
    $parent = '#';
    if (!$node->isRoot()) {
        $parentNode = $node->parents(1)->one();
        if ($parentNode) {
            $parent = $parentNode->id;
        }
    }

    return [
        'id' => $node->id,
        'parent' => $parent,
        'text' => $node->name,
        'icon' => $node->icon ?: 'folder',
        'state' => [
            'selected' => (bool)$node->selected,
            'disabled' => (bool)$node->disabled,
            'opened' => !$node->collapsed,
        ],
        'children' => $childNodes,
        'data' => $node->data_json,
    ];
}


    public function actionCreateNode()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $parent_id = Yii::$app->request->post('parent_id');
        $name = Yii::$app->request->post('name', 'New Folder');

        $node = new Organization();
        $node->name = $name;

        if(!$parent_id){ // root node
            $node->makeRoot();
        }else{
            $parent = Organization::findOne($parent_id);
            $node->appendTo($parent);
        }

        return ['id' => $node->id];
    }

    public function actionRenameNode()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $name = Yii::$app->request->post('name');

        $node = Organization::findOne($id);
        if($node){
            $node->name = $name;
            $node->save(false);
            return ['success'=>true];
        }
        return ['error'=>'Cannot rename node'];
    }

    public function actionDeleteNode()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');

        $node = Organization::findOne($id);
        if($node){
            $node->deleteWithChildren();
            return ['success'=>true];
        }

        return ['error'=>'Cannot delete node'];
    }
public function actionOrgTree()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $roots = Organization::find()->roots()->all();

    $build = function($node) use (&$build) {
        $data = $node->data_json ? $node->data_json : [];
        $item = [
            'id' => (string)$node->id,
            'name' => $node->name,
            // 'title' => $data['leader1_fullname'] ?? '', // ✅ กัน undefined
            'title' => $data['leader1_fullname'] ?? '', // ✅ กัน undefined
            'children' => [],
        ];
        foreach ($node->children()->all() as $child) {
            $item['children'][] = $build($child);
        }
        return $item;
    };

    $tree = [];
    foreach ($roots as $root) {
        $tree[] = $build($root);
    }

    return count($tree) === 1 ? $tree[0] : ['id' => 'root', 'name' => '', 'children' => $tree];
}

}
