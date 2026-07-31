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
        $model = new HelpdeskDetail();
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
        $helpdeskId = (int) $this->request->get('helpdesk_id');
        $helpdesk = $this->findHelpdeskModel($helpdeskId);
        $model = new HelpdeskDetail([
            'helpdesk_id' => $helpdeskId,
        ]);
        $eligibleTechnicians = Helpdesk::TechnicianList($helpdesk->repair_group);
        $eligibleTechnicianIds = array_map(
            static fn($employee): int => (int) $employee->id,
            $eligibleTechnicians
        );
        $assignedTechnicianIds = array_map('intval', HelpdeskDetail::find()
            ->select('emp_id')
            ->where([
                'helpdesk_id' => $helpdeskId,
                'name' => 'repair_team',
            ])
            ->column());

        if ($this->request->isPost) {
            if (!$model->load($this->request->post())) {
                return [
                    'status' => 'error',
                    'message' => 'รูปแบบข้อมูลไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง',
                ];
            }

            $model->helpdesk_id = $helpdeskId;
            $model->emp_id = (int) $model->emp_id;
            $model->name = 'repair_team';
            $model->status = 'active';
            $model->title = 'ช่างผู้รับผิดชอบงานซ่อม';

            if (Helpdesk::repairRoleName($helpdesk->repair_group) === null) {
                $model->addError('emp_id', 'ใบแจ้งซ่อมยังไม่ได้ระบุแผนกช่างที่รับงาน');
            } elseif ($model->emp_id <= 0) {
                $model->addError('emp_id', 'กรุณาเลือกช่างผู้รับผิดชอบ');
            } elseif (!in_array((int) $model->emp_id, $eligibleTechnicianIds, true)) {
                $model->addError('emp_id', 'บุคลากรที่เลือกไม่มีสิทธิ์ในระบบงานซ่อมของแผนกนี้');
            } elseif (in_array((int) $model->emp_id, $assignedTechnicianIds, true)) {
                $model->addError('emp_id', 'ช่างคนนี้ได้รับมอบหมายในงานซ่อมแล้ว');
            }

            if (!$model->hasErrors() && $model->save()) {
                return [
                    'status' => 'success',
                    'message' => 'เพิ่มช่างผู้รับผิดชอบเรียบร้อยแล้ว',
                ];
            }

            $message = $model->getFirstError('emp_id') ?: 'ไม่สามารถเพิ่มช่างผู้รับผิดชอบได้';
            return [
                'status' => 'error',
                'message' => $message,
                'errors' => $model->getErrors(),
            ];
        }

        $model->loadDefaultValues();
        $availableTechnicians = array_values(array_filter(
            $eligibleTechnicians,
            static fn($employee): bool => !in_array((int) $employee->id, $assignedTechnicianIds, true)
        ));

        return [
            'title' => 'เพิ่มช่างผู้รับผิดชอบ',
            'content' => $this->renderAjax('_form', [
                'model' => $model,
                'helpdesk' => $helpdesk,
                'technicians' => $availableTechnicians,
                'repairGroupLabel' => $helpdesk->viewRepairGroup() ?: 'ยังไม่ระบุแผนกช่าง',
            ]),
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

    private function findHelpdeskModel(int $id): Helpdesk
    {
        if ($id > 0 && ($model = Helpdesk::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('ไม่พบใบแจ้งซ่อมที่ต้องการมอบหมายช่าง');
    }
}
