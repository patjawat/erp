<?php

namespace app\modules\purchase\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\Response;
use yii\db\Expression;
use app\models\Approve;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\purchase\models\Order;
use app\modules\purchase\models\OrderSearch;

/**
 * PrOrderController implements the CRUD actions for Order model.
 */
class PrOrderController extends Controller
{
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
     * Lists all Order models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andwhere(['is not', 'pr_number', null]);
        $dataProvider->query->andwhere(['status' => 1]);
        $dataProvider->query->andFilterwhere(['name' => 'order']);
        $dataProvider->pagination->pageSize = 8;

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('@app/modules/purchase/views/order/index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'isAjax' => $this->request->isAjax,
                ]),
            ];
        } else {
            return $this->render('@app/modules/purchase/views/order/index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'isAjax' => $this->request->isAjax,
            ]);
        }
    }

    // รายการที่อนุมัติแล้ว

    /**
     * Displays a single Order model.
     *
     * @param int $id ID
     *
     * @return string
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        \Yii::$app->session->set('name', 'pr_item');

        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Order model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $userCreate = UserHelper::GetEmployee();
        $model = new Order([
            'name' => 'order',
            'status' => $this->request->get('status'),
            'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
            'data_json' => [
                'leader1' => $userCreate->leaderUser()['leader1'],
            ],
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                $oldObj = $model->data_json;
                $vendor = $model->vendor;
                
                $model->data_json = [
                    'pr_create_date' => AppHelper::convertToGregorian($model->data_json['pr_create_date']),
                    'due_date' => AppHelper::convertToGregorian($model->data_json['due_date']),
                    'vendor_name' => isset($vendor->data_json['title']) ? $vendor->data_json['title'] : '-',
                    'vendor_address' => isset($vendor->data_json['address']) ? $vendor->data_json['address'] : '-',
                    'vendor_phone' => isset($vendor->data_json['vendor_phone']) ? $vendor->data_json['phone'] : '',
                    'account_name' => isset($vendor->data_json['account_name']) ? $vendor->data_json['account_name'] : '',
                    'account_number' => isset($vendor->data_json['account_number']) ? $vendor->data_json['account_number'] : '',
                ];

                $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
                $model->save(false);
               
                return $this->redirect(['/purchase/order/view', 'id' => $model->id]);
            } else {
                return false;
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Order model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id ID
     *
     * @return string|Response
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldObj = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                // validate all models
                $vendor = $model->vendor;
                $newObj = [
                    'pr_create_date' => AppHelper::convertToGregorian($model->data_json['pr_create_date']),
                    'due_date' => AppHelper::convertToGregorian($model->data_json['due_date']),
                    'vendor_name' => $vendor->title,
                    'vendor_address' => isset($vendor->data_json['address']) ? $vendor->data_json['address'] : '-',
                    'vendor_phone' => isset($vendor->data_json['phone']) ? $vendor->data_json['phone'] : '',
                    'account_name' => isset($vendor->data_json['account_name']) ? $vendor->data_json['account_name'] : '',
                    'account_number' => isset($vendor->data_json['account_number']) ? $vendor->data_json['account_number'] : '',
                ];

                $model->data_json = ArrayHelper::merge($oldObj, $model->data_json, $newObj);
                \Yii::$app->response->format = Response::FORMAT_JSON;

                $model->save(false);

                return $this->redirect(['/purchase/order/view', 'id' => $model->id]);
            } else {
                return false;
            }
        } else {
            // Yii::$app->response->format = Response::FORMAT_JSON;

            $model->loadDefaultValues();
            $oldObj = $model->data_json;
            $model->data_json = [
                'pr_create_date' => AppHelper::convertToThai($model->data_json['pr_create_date']),
                'due_date' => AppHelper::convertToThai($model->data_json['due_date']),
            ];
            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
            // return $model->data_json;

            if ($this->request->isAjax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;

                return [
                    'title' => $this->request->get('title'),
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                    ]),
                ];
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }
    }

    /**
     * Deletes an existing Order model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id ID
     *
     * @return Response
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    // รายการที่ขอซื้อ
    public function actionList()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterwhere(['name' => 'order', 'status' => 1]);
        $dataProvider->query->andWhere(new Expression("JSON_EXTRACT(data_json, '$.pr_director_confirm') = ''"));

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('@app/modules/purchase/views/order/list_style1', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'title' => 'ขอซื้อ/ขอจ้าง',
                ]),
            ];
        } else {
            return $this->render('@app/modules/purchase/views/order/list_style1', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'title' => 'ขอซื้อ/ขอจ้าง',
            ]);
        }
    }

    // public function actionAcceptOrderList()
    // {
    //     $searchModel = new OrderSearch();
    //     $dataProvider = $searchModel->search($this->request->queryParams);
    //     $dataProvider->query->andFilterwhere(['name' => 'order','status' => 1]);
    //     $dataProvider->query->andWhere(new Expression("JSON_EXTRACT(data_json, '$.pr_director_confirm') = 'Y'"));

    //     if ($this->request->isAjax) {
    //         Yii::$app->response->format = Response::FORMAT_JSON;
    //         return [
    //             'title' => $this->request->get('title'),
    //             'content' => $this->renderAjax('@app/modules/purchase/views/order/list_style1', [
    //                 'searchModel' => $searchModel,
    //                 'dataProvider' => $dataProvider,
    //                 'title' => 'อนุมิติ'
    //             ]),
    //         ];
    //     } else {

    //     return $this->render('@app/modules/purchase/views/order/list_style1', [
    //         'searchModel' => $searchModel,
    //         'dataProvider' => $dataProvider,
    //          'title' => 'อนุมิติ'
    //     ]);
    //     }
    // }

    // ตรวจสอบความถูกต้อง
    public function actionCreatevalidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Order();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            if (isset($model->data_json['pr_create_date'])) {
                preg_replace('/\D/', '', $model->data_json['pr_create_date']) == '' ? $model->addError('data_json[pr_create_date]', $requiredName) : null;
            }
            if (isset($model->data_json['due_date'])) {
                preg_replace('/\D/', '', $model->data_json['due_date']) == '' ? $model->addError('data_json[due_date]', $requiredName) : null;
            }

            if (isset($model->data_json['leader1'])) {
                $model->data_json['leader1'] == '' ? $model->addError('data_json[leader1]', $requiredName) : null;
            }

            if (isset($model->vendor_id)) {
                $model->vendor_id == '' ? $model->addError('vendor_id', $requiredName) : null;
            }
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    // ตรวจสอบความถูกต้อง
    public function actionCheckervalidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Order();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            if (isset($model->data_json['pr_leader_confirm'])) {
                $model->data_json['pr_leader_confirm'] == '' ? $model->addError('data_json[pr_leader_confirm]', $requiredName) : null;
            }

            if (isset($model->data_json['pr_officer_checker'])) {
                $model->data_json['pr_officer_checker'] == '' ? $model->addError('data_json[pr_officer_checker]', $requiredName) : null;
            }
            if (isset($model->data_json['pr_director_confirm'])) {
                $model->data_json['pr_director_confirm'] == '' ? $model->addError('data_json[pr_director_confirm]', $requiredName) : null;
            }
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id ID
     *
     * @return Order the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    // จนท.พัสดุตรวจสอบ
    public function actionCheckerConfirm($id)
    {
        // $model = $this->findModel($id);
        $model = Approve::findOne($id);
        $me = UserHelper::GetEmployee();

        $oldObj = $model->data_json;
        if ($model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $model->emp_id = $me->id;
            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
            $model->save(false);
            
            //ส่งให้ผู้อำนวยการอนุมัติ
            $approve = Approve::findOne(['from_id' => $model->purchase->id,'name' => 'purchase','level' => 3]);
            if($approve){
                 $approve->status = 'Pending';
                $approve->save(false);
            }

            return [
                'status' => 'success',
                'container' => '#purchase-container',
                'model' => $model,
            ];
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $me['avatar'],
                'content' => $this->renderAjax('_checker_confirm', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_checker_confirm', [
                'model' => $model,
            ]);
        }
    }

    public function actionLeaderConfirm($id)
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        $user = UserHelper::GetEmployee();
        // return $user->id;

        $oldObj = $model->data_json;
        if ($model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
            $model->save(false);

            return [
                'status' => 'success',
                'container' => '#purchase-container',
                'model' => $model,
            ];
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $model->viewLeaderUser()['avatar'],
                'content' => $this->renderAjax('_leader_confirm', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_leader_confirm', [
                'model' => $model,
            ]);
        }
    }

    public function actionDirectorConfirm($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);

        $oldObj = $model->data_json;
        if ($model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
            $model->save(false);

            return [
                'status' => 'success',
                'container' => '#purchase-container',
                'model' => $model,
            ];
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => SiteHelper::viewDirector()['avatar'],
                'content' => $this->renderAjax('_director_confirm', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_director_confirm', [
                'model' => $model,
            ]);
        }
    }

    // ส่งใบขอซื้อ
    public function actionPrConfirm($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
            $thaiYear = substr(AppHelper::YearBudget(), 2);
            $model = $this->findModel($id);
            $user = UserHelper::GetEmployee();
            $model->updated_by = $user->id;
            $newObj = [
                'pr_confirm_name' => $user->fullname,
                'pr_confirm_time' => date('Y-m-d H:i:s'),
            ];
            $model->data_json = ArrayHelper::merge(
                $newObj,
                $model->data_json
            );
            $model->pr_number = \mdm\autonumber\AutoNumber::generate('PR-'.$thaiYear.'????');
            $model->status = 1;
            $model->approve = 'Y';
             $model->createApprove();
            if ($model->save()) {
                //ส่งขออนุมัติ
             
                return $this->redirect(['/purchase/order']);
                return [
                    'status' => 'success',
                    'container' => '#purchase-container',
                ];
            } else {
                return [
                    'status' => 'error',
                    'container' => '#purchase-container',
                ];
            }
      
    }

    // อนุมัติตาม status
    public function actionConfirmStatus($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $status = $this->request->get('status');
        $thaiYear = substr(AppHelper::YearBudget(), 2);
        $model = $this->findModel($id);
        $oldObj = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
                // ถ้าอนุมัติผ่านให้ใช้ step ต่อไป
                if ($model->approve == 'Y') {
                    $model->status = $status;
                }
                if ($model->status == 4) {
                    $model->pq_number = \mdm\autonumber\AutoNumber::generate('PQ-'.$thaiYear.'????');
                }
                $model->save(false);

                return [
                    'status' => 'success',
                    'container' => '#purchase-container',
                ];
            } else {
                return false;
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('confirm_status', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('confirm_status', [
                'model' => $model,
            ]);
        }
    }
}
