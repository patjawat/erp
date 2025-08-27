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
    public function actionGetNodes()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $nodes = Organization::find()->all();
        $result = [];
        foreach ($nodes as $node) {
            $result[] = [
                'id' => $node->id,
                'parent' => $node->root == 1 ?  '#' : $node->root, // root node = #
                'text' => $node->name,
                'icon' => $node->icon ?: 'folder',
                'state' => [
                    'selected' => (bool)$node->selected,
                    'disabled' => (bool)$node->disabled,
                    'opened' => !$node->collapsed,
                ],
                'data' => $node->data_json,
            ];
            // $result[] = [
            //     'id' => $node->id,
            //     'parent' => $node->root ?: '#',
            //     'text' => $node->name,
            //     'icon' => $node->icon ?: 'folder',
            //     'state' => [
            //         'selected' => $node->selected,
            //         'disabled' => $node->disabled,
            //         'opened' => !$node->collapsed,
            //     ],
            //     'data' => $node->data_json,
            // ];
        }
        return $result;
    }

    public function actionCreateNode()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $parent_id = Yii::$app->request->post('parent_id');
        $name = Yii::$app->request->post('name', 'New Folder');

        $node = new Organization();
        $node->root = $parent_id ?: null;
        $node->name = $name;
        if ($node->save()) return ['id' => $node->id];
        return ['error' => 'Cannot create node'];
    }

    public function actionRenameNode()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $name = Yii::$app->request->post('name');

        $node = Organization::findOne($id);
        if ($node) {
            $node->name = $name;
            if ($node->save()) return ['success' => true];
        }
        return ['error' => 'Cannot rename node'];
    }

    public function actionDeleteNode()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $node = Organization::findOne($id);
        if ($node && $node->delete()) return ['success' => true];
        return ['error' => 'Cannot delete node'];
    }
}
