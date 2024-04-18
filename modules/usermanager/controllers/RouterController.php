<?php

namespace app\modules\usermanager\controllers;
use Yii;
use app\modules\usermanager\models\AuthItemRouter;
use app\modules\usermanager\models\AuthItemRouterSearch;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class RouterController extends \yii\web\Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new AuthItemRouterSearch([
            'type' => 2
        ]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = ['pageSize' => 10];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $model = new AuthItemRouter();

        if ($model->load(Yii::$app->request->post()) && $model->save(false)) {
            return  $model->save();
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->renderAjax('_form', [
            'model' => $model,
        ]);
        }else{
            return $this->render('_form', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model =  AuthItemRouter::findOne([
            'name' => $id
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->save(false)) {
            AuthItemRouter::deleteAll([
                'name' => $id
            ]);
            return  $model->save();
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->renderAjax('_form', [
            'model' => $model,
        ]);
        }else{
            return $this->render('_form', [
                'model' => $model,
            ]);
        }
    }

    public function actionDelete($id)
    {
         AuthItemRouter::deleteAll(['name' => $id]);
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return true;
        }
    }


}
