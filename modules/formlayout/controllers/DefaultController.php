<?php

namespace app\modules\formlayout\controllers;

use Yii;
use yii\web\Controller;
use setasign\Fpdi\Fpdi;
use setasign\Fpdf\Fpdf;
/**
 * Default controller for the `formdesign` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
public function actionLeaveForm()
{
    return $this->render('leave-form');
}

public function actionLeavePdf()
{
    $post = Yii::$app->request->post();

    // แปลงลายเซ็นเป็นภาพ
    $signatureData = str_replace('data:image/png;base64,', '', $post['signature']);
    $signatureData = base64_decode($signatureData);
    $signaturePath = Yii::getAlias('@webroot/uploads/signature.png');
    file_put_contents($signaturePath, $signatureData);

    // TCPDF
    require_once(Yii::getAlias('@vendor/tecnickcom/tcpdf/tcpdf.php'));
    $pdf = new \TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('thsarabun', '', 16); // ต้องติดตั้งฟอนต์ภาษาไทยไว้ก่อน

    $pdf->Cell(0, 10, 'แบบฟอร์มการลาพักผ่อน', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->Cell(0, 10, 'ชื่อ: ' . $post['fullname'], 0, 1);
    $pdf->Cell(0, 10, 'ตำแหน่ง: ' . $post['position'], 0, 1);
    $pdf->Cell(0, 10, 'วันที่เริ่มลา: ' . $post['start_date'], 0, 1);
    $pdf->Cell(0, 10, 'วันที่สิ้นสุด: ' . $post['end_date'], 0, 1);
    $pdf->Cell(0, 10, 'จำนวนวันลา: ' . $post['leave_days'], 0, 1);
    $pdf->Cell(0, 10, 'เหตุผล: ' . $post['reason'], 0, 1);

    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'ลงชื่อผู้ลา', 0, 1);
    $pdf->Image($signaturePath, 30, $pdf->GetY(), 60, 25, 'PNG');

    $pdf->Ln(30);
    $pdf->Cell(0, 10, 'วันที่: ' . date('d/m/Y'), 0, 1);

    Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
    Yii::$app->response->headers->add('Content-Type', 'application/pdf');
    return $pdf->Output('leave-request.pdf', 'I');
}

}
