<?php

namespace app\modules\helpdesk\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\am\models\Asset;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\Employees;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\helpdesk\models\HelpdeskSearch;

/**
 * RepairController implements the CRUD actions for Repair model.
 */
class RepairController extends Controller
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
     * Lists all Repair models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new HelpdeskSearch([
            'repair_group' => $this->request->get('repair_group'),
            'status' => $this->request->get('status')
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'repair']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'code', $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.title')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.repair_note')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.note')"), $searchModel->q],
        ]);
        if($searchModel->date_between){
            try {
               $dataProvider->query->andFilterWhere([
                   'between', 
                   new Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.\"{$searchModel->date_between}\"'))"),  
                   AppHelper::convertToGregorian($searchModel->date_start), 
                   AppHelper::convertToGregorian($searchModel->date_end), 
                ]);
                            //code...
            } catch (\Throwable $th) {
                //throw $th;
            }
            }
        // $dataProvider->query->andFilterWhere(['status' => 1]);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '',
                'content' => $this->renderAjax('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    // แสดงรายการแจ้งซ่อม (ร้องขอ)
    // public function actionUserRequestOrder()
    // {
    //     $searchModel = new HelpdeskSearch([
    //         'repair_group' => $this->request->get('repair_group'),
    //         'status' => $this->request->get('status')
    //     ]);
    //     $dataProvider = $searchModel->search($this->request->queryParams);
    //     $dataProvider->query->andFilterWhere(['name' => 'repair']);

    //     if ($this->request->isAjax) {
    //         Yii::$app->response->format = Response::FORMAT_JSON;
    //         return [
    //             'title' => '',
    //             'content' => $this->renderAjax('_user_request_order', [
    //                 'searchModel' => $searchModel,
    //                 'dataProvider' => $dataProvider,
    //             ]),
    //         ];
    //     } else {
    //         return $this->render('_user_request_order', [
    //             'searchModel' => $searchModel,
    //             'dataProvider' => $dataProvider,
    //         ]);
    //     }
    // }

    // ปริมาณงานต่อคน
    // public function actionUserJob()
    // {
    //     $repair_group = $this->request->get('repair_group');
    //     $auth_item = $this->request->get('auth_item');

    //     $sql = "SELECT x3.*,ROUND(((x3.rating_user/ x3.total_user) * 100),0) as p FROM ( SELECT x1.*,
    //         (SELECT (count(h.id)) FROM helpdesk h  WHERE h.name = 'repair' AND h.repair_group = :repair_group AND JSON_CONTAINS(h.data_json->'\$.join',CONCAT('" . '"' . "',x1.user_id,'" . '"' . "'))) as rating_user
    //         FROM (SELECT DISTINCT e.user_id, concat(e.fname,' ',e.lname) as fullname,
    //         (SELECT count(DISTINCT id) FROM employees e INNER JOIN auth_assignment a ON a.user_id = e.user_id) as total_user
    //         FROM employees e
    //         INNER JOIN auth_assignment a ON a.user_id = e.user_id  where a.item_name = :auth_item) as x1
    //         GROUP BY x1.user_id) as x3;";
    //     $querys = Yii::$app
    //         ->db
    //         ->createCommand($sql)
    //         ->bindValue(':repair_group', $repair_group)
    //         ->bindValue(':auth_item', $auth_item)
    //         ->queryAll();

    //     if ($this->request->isAjax) {
    //         Yii::$app->response->format = Response::FORMAT_JSON;
    //         return [
    //             'title' => $this->request->get('title'),
    //             'content' => $this->renderAjax('user_job', [
    //                 'querys' => $querys,
    //             ]),
    //         ];
    //     } else {
    //         return $this->render('user_job', [
    //             'querys' => $querys,
    //         ]);
    //     }
    // }

    // แสดงการให้คะแนน
    public function actionViewRating()
    {
        $repair_group = $this->request->get('repair_group');
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('view_rating', [
                    'repair_group' => $repair_group,
                ]),
            ];
        } else {
            return $this->render('view_rating', [
                'repair_group' => $repair_group,
            ]);
        }
    }

    // รายการที่รับเรื่องแล้ว
    public function actionListAccept()
    {
        $repairGroup = $this->request->get('repair_group');
        $searchModel = new HelpdeskSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'repair']);
        $dataProvider->query->andFilterWhere(['repair_group' => $repairGroup]);
        $dataProvider->query->andFilterWhere(['in', 'status', [2, 3]]);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    public function actionTimeline()
    {
        $id = $this->request->get('id');
        $model = HelpDesk::findOne($id);
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('timeline', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('timeline', [
                'model' => $model,
            ]);
        }
    }

    public function actionHistory()
    {
        $code = $this->request->get('code');
        $userId = Yii::$app->user->id;
        $searchModel = new HelpdeskSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        if (isset($code)) {
            $dataProvider->query->andFilterWhere(['name' => 'repair', 'code' => $code]);
        } else {
            $dataProvider->query->andFilterWhere(['name' => 'repair', 'created_by' => $userId]);
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '',
                'summary' => count($dataProvider->getModels()),
                'content' => $this->renderAjax('history', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('history', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**
     * Displays a single Repair model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;
        $title = $this->request->get('title');
        $model = $this->findModel($id);
        $asset = Asset::findOne(['code' => $model->code]);
        if ($model->code && isset($asset)) {
            return $this->render('view', [
                'model' => $model,
                'asset' => $asset
            ]);
        } else {
            return $this->render('view_general', [
                'model' => $model
            ]);
        }
    }

    // นับจำนวน
    public function actionSummary()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $repairGroup = $this->request->get('repair_group');
        $sql_old = "SELECT c.code,c.title,count(h.id) as total FROM helpdesk h
        LEFT JOIN categorise c ON c.code = h.status AND c.name = 'repair_status'
        where JSON_EXTRACT(h.data_json,'\$.send_type') = 'general'
        GROUP BY c.id";
        $sql = "SELECT c.code,c.title,count(h.id) as total FROM helpdesk h
              LEFT JOIN categorise c ON c.code = h.status AND c.name = 'repair_status'
              where h.repair_group = :repair_group
              GROUP BY c.id";
        $query = Yii::$app
            ->db
            ->createCommand($sql)
            ->bindValue(':repair_group', $repairGroup)
            ->queryAll();
        return $query;
    }

    public function actionViewTask($id)
    {
        $title = $this->request->get('title');
        $model = $this->findModel($id);
        $asset = Asset::findOne(['code' => $model->code]);
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $title,
                'content' => $this->renderAjax('view_task', ['model' => $model]),
            ];
        } else {
            if ($asset) {
                return $this->render('view', [
                    'model' => $model,
                    'asset' => $asset
                ]);
            } else {
                return $this->render('view_general', [
                    'model' => $model
                ]);
            }
        }
    }

    /**
     * Creates a new Repair model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $container = $this->request->get('container');
        $code = $this->request->get('code');

        // ตรวจสอบว่าเป็นครุภัณ์หรืแไม่
        $sqlCheckAssetType = "SELECT asset_item.title,asset_type.title,asset_type.code FROM asset a INNER JOIN categorise asset_item ON asset_item.code = a.asset_item AND asset_item.name = 'asset_item' INNER JOIN categorise asset_type ON asset_type.code = asset_item.category_id AND asset_type.name = 'asset_type' WHERE a.code = :code;";
        $checkAssetType = Yii::$app
            ->db
            ->createCommand($sqlCheckAssetType)
            ->bindValue(':code', $code)
            ->queryOne();

        // return $checkAssetType;
        $repair_group = '';
        try {
            if (isset($checkAssetType) && $checkAssetType['code'] == 11) {
                $repair_group = 3;
            } elseif ($checkAssetType['code'] == 12) {
                $repair_group = 2;
            } else {
                $repair_group = 1;
            }
            // code...
        } catch (\Throwable $th) {
            $repair_group = '';
        }

        $emp = UserHelper::GetEmployee();
        $model = new Helpdesk([
            'name' => 'repair',
            'code' => $code,
            'repair_group' => $repair_group,
            'data_json' => [
                'send_type' => $this->request->get('send_type'),
                'location' => $emp->departmentName()
            ]
        ]);

        $model->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                // && $model->save()
                $this->changAssetStatus($model->code);

                // template message
                $emp = Yii::$app->employee::GetEmployee();

                $message = 'แจ้งงานซ่อมจาก ' . $emp->departmentName() . "\nสถานที่อื่นๆ : " . $model->data_json['location_other'] . (isset($checkAssetType['title']) ? "\nประเภท :" . $checkAssetType['title'] . "\nเลขคุภัณฑ์ : " . $code : '') . "\nอาการ : " . $model->data_json['title'] . "\nความเร่งด่วน : " . $model->UrgencyName() . "\nเพิ่มเติม  : " . $model->data_json['note'] . "\nเบอร์โทร  : " . $model->data_json['note'] . "\nผู้ร้องขอ  : " . $emp->fullname;
                try {
                    $response = Yii::$app->lineNotify->sendMessage($message, $model->repair_group);
                    return [
                        'status' => 'success',
                        'container' => '#helpdesk-container',
                        'response' => $response
                    ];
                } catch (\Exception $e) {
                    return [
                        'status' => false,
                        'container' => '#helpdesk-container',
                        'error' => $e->getMessage()
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('create', [
                'model' => $model,
            ]),
        ];
    }

    /**
     * Updates an existing Repair model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldObj = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                try {
                    if ($model->status == 4) {
                        $asset = Asset::findOne(['code' => $model->code]);
                        $asset->asset_status = 1;
                        $asset->save();
                    }
                } catch (\Throwable $th) {
                    // throw $th;
                }

                $convertDate = [
                    'start_job_date' => $model->data_json['start_job_date'] !== '__/__/____' ? AppHelper::convertToGregorian($model->data_json['start_job_date']) : '',
                    'repair_type_date' => $model->data_json['repair_type_date'] !== '__/__/____' ? AppHelper::convertToGregorian($model->data_json['repair_type_date']) : '',
                    'end_job_date' => $model->data_json['end_job_date'] !== '__/__/____' ? AppHelper::convertToGregorian($model->data_json['end_job_date']) : '',
                ];
                $model->data_json = ArrayHelper::merge($oldObj,$model->data_json, $convertDate);
               $model->save();

                return [
                    'status' => 'success',
                    'container' => '#helpdesk-container',
                ];
            }
        } else {

            $model->loadDefaultValues();
            try {
            $model->data_json = [
                'start_job_date' => $model->data_json['start_job_date'] !== "" ? AppHelper::convertToThai($model->data_json['start_job_date']) : '__/__/____',
                'repair_type_date' => $model->data_json['repair_type_date'] !== "" ? AppHelper::convertToThai($model->data_json['repair_type_date']) : '__/__/____',
                'end_job_date' => $model->data_json['end_job_date'] !== "" ?  AppHelper::convertToThai($model->data_json['end_job_date']) : '__/__/____',
            ];
            
            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);

        } catch (\Throwable $th) {
            // throw $th;
        }
        }
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('update', ['model' => $model]);
        }
    }

    // ตรวจสอบความถูกต้อง
    public function actionCreateValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Helpdesk();

        if ($this->request->isPost && $model->load($this->request->post())) {
            $requiredName = 'ต้องระบุ';
            $model->data_json['title'] == '' ? $model->addError('data_json[title]', 'ต้องระบุอาการ...') : null;
            $model->data_json['urgency'] == '' ? $model->addError('data_json[urgency]', 'ต้องระบุความเร่งด่วน...') : null;
            $model->data_json['location'] == '' ? $model->addError('data_json[location]', 'ต้องระบุสถานะที่...') : null;
            $model->repair_group == '' ? $model->addError('repair_group', 'ต้องระบุ...') : null;

            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }

    // ตรวจสอบความถูกต้อง
    public function actionCancelJobValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Helpdesk();

        if ($this->request->isPost && $model->load($this->request->post())) {
            $requiredName = 'ต้องระบุ';
            $model->data_json['repair_note'] == '' ? $model->addError('data_json[repair_note]', 'ต้องระบุเหตุผล...') : null;

            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }

    // ยกเลิกงานซ่อม
    public function actionCancelJob($id)
    {
        $model = $this->findModel($id);
        $user = UserHelper::GetEmployee();
        $oldObj = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $newObj = [
                    'cancel_name' => $user->fullname,
                    'repair_status' => 'ยกเลิกซ่อม'
                ];
                $model->data_json = ArrayHelper::merge($oldObj, $newObj);
                try {
                    $asset = Asset::findOne(['code' => $model->code]);
                    // 3 คือ รอจำหน่าย
                    $asset->asset_status = $model->move_out == 1 ? 3 : 1;
                    $asset->save();
                } catch (\Throwable $th) {
                    // throw $th;
                }
                // return $model;
                $model->status = 5;
                $model->save();
                return [
                    'status' => 'success',
                    'container' => '#helpdesk-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_cancel_job', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_cancel_job', ['model' => $model]);
        }
    }

    // ย้ายกลุ่มงาน
    public function actionSwitchGroup($id)
    {
        $model = $this->findModel($id);
        $oldObj = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
                $model->save();
                return [
                    'status' => 'success',
                    'container' => '#helpdesk-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_switch_group', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_switch_group', ['model' => $model]);
        }
    }

    // รับเรื่อง
    public function actionAcceptJob($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $user = UserHelper::GetEmployee();
        $model->updated_by = $user->id;
        $newObj = [
            'accept_name' => $user->fullname,
            'accept_time' => date('Y-m-d H:i:s'),
        ];
        $model->data_json = ArrayHelper::merge(
            $newObj, $model->data_json
        );
        $model->status = 2;
        $model->save();

        return [
            'status' => 'success',
            'container' => '#helpdesk-container',
        ];
    }

    // การให้คะแนน
    public function actionRating($id)
    {
        $model = $this->findModel($id);
        $oldObj = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
                // return $model;
                $model->save();
                return [
                    'status' => 'success',
                    'container' => '#helpdesk-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('rating', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('rating', ['model' => $model]);
        }
    }

    /**
     * Deletes an existing Repair model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Repair model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Repair the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Helpdesk::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function changAssetStatus($code)
    {
        $model = Asset::findOne(['code' => $code]);
        if ($model) {
            $model->asset_status = 5;
            $model->save();
        }
    }
}
