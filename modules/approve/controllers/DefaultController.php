<?php

namespace app\modules\approve\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use app\components\UserHelper;

/**
 * Default controller for the `Approve` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }


    public function actionUpdateFilterStatus()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $data = Yii::$app->request->post('status', []);
        $name = Yii::$app->request->post('name');
        $checkedStatuses = is_array($data) ? $data : [];
        $model = Categorise::findOne(['name' => $name, 'emp_id' => UserHelper::GetEmployee()->id]);

        if (!$model) {
            $model = new Categorise();
            $model->name = $name;
            $model->emp_id = UserHelper::GetEmployee()->id;
        }
        $model->data_json = $checkedStatuses;
        if ($model->save(false)) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'error'];
        }
    }
    
}
