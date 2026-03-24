<?php

namespace app\modules\helpdesk2\controllers;
use Yii;
use yii\helpers\Html;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\helpdesk2\models\HelpdeskDetail;
use app\modules\helpdesk2\models\HelpdeskSearch;
use app\modules\hr\models\Employees;

/**
 * TeamController implements the CRUD actions for Helpdesk model.
 */
class TeamController extends Controller
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
        $category_id = $this->request->get('category_id');
        $searchModel = new HelpdeskSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['category_id' => $category_id,'name' => 'repair_team']);
        
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'ผู้ร่วมดำเนินงาน',
                'content' => $this->renderAjax('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        }else{
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }
    
    public function actionList()
    {
        $helpdesk_id = $this->request->get('helpdesk_id');
        $listTeam = HelpdeskDetail::find()->where(['name' => 'repair_team','helpdesk_id' => $helpdesk_id])->all();
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'ผู้ร่วมดำเนินงาน',
                'content' => $this->renderAjax('list', [
                    'listTeam' => $listTeam,
                ]),
            ];
        }else{
            return $this->render('list', [
                'listTeam' => $listTeam,
            ]);
        }
    }


    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Helpdesk();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
      
            $model->emp_id == '' ? $model->addError('emp_id', $requiredName) : null;
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    
    /**
     * Displays a single Helpdesk model.
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
     * Creates a new Helpdesk model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $helpdesk_id = $this->request->get('helpdesk_id');
        $model = new HelpdeskDetail([
            'helpdesk_id' => $helpdesk_id,
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->name = 'repair_team';
                if($model->save()){
                    try {
                        $helpdesk = Helpdesk::findOne(['id' => $model->helpdesk_id]);
                        if ($helpdesk && !empty($model->emp_id)) {
                            $employee = Employees::findOne(['id' => (int) $model->emp_id]);
                            if (!$employee) {
                                $employee = Employees::findOne(['user_id' => (int) $model->emp_id]);
                            }
                            if ($employee) {
                                $helpdesk->emp_id = (int) $employee->id;
                            } else {
                                $helpdesk->emp_id = (int) $model->emp_id;
                            }
                            $helpdesk->save(false, ['emp_id']);
                        }
                    } catch (\Throwable $e) {
                        // ไม่ให้กระทบการบันทึกทีม
                    }
                    return [
                        'status' => 'success'
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return [
            'title' =>  $this->request->get('title'),
            'content' =>   $this->renderAjax('create', [
                'model' => $model,
            ])
        ];
      
    }

    /**
     * Deletes an existing Helpdesk model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $this->findModel($id)->delete();
        return [
            'status' => 'success'
        ];
    }

    /**
     * Finds the Helpdesk model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Helpdesk the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HelpdeskDetail::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
