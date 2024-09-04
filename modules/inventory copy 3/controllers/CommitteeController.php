<?php

namespace app\modules\inventory\controllers;

use app\modules\inventory\models\StockEvent;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Yii;


class CommitteeController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }


    public function actionCreate()
    {
        $model = new StockEvent([
            'category_id' => $this->request->get('id'),
            'name' => $this->request->get('name'),
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save(false)) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => $this->request->get('title'),
                    'status' => 'success',
                    'container' => '#inventory',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdate($id)
    {

        $model = $this->findModel($id);
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => $this->request->get('title'),
                    'status' => 'success',
                    'container' => '#inventory',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form', [
                'model' => $model,
            ]);
        }
    }

    //คณะกรรมการตรวจรับพัสดุเข้าคลัง
    public function actionList($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        // $model = StockEvent::findOne(['id' => $id,'name' => 'receive_committee']);
        $model = $this->findModel($id);
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('list', ['model' => $model]),
        ];
    }
    protected function findModel($id)
    {
        if (($model = StockEvent::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
