<?php

namespace app\components;

use Yii;
use TCPDF;
use app\models\Uploads;
use yii\base\Component;
use chillerlan\QRCode\QRCode;
use app\components\SiteHelper;
use app\components\ThaiDateHelper;

class PdfHelper extends Component{

    public static function Stamp($model)
{
    $textLines = [
        SiteHelper::getInfo()['company_name'],
        'รับที่ : '.$model->doc_regis_number,
        'วันที่ : '.ThaiDateHelper::formatThaiDateRange($model->doc_transactions_date),
        'เวลา : '.$model->doc_time.' น.',
    ];

    define('FPDF_FONTPATH', Yii::getAlias('@webroot/fonts/'));  // ที่เก็บฟอนต์แปลงแล้ว

        $ref = $model->ref;
        $directory = Yii::getAlias('@app/modules/filemanager/fileupload/' . $ref . '/');
        $checkFileUpload = Uploads::findOne(['ref' => $ref]);
        // if ($checkFileUpload) {
            $fileName = $checkFileUpload->real_filename;
            $filePath = $directory . $fileName;

            // ตรวจสอบว่าไฟล์มีอยู่หรือไม่
            // if (file_exists($filePath) && is_file($filePath)) {
            //     return true;
            // } else {
            //     return false;
            // }
        // } else {
        //     return false;
        // }


    $pdf = new \setasign\Fpdi\Fpdi();
    // $pdfPath = Yii::getAlias('@webroot/uploads/document2.pdf');
    // $outputPath = Yii::getAlias('@webroot/uploads/stamped_output.pdf');
    $pdfPath = $filePath;
    $outputPath = $filePath;

    $pdf->setSourceFile($pdfPath);
    $tplIdx = $pdf->importPage(1); // ใช้หน้าแรก
    $pdf->AddPage();
    $pdf->useTemplate($tplIdx, 0, 0, 210);  // A4 กว้าง 210mm

    // โหลดฟอนต์ไทย
    $pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
    $pdf->AddFont('THSarabunNew', 'B', 'THSarabunNew Bold.php');
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
