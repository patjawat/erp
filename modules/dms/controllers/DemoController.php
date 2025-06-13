<?php
namespace app\modules\dms\controllers;

use Yii;
use yii\web\Response;
use setasign\Fpdi\Fpdi;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use setasign\Fpdi\PdfReader;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;

class DemoController extends Controller
{
  public $enableCsrfValidation = false; // สำหรับทดสอบ

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionUpload()
    {
        $file = UploadedFile::getInstanceByName('file');
        if ($file && $file->extension === 'pdf') {
            $fileName = uniqid() . '.' . $file->extension;
            $path = Yii::getAlias('@webroot/uploads/' . $fileName);
            $file->saveAs($path);
            return $this->redirect(['view', 'file' => $fileName]);
        }

        return $this->render('index', ['error' => 'Upload failed']);
    }

    public function actionView($file)
    {
        return $this->render('view', ['file' => $file]);
    }

    public function actionStamp()
    {
        //  Yii::$app->response->format = Response::FORMAT_JSON;
         $request = Yii::$app->request->post();
        // $file = $request['file'];
        $file = '684bbf40bc446.pdf';
        $text = $request['text'];
        $color = $request['color'];
        $x = $request['x'];
        $y = $request['y'];

        $pdfPath = Yii::getAlias('@webroot/uploads/' . $file);
        $pdf = new Fpdi();
        $pdf->AddPage();
        $pdf->setSourceFile($pdfPath);
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx);

        list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
        $pdf->SetFont('Helvetica', '', 16);
        $pdf->SetTextColor($r, $g, $b);
        $pdf->Text($x * 0.264583, $y * 0.264583, $text);

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'application/pdf');
        return $pdf->Output('S'); // Return as string
    }

}