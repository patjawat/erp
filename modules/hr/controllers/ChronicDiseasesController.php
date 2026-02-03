<?php

namespace app\modules\hr\controllers;

use app\components\ModalHelper;
use Yii;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use app\models\CategoriseSearch;
use yii\web\NotFoundHttpException;

/**
 * ChronicDiseasesController implements the CRUD actions for Categorise model.
 */
class ChronicDiseasesController extends Controller
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
     * Lists all Categorise models.
     *
     * @return string
     */
    public function actionIndex()
    {

        $searchModel = new CategoriseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'chronic_diseases']);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**
     * Displays a single Categorise model.
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
public function actionCreate()
    {
        $name = 'chronic_diseases';
        $title = $this->request->get('title');
        $code = Categorise::find()->where(['name' => $name])->max(new \yii\db\Expression('CAST(code AS UNSIGNED)'))+1;
        $model = new Categorise([
            'name' => $name,
            'code' => $code
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                if($this->request->isAjax){
                    Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'url' => Url::to(['/hr/categorise','name' => $model->name,'title' => $title])
                ];
            }
            }
        } else {
            $model->loadDefaultValues();
        }

        if($this->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => $title,
                    'name' => $name,
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer' => ModalHelper::modalFooterSaveClose()
                ];
            }else{
                return $this->render('create', [
                    'title' => $title,
                    'name' => $name,
                    'model' => $model,
                ]);
            }
    }

    /**
     * Updates an existing Categorise model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $title = $this->request->get('title');
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            // return $this->redirect(['view', 'id' => $model->id]);
            if($this->request->isAjax){
                Yii::$app->response->format = Response::FORMAT_JSON;

                $model->validate();
                $result = [];
                // The code below comes from ActiveForm::validate(). We do not need to validate the model
                // again, as it was already validated by save(). Just collect the messages.
                foreach ($model->getErrors() as $attribute => $errors) {
                    $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
                }
                if (!empty($result)) {
                    return $this->asJson($result);
                }

                if($model->save()){ 
                    return [
                        'status' => 'success',
                        'url' => Url::to(['/hr/categorise','name' =>$model->name,'title' => $title])
                    ];
                }
                }else{
                    return $this->render('index', [
                        'model' => $model,
                    ]);
                }
        }

        if($this->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                    ])
                ];
            }else{
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
    }

    /**
     * Deletes an existing Categorise model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        if($model->delete()){
            return [
                'status' => 'success',
                'container' => '#chronic-diseases',
                'url' => Url::to(['/hr/chronic-diseases'])
            ];
        }
    }


         // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Categorise();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
             $model->title == "" ? $model->addError('title', $requiredName) : null;
           
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }

    /**
     * Finds the Categorise model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Categorise the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Categorise::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
