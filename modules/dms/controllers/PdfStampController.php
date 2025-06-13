<?php

namespace app\modules\dms\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\PdfStampForm;

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
        $model = new PdfStampForm();

        if ($model->load(Yii::$app->request->post())) {
            $model->pdfFile = UploadedFile::getInstance($model, 'pdfFile');
            
            if ($model->validate()) {
                // อัพโลดไฟล์ PDF
                $uploadPath = Yii::getAlias('@webroot/uploads/pdf/');
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                $fileName = uniqid() . '.' . $model->pdfFile->extension;
                $filePath = $uploadPath . $fileName;
                
                if ($model->pdfFile->saveAs($filePath)) {
                    // เก็บข้อมูลไฟล์ใน session
                    Yii::$app->session->set('pdf_file', $fileName);
                    Yii::$app->session->set('pdf_path', $filePath);
                    
                    return $this->redirect(['editor']);
                }
            }
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    /**
     * หน้า editor สำหรับจัดการ stamp
     */
    public function actionEditor()
    {
        $pdfFile = Yii::$app->session->get('pdf_file');
        if (!$pdfFile) {
            return $this->redirect(['index']);
        }

        return $this->render('editor', [
            'pdfFile' => $pdfFile,
        ]);
    }

    /**
     * ดาวน์โหลด PDF ที่ประทับตราแล้ว
     */
    public function actionDownload()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $request = Yii::$app->request;
        $pdfFile = Yii::$app->session->get('pdf_file');
        $pdfPath = Yii::$app->session->get('pdf_path');
        
        if (!$pdfFile || !file_exists($pdfPath)) {
            return ['success' => false, 'message' => 'ไม่พบไฟล์ PDF'];
        }

        $stamps = $request->post('stamps', []);
        
        if (empty($stamps)) {
            return ['success' => false, 'message' => 'ไม่พบข้อมูล stamp'];
        }

        try {
            // สร้าง PDF ใหม่พร้อม stamp
            $outputPath = $this->createStampedPdf($pdfPath, $stamps);
            
            if ($outputPath) {
                $downloadUrl = Yii::getAlias('@web/uploads/pdf/') . basename($outputPath);
                return ['success' => true, 'downloadUrl' => $downloadUrl];
            }
            
            return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการสร้าง PDF'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * สร้าง PDF พร้อม stamp
     */
    private function createStampedPdf($originalPath, $stamps)
    {
        require_once Yii::getAlias('@vendor/tecnickcom/tcpdf/tcpdf.php');
        
        // อ่านไฟล์ PDF เดิม
        $pageCount = $this->getPdfPageCount($originalPath);
        
        // สร้าง TCPDF instance
        $pdf = new \TCPDF();
        $pdf->SetCreator('PDF Stamp System');
        $pdf->SetAuthor('Yii2 Application');
        $pdf->SetTitle('Stamped PDF');
        
        // นำเข้าหน้าจากไฟล์เดิม
        for ($i = 1; $i <= $pageCount; $i++) {
            $pdf->AddPage();
            
            // นำเข้าหน้าจากไฟล์เดิม (ใช้ TCPDI หรือ FPDI)
            // หากไม่มี TCPDI ให้ใช้วิธีอื่น
            
            // เพิ่ม stamps สำหรับหน้านี้
            foreach ($stamps as $stamp) {
                if (isset($stamp['page']) && $stamp['page'] == $i) {
                    $this->addStampToPdf($pdf, $stamp);
                }
            }
        }
        
        // บันทึกไฟล์
        $outputFileName = 'stamped_' . uniqid() . '.pdf';
        $outputPath = Yii::getAlias('@webroot/uploads/pdf/') . $outputFileName;
        
        $pdf->Output($outputPath, 'F');
        
        return $outputPath;
    }

    /**
     * เพิ่ม stamp ลงใน PDF
     */
    private function addStampToPdf($pdf, $stamp)
    {
        // ตั้งค่าสี
        $color = $this->hexToRgb($stamp['color'] ?? '#FF0000');
        $pdf->SetTextColor($color['r'], $color['g'], $color['b']);
        
        // ตั้งค่าฟอนต์
        $pdf->SetFont('dejavusans', 'B', $stamp['fontSize'] ?? 12);
        
        // เพิ่มข้อความ
        $pdf->SetXY($stamp['x'] ?? 10, $stamp['y'] ?? 10);
        
        // หากเป็น stamp แบบหมุน
        if (isset($stamp['rotation']) && $stamp['rotation'] != 0) {
            $pdf->StartTransform();
            $pdf->Rotate($stamp['rotation'], $stamp['x'], $stamp['y']);
            $pdf->Text($stamp['x'], $stamp['y'], $stamp['text']);
            $pdf->StopTransform();
        } else {
            $pdf->Text($stamp['x'], $stamp['y'], $stamp['text']);
        }
        
        // หากมีกรอบ
        if (isset($stamp['border']) && $stamp['border']) {
            $pdf->SetDrawColor($color['r'], $color['g'], $color['b']);
            $pdf->SetLineWidth(1);
            
            $textWidth = $pdf->GetStringWidth($stamp['text']);
            $textHeight = $stamp['fontSize'] ?? 12;
            
            $pdf->Rect(
                $stamp['x'] - 2, 
                $stamp['y'] - $textHeight + 2, 
                $textWidth + 4, 
                $textHeight + 2, 
                'D'
            );
        }
    }

    /**
     * แปลงสี hex เป็น RGB
     */
    private function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }

    /**
     * นับจำนวนหน้าใน PDF
     */
    private function getPdfPageCount($filePath)
    {
        // วิธีง่ายๆ ในการนับหน้า PDF
        $content = file_get_contents($filePath);
        $pattern = '/\/Count\s+(\d+)/';
        
        if (preg_match($pattern, $content, $matches)) {
            return (int)$matches[1];
        }
        
        // fallback method
        $pattern = '/\/Page\W/';
        return preg_match_all($pattern, $content);
    }

    /**
     * ลบไฟล์ PDF ชั่วคราว
     */
    public function actionCleanup()
    {
        Yii::$app->session->remove('pdf_file');
        Yii::$app->session->remove('pdf_path');
        
        return $this->redirect(['index']);
    }
}