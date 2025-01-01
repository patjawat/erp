<?php

namespace app\components;

use yii\base\Component;
use app\models\Categorise;
use yii\httpclient\Client;

class LineNotify extends Component
{
    public $token;

    public function init()
    {
        parent::init();

        // if (!$this->token) {
        //     throw new \Exception('Token cannot be empty.');
        // }
    }

    public function sendMessage($message, $groupId)
    {
        try {
            $group = Categorise::find()->where(['name' => 'line_group', 'code' => $groupId])->one();
            $client = new Client();
            // $token = 'u090Q5IjiP3BOCPbGGdn1Vdj16AZ6mVtz2SV9Bd22ce';
            $token = $group->data_json['token'];
            $response = $client
                ->createRequest()
                ->setMethod('POST')
                ->setUrl('https://notify-api.line.me/api/notify')
                ->setHeaders(['Authorization' => 'Bearer ' . $token])
                ->setData(['message' => $message])
                ->send();

            if ($response->isOk) {
                return $response->data;
            } else {
                throw new \Exception('Failed to send message: ' . $response->content);
            }
            // code...
        } catch (\Throwable $th) {
            // throw $th;
        }
    }
}
