<?php

namespace app\modules\gdoc\controllers;

use Yii;
use yii\web\Controller;
use app\modules\gdoc\components\GoogleService;

class DefaultController extends Controller
{
public function actionIndex()
{
    // ป้องกัน headers already sent โดยใช้ output buffering
    ob_start();
    
    $gdoc = new GoogleService();
    $output = [];
    
    try {
        // 1. สร้างเอกสารจาก Template
        $output[] = "🚀 เริ่มสร้างเอกสาร...";
        
        $docId = $gdoc->createDocumentFromTemplate(
            '1XXZRJ9-CI904UGMMBv1J58lHl9ilgl8R7stPF3IAdRI',
            [
                'name' => 'Patjwat Sriboonruang',
                'date' => date('Y-m-d'),
            ]
        );

        $output[] = "✅ สร้างเอกสารสำเร็จ ID: $docId";

        // 2. ตรวจสอบไฟล์รูปภาพ
        $logoPath = Yii::getAlias('@webroot/images/welcome.png');
        $signPath = Yii::getAlias('@webroot/images/hr.png');

        if (!file_exists($logoPath)) {
            throw new \Exception("ไฟล์โลโก้ไม่พบที่: $logoPath");
        }
        if (!file_exists($signPath)) {
            throw new \Exception("ไฟล์ลายเซ็นไม่พบที่: $signPath");
        }

        // 3. อัปโหลดรูปภาพ
        $output[] = "📁 อัปโหลดรูปภาพ...";
        
        $logoFileId = $gdoc->uploadImageToDrive($logoPath, 'logo_' . time() . '.png');
        $signFileId = $gdoc->uploadImageToDrive($signPath, 'signature_' . time() . '.png');

        $output[] = "✅ อัปโหลดโลโก้สำเร็จ ID: $logoFileId";
        $output[] = "✅ อัปโหลดลายเซ็นสำเร็จ ID: $signFileId";

        // 4. รอให้ไฟล์พร้อมใช้งาน
        $output[] = "⏰ รอให้ไฟล์พร้อมใช้งาน...";
        
        $logoUrl = $gdoc->waitForFileReady($logoFileId, 20);
        $signUrl = $gdoc->waitForFileReady($signFileId, 20);
        
        $output[] = "✅ Logo URL: $logoUrl";
        $output[] = "✅ Signature URL: $signUrl";

        // 5. ค้นหา placeholders
        $placeholders = $gdoc->findPlaceholders($docId);
        $output[] = "🔍 Placeholders ที่พบในเอกสาร: " . implode(', ', $placeholders);

        // 6. แทนที่รูปภาพ
        $output[] = "🖼️ เริ่มแทนที่รูปภาพ...";
        
        $imageReplacements = [
            'logo1' => $logoUrl,
            'signature' => $signUrl,
            'logo' => $logoUrl, // เผื่อมี placeholder แบบนี้
            'sign' => $signUrl   // เผื่อมี placeholder แบบนี้
        ];

        foreach ($imageReplacements as $placeholder => $imageUrl) {
            $fullPlaceholder = '{{' . $placeholder . '}}';
            if (in_array($fullPlaceholder, $placeholders)) {
                $output[] = "🔄 กำลังแทนที่ $fullPlaceholder...";
                
                $result = $gdoc->replaceImagePlaceholder($docId, $placeholder, $imageUrl);
                if ($result) {
                    $output[] = "✅ แทนที่ $placeholder สำเร็จ";
                } else {
                    $output[] = "⚠️ แทนที่ $placeholder ไม่สำเร็จ - ลองวิธีอื่น";
                    
                    // ลองแทรกแบบอื่นถ้าแทนที่ไม่ได้
                    try {
                        // ใช้ public method แทน
                        $position = $gdoc->findPlaceholderPosition($docId, $placeholder);
                        
                        if ($position && $position['found']) {
                            $insertIndex = $position['startIndex'];
                            $gdoc->insertImageAtPosition($docId, $imageUrl, $insertIndex, 100, 100);
                            $output[] = "🔄 แทรกรูปที่ตำแหน่ง $insertIndex แทน";
                        } else {
                            // ถ้าหาตำแหน่งไม่ได้ ให้แทรกที่ท้ายเอกสาร
                            $gdoc->insertImageAtPosition($docId, $imageUrl, 1, 100, 100);
                            $output[] = "🔄 แทรกรูปที่จุดเริ่มต้นของเอกสาร";
                        }
                    } catch (\Exception $e) {
                        $output[] = "❌ ไม่สามารถแทรกรูป $placeholder ได้: " . $e->getMessage();
                    }
                }
                
                // หน่วงเวลาระหว่างการแทนที่
                sleep(2);
            }
        }

        // 7. แชร์เอกสาร
        $output[] = "🔗 กำลังแชร์เอกสาร...";
        $shareResult = $gdoc->shareFileToAnyone($docId);
        if ($shareResult) {
            $output[] = "✅ แชร์เอกสารสำเร็จ";
        } else {
            $output[] = "⚠️ แชร์เอกสารไม่สำเร็จ";
        }

        // 8. สร้างลิงก์ผลลัพธ์
        $output[] = "";
        $output[] = "🎉 <strong>เสร็จสิ้น!</strong>";
        $output[] = "<a target='_blank' href='https://docs.google.com/document/d/{$docId}/edit'>📖 เปิดเอกสาร</a>";
        $output[] = "<a target='_blank' href='$logoUrl'>🖼️ ดูรูป Logo</a>";
        $output[] = "<a target='_blank' href='$signUrl'>🖼️ ดูรูป Signature</a>";

    } catch (\Exception $e) {
        $output[] = "❌ เกิดข้อผิดพลาด: " . $e->getMessage();
        Yii::error("Error in actionIndex: " . $e->getMessage());
        Yii::error("Stack trace: " . $e->getTraceAsString());
    }

    // ส่งออก output ทั้งหมดพร้อมกัน
    ob_end_clean(); // ล้าง output buffer
    
    return $this->asJson([
        'success' => !isset($e),
        'output' => $output,
        'docId' => $docId ?? null
    ]);
    
    // หรือถ้าต้องการแสดงเป็น HTML
    /*
    foreach ($output as $line) {
        echo $line . "<br>\n";
    }
    */
}

    //Export เป็น PDF
    public function actionExportPdf()
    {
        $gdoc = new GoogleService();

        $templateId = '1XXZRJ9-CI904UGMMBv1J58lHl9ilgl8R7stPF3IAdRI/';
        $folderId = '1JuR9Fu_Aqy-ZPEo9MlEjsvwtQz4ds82V'; // หากต้องการเก็บ PDF ไปไว้ในโฟลเดอร์ Google Drive

        $datasets = [
            ['name' => 'ปัจวัฒน์ ศรีบุญเรือง', 'date' => '2025-06-24'],
            ['name' => 'สมหญิง แสงทอง', 'date' => '2025-06-25'],
            ['name' => 'จารุวรรณ ใจดี', 'date' => '2025-06-26'],
        ];

        foreach ($datasets as $index => $data) {
            $docId = $gdoc->createDocumentFromTemplate($templateId, $data, $folderId);
            $pdfContent = $gdoc->exportToPDF($docId);

            $pdfName = "pdf-" . ($index + 1) . ".pdf";

            // 👉 บันทึกในเครื่อง
            file_put_contents(Yii::getAlias("@app/runtime/pdfs/{$pdfName}"), $pdfContent);

            // 👉 หรืออัปโหลดกลับไป Drive (ถ้าต้องการ)
            // $gdoc->uploadPDFToDrive($pdfContent, $pdfName, $folderId);
        }

        return $this->renderContent("✅ แปลงและบันทึก PDF ทั้งหมดสำเร็จไว้ที่ <code>@app/runtime/pdfs/</code>");
    }

    //     public function actionIndex()
    //     {
    //         $gdoc = new GoogleService();

    //         $docId = $gdoc->createDocumentFromTemplate(
    //             '1_vONiEPmDHDSGvfLGc5RJuDoZUqe0FQERO8L1Y4ayx4',
    //             [
    //                 'name' => 'Patjwat Sriboonruang',
    //                 'date' => date('Y-m-d'),
    //             ]
    //         );



    //         return $this->renderContent("✅ สร้างเอกสารใหม่สำเร็จ: <a target='_blank' href='https://docs.google.com/document/d/{$docId}/edit'>เปิดเอกสาร</a>");
    //     }

    //    public function actionExportPdf()
    //     {
    //         $gdoc = new GoogleService();

    //         $templateId = '1_vONiEPmDHDSGvfLGc5RJuDoZUqe0FQERO8L1Y4ayx4'; // ตัวอย่าง: 1_vONiEPmDHDSG...

    //         $datasets = [
    //             ['name' => 'ปัจวัฒน์ ศรีบุญเรือง', 'date' => '2025-06-24'],
    //             ['name' => 'สมหญิง แสงทอง', 'date' => '2025-06-25'],
    //             ['name' => 'จารุวรรณ ใจดี', 'date' => '2025-06-26'],
    //         ];

    //         foreach ($datasets as $index => $data) {
    //             $docId = $gdoc->createDocumentFromTemplate($templateId, $data);
    //             $pdfContent = $gdoc->exportToPDF($docId);

    //             $pdfName = "pdf-" . ($index + 1) . ".pdf";
    //             file_put_contents(Yii::getAlias("@app/runtime/pdfs/{$pdfName}"), $pdfContent);
    //         }

    //         return $this->renderContent("✅ แปลงและบันทึก PDF ทั้งหมดสำเร็จไว้ที่ <code>@app/runtime/pdfs/</code>");
    //     }
}
