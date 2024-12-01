<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\Employees;
use app\modules\hr\models\LeaveRole;
use app\modules\hr\models\LeaveRoleSearch;

/**
 * LeaveRoleController implements the CRUD actions for LeaveRole model.
 */
class LeaveRoleController extends Controller
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
     * Lists all LeaveRole models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new LeaveRoleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCall()
    {
        $employee = Employees::find()->where(['status' => 1])->andwhere(['<>','id',1])->all();
        return $this->render('call', ['employee' => $employee]);
    }

    /**
     * Displays a single LeaveRole model.
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
     * Creates a new LeaveRole model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new LeaveRole();

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
     * Updates an existing LeaveRole model.
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
     * Deletes an existing LeaveRole model.
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
     * Finds the LeaveRole model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return LeaveRole the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = LeaveRole::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionRole()
    {
        

$sql = "WITH x AS (SELECT 
    e.id,
    CONCAT(e.fname, ' ', e.lname) AS fullname,
    e.position_type,
    pt.title,
    l.thai_year,
    DATEDIFF(CURDATE(), e.join_date) / 365 AS years_of_service,
    l.leave_type_id,
    CASE 
        -- ข้าราชการและลูกจ้างประจำ
        WHEN pt.code IN ('PT1', 'PT6') THEN 
            CASE 
                WHEN DATEDIFF(CURDATE(), e.join_date) / 365 >= 10 THEN 30
                WHEN DATEDIFF(CURDATE(), e.join_date) / 365 >= 1 THEN 10
                ELSE 0
            END
        -- พนักงานราชการและพนักงานกระทรวงสาธารณสุข
        WHEN pt.code IN ('PT2', 'PT3') THEN 
            CASE 
                WHEN DATEDIFF(CURDATE(), e.join_date) / 365 >= 1 THEN LEAST(15, 15 + 0) -- รวมปีปัจจุบัน + สะสม
                WHEN DATEDIFF(CURDATE(), e.join_date) / 365 < 0.5 THEN 0
                ELSE 0
            END
        -- ลูกจ้างชั่วคราวและลูกจ้างรายวัน
        WHEN pt.code IN ('PT5') THEN 
            CASE 
                WHEN DATEDIFF(CURDATE(), e.join_date) / 365 >= 0.5 THEN 0
                ELSE 0
            END
        -- Default เผื่อสำหรับพนักงานประเภทอื่น
        ELSE 0
    END AS max_days,
    IFNULL(
        (SELECT SUM(x1.sum_days) 
         FROM `leave` x1 
         WHERE x1.status <> 'cancel' 
         AND x1.leave_type_id = 'LT4' 
         AND x1.emp_id = e.id 
         AND x1.thai_year = :thai_year), 
        0
    ) AS last_year
FROM `employees` e
LEFT JOIN categorise pt ON pt.code = e.position_type AND pt.name = 'position_type'
LEFT JOIN `leave` l ON l.emp_id = e.id
WHERE e.status = 1 
  AND e.id <> 1 
  AND l.thai_year = :thai_year
GROUP BY e.id) SELECT  x.*,
CASE
        WHEN x.last_year > 10 THEN ((x.last_year - 10)+10)
        ELSE x.last_year
    END AS adjusted_days
    
FROM x";
$querys = Yii::$app->db->createCommand($sql,[':thai_year' => 2567])->queryAll();

$data = [];
foreach ($querys as $query) {
    $check = leaveRole::findOne(['emp_id' => $query['id'],
    'thai_year' => $query['thai_year'],
    'position_type_id' => $query['position_type']]);
    if(!$check){
        $new = new leaveRole();
    }else{
        $new = $check;
    }
        $new->emp_id = $query['id'];
         $new->thai_year = $query['thai_year'];
         $new->position_type_id = $query['position_type'];
         $new->work_year = isset($query['years_of_service']) ? $query['years_of_service'] : 0;
        //  $new->last_days = $query['last_year'];
         $new->max_point = $query['max_days'];
         $new->point = $query['last_year'];
         $new->point_use = 0;
        
    $new->save(false);
    $data[] = $new;
}
Yii::$app->response->format = Response::FORMAT_JSON;
return $data;
}
}