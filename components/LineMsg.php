<?php

namespace app\components;

use Yii;
use yii\helpers\Url;
use yii\base\Component;
use app\models\Categorise;
use yii\httpclient\Client;
use app\modules\approve\models\Approve;
use app\modules\booking\models\Booking;
use app\modules\booking\models\Meeting;
use app\modules\booking\models\Vehicle;

class LineMsg extends Component
{
    public $token;

    public function init()
    {
        parent::init();

        // if (!$this->token) {
        //     throw new \Exception('Token cannot be empty.');
        // }
    }


    //ส่ง ข้อความ
    public static function PushMsg($data)
    {

       $getData = Categorise::find()->where(['name' => 'site'])->one();
       $channelAccessToken = $getData->data_json['line_channel_token'];
       $url = 'https://api.line.me/v2/bot/message/push';


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
    
//ส่งข้อความ
    public static function sendMsg($userId, $message)
    {
        $data = [
            'to' => $userId,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $message,
                ],
            ],
        ];

        //ส่งข้อความ
        self::PushMsg($data);
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

     //ส่งข้อความขอนุมัติจัดซื้อจัดจ้าง
     public static function sendPurchase($approveId,$userId)
     {
        $approve = Approve::findOne($approveId);
        $uri = Url::base(true) . Url::to(['/line/purchase/approve', 'id' => $approveId]);

        try {
            $orderTypeName =  $approve->purchase->data_json['order_type_name'];
        } catch (\Throwable $th) {
            $orderTypeName = '';
        }
        
        $altText = 'ขออนุมัติจัดซื้อจัดจ้าง'; // ข้อความสำรอง
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
                        'text' => $orderTypeName,
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
        //ส่งข้อความ
         self::PushMsg($data);
         return true;
     }
     
     // ฟังก์ชันส่ง Flex Message
     public static function sendLeave($approveId,$userId)
     {
        $approve = Approve::findOne($approveId);
        $uri = Url::base(true) . Url::to(['/line/approve/leave', 'id' => $approveId]);

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
        //ส่งข้อความ
         self::PushMsg($data);
         return true;
     }


      // ฟังก์ชันส่ง Flex Message
      public static function sendDocument($model,$userId)
      {
 
         $token = Categorise::find()->where(['name' => 'site'])->one();
         $channelAccessToken = $token->data_json['line_channel_token'];
         $uri = Url::base(true) . Url::to(['/line/documents/show','ref' => $model->document->ref]);
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
          self::PushMsg($data);
          return true;
      }
      

      // ฟังก์ชันส่ง Flex Message ส่งข้อความขอจองห้องประชุม
     public static function BookMeeting($id,$userId)
     {
        $model = Meeting::findOne($id);
        $uri = Url::base(true) . Url::to(['/line/booking-meeting/view', 'id' => $id]);

        $altText = 'ขอใช้'.$model->room->title; // ข้อความสำรอง
        $content = "วันที่ " . Yii::$app->thaiFormatter->asDate($model->date_start, 'medium') . 
        " เวลา " . $model->time_start . "-" . $model->time_end . "\n" .
        "ผู้ติดต่อ " . $model->employee->fullname . "\n" . 
        "โทรศัพท์ " . $model->data_json['phone'];

        
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
                        'size' => 'md',
                    ],
                    [
                        'type' => 'text',
                        'text' => $content,
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
        //ส่งข้อความ
         self::PushMsg($data);
         return true;
     }
 
     
     // ฟังก์ชันส่ง Flex Message
     public static function BookVehicle($id)
     {
        // $approve = Approve::find()->where(['name' => 'vehicle','from_id' => $id,'level' => $level,'status'=> 'Pending'])->one();
        $approve = Approve::findOne($id);
        $book = Vehicle::findOne($approve->from_id);
        $userId = $book->employee->user->line_id;
        
 
        $uri = Url::base(true) . Url::to(['/line/booking-vehicle/approve', 'id' => $approve->id]);
        $content = "ขออนุญาตใช้รถยนต์".($book->carType->title)."\n";
        $content .= "เหตุผล : ".$book->reason."\n";
        $content .= "ไป : ".$book->locationOrg->title."\n";
        $content .= "ประเภทการเดินทาง : ".$book->viewGoType()."\n";
        $content .= "วันที่ " . $book->showDateRange()."\n";
        // $content .= "มีผู้ร่วมเดินทาง : ".$book->data_json['total_person_count']." คน";

        $altText = 'ขออนุมัติยนต์'.$book->carType->title; // ข้อความสำรอง
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
                        'text' => $content,
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
        //ส่งข้อความ
         self::PushMsg($data);
         return true;
     }
}
