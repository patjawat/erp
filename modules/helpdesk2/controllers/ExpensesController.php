<?php

namespace app\modules\helpdesk2\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\helpdesk2\models\HelpdeskDetail;
use app\modules\helpdesk2\models\HelpdeskDetailSearch;

/**
 * ServiceRecordController implements the CRUD actions for HelpdeskDetail model.
 */
class ExpensesController extends Controller
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
     * Lists all HelpdeskDetail models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new HelpdeskDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
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
     * Displays a single HelpdeskDetail model.
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
     * Creates a new HelpdeskDetail model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new HelpdeskDetail([
            'helpdesk_id' => $this->request->get('helpdesk_id')
        ]);

        if ($this->request->isPost) {
            $rowsJson = (string) $this->request->post('expense_rows_json', '');
            if ($rowsJson !== '') {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                $rows = json_decode($rowsJson, true);
                if (!is_array($rows)) {
                    return ['status' => 'error', 'message' => 'รูปแบบรายการค่าใช้จ่ายไม่ถูกต้อง'];
                }

                $helpdeskId = (int) ($this->request->post('helpdesk_id') ?: $model->helpdesk_id);
                $tx = Yii::$app->db->beginTransaction();
                try {
                    HelpdeskDetail::deleteAll([
                        'helpdesk_id' => $helpdeskId,
                        'name' => 'expense_record',
                    ]);

                    $savedCount = 0;
                    foreach ($rows as $row) {
                        $title = trim((string) ($row['title'] ?? ''));
                        if ($title === '') {
                            continue;
                        }
                        $qty = (float) ($row['qty'] ?? 0);
                        $unitPrice = (float) ($row['unit_price'] ?? 0);
                        $total = (float) ($row['total'] ?? ($qty * $unitPrice));
                        $status = trim((string) ($row['status'] ?? 'ค่าใช้จ่าย'));
                        $expenseType = trim((string) ($row['expense_type'] ?? ''));
                        $note = trim((string) ($row['note'] ?? ''));

                        $item = new HelpdeskDetail();
                        $item->helpdesk_id = $helpdeskId;
                        $item->name = 'expense_record';
                        $item->status = $status;
                        $item->title = $title;
                        $item->code = (string) $total;
                        $item->data_json = [
                            'qty' => $qty,
                            'unit_price' => $unitPrice,
                            'total' => $total,
                            'expense_type' => $expenseType,
                            'note' => $note,
                        ];
                        if (!$item->save()) {
                            throw new \RuntimeException('ไม่สามารถบันทึกรายการค่าใช้จ่ายได้');
                        }
                        $savedCount++;
                        $this->CheckUpdateServiceRecordStatus($item);
                    }

                    try {
                        $sumTotal = 0.0;
                        foreach ($rows as $r) {
                            $sumTotal += (float) ($r['total'] ?? 0);
                        }
                        $log = new HelpdeskDetail();
                        $log->helpdesk_id = $helpdeskId;
                        $log->name = 'service_record';
                        $log->status = 'ขั้นตอน 4: บันทึกค่าใช้จ่าย';
                        $log->title = 'บันทึกค่าใช้จ่ายแล้ว ' . $savedCount . ' รายการ';
                        $log->data_json = [
                            'expense_count' => $savedCount,
                            'expense_total' => $sumTotal,
                        ];
                        $log->save(false);
                    } catch (\Throwable $e) {
                        // ไม่ให้กระทบการบันทึกหลัก
                    }
                    $tx->commit();
                    return ['status' => 'success'];
                } catch (\Throwable $e) {
                    $tx->rollBack();
                    return ['status' => 'error', 'message' => $e->getMessage()];
                }
            }

            if ($model->load($this->request->post()) && $model->save()) {
                $this->CheckUpdateServiceRecordStatus($model);
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success'
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        $expenseRows = HelpdeskDetail::find()
            ->where([
                'helpdesk_id' => $model->helpdesk_id,
                'name' => 'expense_record',
            ])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                    'expenseRows' => $expenseRows,
                ])
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
                'expenseRows' => $expenseRows,
            ]);
        }
    }

    /**
     * Updates an existing HelpdeskDetail model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            $this->CheckUpdateServiceRecordStatus($model);
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success'
            ];
        }

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing HelpdeskDetail model.
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

    public function actionTimeline($helpdesk_id)
    {
        $lists = HelpdeskDetail::find()
            ->where(['name' => 'service_record', 'helpdesk_id' => $helpdesk_id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('timeline', [
                    'lists' => $lists,
                ])
            ];
        } else {
            return $this->render('timeline', [
                'lists' => $lists,
            ]);
        }
    }

    private static function CheckUpdateServiceRecordStatus($model)
    {
        try {
            // บึนทึกยี่ห้ออัตโนมัติ
            $status = $model->status;
            $modelStatus = Categorise::findOne(['name' => 'service_record_status', 'title' => $status]);
            if (!$modelStatus) {
                $modelNewStatus = new Categorise(['name' => 'service_record_status', 'code' => $status, 'title' => $status]);
                $modelNewStatus->save();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    /**
     * Finds the HelpdeskDetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return HelpdeskDetail the loaded model
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
