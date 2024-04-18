<?php

namespace app\controllers;
use app\models\Complain;
use Yii;
use app\models\SiteSetting;
use yii\web\Response;
use app\modules\hr\models\Employees;


class ApiController extends \yii\web\Controller
{

    public function beforeAction($action)
    {
        if ($action->id == 'index') {
            $this->enableCsrfValidation = false; //ปิดการใช้งาน csrf
        }
    
        return parent::beforeAction($action);
    }

    public function behaviors(): array
{
    $behaviors = parent::behaviors();
    $behaviors['corsFilter'] = [
        'class' => \yii\filters\Cors::class,
        'cors' => [
            'Origin' => [Yii::$app->request->getOrigin()],
            'Access-Control-Allow-Credentials' => true,
        ],
    ];
    return $behaviors;
}

    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        // $sqlHr = "SELECT 
        // e.id as id,
        // e.gender,
        // pn.title as position_name,
        // pg.title  as position_group,
        // pt.title  as position_type
        // FROM employees e
        // LEFT JOIN categorise pn ON pn.code = e.position_name AND pn.name = 'position_name'
        // LEFT JOIN categorise pg ON pg.code = e.position_group AND pg.name = 'position_group'
        // LEFT JOIN categorise pt ON pt.code = e.position_type AND pt.name = 'position_type';";
        // $sqlHr = "select
        // e.id,
        // e.gender,
        // JSON_UNQUOTE(e.data_json->'$.position_name_text') as position_name,
        // JSON_UNQUOTE(e.data_json->'$.position_group') as position_group,
        // JSON_UNQUOTE(e.data_json->'$.position_type') as position_type,
        // JSON_UNQUOTE(e.data_json->'$.position_level_text') as position_level,
        // JSON_UNQUOTE(e.data_json->'$.status_text') as status_name,
        // JSON_UNQUOTE(e.data_json->'$.department_text') as department
        // FROM employees e";
        $sqlHr = "select
        e.id,
        IFNULL(e.gender,'') as gender,
        IFNULL(CAST(e.data_json->'$.position_name_text' as CHAR),'') as position_name,
        IFNULL(CAST(e.data_json->'$.position_group' as CHAR),'') as position_group,
        IFNULL(CAST(e.data_json->'$.position_type' as CHAR),'') as position_type,
        IFNULL(CAST(e.data_json->'$.position_level_text' as CHAR),'') as position_level,
        IFNULL(CAST(e.data_json->'$.status_text' as CHAR),'') as status_name,
        IFNULL(t.name,'') as department
        
        FROM employees e
        LEFT JOIN tree t ON t.id = e.department";
        $querys = Yii::$app->db->createCommand($sqlHr)->queryAll();
        $hrData = [];

        // foreach ($querys as $item) {
        //     $hrData[] = [
        //         'id' => $item['id'],
        //         'gender' => $item['gender'],
        //         'poaition_name' => $item['position_name'],
        //         'position_group' => $item['position_group'],
        //         'position_type' => $item['position_type'],
        //         'position_level' => $item['position_level'],
        //         'status_name' => $item['status_name'],
        //         'department' => $item['department']
        //     ];
        // }
        
        foreach (Employees::find()->all() as $model) {
                 $hrData[] = [
                'id' => $model->id,
                'gender' => $model->gender,
                'poaition_name' => isset($model->data_json['position_name_text']) ? $model->data_json['position_name_text'] : '',
                'position_group' => isset($model->data_json['position_group_text']) ? $model->data_json['position_group_text'] : '',
                'position_type' => isset($model->data_json['position_type_text']) ? $model->data_json['position_type_text'] : '',
                'position_level' => isset($model->data_json['position_level_text']) ? $model->data_json['position_level_text'] : '',
                'status_name' => isset($model->data_json['status_text']) ? $model->data_json['status_text'] : '',
                'department' => isset($model->data_json['department_text']) ? $model->data_json['department_text'] : '',
                'age' => $model->age_y
            ];
        }

        return $hrData;
        // return [
        //     'hr' => $hrData
        // ];
   
  
        
  
    }

}