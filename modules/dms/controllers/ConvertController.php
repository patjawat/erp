<?php

// 1. ตัวอย่าง Controller สำหรับหน้าหลัก
namespace app\modules\dms\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use app\components\StampHelper;
use yii\filters\AccessControl;
use app\components\PdfStampGenerator;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentSearch;

class ConvertController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'view', 'upload-with-stamp', 'download'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // เฉพาะผู้ใช้ที่ล็อกอินแล้ว
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * หน้าแสดงรายการเอกสารทั้งหมด
     */
    public function actionIndex()
    {
      $cmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=/app/web/pdfs/output.pdf /app/web/pdfs/xxx.pdf";
        exec($cmd);

    }

}
