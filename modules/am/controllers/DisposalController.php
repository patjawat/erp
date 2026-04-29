<?php

namespace app\modules\am\controllers;

use Yii;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * DisposalController implements the CRUD actions for Asset Disposal.
 */
class DisposalController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all disposed Asset models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AssetSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        // กรองเฉพาะครุภัณฑ์ที่จำหน่ายแล้ว
        $dataProvider->query->andWhere(['lifecycle_status' => Asset::LIFECYCLE_DISPOSED]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single disposed Asset model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Finds the Asset model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Asset the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Asset::findOne(['id' => $id, 'lifecycle_status' => Asset::LIFECYCLE_DISPOSED])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('ไม่พบข้อมูลครุภัณฑ์ที่ถูกจำหน่าย.');
    }
}
