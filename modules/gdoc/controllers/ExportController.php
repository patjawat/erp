<?php
// modules/gdoc/controllers/ExportController.php

namespace app\modules\gdoc\controllers;

use yii\web\Controller;
use yii\web\Response;
use app\modules\gdoc\components\GoogleService;

class ExportController extends Controller
{
    public function actionPdf()
    {
        \Yii::$app->response->format = Response::FORMAT_RAW;
        \Yii::$app->response->headers->add('Content-Type', 'application/pdf');
        \Yii::$app->response->headers->add('Content-Disposition', 'attachment; filename="document.pdf"');

        $service = new GoogleService();

        // ใส่ Document Template ID ของคุณ
        $templateId = 'xxxxxxxxxxxxxxxxxxxxxxxxxxx';
        $replacements = [
            'name' => 'นายปัจวัฒน์ ศรีบุญเรือง',
            'position' => 'เจ้าหน้าที่ไอที',
            'date' => date('d/m/Y'),
        ];

        $docId = $service->copyAndReplace($templateId, 'เอกสารใหม่', $replacements);
        $pdfContent = $service->exportAsPdf($docId);

        return $pdfContent;
    }
}
