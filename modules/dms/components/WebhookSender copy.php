<?php

namespace app\modules\dms\components;

use Yii;
use yii\helpers\Json;
use GuzzleHttp\Client;
use app\models\Uploads;
use yii\base\Component;
use app\models\Categorise;
use app\components\SiteHelper;
use app\modules\filemanager\components\FileManagerHelper;

class WebhookSender extends Component
{
    private $_client;

    public function init()
    {
        parent::init();
        $this->_client = new Client(['timeout' => 15]);
    }

    public static function sendToAgencies($documentData)
    {
        $orgIds = $documentData->document_org;
        if (is_string($orgIds)) {
            $orgIds = \yii\helpers\Json::decode($orgIds);
        }

        $agencies = Categorise::find()
            ->where(['code' => $orgIds, 'name' => 'document_org'])
            ->all();

        if (empty($agencies)) {
            return ["error" => "No agencies found"];
        }

        $client = new Client(['timeout' => 30, 'http_errors' => false]);
        $results = [];

        foreach ($agencies as $agency) {

            try {
                $app = SiteHelper::getInfo();
                $dataJson = is_string($agency->data_json) ? \yii\helpers\Json::decode($agency->data_json) : $agency->data_json;
                $url = rtrim($dataJson['url'] ?? '', '/') . '/dms/webhook/receive';

                $ref = $documentData->ref;
                $fileUpload = Uploads::find()->where(['name' => 'document', 'ref' => $ref])->one();
                $filename = $fileUpload->real_filename;
                $filepath = FileManagerHelper::getUploadPath() . $ref . '/' . $filename;
                $fullPathToFile = $filepath; // เช่น 'uploads/doc1.pdf'
                // 1. เตรียมข้อมูล JSON ที่ต้องการส่ง
                $payload = [
                    "request_id" => $documentData->ref . '_' . $agency->id, // สร้าง ID เฉพาะตัว
                    "hoscode" => $app['hoscode'],
                    "hosname" => $app['company_name'],
                    "form_org_name" => $documentData->documentOrg->title,
                    "topic" => $documentData->topic,
                    "doc_regis_number" => $documentData->doc_regis_number,
                    "doc_number" => $documentData->doc_number,
                    "doc_date" => $documentData->doc_date,
                    "sender_app" => Yii::$app->name,
                    "timestamp" => date('Y-m-d H:i:s')
                ];

                $multipart = [
                    [
                        'name'     => 'document_data', // ส่งข้อมูล JSON
                        'contents' => json_encode($payload),
                    ],
                    [
                        'name'     => 'attachment',    // ส่งไฟล์จริง
                        'contents' => fopen($fullPathToFile, 'r'), // ต้องมีบรรทัดนี้!
                        'filename' => 'document.pdf'
                    ],
                ];

                $response = $client->post($url, [
                    'multipart' => $multipart // ต้องใช้ key 'multipart' ห้ามใช้ 'json'
                ]);

                if (!empty($relativeFilePath) && file_exists($fullPathToFile)) {
                    $multipart[] = [
                        'name'     => 'attachment', // ชื่อ Field สำหรับไฟล์
                        'contents' => fopen($fullPathToFile, 'r'),
                        'filename' => basename($fullPathToFile)
                    ];
                }

                try {
                    $response = $client->post($url, [
                        'headers' => [
                            'X-Webhook-Secret' => Yii::$app->params['webhook_secret'] ?? 'default_secret',
                        ],
                        'multipart' => $multipart // ส่งแบบ Multipart
                    ]);

                    $results[$agency->id] = [
                        'status' => $response->getStatusCode(),
                        'body'   => \yii\helpers\Json::decode($response->getBody()->getContents())
                    ];
                } catch (\Exception $e) {
                    Yii::error("Webhook Error: " . $e->getMessage());
                    $results[$agency->id] = "Exception: " . $e->getMessage();
                }
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        return $results;
    }



    public static function receive($payload = null)
    {
        $request = \Yii::$app->request;

        // 1. ตรวจสอบข้อมูล JSON (กรณีส่งมาในฟิลด์ 'document_data' แบบ Multipart)
        $jsonRaw = $request->post('document_data');
        $jsonRaw = $request->post('document_data');
    if (!empty($jsonRaw)) {
        $payload = \yii\helpers\Json::decode($jsonRaw);
    } elseif (empty($payload)) {
        $rawBody = $request->getRawBody();
        $payload = !empty($rawBody) ? \yii\helpers\Json::decode($rawBody) : null;
    }
        if (!empty($jsonRaw)) {
            $payload = \yii\helpers\Json::decode($jsonRaw);
        }
        // กรณีเผื่อไว้สำหรับ legacy หรือการเทสแบบ Raw JSON ทั่วไป
        elseif (empty($payload)) {
            $rawBody = $request->getRawBody();
            $payload = !empty($rawBody) ? \yii\helpers\Json::decode($rawBody) : null;
        }

        if (empty($payload)) {
            \Yii::error("Payload is empty");
            return ['status' => 'error', 'message' => 'No data received'];
        }

        // 2. จัดการไฟล์ที่แนบมา (Field name: 'attachment')
        $fileInfo = null;
        $uploadedFile = \yii\web\UploadedFile::getInstanceByName('attachment');
        if ($uploadedFile) {
            $savePath = \Yii::getAlias('@runtime/webhooks/files/');
            if (!is_dir($savePath)) {
                mkdir($savePath, 0777, true);
            }

            // 1. ใช้ request_id หรือค่า Unique จาก Payload มาตั้งชื่อไฟล์แทน timestamp
            // เพื่อให้ไฟล์ของเอกสารฉบับนี้มีชื่อเดิมเสมอ แม้จะส่งใหม่
            $requestId = $payload['request_id'] ?? ($payload['doc_regis_number'] ?? time());
            $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $uploadedFile->name); // ล้างอักขระพิเศษ
            $fileName = $requestId . '_' . $safeName;
            $fullPath = $savePath . $fileName;

            // 2. ตรวจสอบว่ามีไฟล์เดิมอยู่แล้วหรือไม่ (กรณีส่งซ้ำเพื่อแก้ไข)
            $isUpdate = file_exists($fullPath);

            if ($uploadedFile->saveAs($fullPath)) {
                $fileInfo = [
                    'original_name' => $uploadedFile->name,
                    'saved_path' => $fullPath,
                    'filename' => $fileName,
                    'size' => $uploadedFile->size,
                    'type' => $uploadedFile->type,
                    'updated' => $isUpdate // บอกให้รู้ว่าเป็นไฟล์ที่มาทับของเดิม
                ];
            } else {
                \Yii::error("Failed to save uploaded file.");
            }
        }

        // 3. กำหนด Path สำหรับบันทึก Log JSON
        $filePath = \Yii::getAlias('@runtime/webhooks/document_receive.json');
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        try {
            $existingData = [];
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                $existingData = \yii\helpers\Json::decode($content) ?? [];
            }

            // --- ส่วนที่เพิ่มเข้าไปเพื่อตรวจสอบการส่งซ้ำ ---
            $currentRequestId = $payload['request_id'] ?? null; // สมมติว่าฝั่งส่งระบุ request_id มาให้
            if ($currentRequestId) {
                foreach ($existingData as $item) {
                    if (isset($item['content']['request_id']) && $item['content']['request_id'] === $currentRequestId) {
                        // หากพบ ID ซ้ำ ให้หยุดทำงานและแจ้งกลับฝั่งส่งทันที
                        return [
                            'status' => 'duplicate',
                            'message' => 'Request ID: ' . $currentRequestId . ' has already been processed.'
                        ];
                    }
                }
            }
            // ------------------------------------------
            // 4. รวมข้อมูล JSON และข้อมูลไฟล์เข้าด้วยกัน
            $newData = [
                'received_at' => date('Y-m-d H:i:s'),
                'sender_ip' => $request->userIP,
                'content' => $payload,
                'attachment_info' => $fileInfo // เก็บข้อมูลไฟล์ไว้ใน log ด้วย
            ];

            array_unshift($existingData, $newData);

            file_put_contents(
                $filePath,
                \yii\helpers\Json::encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                LOCK_EX
            );

            return [
                'status' => 'success',
                'message' => 'Data and file logged successfully',
                'file_received' => ($fileInfo !== null),
                'log_path' => $filePath
            ];
        } catch (\Exception $e) {
            \Yii::error("File Write Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Failed to write file'];
        }
    }

    // ฟังก์ชันนับจำนวนทั้งหมด
    public static function countReceivedDocuments()
    {
        $filePath = Yii::getAlias('@runtime/webhooks/document_receive.json');

        if (!file_exists($filePath)) {
            return 0;
        }

        try {
            $content = file_get_contents($filePath);
            $data = \yii\helpers\Json::decode($content);

            return is_array($data) ? count($data) : 0;
        } catch (\Exception $e) {
            Yii::error("Count Error: " . $e->getMessage());
            return 0;
        }
    }
}
