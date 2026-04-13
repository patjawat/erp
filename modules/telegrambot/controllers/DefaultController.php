<?php

namespace app\modules\telegrambot\controllers;
use app\models\Categorise;
use app\modules\telegrambot\components\TelegramBot;
use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * Default controller for the `telegrambot` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {

   $model = Categorise::findOne(['name'=>'telegram_setting']);

if(!$model){
$model = new Categorise();
$model->name='telegram_setting';
}

if($model->load(Yii::$app->request->post())){

$model->data_json = json_encode($model->data_json);

$model->save(false);

Yii::$app->session->setFlash('success','บันทึกสำเร็จ');

return $this->refresh();

}

$model->data_json = json_decode((string) ($model->data_json ?? ''), true);

return $this->render('index',[
'model'=>$model
]);
    }

     public $enableCsrfValidation = false; // ปิด CSRF สำหรับ webhook

    public function actionWebhook()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $botToken = Yii::$app->getModule('telegrambot')->botToken;
        $telegram = new TelegramBot($botToken);

        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($input['message'])) {
            $chatId = $input['message']['chat']['id'];
            $text = $input['message']['text'];

            // ตอบกลับข้อความ
            $telegram->sendMessage($chatId, "คุณส่งข้อความว่า: " . $text);

            return ['status' => 'ok'];
        }

        return ['status' => 'no_message'];
    }

}

