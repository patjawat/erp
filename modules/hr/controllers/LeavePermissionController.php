<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\Employees;
use app\modules\hr\models\LeavePermission;
use app\modules\hr\models\LeavePermissionSearch;

/**
 * LeavePermissionController implements the CRUD actions for LeavePermission model.
 */
class LeavePermissionController extends Controller
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
     * Lists all LeavePermission models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new LeavePermissionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('employee');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'cid', $searchModel->q],
            ['like', 'email', $searchModel->q],
            ['like', 'fname', $searchModel->q],
            ['like', 'lname', $searchModel->q],
        ]);


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single LeavePermission model.
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
     * Creates a new LeavePermission model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new LeavePermission();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing LeavePermission model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing LeavePermission model.
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
     * Finds the LeavePermission model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return LeavePermission the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = LeavePermission::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    //คำนวนวันลา
    public function actionViewCalLeave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' => 'คำนวนวันลาปีงบประมาน '.AppHelper::YearBudget(),
            'content' => $this->renderAjax('view_cal_leave')
        ];
    }
    public function actionCalLeaveDays()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $thaiYear = AppHelper::YearBudget();
        $employees = Employees::find()->where(['status' => 1])->andWhere(['!=', 'id', 1])->all();
        $datas = [];
        foreach ($employees as $key => $emp) {
            $model = new LeavePermission();
            $updateLeave = $this->getLeaveDays($emp->id,($thaiYear-1));
            $datas[] = 
            [
                'status' => 'new Version',
                'data' => $updateLeave,
                'emp_id' => $emp->id,
                'thai_year' =>($thaiYear-1)
                   
            ];
            // return count($updateLeave);
            if($updateLeave){
                $model->emp_id = $emp->id;
                $model->thai_year = $thaiYear;
                $model->position_type_id = $emp->position_type;
                $model->leave_over = 10;
                $model->leave_type_id = 'LT4';
                $model->year_of_service = $emp->workYear() ?? 0;
                $model->leave_over_before = $updateLeave['send_leave'];
                // $model->save(false);
            }else{
                $model->emp_id = $emp->id;
                $model->thai_year = $thaiYear;
                $model->position_type_id = $emp->position_type;
                $model->leave_over = 10;
                $model->leave_type_id = 'LT4';
                $model->year_of_service = $emp->workYear() ?? 0;
                $model->leave_over_before = 0;
                // $model->save(false);
               
            }
            
        }
        return $datas;
        
    }

    protected function getLeaveDays($emp_id,$thaiYear)
    {
        $sql = "WITH x AS (
                    SELECT 
                        e.id,
                        CONCAT(e.fname, ' ', e.lname) AS fullname,
                        e.position_type,
                        pt.title AS position_title,
                        l.thai_year,
                        TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) AS years_of_service,
                        l.leave_type_id,
                        lt.title AS leave_title,
                        CASE 
                            WHEN pt.code IN ('PT1', 'PT6') THEN 
                                CASE 
                                    WHEN TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) >= 10 THEN 30
                                    WHEN TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) >= 1 THEN 10
                                    ELSE 0
                                END
                            WHEN pt.code IN ('PT2', 'PT3') THEN 
                                CASE 
                                    WHEN TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) >= 1 THEN 15
                                    ELSE 0
                                END
                            WHEN pt.code IN ('PT5') THEN 
                                CASE 
                                    WHEN TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) >= 0.5 THEN 0
                                    ELSE 0
                                END
                            ELSE 0
                        END AS max_leave,
                        IFNULL(
                            (SELECT SUM(x1.sum_days) 
                            FROM `leave` x1 
                            WHERE x1.status = 'Allow' 
                            AND x1.emp_id = e.id 
                            AND x1.leave_type_id = l.leave_type_id 
                            AND x1.thai_year = :thai_year), 
                            0
                        ) AS used_leave,
                    lp.leave_over,
                    lp.leave_over_before
                    FROM `employees` e
                    LEFT JOIN categorise pt ON pt.code = e.position_type AND pt.name = 'position_type'
                    LEFT JOIN `leave` l ON l.emp_id = e.id
                    LEFT JOIN leave_permission lp ON lp.emp_id = e.id
                    LEFT JOIN categorise lt ON lt.code = l.leave_type_id AND lt.name = 'leave_type'
                    WHERE e.status = 1 
                    AND e.id <> 1 
                    AND l.thai_year = :thai_year
                    AND l.emp_id = :emp_id
                    AND l.leave_type_id = 'LT4'
                    AND lp.thai_year = :thai_year
                    GROUP BY e.id, l.leave_type_id, pt.title, lt.title, l.thai_year
                )
                SELECT x.*,((x.leave_over+leave_over_before)-used_leave) as send_leave
                FROM x;";
            $querys = Yii::$app->db->createCommand($sql)
            ->bindValue(':thai_year',$thaiYear)
            ->bindValue(':emp_id',$emp_id)
            ->queryOne();
    }
}
