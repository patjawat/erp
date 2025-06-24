<?php

namespace app\modules\gdoc\controllers;

use Yii;
use yii\web\Controller;
use app\modules\gdoc\components\GDocService;

class DefaultController extends Controller
{

    //รองรับการส่ง Folder ID
     public function actionIndex()
    {
        $gdoc = new GDocService();

        $docId = $gdoc->createDocumentFromTemplate(
            '1_vONiEPmDHDSGvfLGc5RJuDoZUqe0FQERO8L1Y4ayx4', // Template ID
            [
                'name' => 'Patjwat Sriboonruang',
                'date' => date('Y-m-d'),
            ],
            '1w0gLatDtAW1B7vMqTllSJpKtSqMIcq4Y' // (ถ้าต้องการเก็บไว้ในโฟลเดอร์ที่ระบุ)
        );

        // แชร์เอกสารให้สาธารณะ
        $gdoc->shareFileToAnyone($docId);

        return $this->renderContent("✅ สร้างเอกสารใหม่สำเร็จ: <a target='_blank' href='https://docs.google.com/document/d/{$docId}/edit'>เปิดเอกสาร</a>");
    }


    //Export เป็น PDF
    public function actionExportPdf()
    {
        $gdoc = new GDocService();

        $templateId = '1_vONiEPmDHDSGvfLGc5RJuDoZUqe0FQERO8L1Y4ayx4';
        $folderId = '1w0gLatDtAW1B7vMqTllSJpKtSqMIcq4Y'; // หากต้องการเก็บ PDF ไปไว้ในโฟลเดอร์ Google Drive

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
//         $gdoc = new GDocService();

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
//         $gdoc = new GDocService();

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
