<?php

namespace app\controllers;
use Yii;
use app\models\Categorise;
use yii\web\Response;
use yii\helpers\ArrayHelper;

class SettingController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $colors =  Categorise::find()->where(['name' => 'theme_color'])->all();
        return $this->render('index',['colors' => $colors]);
    }

    public function actionCompany()
    {
        $model = Categorise::findOne(['name' => 'site']);
        $old = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->data_json = ArrayHelper::merge($old,$model->data_json);
                $model->save();
                return $this->redirect('/setting/company');
            }
        }
        return $this->render('company',['model' => $model]);
    }

    //ตั้งค่า Line official
    public function actionLineOfficial()
    {
        $model = Categorise::findOne(['name' => 'site']);
        if ($this->request->isPost) {
            $old = $model->data_json;
            if ($model->load($this->request->post())) {
                $model->data_json = ArrayHelper::merge($old,$model->data_json);
                $model->save();
                return $this->redirect('/setting/line-official');
            }
        }
        return $this->render('line_official',['model' => $model]);
    }


    public function actionLayout()
    {
        $model = Categorise::findOne(['name' => 'site']);
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['layout']);
            }
        } else {
            $model->loadDefaultValues();
            return $this->render('layouts',['model' => $model]);
        }
    }

//ตั้งค่าสี

public function actionSetColor()
{
    $color = $this->request->get('color');
    $colorName = $this->request->get('colorname');
    Yii::$app->response->format  = Response::FORMAT_JSON;
   
    $color = [
        'theme_color_name' => $colorName,
        'theme_color' => $color,
    ];
    $model = Categorise::findOne(['name' => 'site']);
    $model->data_json = ArrayHelper::merge($model->data_json, $color);
    return $model->save();
    
}

//สร้าง key สำหรับ api เชื่อมกับ looker studio
public function actionGenkey(){
    
    $model =  Categorise::findOne(1);

    $model->data_json = ArrayHelper::merge($model->data_json,[
        'token' => substr(Yii::$app->getSecurity()->generateRandomString(),10)
    ] );
    if($model->save(false)){
        return $this->redirect('/setting/looker');
    }
    
}

public function actionLooker()
{
    $model =  Categorise::findOne(1);

    if ($this->request->isPost) {
        if ($model->load($this->request->post())) {
             return $this->request->post();
            // $emp->data_json = ArrayHelper::merge($emp->data_json );
            // $emp->save(false);

            // return $this->redirect('/setting');
        }
    } else {
        $model->loadDefaultValues();
    }

    if($this->request->isAjax) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return[
            'title' => '<i class="fas fa-user-plus"></i> สร้างใหม่',
            'content' => $this->renderAjax('create', ['model' => $model]),
            'footer' =>''
        ];
    }else {

        return $this->render('looker', [
            'model' => $model,
        ]);
    }
}


}





// {
//     "fax": "042892379",
//     "email": "dansaihospital@gmail.com",
//     "phone": "042891276",
//     "address": "168 ม.3 ต.ด่านซ้าย อ.ด่านซ้าย จ.เลย 42120",
//     "hoscode": "11447",
//     "website": "dansaihospital.com",
//     "province": "เลย",
//     "company_name": "โรงพยาบาลสมเด็จพระยุพราชด่านซ้าย",
//     "director_name": "นพ.สันทัด บุญเรือง",
//     "line_liff_app": "2005893839-2ynww1B4",
//     "line_liff_about": "2005893839-lrKJJjOg",
//     "line_liff_login": "2005893839-JAYvvA6G",
//     "director_position": "นายแพทย์ชำนาญการพิเศษ รักษาการในตำแหน่งผู้อำนวยการโรงพยาบาลสมเด็จพระยุพราชด่านซ้าย",
//     "line_liff_profile": "2005893839-1vEqqXoQ",
//     "line_liff_service": "2005893839-Zz0NN7Xk",
//     "line_liff_register": "2005893839-9qRwwMWG",
//     "line_liff_dashboard": "2005893839-d5355D6g",
//     "line_liff_user_connect": "2005893839-Lyjoo921"
// }