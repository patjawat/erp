<?php

namespace app\modules\helpdesk\controllers;
use Yii;
use yii\helpers\Html;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\helpdesk\models\HelpdeskSearch;

/**
 * TeamController implements the CRUD actions for Helpdesk model.
 */
class SparePartController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Helpdesk models.
     *
     * @return string
     */
    public function actionIndex()
    {
      

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'เบิกอะไหล่',
                'content' => $this->renderAjax('index'),
            ];
        }else{
            return $this->render('index');
        }
    }

}
