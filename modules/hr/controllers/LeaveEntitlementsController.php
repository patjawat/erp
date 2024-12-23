<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\Organization;
use app\modules\hr\models\LeaveEntitlements;
use app\modules\hr\models\LeaveEntitlementsSearch;

/**
 * LeaveEntitlementsController implements the CRUD actions for LeaveEntitlements model.
 */
class LeaveEntitlementsController extends Controller
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
     * Lists all LeaveEntitlements models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new LeaveEntitlementsSearch([
            'thai_year' => AppHelper::YearBudget()
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('employee');
        // ค้นหาคามกลุ่มโครงสร้าง
        $org1 = Organization::findOne($searchModel->q_department);
        // ถ้ามีกลุ่มย่อย
        if (isset($org1) && $org1->lvl == 1) {
            $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
            FROM tree t1
            JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
            WHERE t2.name = :name;';
            $querys = Yii::$app
                ->db
                ->createCommand($sql)
                ->bindValue(':name', $org1->name)
                ->queryAll();
            $arrDepartment = [];
            foreach ($querys as $tree) {
                $arrDepartment[] = $tree['id'];
            }

            if (count($arrDepartment) > 0) {
                $dataProvider->query->andWhere(['in', 'department', $arrDepartment]);
            } else {
                $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
            }
        } else {
            $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
        }
        

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single LeaveEntitlements model.
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
     * Creates a new LeaveEntitlements model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new LeaveEntitlements([
           'thai_year' => AppHelper::YearBudget()
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $check = LeaveEntitlements::find()
                    ->where(['emp_id' => $model->emp_id])
                    ->andWhere(['leave_type_id' => $model->leave_type_id])
                    ->andWhere(['thai_year' => $model->thai_year])
                    ->one();
                if($check){
                    return [
                        'status' => 'error',
                        'message' => 'มีการบันทึกข้อมูลไว้แล้ว'
                    ];
                }

                if($model->save()){
                    return [
                        'status' => 'success',
                        'message' => 'บันทึกข้อมูลสำเร็จ',
                        'container' => '#leave'
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }
        if ($this->request->isAJax) {
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

    // กำหนดสิทธทั้งหมด
    public function actionCreateAll()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        if ($this->request->isPost) {
            $thaiYear = $this->request->post('thai_year');

            $check = LeaveEntitlements::find()->andWhere(['thai_year' => $thaiYear])->count();

            if ($check > 0) {
                return [
                    'status' => false,
                    'message' => 'มีการบันทึกข้อมูลไว้แล้ว'
                ];
            }
            
            $sql = "SELECT x4.*, (x4.days - x4.use_leave) AS total,
                        (
                            CASE 
                                WHEN x4.accumulation = 0 THEN 10
                                WHEN (x4.days - x4.use_leave + 10) > x4.max_days AND x4.accumulation = 1 THEN x4.max_days
                                ELSE (x4.days - x4.use_leave + 10)
                            END
                        ) AS froward_days
                    FROM (
                        SELECT 
                            x3.*,
                            COALESCE(
                                (SELECT days 
                                FROM leave_entitlements 
                                WHERE emp_id = x3.emp_id 
                                AND leave_type_id = x3.leave_type_id 
                                AND thai_year = :thai_year), 
                                0
                            ) AS days,
                            COALESCE(
                                (SELECT SUM(total_days) 
                                FROM `leave` 
                                WHERE emp_id = x3.emp_id 
                                AND leave_type_id = x3.leave_type_id 
                                AND thai_year = :thai_year), 
                                0
                            ) AS use_leave
                        FROM (
                            SELECT 
                                x2.*,
                                COALESCE((
                                    SELECT max_days 
                                    FROM `leave_policies` 
                                    WHERE position_type_id = x2.position_type 
                                    AND year_of_service <= x2.years_of_service 
                                    ORDER BY year_of_service DESC 
                                    LIMIT 1
                                ),0) AS max_days
                            FROM (
                                SELECT 
                                    x1.*
                                FROM (
                                    SELECT 
                                        e.id AS emp_id,
                                        CONCAT(e.fname, ' ', e.lname) AS fullname,
                                        lt.title AS leave_type_name,
                                        l.leave_type_id,
                                        e.position_type,
                                        pt.title AS position_type_name,
                                        COALESCE(lp.accumulation,0) as accumulation,
                                        TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) AS years_of_service
                                    FROM employees e
                                    LEFT JOIN leave_policies lp 
                                        ON lp.position_type_id = e.position_type
                                    LEFT JOIN `leave` l 
                                        ON e.id = l.emp_id 
                                    AND l.leave_type_id = 'LT4'
                                    JOIN categorise lt 
                                        ON l.leave_type_id = lt.code 
                                    AND lt.name = 'leave_type'
                                    JOIN categorise pt 
                                        ON e.position_type = pt.code 
                                    AND pt.name = 'position_type'
                                    GROUP BY e.id
                                    ORDER BY e.id ASC
                                ) AS x1
                            ) AS x2
                        ) AS x3
                    ) AS x4";
$querys = Yii::$app->db->createCommand($sql)
->bindValue('thai_year',($thaiYear-1))
->queryAll();
$data = [];
foreach ($querys as $item) {
    $newModel = new LeaveEntitlements(
        [
            'emp_id' => $item['emp_id'],
            'leave_type_id' => $item['leave_type_id'],
            'position_type_id' => $item['position_type'],
            'year_of_service' => (int)$item['years_of_service'],
            'month_of_service' => 0,
            'days' => $item['froward_days'],
            'thai_year' => $thaiYear
            ]
        );
        if($newModel->save(false)){
            $data[] = [
                'emp_id' => $item['emp_id'],
                'fullname' => $item['fullname'],
            ];
        }
       
}
return [
    'status' => 'success',
    'container' => '#leave'
];
       

        
       
            return $this->request->post();
        } else {
            return 'No';
            
        }
        // if ($this->request->isAJax) {

        //     return [
        //         'title' => $this->request->get('title'),
        //         'content' => $this->renderAjax('create_all', [
        //             'model' => $model,
        //         ]),
        //     ];
        // } else {
        //     return $this->render('create_all', [
        //         'model' => $model,
        //     ]);
        // }
    }

    /**
     * Updates an existing LeaveEntitlements model.
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
     * Deletes an existing LeaveEntitlements model.
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
     * Finds the LeaveEntitlements model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return LeaveEntitlements the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = LeaveEntitlements::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
