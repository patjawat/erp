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


    $textLines = [
        'โรงพยาบาลสมเด็จพยุพราชด่านซ้าย ทดสอบระบบ',
        'รับที่ : 1204/2567',
        'วันที่ : 14 มิ.ย. 2567',
        'เวลา : 09:45 น.',
    ];

    define('FPDF_FONTPATH', Yii::getAlias('@webroot/fonts/'));  // ที่เก็บฟอนต์แปลงแล้ว

    $pdf = new \setasign\Fpdi\Fpdi();
    $outputPath = Yii::getAlias('@webroot/uploads/stamped_output.pdf');
    $pdfPath = Yii::getAlias('@webroot/uploads/document2.pdf');

    $pdf->setSourceFile($pdfPath);
    $tplIdx = $pdf->importPage(1); // ใช้หน้าแรก
    $pdf->AddPage();
    $pdf->useTemplate($tplIdx, 0, 0, 210);  // A4 กว้าง 210mm

    // โหลดฟอนต์ไทย
    $pdf->AddFont('THSarabunNew', '', 'THSarabunNew.json');
    $pdf->AddFont('THSarabunNew', 'B', 'THSarabunNew Bold.json');
    $pdf->SetFont('THSarabunNew', '', 18);



    $pageWidth = 210;
    $marginTop = 5;
    $marginRight = 10;
    $textBoxWidth = 65;
    $textBoxHeight = count($textLines) * 5; // 5 คือความสูงแต่ละบรรทัด

    $posX = $pageWidth - $textBoxWidth - $marginRight;
    $posY = $marginTop;

    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.8);
    $pdf->SetFillColor(255, 255, 255);

    // วาดกรอบสี่เหลี่ยม
    $pdf->Rect($posX, $posY, $textBoxWidth, $textBoxHeight, 'D');
    $pdf->SetY($posY);
    foreach ($textLines as $i => $line) {
        $pdf->SetX($posX);
            $pdf->SetFont('THSarabunNew', 'B', 12); // ตัวหนา
        $pdf->Cell($textBoxWidth, 5, iconv('UTF-8', 'cp874', $line), 0, 2, 'L', true);
    }
    $pdf->Output($outputPath, 'F');

    return [
        'success' => true,
        'file' => '/uploads/stamped_output.pdf',
        'message' => 'ประทับตราสำเร็จ',
    ];
}


}