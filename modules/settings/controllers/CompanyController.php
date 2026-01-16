<?php

namespace app\modules\settings\controllers;

use Yii;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\LogHelper;
use app\components\UserHelper;

class CompanyController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $model = Categorise::findOne(['name' => 'site']);
        $old = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->data_json = ArrayHelper::merge($old,$model->data_json);
                if($model->save()){
                    $userId =  $model->data_json['director_name'];
                    $this->updateDirector($userId);
                }
                $me = UserHelper::GetEmployee();
                $data = [
                    "fullname" =>$me->fullname,
                    'title' => 'แก้ไขข้อมูลองกรณ์',
                    'data' => $model
                ];
                // LogHelper::log('update_setting',$data);
                return $this->redirect('/settings/company');
            }
        }
        return $this->render('index',['model' => $model]);
    }

    //ปรับปรุง roles ระดับผู้อำนวยการ
    protected function updateDirector($userId)
    {
        // ลบสิทธิ์ director จากทุกคนที่ไม่ใช่ userId นี้
    Yii::$app->db->createCommand()
        ->delete('auth_assignment', ['and', ['item_name' => 'director'], ['!=', 'user_id', $userId]])
        ->execute();
    Yii::$app->db->createCommand()
        ->delete('auth_item_child', ['and', ['child' => 'director']])
        ->execute();

        $checkOver = Yii::$app->db->createCommand("SELECT count(*)  FROM `auth_assignment` WHERE `item_name` = 'director'")->queryScalar();
        if($checkOver == 0){
            Yii::$app->db->createCommand()
        ->insert('auth_assignment', [
            'item_name' => 'director',
            'user_id' => $userId,
            'created_at' => time(), // ปกติ table นี้จะมีฟิลด์เวลาสร้าง
        ])
        ->execute();
        }
        
    }

}
