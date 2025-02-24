<?php

namespace app\components;

use Yii;
use yii\helpers\Url;
use app\models\Approve;
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

    public static function sendPushMessage($userId, $message)
    {
        $token = Categorise::find()->where(['name' => 'site'])->one();
        $channelAccessToken = $token->data_json['line_channel_token'];
        $url = 'https://api.line.me/v2/bot/message/push';
        $data = [
            'to' => $userId,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $message,
                ],
            ],
        ];

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($url)
            ->addHeaders([
                'Authorization' => 'Bearer ' . $channelAccessToken,
                'Content-Type' => 'application/json',
            ])
            ->setContent(json_encode($data))
            ->send();

        if (!$response->isOk) {
            Yii::error('Failed to send LINE message: ' . $response->content, __METHOD__);
            return false;
        }

        return true;
    }

     // ฟังก์ชันส่ง Flex Message
     public function sendFlexMessage($userId, $altText, $flexContent)
     {
        $token = Categorise::find()->where(['name' => 'site'])->one();
        $channelAccessToken = $token->data_json['line_channel_token'];
         $url = 'https://api.line.me/v2/bot/message/push';
 
         $data = [
             'to' => $userId,
             'messages' => [
                 [
                     'type' => 'flex',
                     'altText' => $altText,
                     'contents' => $flexContent,
                 ],
             ],
         ];
 
         $client = new Client();
         $response = $client->createRequest()
             ->setMethod('POST')
             ->setUrl($url)
             ->addHeaders([
                 'Authorization' => 'Bearer ' . $channelAccessToken,
                 'Content-Type' => 'application/json',
             ])
             ->setContent(json_encode($data))
             ->send();
 
         if (!$response->isOk) {
             Yii::error('Failed to send LINE Flex message: ' . $response->content, __METHOD__);
             return false;
         }
 
         return true;
     }
     // ฟังก์ชันส่ง Flex Message
     public static function sendLeave($approveId,$userId)
     {

        $token = Categorise::find()->where(['name' => 'site'])->one();
        $channelAccessToken = $token->data_json['line_channel_token'];
        $approve = Approve::findOne($approveId);
        $uri = Url::base(true) . Url::to(['/line/approve/leave', 'id' => $approveId]);
        $url = 'https://api.line.me/v2/bot/message/push';
 
        $altText = 'ขออนุมัติ'.$approve->leave->leaveType->title; // ข้อความสำรอง
        $flexContent = [
            'type' => 'bubble',
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => $altText,
                        'weight' => 'bold',
                        'size' => 'xl',
                    ],
                    [
                        'type' => 'text',
                        'text' => $approve->leave->employee->fullname,
                        'size' => 'md',
                        'wrap' => true,
                    ],
                ],
            ],
            'footer' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'button',
                        'style' => 'primary',
                        'action' => [
                            'type' => 'uri',
                            'label' => 'ดำเนินการ',
                            // 'uri' => Url::base(true).'/line/leave' // ลิงก์ที่คุณต้องการให้ผู้ใช้งานเปิด
                            'uri' =>$uri // ลิงก์ที่คุณต้องการให้ผู้ใช้งานเปิด
                        ],
                    ],
                ],
            ],
        ];
        
         $data = [
             'to' => $userId,
             'messages' => [
                 [
                     'type' => 'flex',
                     'altText' => $altText,
                     'contents' => $flexContent,
                 ],
             ],
         ];
 
         $client = new Client();
         $response = $client->createRequest()
             ->setMethod('POST')
             ->setUrl($url)
             ->addHeaders([
                 'Authorization' => 'Bearer ' . $channelAccessToken,
                 'Content-Type' => 'application/json',
             ])
             ->setContent(json_encode($data))
             ->send();
 
         if (!$response->isOk) {
             Yii::error('Failed to send LINE Flex message: ' . $response->content, __METHOD__);
             return false;
         }
 
         return true;
     }


      // ฟังก์ชันส่ง Flex Message
      public static function sendDocument($model,$userId)
      {
 
         $token = Categorise::find()->where(['name' => 'site'])->one();
         $channelAccessToken = $token->data_json['line_channel_token'];
        //  $uri = Url::base(true) . Url::to(['/line/documents/view', 'id' => $model->id]);
         $uri = Url::base(true) . Url::to(['/line/documents/show','ref' => $model->document->ref]);
        //  $uri = Url::base(true) . Url::to(['/line/documents/view','id' => $model->id]);
         $uriComment = Url::base(true) . Url::to(['/line/documents/comment','id' => $model->id]);
         $url = 'https://api.line.me/v2/bot/message/push';
         $altText = "หนังสือ";
         $flexContent = [
             'type' => 'bubble',
             'body' => [
                 'type' => 'box',
                 'layout' => 'vertical',
                 'contents' => [
                     [
                         'type' => 'text',
                         'text' => $altText,
                         'weight' => 'bold',
                         'size' => 'xl',
                     ],
                     [
                         'type' => 'text',
                         'text' => $model->document->topic,
                         'size' => 'md',
                         'wrap' => true,
                     ],
                 ],
             ],
             'footer' => [
                 'type' => 'box',
                 'layout' => 'horizontal', // เปลี่ยน layout เป็น horizontal เพื่อให้ปุ่มแสดงในแนวนอน
                 'spacing' => 'md', // กำหนดช่องว่างระหว่างปุ่ม (xs, sm, md, lg, xl)
                 'contents' => [
                     [
                         'type' => 'button',
                         'style' => 'primary',
                         'action' => [
                             'type' => 'uri',
                             'label' => 'อ่าน',
                             'uri' =>$uri // ลิงก์ที่คุณต้องการให้ผู้ใช้งานเปิด
                         ],
                     ],
                     [
                        'type' => 'button',
                        'style' => 'secondary',
                        'action' => [
                            'type' => 'uri',
                            'label' => 'ลงความเห็น',
                            'uri' =>$uriComment // ลิงก์ที่คุณต้องการให้ผู้ใช้งานเปิด
                        ],
                    ],
                 ],
             ],
         ];
         
          $data = [
              'to' => $userId,
              'messages' => [
                  [
                      'type' => 'flex',
                      'altText' => $altText,
                      'contents' => $flexContent,
                  ],
              ],
          ];
  
          $client = new Client();
          $response = $client->createRequest()
              ->setMethod('POST')
              ->setUrl($url)
              ->addHeaders([
                  'Authorization' => 'Bearer ' . $channelAccessToken,
                  'Content-Type' => 'application/json',
              ])
              ->setContent(json_encode($data))
              ->send();
  
          if (!$response->isOk) {
              Yii::error('Failed to send LINE Flex message: ' . $response->content, __METHOD__);
              return false;
          }
  
          return true;
      }
      
 
}
