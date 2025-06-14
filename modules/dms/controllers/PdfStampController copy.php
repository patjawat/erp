<?php

namespace app\modules\dms\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class PdfStampController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
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
     * แสดงหน้าหลักสำหรับอัพโลด PDF และจัดการ stamp
     */
    public function actionIndex()
    {
      return $this->render('index');
    }


    public function actionStampSave()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $text = Yii::$app->request->post('text');
    $left = Yii::$app->request->post('left');
    $top = Yii::$app->request->post('top');

    // ใช้ FPDI ใส่ข้อความลง PDF
    $pdfPath = Yii::getAlias('@webroot/uploads/document2.pdf');
    $outputPath = Yii::getAlias('@webroot/uploads/stamped_output.pdf');

    $pdf = new \setasign\Fpdi\Fpdi();
    $pdf->AddPage();

    // ✅ เพิ่มฟอนต์ภาษาไทย (ที่เราสร้างไว้)
    $pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
    $pdf->SetFont('THSarabunNew', '', 18);


    $pdf->setSourceFile($pdfPath);
    $tplIdx = $pdf->importPage(1);
    $pdf->useTemplate($tplIdx);

    $pdf->SetFont('Helvetica', 'B', 16);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->SetXY($left / 3.8, $top / 3.8); // scale px to mm (approx)
    $pdf->Write(0, $text);

    $pdf->Output($outputPath, 'F');

    return ['success' => true, 'file' => '/uploads/stamped_output.pdf'];
}


}