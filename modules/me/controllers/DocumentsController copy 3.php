<?php

namespace app\modules\me\controllers;
use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentTags;
use app\modules\dms\models\DocumentSearch;
use app\modules\dms\models\DocumentTagsSearch;

class DocumentsController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $emp = UserHelper::GetEmployee();
        $department = $emp->department;
        $searchModel = new DocumentSearch([
            'document_group' => 'receive', 
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('documentTags');
        $dataProvider->query->andWhere("JSON_CONTAINS(documents.data_json->'$.tags_employee', '\"$emp->id\"', '$')");
        // $dataProvider->query->andFilterWhere("NOT JSON_CONTAINS(view_json, :json, '$')")->addParams([':json' => json_encode(['emp_id' => $emp->id])]);
        // $dataProvider->query->andWhere("JSON_CONTAINS(view_json, :json, '$')")->addParams([':json' => json_encode(['emp_id' => $emp->id])]);
        
        $dataProviderDepartment = $searchModel->search($this->request->queryParams);
        $dataProviderDepartment->query->andWhere("JSON_CONTAINS(view_json, :json, '$')")->addParams([':json' => json_encode(['emp_id' => $emp->id])]);
        $dataProviderDepartment->query->andFilterWhere([
            '>', 
            new Expression("FIND_IN_SET(:department, JSON_UNQUOTE(data_json->'$.department_tag'))"), 
            0
            ])->addParams([':department' => $emp->department]);

            if($searchModel->show_reading == '1'){  
                //   แสดงที่ยังไม่ได้อ่าน
            // $dataProvider->query->andWhere("NOT JSON_CONTAINS(view_json, :json, '$')")->addParams([':json' => json_encode(['emp_id' => $emp->id])]);
            // $dataProviderDepartment->query->andWhere("NOT JSON_CONTAINS(view_json, :json, '$')")->addParams([':json' => json_encode(['emp_id' => $emp->id])]);
            }else{
            //   แสดงที่เจ้าตัวอ่านแล้ว
            // $dataProvider->query->andWhere("JSON_CONTAINS(view_json, :json, '$')")->addParams([':json' => json_encode(['emp_id' => $emp->id])]);
            // $dataProviderDepartment->query->andWhere("JSON_CONTAINS(view_json, :json, '$')")->addParams([':json' => json_encode(['emp_id' => $emp->id])]);
                
            }
            
    //     $dataProvider->query->andWhere(['or',
    //     new Expression("JSON_CONTAINS(documents.data_json->'$.employee_tag', :empId, '$')", [
    //         ':empId' => json_encode($emp->id)
    //     ]),
    //     new Expression(
    //         'FIND_IN_SET(:department, JSON_UNQUOTE(JSON_EXTRACT(documents.data_json, "$.tags_department"))) > 0',
    //         [':department' => $department]
    //     )
    // ]);

        
        // $dataProvider->query->andWhere("JSON_CONTAINS(tags_employee, '\"$emp->id\"', '$')");
        // $dataProvider->query->andFilterWhere([
        //     '>', 
        //     new Expression("FIND_IN_SET(:department, JSON_UNQUOTE(documents.data_json->'$.tags_department'))"), 
        //     0
        //     ])->addParams([':department' => $emp->department]);
        
        // $dataProvider->query->andWhere([
        //     'or',
        //     ['and', ["JSON_CONTAINS(tags_employee, '\"$emp->id\"', '$')"]], 
        //     ['and', [
        //         '>', 
        //         new Expression("FIND_IN_SET(:department, JSON_UNQUOTE(documents.data_json->'$.tags_department'))"), 
        //         0
        //         ]] 
        // ]);
        // if($searchModel->show_reading == '1'){  
              // แสดงที่ยังไม่ได้อ่าน
        // $dataProvider->query->andWhere("NOT JSON_CONTAINS(view_json, :json, '$')")->addParams([':json' => json_encode(['emp_id' => $emp->id])]);
        // }else{
          // แสดงที่เจ้าตัวอ่านแล้ว
        // $dataProvider->query->andWhere("JSON_CONTAINS(view_json, :json, '$')")->addParams([':json' => json_encode(['emp_id' => $emp->id])]);
            
        // }
            if($this->request->isAJax){
                Yii::$app->response->format = Response::FORMAT_JSON;
    
                    return [
                        'title' => $this->request->get('tilte'),
                        'content' => $this->renderAjax('list_show', [
                            'list' => $this->request->get('list'),
                            'searchModel' => $searchModel,
                            'dataProvider' => $dataProvider,
                        ])
                     ];
                }else{
                    return $this->render('index', [
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'dataProviderDepartment' => $dataProviderDepartment
                    ]);
                }
    }

    public function actionView($id)
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $emp = UserHelper::GetEmployee();

        $view_count[] = [
            'date_time' => date('Y-m-d H:i:s'),
            'emp_id' => $emp->id,
            'fullname' => $emp->fullname,
            'department' => $emp->departmentName(),
        ];
        if ($model->view_json === null) {
            $model->view_json = [];
        }

        $checkView = DocumentTags::find()->where([
            'name' => 'employee',
            'tag_id' => $emp->id,
            'document_id' => $model->id,
            'name' => 'employee'
        ])->one();
       
        if($checkView){
            $reading =  $checkView;
            $reading->name = 'employee';
            $reading->reading =  date('Y-m-d H:i:s');
            $reading->tag_id = $emp->id;
            $reading->document_id = $model->id;
            $reading->save(false);
        }
    
        
        $model->view_json  = ArrayHelper::merge($view_count, $model->view_json);
        $model->save();
        
       
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->renderAjax('@app/modules/dms/views/documents/view_title',['model' => $model]),
                'content' => $this->renderAjax('@app/modules/dms/views/documents/view', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('@app/modules/dms/views/documents/view', [
                'model' => $model,
            ]);
        }
    }


//แสดง File และแสดงความเห็น
    public function actionFileComment($id)
    {
        $model = $this->findModel($id);
        if($this->request->isAJax){
            Yii::$app->response->format = Response::FORMAT_JSON;

                return [
                    'title' => $this->request->get('tilte'),
                    'content' => $this->renderAjax('file_comment', [
                        'model' => $model,
                    ])
                 ];
            }else{
                return $this->render('file_comment', [
                    'model' => $model,
                ]);
            }
    }

    
//แสดง File และแสดงความเห็น
public function actionShareFile($id)
{
    $model = $this->findModel($id);
    if($this->request->isAJax){
        Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => '<i class="fas fa-share"></i> ส่งต่อ',
                'content' => $this->renderAjax('share_file', [
                    'model' => $model,
                ])
             ];
        }else{
            return $this->render('share_file', [
                'model' => $model,
            ]);
        }
}

    
    public function actionShow($id)
    {
        $model = $this->findModel($id);
        if(!Yii::$app->user->isGuest){

            $id = Yii::$app->request->get('id');
            $fileUpload = Uploads::findOne(['ref' => $model->ref]);
            $filename = $fileUpload->real_filename;
            $filepath = FileManagerHelper::getUploadPath().$fileUpload->ref.'/'. $filename;
            if (!file_exists($filepath)) {
                throw new \yii\web\NotFoundHttpException('The requested file does not exist.');
            }
            
            $this->setHttpHeaders($fileUpload->type);
            \Yii::$app->response->data = file_get_contents($filepath);
            return \Yii::$app->response;

        }else{
            return false;
        }

    }
    
    protected function setHttpHeaders($type)
    {
        
        \Yii::$app->response->format = yii\web\Response::FORMAT_RAW;
        if($type == 'png'){
            \Yii::$app->response->headers->add('content-type','image/png');
        }
        
        if($type == 'pdf'){
            \Yii::$app->response->headers->add('content-type','application/pdf');

        }
        }


    /**
     * Finds the Documents model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Documents the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Documents::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


    

}
