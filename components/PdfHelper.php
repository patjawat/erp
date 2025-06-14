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
    // เตรียมข้อความประทับตรา
    $textLines = [
        SiteHelper::getInfo()['company_name'],
        'รับที่ : ' . $model->doc_regis_number,
        'วันที่ : ' . ThaiDateHelper::formatThaiDateRange($model->doc_transactions_date),
        'เวลา : ' . $model->doc_time . ' น.',
    ];

    // ระบุที่เก็บฟอนต์แปลงแล้ว
    define('FPDF_FONTPATH', Yii::getAlias('@webroot/fonts/'));

    // หาพาธไฟล์ PDF ต้นฉบับ
    $ref = $model->ref;
    $directory = Yii::getAlias('@app/modules/filemanager/fileupload/' . $ref . '/');
    $checkFileUpload = Uploads::findOne(['ref' => $ref]);

    if (!$checkFileUpload) {
        return ['success' => false, 'message' => 'ไม่พบไฟล์'];
    }

    $fileName = $checkFileUpload->real_filename;
    $filePath = $directory . $fileName;
    $outputPath = $filePath; // จะเขียนทับไฟล์เดิม

    // สร้างออบเจกต์ PDF และโหลดไฟล์ต้นฉบับ
    $pdf = new \setasign\Fpdi\Fpdi();
    $pageCount = $pdf->setSourceFile($filePath);

    // โหลดฟอนต์ THSarabun
    $pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
    $pdf->AddFont('THSarabunNew', 'B', 'THSarabunNew Bold.php');

    // คำนวณตำแหน่งกล่องข้อความ
    $pageWidth = 210; // A4
    $marginTop = 5;
    $marginRight = 10;
    $textBoxWidth = 65;
    $textBoxHeight = count($textLines) * 5;
    $posX = $pageWidth - $textBoxWidth - $marginRight;
    $posY = $marginTop;

    // วนลูปทุกหน้า
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $tplIdx = $pdf->importPage($pageNo);
        $pdf->AddPage();
        $pdf->useTemplate($tplIdx, 0, 0, 210);

        // ประทับตราเฉพาะหน้าแรก
        if ($pageNo === 1) {
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetLineWidth(0.8);
            $pdf->SetFillColor(255, 255, 255);

            // วาดกรอบ
            $pdf->Rect($posX, $posY, $textBoxWidth, $textBoxHeight, 'D');
            $pdf->SetY($posY);

            foreach ($textLines as $line) {
                $pdf->SetX($posX);
                $pdf->SetFont('THSarabunNew', 'B', 12);
                $pdf->Cell($textBoxWidth, 5, iconv('UTF-8', 'cp874', $line), 0, 2, 'L', true);
            }
        }
    }

    // บันทึกไฟล์ (เขียนทับหรือเปลี่ยนชื่อก็ได้)
    $pdf->Output($outputPath, 'F');

    return [
        'success' => true,
        'file' => str_replace(Yii::getAlias('@webroot'), '', $outputPath),
        'message' => 'ประทับตราเฉพาะหน้าแรกสำเร็จ',
    ];
}

    
}
