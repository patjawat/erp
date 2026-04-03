<?php

namespace app\modules\me\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\hr\models\Employees;
use app\modules\leave\models\LeaveSearch;
use app\modules\helpdesk\models\HelpdeskSearch;
use app\modules\attendance\models\CheckinRecord;
use app\modules\appreciation\models\Appreciation;
use app\modules\usermanager\models\User;

/**
 * Default controller for the `me` module
 */
class DefaultController extends Controller
{
    public function actionIndex()
    {
        // clear session คลัง
        foreach (['sub-warehouse', 'main-warehouse', 'asset_type'] as $key) {
            Yii::$app->session->remove($key);
        }

        $model = UserHelper::GetEmployee();

        if ($model === null) {
            return $this->render('warning');
        }

        $info = $model->getInfo();
    //  return $model;
        if (!$model->hasPersonnelPositionConfigured()) {
            return $this->render('warning');
        }
        $searchModel = new LeaveSearch([
            'thai_year' => AppHelper::YearBudget(),
            'emp_id' => $model->id
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        //เช็คจากการตั้งค่าใน Employees ถ้าเป็น รพ.สต.
        if ($model->branch == 'BRANCH') {
            return $this->redirect(['/me/store-v2/dashboard']);
        }

        $todayCheckinCount = 0;
        $appreciationReceivedCount = 0;
        if ($model && $model->id) {
            try {
                $todayCheckinCount = CheckinRecord::find()
                    ->andWhere(['emp_id' => $model->id])
                    ->andWhere(['>=', 'checkin_at', date('Y-m-d 00:00:00')])
                    ->andWhere(['<=', 'checkin_at', date('Y-m-d 23:59:59')])
                    ->count();
            } catch (\Throwable $e) {
                $todayCheckinCount = 0;
            }
            try {
                $appreciationReceivedCount = (int) Appreciation::find()->andWhere(['to_emp_id' => $model->id])->count();
            } catch (\Throwable $e) {
                $appreciationReceivedCount = 0;
            }
        }

        return $this->render('index', [
            'model' => $model ? $model : new Employees(),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'todayCheckinCount' => $todayCheckinCount,
            'appreciationReceivedCount' => $appreciationReceivedCount,
        ]);
    }


    public function actionWarning()
    {
        return $this->render('warning');
    }

    public function actionV2()
    {
        $model = UserHelper::GetEmployee();

        $searchModel = new LeaveSearch([
            'thai_year' => AppHelper::YearBudget(),
            'emp_id' => $model->id
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);


        return $this->render('me_v2', [
            'model' => $model ? $model : new Employees(),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionV3()
    {
        $model = UserHelper::GetEmployee();

        $searchModel = new LeaveSearch([
            'thai_year' => AppHelper::YearBudget(),
            'emp_id' => $model->id
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);


        return $this->render('me_v3', [
            'model' => $model ? $model : new Employees(),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }




    public function actionTeam()
    {
        return $this->render('team_work', []);
    }

    /**
     * หน้าโปรไฟล์ของฉัน (/me/default/profile หรือ /me/profile ถ้ามี rule)
     */
    public function actionProfile()
    {
        $model = UserHelper::GetEmployee();
        if ($model === null) {
            return $this->render('warning');
        }
        return $this->render('profile', [
            'model' => $model,
        ]);
    }

    /**
     * ตั้งค่าบัญชีเข้าใช้งานระบบ (เปลี่ยนชื่อเข้าใช้ / รหัสผ่าน) บันทึกแล้ว redirect ไป /me/profile
     */
    public function actionAccount()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/me/profile']);
        }
        $model = User::findOne(Yii::$app->user->id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบบัญชี');
        }
        $model->password = '';
        $model->confirm_password = '';

        $post = Yii::$app->request->post('User', []);
        $newPassword = trim($post['password'] ?? '');
        $newConfirm = trim($post['confirm_password'] ?? '');

        if ($model->load(Yii::$app->request->post())) {
            if ($newPassword === '') {
                $model->password = $model->password_hash;
                $model->confirm_password = $model->password_hash;
            }
            if ($model->validate()) {
                if ($newPassword !== '') {
                    $model->setPassword($newPassword);
                }
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'บันทึกการตั้งค่าบัญชีเรียบร้อย');
                    return $this->redirect(['/me/profile']);
                }
            } else {
                if ($newPassword === '') {
                    $model->password = '';
                    $model->confirm_password = '';
                }
            }
        }

        return $this->render('account', [
            'model' => $model,
        ]);
    }

    public function actionRepairMe()
    {
        $userId = Yii::$app->user->id;
        $searchModel = new HelpdeskSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'repair', 'created_by' => $userId]);
        $dataProvider->query->andFilterWhere(['in', 'status', [1, 2, 3]]);
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'summary' => $dataProvider->getTotalCount(),
            'content' => $this->renderAjax('repair_me', [
                'dataProvider' => $dataProvider,
            ])
        ];
    }

    public function actionRepair()
    {
        $userId = Yii::$app->user->id;
        $searchModel = new HelpdeskSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        // $dataProvider->query->andFilterWhere(['name' => 'repair', 'created_by' => $userId]);
        // $dataProvider->query->andFilterWhere(['in', 'status', [1, 2, 3]]);
        $dataProvider->pagination->pageSize = 4;
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'summary' => $dataProvider->getTotalCount(),
            'content' => $this->renderAjax('activity', [
                'dataProvider' => $dataProvider,
            ])
        ];
    }
}
