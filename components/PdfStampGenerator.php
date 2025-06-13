<?php
namespace app\components;

use Yii;
use yii\base\Component;
use TCPDF;
use chillerlan\QRCode\QRCode;

class PdfStampGenerator extends Component
{
    public $logoPath;
    public $fontPath;
    public $stampWidth = 300;
    public $stampHeight = 200;
    public $defaultPosition = ['x' => 50, 'y' => 50];
    
    public function init()
    {
        parent::init();
        
        // ตั้งค่าเริ่มต้น
        if (!$this->logoPath) {
            $this->logoPath = Yii::getAlias('@webroot/images/government-logo.png');
        }
        
        if (!$this->fontPath) {
            $this->fontPath = Yii::getAlias('@webroot/fonts/THSarabunNew.ttf');
        }
    }
    
    /**
     * สร้างตราประทับดิจิตอล
     */
    public function createDigitalStamp($stampData)
    {
        // สร้างรูปภาพตราประทับ
        $image = imagecreate($this->stampWidth, $this->stampHeight);
        
        // กำหนดสี
        $colors = $this->defineColors($image);
        
        // วาดกรอบตราประทับ
        $this->drawStampBorder($image, $colors);
        
        // เพิ่มโลโก้ราชการ
        $this->addGovernmentLogo($image);
        
        // เพิ่มข้อมูลเอกสาร
        $this->addDocumentInfo($image, $stampData, $colors);
        
        // เพิ่ม QR Code
        $this->addQRCode($image, $stampData);
        
        return $image;
    }
    
    /**
     * กำหนดสีที่ใช้
     */
    private function defineColors($image)
    {
        return [
            'white' => imagecolorallocate($image, 255, 255, 255),
            'black' => imagecolorallocate($image, 0, 0, 0),
            'blue' => imagecolorallocate($image, 0, 51, 153),
            'gray' => imagecolorallocate($image, 128, 128, 128),
            'red' => imagecolorallocate($image, 204, 0, 0)
        ];
    }
    
    /**
     * วาดกรอบตราประทับ
     */
    private function drawStampBorder($image, $colors)
    {
        // วาดกรอบนอก
        imagerectangle($image, 0, 0, $this->stampWidth-1, $this->stampHeight-1, $colors['blue']);
        imagerectangle($image, 2, 2, $this->stampWidth-3, $this->stampHeight-3, $colors['blue']);
        
        // พื้นหลังสีขาว
        imagefill($image, 1, 1, $colors['white']);
    }
    
    /**
     * เพิ่มโลโก้ราชการ
     */
    private function addGovernmentLogo($image)
    {
        if (file_exists($this->logoPath)) {
            $logo = imagecreatefrompng($this->logoPath);
            if ($logo) {
                // ปรับขนาดโลโก้
                $logoWidth = 60;
                $logoHeight = 60;
                
                $resizedLogo = imagecreatetruecolor($logoWidth, $logoHeight);
                imagealphablending($resizedLogo, false);
                imagesavealpha($resizedLogo, true);
                
                imagecopyresampled(
                    $resizedLogo, $logo,
                    0, 0, 0, 0,
                    $logoWidth, $logoHeight,
                    imagesx($logo), imagesy($logo)
                );
                
                // วางโลโก้ตรงกลางด้านบน
                imagecopy($image, $resizedLogo, 15, 15, 0, 0, $logoWidth, $logoHeight);
                
                imagedestroy($logo);
                imagedestroy($resizedLogo);
            }
        }
    }
    
    /**
     * เพิ่มข้อมูลเอกสาร
     */
    private function addDocumentInfo($image, $stampData, $colors)
    {
        $fontSize = 3;
        $lineHeight = 15;
        $startX = 85;
        $startY = 25;
        
        // หัวเรื่อง "โรงพยาบาลชุมชนลำปลายมาศ"
        if (isset($stampData['organization'])) {
            imagestring($image, $fontSize, $startX, $startY, 
                       $this->convertToTIS620($stampData['organization']), $colors['blue']);
            $startY += $lineHeight;
        }
        
        // เลขที่หนังสือ
        if (isset($stampData['document_number'])) {
            imagestring($image, $fontSize, $startX, $startY, 
                       'เลขที่: ' . $stampData['document_number'], $colors['black']);
            $startY += $lineHeight;
        }
        
        // วันที่และเวลา
        if (isset($stampData['date']) && isset($stampData['time'])) {
            imagestring($image, $fontSize, $startX, $startY, 
                       'ลงวันที่: ' . $stampData['date'], $colors['black']);
            $startY += $lineHeight;
            
            imagestring($image, $fontSize, $startX, $startY, 
                       'เวลา: ' . $stampData['time'] . ' น.', $colors['black']);
        }
    }
    
    /**
     * เพิ่ม QR Code
     */
    private function addQRCode($image, $stampData)
    {
        if (isset($stampData['qr_data'])) {
            try {
                $qrCode = new QRCode();
                $qrData = $qrCode->render($stampData['qr_data']);
                
                // บันทึก QR Code เป็นไฟล์ชั่วคราว
                $tempQR = tempnam(sys_get_temp_dir(), 'qr');
                file_put_contents($tempQR, $qrData);
                
                $qrImage = imagecreatefrompng($tempQR);
                if ($qrImage) {
                    // ปรับขนาด QR Code
                    $qrSize = 50;
                    $resizedQR = imagecreatetruecolor($qrSize, $qrSize);
                    
                    imagecopyresampled(
                        $resizedQR, $qrImage,
                        0, 0, 0, 0,
                        $qrSize, $qrSize,
                        imagesx($qrImage), imagesy($qrImage)
                    );
                    
                    // วาง QR Code มุมขวาล่าง
                    imagecopy($image, $resizedQR, 
                             $this->stampWidth - $qrSize - 10, 
                             $this->stampHeight - $qrSize - 10, 
                             0, 0, $qrSize, $qrSize);
                    
                    imagedestroy($qrImage);
                    imagedestroy($resizedQR);
                }
                
                unlink($tempQR);
            } catch (\Exception $e) {
                Yii::error('QR Code generation failed: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * เพิ่มตราประทับลงใน PDF
     */
    public function addStampToPdf($pdfPath, $stampData, $position = null)
    {
        if (!$position) {
            $position = $this->defaultPosition;
        }
        
        try {
            $pdf = new TCPDF();
            $pdf->SetCreator('Digital Stamp System');
            $pdf->SetTitle('Stamped Document');
            
            // ตั้งค่า Font สำหรับภาษาไทย
            $pdf->SetFont('freeserif', '', 14);
            
            // โหลด PDF เดิม
            $pageCount = $pdf->setSourceFile($pdfPath);
            
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $pdf->AddPage();
                $tplId = $pdf->importPage($pageNo);
                $pdf->useTemplate($tplId);
                
                // สร้างและเพิ่มตราประทับ
                $this->addStampToPage($pdf, $stampData, $position);
            }
            
            return $pdf->Output('', 'S');
            
        } catch (\Exception $e) {
            throw new \Exception('PDF stamping failed: ' . $e->getMessage());
        }
    }
    
    /**
     * เพิ่มตราประทับลงในหน้า PDF
     */
    private function addStampToPage($pdf, $stampData, $position)
    {
        // สร้างตราประทับ
        $stampImage = $this->createDigitalStamp($stampData);
        
        // บันทึกเป็นไฟล์ชั่วคราว
        $tempFile = tempnam(sys_get_temp_dir(), 'stamp');
        imagepng($stampImage, $tempFile);
        
        // เพิ่มลงใน PDF
        $pdf->Image($tempFile, $position['x'], $position['y'], 75, 50, 'PNG');
        
        // ทำความสะอาด
        unlink($tempFile);
        imagedestroy($stampImage);
    }
    
    /**
     * แปลงข้อความเป็น TIS-620 สำหรับภาษาไทย
     */
    private function convertToTIS620($text)
    {
        return iconv('UTF-8', 'TIS-620//IGNORE', $text);
    }
    
    /**
     * สร้างข้อมูลสำหรับตรวจสอบ
     */
    public function generateVerificationData($documentId, $timestamp = null)
    {
        if (!$timestamp) {
            $timestamp = time();
        }
        
        return [
            'document_id' => $documentId,
            'timestamp' => $timestamp,
            'hash' => hash('sha256', $documentId . $timestamp . Yii::$app->params['secretKey']),
            'verify_url' => \yii\helpers\Url::to(['document/verify', 'id' => $documentId], true)
        ];
    }
}