<?php

namespace app\modules\hr\controllers;

use Yii;
use app\models\Tree;
use yii\helpers\Html;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use app\models\CategoriseSearch;

use yii\web\NotFoundHttpException;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;

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
        foreach ($children as $child) {
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

        if (!$parent_id) { // root node
            $node->makeRoot();
        } else {
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
        if ($node) {
            $node->name = $name;
            $node->save(false);
            return ['success' => true];
        }
        return ['error' => 'Cannot rename node'];
    }

    public function actionDeleteNode()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');

        $node = Organization::findOne($id);
        if ($node) {
            $node->deleteWithChildren();
            return ['success' => true];
        }

        return ['error' => 'Cannot delete node'];
    }
    public function actionOrgTree()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $roots = Organization::find()->roots()->all();

        $build = function ($node) use (&$build) {
            return [
                'id' => (string)$node->id,
                'data' => [
                    'name' => $node->name,
                    // 'imageURL' => $node->image_url ?? 'https://i.pravatar.cc/150?img=68', // กำหนด default
                    // 'imageURL' => 'https://i.pravatar.cc/50?img=' . rand(1,70),
                    'imageURL' => isset($node->data_json['leader1']) ? $this->getEmployee($node->data_json['leader1']) : '',
                    'avatar' => isset($node->data_json['leader1']) ? $this->getEmployee($node->data_json['leader1']) : '',
                    'borderColor' => $node->border_color ?? '#94ddff', // กำหนด default
                ],
                'children' => array_map($build, $node->children()->all()),
            ];
        };

        $tree = array_map($build, $roots);

        // ถ้า root มีแค่ 1 node ให้ส่ง node นั้น, ถ้ามากกว่า 1 ให้สร้าง root เปล่า
        return count($tree) === 1 ? $tree[0] : [
            'id' => 'root',
            'data' => [
                'name' => 'Root',
                'imageURL' => '',
                'borderColor' => '#cccccc',
            ],
            'children' => $tree,
        ];
    }

   protected function getEmployee($id)
    {
        $model = Employees::findOne($id);
        if($model){
            return Html::img($model->showAvatar(),['class' => 'avatar avatar-sm']);
        }
    }
    
}
