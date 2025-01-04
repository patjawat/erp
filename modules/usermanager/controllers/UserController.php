<?php

namespace app\modules\usermanager\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use \dominus77\sweetalert2\Alert;
use yii\web\NotFoundHttpException;
use dektrium\user\models\User as BaseUser;
use app\modules\usermanager\models\User;
use dektrium\user\models\RegistrationForm;
use app\modules\usermanager\models\UserSearch;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->joinWith(['employee']);
        $dataProvider->pagination = ['pageSize' => 20];
        // $dataProvider->query->andFilterWhere(['username' => $searchModel->q]);
        $dataProvider->query->andFilterWhere(['or',
        ['like', 'username', $searchModel->q],
        ['like', new Expression("concat(employees.fname,' ',employees.lname)"), $searchModel->q],
        ['like', 'user.email', $searchModel->q]

    ]);
        if (Yii::$app->request->isAjax) {
        Yii::$app->response->format = Response::FORMAT_JSON;
            return $this->renderAjax('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }
    public function actionCreate()
    {
        $model = new User();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->setPassword($model->password);
            $model->generateAuthKey();
            if($model->save()){
              $model->assignment();
            }
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->getRoleByUser();
        $model->password = $model->password_hash;
        $model->confirm_password = $model->password_hash;
        $oldPass = $model->password_hash;
        $model->fullname = ($model->employee->fullname ?? '-');

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
          if($oldPass!==$model->password){
            $model->setPassword($model->password);
          }
          if($model->save()){
            $model->assignment();
          }

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    public function actionUpdateEmployee($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (($checker = User::findOne($id)) !== null) {
             $model = $checker;
        }else{
            return[
                'title' => '<i class="fa-solid fa-triangle-exclamation text-danger"></i> คำเตือน | ไม่สามารถกำหนดสิทธิได้',
                'content' => $this->renderAjax('warning')
            ];
        }

        $model->getRoleByUser();
        $model->password = $model->password_hash;
        $model->confirm_password = $model->password_hash;
        $oldPass = $model->password_hash;
        $model->fullname = $model->employee->fullname;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
          if($oldPass!==$model->password){
            $model->setPassword($model->password);
          }
          if($model->save()){
            $model->assignment();
          }

            // return $this->redirect(['view', 'id' => $model->id]);
            return [
                'status' => 'success',
                
            ];
        } else {
            return[
                'title' => 'กำหนดสิทธิ',
                'content' => $this->renderAjax('update_emp', [
                    'model' => $model,
                ])
            ];
        }
    }

    public function actionProfile(){
        $id = Yii::$app->user->id;
        $model =  $this->findModel($id);
        $model->getRoleByUser();
        $model->password = $model->password_hash;
        $model->confirm_password = $model->password_hash;
        $oldPass = $model->password_hash;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
          if($oldPass!==$model->password){
            $model->setPassword($model->password);
          }
          if($model->save()){
            $model->assignment();
          }
          Yii::$app->session->setFlash(Alert::TYPE_SUCCESS, [
            'title' => 'บันทึกสำเร็จ',
        ]);
            return $this->redirect(['profile']);
        } else {
            return $this->render('profile', [
                'model' => $model,
            ]);
        }
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    // สลับ user
    public function actionSwitchTo($id)
    {
        if (Yii::$app->user->can('admin')){
            $model = $this->findModel($id);
            Yii::$app->user->login($model);
            return $this->redirect(['/usermanager']);
        }else{
            return $this->renderContent('<h2>ไม่ได้รับสิทธิ</h2>');
        }
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
