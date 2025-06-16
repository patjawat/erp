<?php

namespace app\components;

use Yii;
use TCPDF;
use app\models\Uploads;
use yii\base\Component;
use chillerlan\QRCode\QRCode;
use app\components\SiteHelper;
use app\components\ThaiDateHelper;

class PdfHelper extends Component
{


    public static function Stamp($model)
    {
        // เตรียมข้อความประทับตรา
        $companyName = SiteHelper::getInfo()['company_name'];
        $textLines = [
            $companyName,
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

        // ใช้ขนาดมาตรฐานตราประทับหนังสือราชการ (วงกลม เส้นผ่านศูนย์กลาง 3.5 ซม.)
        // แต่กล่องข้อความจะใช้ขนาดใกล้เคียงเพื่อให้ข้อความอยู่ในกรอบ
        $pageWidth = 210; // A4
        $marginTop = 5; // ขอบบน
        $marginRight = 10; // ขอบขวา

        // คำนวณความกว้างของกล่องตามความยาวชื่อบริษัท
        $pdf->SetFont('THSarabunNew', 'B', 13);
        $companyNameWidth = $pdf->GetStringWidth(iconv('UTF-8', 'cp874', $companyName)) + 10; // padding ซ้ายขวา 5mm
        $minBoxWidth = 50; // ขั้นต่ำ 50mm
        $textBoxWidth = max($companyNameWidth, $minBoxWidth);
        $textBoxHeight = 25; // 25 มม.
        $posX = $pageWidth - $textBoxWidth - $marginRight;
        $posY = $marginTop;

        // วนลูปทุกหน้า
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplIdx = $pdf->importPage($pageNo);
            $pdf->AddPage();
            $pdf->useTemplate($tplIdx, 0, 0, 210);

            // ประทับตราเฉพาะหน้าแรก
            if ($pageNo === 1) {
                // กำหนดสีเส้นกรอบเป็นสีน้ำเงิน
                $pdf->SetDrawColor(54, 52, 141, 61);
                $pdf->SetLineWidth(0.8);

                // กำหนดสีตัวหนังสือ (54, 52, 141, 61)
                $pdf->SetTextColor(54, 52, 141, 61);

                // วาดกรอบ
                $pdf->Rect($posX, $posY, $textBoxWidth, $textBoxHeight, 'D');
                $pdf->SetY($posY);

                foreach ($textLines as $i => $line) {
                    $pdf->SetX($posX);
                    $pdf->SetFont('THSarabunNew', 'B', 13); // ใช้ขนาดฟอนต์ 13 pt

                    // จัดกึ่งกลางเฉพาะบรรทัดแรก (ชื่อบริษัท)
                    $align = ($i === 0) ? 'C' : 'L';
                    $pdf->Cell($textBoxWidth, 5, iconv('UTF-8', 'cp874', $line), 0, 2, $align, false);
                }

                // รีเซ็ตสีตัวหนังสือกลับเป็นสีดำ (ถ้าต้องการ)
                $pdf->SetTextColor(0, 0, 0);
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
