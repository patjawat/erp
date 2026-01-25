<?php

namespace app\modules\dms\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\Document;
use yii\helpers\Json;
use app\modules\dms\components\WebhookSender;

class WebhookController extends Controller
{
    // 1. สำคัญมาก: ต้องปิดการตรวจสอบ CSRF เพราะเป็นการรับข้อมูลจากภายนอก
    public $enableCsrfValidation = false;

public function actionReceive()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    // ข้อมูลตอนนี้จะเป็น Array ที่สมบูรณ์แล้ว
    // ส่งต่อไปให้ Component จัดการบันทึก
    return WebhookSender::receive();
}


    /**
     * ฟังก์ชันช่วยดาวน์โหลดไฟล์จาก URL
     */
    protected function downloadFiles($attachments, $documentId)
    {
        foreach ($attachments as $file) {
            $url = $file['download_url'];
            $fileName = $file['file_name'];
            $savePath = Yii::getAlias('@webroot/uploads/dms/') . $fileName;

            // ใช้ copy() หรือ Guzzle ดึงไฟล์มาเก็บ
            try {
                if (@copy($url, $savePath)) {
                    // ตรงนี้อาจจะเพิ่มการบันทึกข้อมูลลงตาราง FileAttachment ของคุณ
                    Yii::info("Download success: " . $fileName);
                }
            } catch (\Exception $e) {
                Yii::error("Download failed: " . $e->getMessage());
            }
        }
    }
}