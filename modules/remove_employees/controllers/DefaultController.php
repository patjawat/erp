<?php

namespace app\modules\employees\controllers;

use app\modules\employees\models\Employees;
use Yii;
use app\components\SiteHelper;
use app\modules\employees\models\EmployeesSearch;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Default controller for the `employees` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
    
        $searchModel = new EmployeesSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $dataProvider->sort->defaultOrder = [
            'id' => SORT_DESC,//เรียงจากมาไปหาน้อย
        ];
        $dataProvider->pagination->pageSize=8;

        if(Yii::$app->request->get('display') == 'grid'){
             SiteHelper::setDisplayGrid();
        }
        
        if(Yii::$app->request->get('display') == 'list'){
            SiteHelper::setDisplayList();
        }
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Employees model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        
        $model = new Employees();
        
        if ($this->request->isPost) {
        Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
                // return [
                //     'status' => 'success',
                //     'container' => '#employee-container',
                //     'data' => $model
                // ];
            }else{
               return ActiveForm::validate($model);
            }
        } else {
            $model->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $model->loadDefaultValues();
            
        }

        if($this->request->isAjax){
        Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-plus"></i> ทะเบียนพนักงาน / (<code>สร้างใหม่</code>)',
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ])
            ];
        }else{
            return $this->render('create', [
                'model' => $model,
            ]);
        }
       
    }


    public function actionFormFamily()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    
    // $model = UserHelper::GetEmployee();
    $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        if ($this->request->isPost) {
        $post =  $this->request->post('Employees');
        $model->data_json =  ArrayHelper::merge($model->data_json, $post['data_json']);
       if($model->save(false)){

           return [
               'status' => 'success',
               'data' => $model
            ];
        }
    } else {

    return [
        'title' => '<h4>ครอบครัว</h4>',
        'content' => $this->renderAjax('_form_family',[
            'model' => $model,
            ])
    ];
}
}


    /**
     * Updates an existing Employees model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $form = $this->request->get('form');
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if($this->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => '<i class="fa-regular fa-pen-to-square"></i> ทะเบียนพนักงาน / (<code>แก้ไข</code>)'.$form,
                    // 'content' => $this->renderAjax('form/'.$form, [
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                    ])
                ];
            }else{
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
    }

    

    /**
     * Deletes an existing Employees model.
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
     * Finds the Employees model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Employees the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = \app\modules\employees\models\Employees::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
